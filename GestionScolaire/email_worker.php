<?php
// email_worker.php
// CLI worker: processes pending email_queue rows. Run via cron (e.g. * * * * * php /path/to/email_worker.php)
// Requires: config.php and vendor/autoload.php

if (php_sapi_name() !== 'cli') {
    // Prevent web access
    http_response_code(403);
    echo "Forbidden";
    exit();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;

$db = new ConnexionBase();
$max_per_run = 10;

// Fetch pending rows
$rows = $db->pdo->query("SELECT id, to_email, subject, body, attachments, attempts FROM email_queue WHERE status = 'pending' ORDER BY created_at ASC LIMIT {$max_per_run}")->fetchAll(PDO::FETCH_ASSOC);
if (empty($rows)) {
    // Nothing to do
    exit(0);
}

// Lock selected rows for processing by updating status -> processing
$ids = array_column($rows, 'id');
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$update = $db->pdo->prepare("UPDATE email_queue SET status = 'processing', updated_at = NOW() WHERE id IN ($placeholders)");
$update->execute($ids);

// Re-fetch them to ensure we have current data
$rows = $db->pdo->query("SELECT id, to_email, subject, body, attachments, attempts FROM email_queue WHERE id IN ($placeholders)")->fetchAll(PDO::FETCH_ASSOC);

// Helper: generate PDF bulletin for a student number
function generateStudentBulletinPdf($db, $numero)
{
    $stmt = $db->pdo->prepare(
        "SELECT numero_etudiant, nom_etudiant, prenom_etudiant FROM etudiants WHERE numero_etudiant = ?"
    );
    $stmt->execute([$numero]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) return null;

    $notesStmt = $db->pdo->prepare(
        "SELECT m.DésignationModule, COALESCE(m.Coefficient,0) AS Coefficient, n.Note, (n.Note * COALESCE(m.Coefficient,0)) AS NotePonderee
         FROM Notes n JOIN modules m ON n.Code_Module = m.CodeModule
         WHERE n.Num_Etudiant = ? ORDER BY m.DésignationModule"
    );
    $notesStmt->execute([$numero]);
    $notes = $notesStmt->fetchAll(PDO::FETCH_ASSOC);

    ob_start();
    ?>
    <html><head><meta charset="utf-8"><style>body{font-family:Arial}</style></head><body>
    <h2>Bulletin - <?= htmlspecialchars($student['nom_etudiant'].' '.$student['prenom_etudiant']) ?></h2>
    <p>Numéro: <?= htmlspecialchars($student['numero_etudiant']) ?></p>
    <table border="1" cellpadding="6" cellspacing="0" width="100%">
      <tr><th>Module</th><th>Coeff</th><th>Note</th><th>Note pondérée</th></tr>
      <?php foreach($notes as $n): ?>
        <tr>
          <td><?= htmlspecialchars($n['DésignationModule']) ?></td>
          <td style="text-align:center"><?= htmlspecialchars($n['Coefficient']) ?></td>
          <td style="text-align:center"><?= $n['Note'] !== null ? number_format($n['Note'],2,',',' ') : 'N/A' ?></td>
          <td style="text-align:center"><?= $n['Note'] !== null ? number_format($n['NotePonderee'],2,',',' ') : 'N/A' ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
    </body></html>
    <?php
    $html = ob_get_clean();
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4','portrait');
    $dompdf->render();
    return $dompdf->output();
}

// Process each row
foreach ($rows as $row) {
    $id = (int)$row['id'];
    $to = $row['to_email'];
    $subject = $row['subject'];
    $body = $row['body'];
    $attempts = (int)$row['attempts'];
    $attachments = json_decode($row['attachments'], true) ?: [];

    try {
        // If attachments indicate generating a bulletin for a student, do so
        $pdfBinary = null;
        if (!empty($attachments) && isset($attachments['type']) && $attachments['type'] === 'bulletin_by_student' && !empty($attachments['numero_etudiant'])) {
            $pdfBinary = generateStudentBulletinPdf($db, $attachments['numero_etudiant']);
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = getenv('MAILER_HOST') ?: 'smtp.example.com';
        $mail->SMTPAuth = true;
        $mail->Username = getenv('MAILER_USER') ?: '';
        $mail->Password = getenv('MAILER_PASS') ?: '';
        $mail->SMTPSecure = getenv('MAILER_SECURE') ?: 'tls';
        $mail->Port = getenv('MAILER_PORT') ?: 587;

        $from = getenv('MAILER_FROM') ?: 'noreply@example.com';
        $fromName = getenv('MAILER_FROM_NAME') ?: 'Faculté';
        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->isHTML(false);
        $mail->Body = $body;

        if ($pdfBinary !== null) {
            $filename = 'bulletin_' . ($attachments['numero_etudiant'] ?? 'student') . '_' . time() . '.pdf';
            $mail->addStringAttachment($pdfBinary, $filename, 'base64', 'application/pdf');
        }

        $mail->send();

        // mark queue row done and insert history
        $db->pdo->prepare("UPDATE email_queue SET status = 'done', attempts = attempts + 1, updated_at = NOW() WHERE id = ?")->execute([$id]);
        $numero_for_history = $attachments['numero_etudiant'] ?? null;
        $stmt = $db->pdo->prepare("INSERT INTO bulletin_email_history (numero_etudiant, email, sent_by, status, message, created_at) VALUES (?, ?, NULL, 'sent', ?, NOW())");
        $stmt->execute([$numero_for_history, $to, 'Sent by worker']);

    } catch (Exception $e) {
        // increment attempts, set back to pending (or failed after threshold)
        $attempts++;
        $maxAttempts = 5;
        if ($attempts >= $maxAttempts) {
            $db->pdo->prepare("UPDATE email_queue SET status = 'failed', attempts = ?, updated_at = NOW() WHERE id = ?")->execute([$attempts, $id]);
        } else {
            $db->pdo->prepare("UPDATE email_queue SET status = 'pending', attempts = ?, updated_at = NOW() WHERE id = ?")->execute([$attempts, $id]);
        }
        $db->pdo->prepare("INSERT INTO bulletin_email_history (numero_etudiant, email, sent_by, status, message, created_at) VALUES (?, ?, NULL, 'failed', ?, NOW())")->execute([($attachments['numero_etudiant'] ?? null), $to, $e->getMessage()]);
    }
}

exit(0);