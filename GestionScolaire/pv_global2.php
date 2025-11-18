<?php
require_once 'connexion_base.php';
$db = new ConnexionBase();

// Get filieres for the select
$filieres = $db->pdo->query("SELECT Id, CodeFilière, Désignation FROM filières ORDER BY CodeFilière")->fetchAll(PDO::FETCH_ASSOC);

// Selected filiere id (by Id)
$filiere_id = isset($_GET['filiere_id']) && $_GET['filiere_id'] !== '' ? trim($_GET['filiere_id']) : '';

$etudiants = [];
$message = '';

if (!empty($filiere_id)) {
    // Fetch students for this filiere and compute weighted average per student
    try {
        $sql = "
            SELECT 
                e.numero_etudiant,
                e.nom_etudiant,
                e.prenom_etudiant,
                -- weighted average: SUM(note * coeff)/SUM(coeff)
                ROUND(
                    SUM(CASE WHEN n.Note REGEXP '^[0-9]+(\\.[0-9]+)?$' THEN (n.Note * COALESCE(m.Coefficient,0)) ELSE 0 END) /
                    NULLIF(SUM(CASE WHEN n.Note REGEXP '^[0-9]+(\\.[0-9]+)?$' THEN COALESCE(m.Coefficient,0) ELSE 0 END), 0)
                , 2) AS moyenne
            FROM etudiants e
            INNER JOIN filières f ON e.FilièreId = f.Id
            LEFT JOIN Notes n ON e.numero_etudiant = n.Num_Etudiant
            LEFT JOIN modules m ON n.Code_Module = m.CodeModule
            WHERE f.Id = ?
            GROUP BY e.numero_etudiant, e.nom_etudiant, e.prenom_etudiant
            ORDER BY moyenne DESC
        ";
        $stmt = $db->pdo->prepare($sql);
        $stmt->execute([$filiere_id]);
        $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = 'Erreur lors de la récupération des étudiants: ' . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>PV Global 2 - Par Filière</title>
    <link rel="stylesheet" href="assets/css/modern-style.css">
    <style>
        body { font-family: Inter, Arial, sans-serif; margin: 2rem; }
        .container { max-width: 1100px; margin: 0 auto; }
        .header { margin-bottom: 1rem; }
        .controls { display:flex; gap:1rem; align-items:center; margin-bottom:1rem; }
        select, button { padding: 0.6rem 0.8rem; border-radius:6px; border:1px solid #ccc; }
        table { width:100%; border-collapse:collapse; margin-top:1rem; }
        th, td { padding:0.75rem 0.5rem; border:1px solid #eee; text-align:left; }
        th { background:#f1f5f9; }
        .muted { color:#6b7280; }
        .empty { text-align:center; padding:1.5rem; color:#6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PV Global — Par Filière</h1>
            <p class="muted">Sélectionnez une filière pour afficher les étudiants et leurs moyennes pondérées (calcul identique au bulletin).</p>
        </div>

        <form method="GET" class="controls">
            <label for="filiere_id">Filière:</label>
            <select id="filiere_id" name="filiere_id">
                <option value="">-- Choisir une filière --</option>
                <?php foreach($filieres as $f): ?>
                    <option value="<?= $f['Id'] ?>" <?= ($filiere_id !== '' && $filiere_id == $f['Id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($f['CodeFilière']) ?> - <?= htmlspecialchars($f['Désignation']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Afficher</button>
            <a href="pv_global.php" style="margin-left:auto; text-decoration:none; padding:0.5rem 0.8rem; border:1px solid #ddd; border-radius:6px;">Retour PV Global</a>
        </form>

        <?php if ($message): ?>
            <div class="message error"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if (empty($filiere_id)): ?>
            <div class="empty">Veuillez sélectionner une filière pour voir la liste des étudiants.</div>
        <?php else: ?>
            <div class="stats">
                <strong>Étudiants trouvés: <?= count($etudiants) ?></strong>
            </div>

            <?php if (count($etudiants) === 0): ?>
                <div class="empty">Aucun étudiant trouvé pour cette filière.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>N° Étudiant</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Moyenne (pondérée)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($etudiants as $et): ?>
                            <tr>
                                <td><?= htmlspecialchars($et['numero_etudiant']) ?></td>
                                <td><?= htmlspecialchars($et['nom_etudiant']) ?></td>
                                <td><?= htmlspecialchars($et['prenom_etudiant']) ?></td>
                                <td>
                                    <?php if (is_null($et['moyenne'])): ?>
                                        N/A
                                    <?php else: ?>
                                        <?= htmlspecialchars(number_format($et['moyenne'],2)) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="bulletin_etudiant.php?numero=<?= urlencode($et['numero_etudiant']) ?>">Voir Bulletin</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</body>
</html>
