<?php
require_once 'config.php';

// Strict: Only Users can access this page
if (!$is_user) {
    header("Location: menu_principal.php");
    exit();
}

$db = new ConnexionBase();
$user_email = $_SESSION['user_email'] ?? '';
$bulletin = [];
$etudiant_info = null;
$message = '';
$message_type = 'info';
$moyenne = 0;
$total_coefficients = 0;
$total_notes_ponderees = 0;
$numero_recherche = '';
$bulletin_trouve = false;

// -----------------------------
// Handle "save email" request
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_email'])) {
    $numero_for_email = trim($_POST['email_numero'] ?? '');
    $email_to_save = trim($_POST['student_email'] ?? '');

    if ($numero_for_email === '') {
        $message = "❌ Numéro d'étudiant manquant pour la mise à jour de l'email.";
        $message_type = 'error';
    } elseif ($email_to_save === '') {
        $message = "❌ Veuillez saisir une adresse e-mail.";
        $message_type = 'error';
    } elseif (!filter_var($email_to_save, FILTER_VALIDATE_EMAIL)) {
        $message = "❌ L'adresse e-mail n'est pas valide.";
        $message_type = 'error';
    } else {
        // Optional: Restrict who can update the email.
        // If you store the logged student's numero in the session (recommended), uncomment and adjust:
        // if ($_SESSION['user_role'] === 'User' && ($_SESSION['user_numero'] ?? '') !== $numero_for_email) {
        //     $message = "❌ Vous n'êtes pas autorisé à modifier cet email.";
        //     $message_type = 'error';
        // } else {
            try {
                $upd = $db->pdo->prepare("UPDATE etudiants SET email = ? WHERE numero_etudiant = ?");
                $upd->execute([$email_to_save, $numero_for_email]);

                // Re-fetch student info if currently displayed
                if (isset($_POST['current_view_numero']) && $_POST['current_view_numero'] === $numero_for_email) {
                    $req = $db->pdo->prepare("SELECT e.id_etudiant, e.numero_etudiant, e.nom_etudiant, e.prenom_etudiant, e.civilite, f.CodeFilière, f.Désignation as nom_filiere, e.date_naissance, e.adresse, e.localisation, e.email
                        FROM etudiants e
                        LEFT JOIN filières f ON e.FilièreId = f.Id
                        WHERE e.numero_etudiant = ?");
                    $req->execute([$numero_for_email]);
                    $etudiant_info = $req->fetch(PDO::FETCH_ASSOC);
                    $bulletin_trouve = true;
                    $numero_recherche = $numero_for_email;
                }

                $message = "✅ Votre adresse e-mail a été enregistrée avec succès.";
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = "❌ Erreur lors de l'enregistrement de l'email : " . $e->getMessage();
                $message_type = 'error';
            }
        // }
    }
}

// Check if user submitted search form for bulletin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['numero_etudiant']) && !isset($_POST['save_email'])) {
    $numero_recherche = trim($_POST['numero_etudiant']);

    if (empty($numero_recherche)) {
        $message = "❌ Veuillez entrer votre numéro d'étudiant";
        $message_type = 'error';
    } else {
        // Fetch student info by number
        $requete_etudiant = $db->pdo->prepare("
            SELECT e.id_etudiant, e.numero_etudiant, e.nom_etudiant, e.prenom_etudiant, 
                   e.civilite, f.CodeFilière, f.Désignation as nom_filiere, 
                   e.date_naissance, e.adresse, e.localisation, e.email
            FROM etudiants e
            LEFT JOIN filières f ON e.FilièreId = f.Id
            WHERE e.numero_etudiant = ?
        ");
        $requete_etudiant->execute([$numero_recherche]);
        $etudiant_info = $requete_etudiant->fetch(PDO::FETCH_ASSOC);

        if (!$etudiant_info) {
            $message = "❌ Aucun étudiant trouvé avec le numéro: " . htmlspecialchars($numero_recherche);
            $message_type = 'error';
        } else {
            $bulletin_trouve = true;
            $numero_etudiant = $etudiant_info['numero_etudiant'];

            // Fetch notes for this student
            $requete_notes = $db->pdo->prepare("
                SELECT m.CodeModule, m.DésignationModule, m.Coefficient, n.Note,
                       (n.Note * m.Coefficient) as NotePonderee
                FROM Notes n
                JOIN modules m ON n.Code_Module = m.CodeModule
                WHERE n.Num_Etudiant = ?
                ORDER BY m.DésignationModule
            ");
            $requete_notes->execute([$numero_recherche]);
            $bulletin = $requete_notes->fetchAll(PDO::FETCH_ASSOC);

            // Calculate weighted average
            if (!empty($bulletin)) {
                foreach($bulletin as $note) {
                    if ($note['Note'] !== null) {
                        $total_coefficients += $note['Coefficient'];
                        $total_notes_ponderees += $note['NotePonderee'];
                    }
                }

                if ($total_coefficients > 0) {
                    $moyenne = $total_notes_ponderees / $total_coefficients;
                }

                $message = "✅ Bulletin trouvé avec succès!";
                $message_type = 'success';
            } else {
                $message = "⚠️ Aucune note enregistrée pour ce numéro d'étudiant.";
                $message_type = 'warning';
                $bulletin_trouve = true; // Still found the student, just no grades
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Bulletin de Notes</title>
    <style>
        /* (kept the same styles as before) */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .navbar { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 15px 20px; margin-bottom: 30px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
        .nav-links a { color: #667eea; text-decoration: none; margin: 0 15px; font-weight: 600; transition: color 0.3s ease; }
        .logout-link { background: #ff6b6b; color: white !important; padding: 8px 16px; border-radius: 4px; margin: 0; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        h1 { color: #1e3c72; text-align: center; margin-bottom: 10px; font-size: 32px; }
        .subtitle { text-align: center; color: #666; margin-bottom: 30px; font-size: 14px; }
        .message { padding: 15px; margin: 20px 0; border-radius: 6px; font-weight: 600; text-align: center; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .message.warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .message.info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .student-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 8px; margin-bottom: 30px; }
        .student-header h2 { font-size: 24px; margin-bottom: 15px; }
        .student-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .info-item { background: rgba(255,255,255,0.1); padding: 12px; border-radius: 6px; border-left: 3px solid rgba(255,255,255,0.3); }
        .info-label { font-size: 12px; text-transform: uppercase; opacity: 0.9; margin-bottom: 4px; }
        .info-value { font-size: 16px; font-weight: 600; }
        .email-form { margin-top: 12px; display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
        .email-form input[type="email"] { padding:8px 10px; border-radius:6px; border:none; min-width:260px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .email-form button { padding:10px 14px; border-radius:8px; border:0; background:#fff; color:#4f46e5; font-weight:700; cursor:pointer; }
        .moyenne-box { text-align: center; background: <?= ($moyenne >= 10) ? '#d4edda' : '#f8d7da' ?>; color: <?= ($moyenne >= 10) ? '#155724' : '#721c24' ?>; border: 2px solid <?= ($moyenne >= 10) ? '#28a745' : '#dc3545' ?>; border-radius: 8px; padding: 20px; margin: 30px 0; font-size: 20px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #1e3c72; color: white; padding: 15px; text-align: left; font-weight: 600; border-bottom: 3px solid #667eea; }
        td { padding: 12px 15px; border-bottom: 1px solid #e0e0e0; }
        tr:hover { background: #f8f9fa; }
        .module-name { font-weight: 600; color: #1e3c72; }
        .note-value { font-weight: bold; text-align: center; }
        .note-good { color: #28a745; }
        .note-bad { color: #dc3545; }
        .coefficient { text-align: center; color: #666; }
        .print-btn { display: block; margin: 30px auto; padding: 12px 30px; background: #667eea; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.3s ease; }
        .print-btn:hover { background: #764ba2; }
        .no-data { text-align: center; padding: 40px; color: #999; font-size: 16px; }
        .search-section { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin: 30px 0; text-align: center; }
        .search-section h2 { margin: 0 0 20px 0; font-size: 24px; }
        .search-form { display: flex; gap: 10px; justify-content: center; align-items: center; flex-wrap: wrap; margin: 20px 0; }
        .search-form input { padding: 12px 16px; border: none; border-radius: 4px; font-size: 14px; min-width: 250px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .search-form button { background: white; color: #667eea; border: none; padding: 12px 30px; border-radius: 4px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .search-form button:hover { background: #f0f0f0; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .search-help { margin-top: 15px; font-size: 13px; opacity: 0.9; }
        .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #e0e0e0; color: #999; font-size: 12px; }
        @media print { body { background: white; } .navbar, .print-btn { display: none; } .container { box-shadow: none; padding: 20px; } }
    </style>
</head>
<body>
    <!-- Navigation -->
    <div class="navbar">
        <div class="nav-links">
            <a href="menu_principal.php">🏠 Accueil</a>
            <a href="student_bulletin.php">📋 Mon Bulletin</a>
        </div>
        <a href="logout.php" class="logout-link">🚪 Déconnexion</a>
    </div>

    <!-- Main Container -->
    <div class="container">
        <h1>📋 Mon Bulletin de Notes</h1>
        <p class="subtitle">Recherchez votre bulletin par numéro d'étudiant</p>

        <!-- Search Section -->
        <div class="search-section">
            <h2>🔍 Rechercher Votre Bulletin</h2>

            <form method="POST" class="search-form">
                <input 
                    type="text" 
                    name="numero_etudiant" 
                    placeholder="Entrez votre numéro d'étudiant (ex: 1, 2, 3...)" 
                    value="<?php echo htmlspecialchars($numero_recherche); ?>"
                    required
                >
                <button type="submit">🔎 Rechercher</button>
            </form>

            <div class="search-help">
                💡 Entrez votre numéro d'étudiant pour accéder à votre bulletin de notes personnalisé.
            </div>
        </div>

        <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <?php if ($bulletin_trouve && $etudiant_info): ?>
        <!-- Student Information -->
        <div class="student-header">
            <h2><?php echo htmlspecialchars($etudiant_info['civilite'] . ' ' . $etudiant_info['nom_etudiant'] . ' ' . $etudiant_info['prenom_etudiant']); ?></h2>

            <div class="student-info-grid">
                <div class="info-item">
                    <div class="info-label">Numéro Étudiant</div>
                    <div class="info-value"><?php echo htmlspecialchars($etudiant_info['numero_etudiant']); ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Filière</div>
                    <div class="info-value"><?php echo htmlspecialchars($etudiant_info['CodeFilière'] ?? 'N/A'); ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Programme</div>
                    <div class="info-value"><?php echo htmlspecialchars($etudiant_info['nom_filiere'] ?? 'N/A'); ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Localisation</div>
                    <div class="info-value"><?php echo htmlspecialchars($etudiant_info['localisation'] ?? 'N/A'); ?></div>
                </div>
            </div>

            <!-- Email capture for the student -->
            <div style="margin-top:16px;">
                <form method="POST" class="email-form" onsubmit="return confirm('Enregistrer cette adresse e-mail pour recevoir des notifications ?');">
                    <input type="hidden" name="email_numero" value="<?php echo htmlspecialchars($etudiant_info['numero_etudiant']); ?>">
                    <!-- Also pass current viewed numero to let server re-fetch after save -->
                    <input type="hidden" name="current_view_numero" value="<?php echo htmlspecialchars($etudiant_info['numero_etudiant']); ?>">
                    <label for="student_email" style="display:none">Email</label>
                    <input type="email" id="student_email" name="student_email" placeholder="Votre e-mail (ex: nom@exemple.com)" value="<?php echo htmlspecialchars($etudiant_info['email'] ?? ''); ?>" required>
                    <button type="submit" name="save_email" value="1">💾 Enregistrer l'email</button>
                </form>

                <div style="margin-top:8px; color:#fff; opacity:0.85; font-size:13px;">
                    Nous utiliserons cette adresse uniquement pour vous envoyer votre bulletin et notifications administratives.
                </div>
            </div>
        </div>
        <!-- Insert directly AFTER the email form (the form with name="save_email" / class="email-form") -->
<div style="margin-top:12px;">
  <button id="sendBulletinBtn" type="button">📧 Envoyer mon bulletin à cette adresse</button>
  <span id="sendStatus" style="margin-left:10px;color:#333"></span>
</div>

<script>
document.getElementById('sendBulletinBtn').addEventListener('click', function(){
  if (!confirm('Voulez-vous envoyer votre bulletin à l\'adresse enregistrée ?')) return;
  var formData = new FormData();
  // send the numero (if you keep it in a hidden field) or rely on session
  formData.append('numero_etudiant', '<?php echo htmlspecialchars($etudiant_info['numero_etudiant'] ?? ''); ?>');

  var btn = this;
  btn.disabled = true;
  document.getElementById('sendStatus').textContent = 'Envoi en cours...';
  fetch('send_bulletin.php', {
    method: 'POST',
    body: formData,
    credentials: 'same-origin'
  }).then(res => res.json())
    .then(json => {
      if (json.status === 'ok') {
        document.getElementById('sendStatus').textContent = '✅ Email envoyé';
      } else {
        document.getElementById('sendStatus').textContent = '❌ ' + (json.message || 'Erreur');
      }
      btn.disabled = false;
    }).catch(function(err){
      document.getElementById('sendStatus').textContent = '❌ Erreur réseau : ' + (err);
      btn.disabled = false;
    });
});
</script>

        <?php if (!empty($bulletin)): ?>
        <!-- Weighted Average -->
        <div class="moyenne-box">
            Moyenne Générale: <strong><?php echo number_format($moyenne, 2, ',', ' '); ?>/20</strong>
            <span style="font-size: 14px; display: block; margin-top: 8px;">
                <?php 
                if ($moyenne >= 10) {
                    echo "✅ Réussi";
                } else {
                    echo "❌ À améliorer";
                }
                ?>
            </span>
        </div>

        <!-- Grades Table -->
        <table>
            <thead>
                <tr>
                    <th>Module</th>
                    <th style="text-align: center;">Coeff.</th>
                    <th style="text-align: center;">Note</th>
                    <th style="text-align: center;">Note Pondérée</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($bulletin as $note): ?>
                <tr>
                    <td class="module-name"><?php echo htmlspecialchars($note['DésignationModule']); ?></td>
                    <td class="coefficient"><?php echo htmlspecialchars($note['Coefficient']); ?></td>
                    <td class="note-value <?php echo ($note['Note'] >= 10) ? 'note-good' : 'note-bad'; ?>">
                        <?php echo $note['Note'] !== null ? number_format($note['Note'], 2, ',', ' ') : 'N/A'; ?>
                    </td>
                    <td class="note-value">
                        <?php echo $note['Note'] !== null ? number_format($note['NotePonderee'], 2, ',', ' ') : 'N/A'; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Print Button -->
        <button class="print-btn" onclick="window.print();">
            🖨️ Imprimer Bulletin
        </button>

        <?php else: ?>
        <div class="no-data">
            <p>📭 Aucune note enregistrée pour ce numéro d'étudiant.</p>
            <p style="margin-top: 10px; font-size: 14px;">Veuillez contacter l'administration si vous pensez qu'il y a une erreur.</p>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="no-data" style="background: #cfe9ff; color: #084298; padding: 30px; border-radius: 6px; margin-top: 20px;">
            <p>ℹ️ Saisissez votre numéro d'étudiant ci-dessus pour consulter votre bulletin.</p>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            <p>Système de Gestion Scolaire © 2025 | Accès réservé aux utilisateurs connectés</p>
            <p>Bulletin généré le <?php echo date('d/m/Y à H:i'); ?></p>
        </div>
    </div>
</body>
</html>