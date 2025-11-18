
<?php
session_start();
require_once 'connexion_base.php';
$db = new ConnexionBase();

// التحقق من أن المستخدم طالب
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'User') {
    header("Location: menu_principal.php");
    exit();
}

$message = '';
$bulletin = null;
$etudiant_info = null;

// البحث عن bulletin برقم الطالب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rechercher'])) {
    $numero_etudiant = trim($_POST['numero_etudiant']);
    
    if (empty($numero_etudiant)) {
        $message = "❌ Veuillez entrer votre numéro d'étudiant";
    } else {
        // البحث عن معلومات الطالب
        $requete_etudiant = $db->pdo->prepare("
            SELECT e.numero_etudiant, e.nom_etudiant, e.prenom_etudiant, 
                   f.CodeFilière, f.Désignation as nom_filiere
            FROM etudiants e
            LEFT JOIN filières f ON e.FilièreId = f.Id
            WHERE e.numero_etudiant = ?
        ");
        $requete_etudiant->execute([$numero_etudiant]);
        $etudiant_info = $requete_etudiant->fetch(PDO::FETCH_ASSOC);
        
        if ($etudiant_info) {
            // البحث عن نقاط الطالب
            $requete_notes = $db->pdo->prepare("
                SELECT m.CodeModule, m.DésignationModule, m.Coefficient, n.Note,
                       (n.Note * m.Coefficient) as NotePonderee
                FROM Notes n
                JOIN modules m ON n.Code_Module = m.CodeModule
                WHERE n.Num_Etudiant = ?
                ORDER BY m.DésignationModule
            ");
            $requete_notes->execute([$numero_etudiant]);
            $bulletin = $requete_notes->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($bulletin)) {
                $message = "ℹ️ Aucune note enregistrée pour ce numéro d'étudiant";
            }
        } else {
            $message = "❌ Aucun étudiant trouvé avec ce numéro";
        }
    }
}

// حساب المتوسط
$moyenne = 0;
$total_coefficients = 0;
$total_notes_ponderees = 0;

if ($bulletin) {
    foreach($bulletin as $note) {
        if ($note['Note'] !== null) {
            $total_coefficients += $note['Coefficient'];
            $total_notes_ponderees += $note['NotePonderee'];
        }
    }
    
    if ($total_coefficients > 0) {
        $moyenne = $total_notes_ponderees / $total_coefficients;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Bulletin</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .search-form { background: #e7f3ff; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input { padding: 10px; width: 300px; border: 1px solid #ddd; border-radius: 4px; }
        .btn-rechercher { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; font-weight: bold; }
        .error { background: #f8d7da; color: #721c24; }
        .success { background: #d4edda; color: #155724; }
        .info { background: #cce7ff; color: #004085; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #007bff; color: white; }
        .moyenne { background: #28a745; color: white; padding: 15px; border-radius: 4px; text-align: center; font-weight: bold; margin: 20px 0; font-size: 1.2em; }
        .etudiant-info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .note-valide { color: #28a745; font-weight: bold; }
        .note-echoue { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Mon Bulletin de Notes</h1>
        
        <?php if ($message): ?>
            <div class="message <?= strpos($message, '❌') !== false ? 'error' : (strpos($message, 'ℹ️') !== false ? 'info' : 'success') ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <div class="search-form">
            <h3>🔍 Rechercher mon bulletin</h3>
            <form method="POST" action="user_bulletin.php">
                <div class="form-group">
                    <label for="numero_etudiant">Numéro d'étudiant:</label>
                    <input type="text" id="numero_etudiant" name="numero_etudiant" required 
                           placeholder="Exemple: 1, 2, 3..." value="<?= isset($_POST['numero_etudiant']) ? htmlspecialchars($_POST['numero_etudiant']) : '' ?>">
                </div>
                <button type="submit" name="rechercher" class="btn-rechercher">🔍 Rechercher mon bulletin</button>
            </form>
        </div>

        <?php if ($etudiant_info): ?>
            <div class="etudiant-info">
                <h3>👨‍🎓 Informations de l'étudiant</h3>
                <p><strong>Nom complet:</strong> <?= htmlspecialchars($etudiant_info['prenom_etudiant']) ?> <?= htmlspecialchars($etudiant_info['nom_etudiant']) ?></p>
                <p><strong>Numéro étudiant:</strong> <?= htmlspecialchars($etudiant_info['numero_etudiant']) ?></p>
                <p><strong>Filière:</strong> <?= htmlspecialchars($etudiant_info['CodeFilière']) ?> - <?= htmlspecialchars($etudiant_info['nom_filiere']) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($bulletin && !empty($bulletin)): ?>
            <h3>📋 Détail des notes</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Coefficient</th>
                        <th>Note /20</th>
                        <th>Note Pondérée</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($bulletin as $note): ?>
                        <?php if ($note['Note'] !== null): ?>
                        <tr>
                            <td><?= htmlspecialchars($note['DésignationModule']) ?> (<?= $note['CodeModule'] ?>)</td>
                            <td><?= $note['Coefficient'] ?></td>
                            <td class="<?= $note['Note'] >= 10 ? 'note-valide' : 'note-echoue' ?>">
                                <?= number_format($note['Note'], 2) ?>
                            </td>
                            <td><?= number_format($note['NotePonderee'], 2) ?></td>
                            <td class="<?= $note['Note'] >= 10 ? 'note-valide' : 'note-echoue' ?>">
                                <?= $note['Note'] >= 10 ? '✅ Validé' : '❌ Échoué' ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if ($total_coefficients > 0): ?>
            <div class="moyenne">
                📈 Moyenne Générale: <?= number_format($moyenne, 2) ?> / 20
                <br>
                <small>Total coefficients: <?= $total_coefficients ?> | Total notes pondérées: <?= number_format($total_notes_ponderees, 2) ?></small>
            </div>
            <?php endif; ?>
        <?php elseif ($etudiant_info && empty($bulletin)): ?>
            <div class="message info">
                ℹ️ Aucune note enregistrée pour cet étudiant.
            </div>
        <?php endif; ?>

        <br>
        <a href="menu_principal.php" style="background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;">← Retour au menu</a>
    </div>
</body>
</html>
