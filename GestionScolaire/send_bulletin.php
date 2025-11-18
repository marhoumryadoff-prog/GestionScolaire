<?php
// student_send_bulletin.php
// Improved: returns JSON, logs failures in bulletin_email_history, provides clear error messages.
// Requires: config.php (session + ConnexionBase), vendor/autoload.php (PHPMailer + Dompdf)

header('Content-Type: application/json; charset=utf-8');
// helpful error display in dev; remove or disable in production
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo json_encode(['status' => 'error', 'message' => 'Missing composer autoload (vendor/autoload.php). Run composer install.']);
    exit;
}
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $db = new ConnexionBase();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Ensure user is logged-in student
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'User') {
    echo json_encode(['status' => 'error', 'message' => 'Forbidden: not logged in as a student.']);
    http_response_code(403);
    exit;
}

// Prefer session-stored student numero; otherwise accept POST but validate ownership
$session_numero = $_SESSION['user_numero'] ?? '';
$posted_numero = trim($_POST['numero_etudiant'] ?? '');

$numero = $session_numero !== '' ? $session_numero : $posted_numero;

if ($numero === '') {
    echo json_encode(['status' => 'error', 'message' => 'Missing student number.']);
    http_response_code(400);
    exit;
}

// Verify the student exists and belongs to the logged-in user (if session set)
$stmt = $db->pdo->prepare("SELECT numero_etudiant, nom_etudiant, prenom_etudiant, email FROM etudiants WHERE numero_etudiant = ?");
$stmt->execute([$numero]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo json_encode(['status' => 'error', 'message' => 'Student not found.']);
    http_response_code(404);
    exit;
}

// Optionally, if you store user->numero mapping, ensure the session owner matches
if ($session_numero !== '' && $session_numero !== $student['numero_etudiant']) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: session student mismatch.']);
    http_response_code(403);
    exit;
}

if (empty($student['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'No email on file. Please add your email first.']);
    http_response_code(400);
    exit;
}

// rate limit: last send timestamp
$limitMinutes = 2;
$limitStmt = $db->pdo->prepare("SELECT created_at FROM bulletin_email_history WHERE numero_etudiant = ? ORDER BY created_at DESC LIMIT 1");
$limitStmt->execute([$student['numero_etudiant']]);
$last = $limitStmt->fetch(PDO::FETCH_ASSOC);
if ($last) {
    $lastTime = strtotime($last['created_at']);
    if (time() - $lastTime < ($limitMinutes * 60)) {
        echo json_encode(['status' => 'error', 'message' => "Please wait {$limitMinutes} minutes between sends."]);
        http_response_code(429);
        exit;
    }
}

// fetch notes
$notesStmt = $db->pdo->prepare(
    "SELECT m.CodeModule, m.DésignationModule, COALESCE(m.Coefficient,0) AS Coefficient, n.Note, (n.Note * COALESCE(m.Coefficient,0)) AS NotePonderee
     FROM Notes n
     JOIN modules m ON n.Code_Module = m.CodeModule
     WHERE n.Num_Etudiant = ?
     ORDER BY m.DésignationModule"
);
$notesStmt->execute([$student['numero_etudiant']]);
$notes = $notesStmt->fetchAll(PDO::FETCH_ASSOC);

// render HTML
ob_start();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><style>
body{font-family:Arial,Helvetica,sans-serif;color:#222}
.header{background:#f3f4f6;padding:10px;border-radius:6px}
.table{width:100%;border-collapse:collapse;margin-top:12px}
.table th{background:#1e3c72;color:#fff;padding:8px;text-align:left}
.table td{padding:8px;border-bottom:1px solid #eee}
.footer{margin-top:12px;color:#666;font-size:12px}
</style></head>
<body>
  <div class="header">
    <h2>Bulletin de Notes - <?= htmlspecialchars($student['nom_etudiant'].' '.$student['prenom_etudiant']) ?></h2>
    <div>Numéro: <?= htmlspecialchars($student['numero_etudiant']) ?></div>
    <div>Date: <?= date('d/m/Y') ?></div>
  </div>
  <table class="table">
    <thead>
      <tr><th>Module</th><th>Coeff.</th><th>Note</th><th>Note pondérée</th></tr>
    </thead>
    <tbody>
      <?php foreach($notes as $n): ?>
      <tr>
        <td><?= htmlspecialchars($n['DésignationModule']) ?></td>
        <td style="text-align:center"><?= htmlspecialchars($n['Coefficient']) ?></td>
        <td style="text-align:center"><?= $n['Note'] !== null ? number_format($n['Note'],2,',',' ') : 'N/A' ?></td>
        <td style="text-align:center"><?= $n['Note'] !== null ? number_format($n['NotePonderee'],2,',',' ') : 'N/A' ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="footer"><p>Faculté des Sciences - Système de Gestion Scolaire</p></div>
</body>
</html>
<?php
$html = ob_get_clean();

// generate pdf
try {
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $pdf = $dompdf->output();
} catch (Exception $e) {
    $db->pdo->prepare("INSERT INTO bulletin_email_history (numero_etudiant, email, sent_by, status, message, created_at) VALUES (?, ?, ?, 'failed', ?, NOW())")
        ->execute([$student['numero_etudiant'], $student['email'], $_SESSION['user_id'] ?? null, 'PDF generation error: '.$e->getMessage()]);
    echo json_encode(['status'=>'error','message'=>'PDF generation failed: '.$e->getMessage()]);
    http_response_code(500);
    exit;
}

// send email
try {
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
    $mail->addAddress($student['email'], $student['nom_etudiant'].' '.$student['prenom_etudiant']);
    $mail->Subject = 'Votre bulletin de notes';
    $mail->isHTML(true);
    $mail->Body = '<p>Bonjour '.htmlspecialchars($student['prenom_etudiant']).',</p><p>Veuillez trouver en pièce jointe votre bulletin de notes.</p><p>Cordialement,<br>Administration</p>';
    $mail->addStringAttachment($pdf, 'bulletin_'.$student['numero_etudiant'].'.pdf', 'base64', 'application/pdf');

    // send
    $mail->send();

    $db->pdo->prepare("INSERT INTO bulletin_email_history (numero_etudiant, email, sent_by, status, message, created_at) VALUES (?, ?, ?, 'sent', ?, NOW())")
        ->execute([$student['numero_etudiant'], $student['email'], $_SESSION['user_id'] ?? null, 'Sent by student']);

    echo json_encode(['status'=>'ok','message'=>'Email sent']);
    exit;
} catch (Exception $e) {
    // Store full exception message for debugging (will appear in DB)
    $msg = 'Mailer error: ' . $e->getMessage();
    $db->pdo->prepare("INSERT INTO bulletin_email_history (numero_etudiant, email, sent_by, status, message, created_at) VALUES (?, ?, ?, 'failed', ?, NOW())")
        ->execute([$student['numero_etudiant'], $student['email'], $_SESSION['user_id'] ?? null, $msg]);

    echo json_encode(['status'=>'error','message'=>'Mailer Error: '.$e->getMessage()]);
    http_response_code(500);
    exit;
}