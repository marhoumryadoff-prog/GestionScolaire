<?php
require_once 'config.php';
$db = new ConnexionBase();

try {
    $requete = $db->pdo->query("
        SELECT 
            n.Note,
            e.numero_etudiant,
            e.nom_etudiant,
            e.prenom_etudiant,
            m.DésignationModule,
            m.Coefficient,
            f.CodeFilière
        FROM Notes n
        JOIN etudiants e ON n.Num_Etudiant = e.numero_etudiant
        JOIN modules m ON n.Code_Module = m.CodeModule
        LEFT JOIN filières f ON e.FilièreId = f.Id
        
        -- KEY CHANGE: ORDER BY Filière, then Module, then Student Name
        ORDER BY f.CodeFilière, m.DésignationModule, e.nom_etudiant
    ");
    $notes_list = $requete->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $notes_list = [];
    $error_message = "Erreur de chargement des notes: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Notes et Bulletins</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #1e3c72; text-align: center; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #007bff; color: white; font-weight: bold; }
        .note-column { text-align: center; font-weight: bold; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; }
        .group-header { background-color: #f0f0f0; font-weight: bold; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📚 Rapport Général des Notes Enregistrées</h1>
        
        <?php if (isset($error_message)): ?>
            <div class="error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <p>Nombre total de notes enregistrées : <strong><?= count($notes_list) ?></strong></p>

        <?php if (!empty($notes_list)): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>N° Étudiant</th>
                            <th>Nom & Prénom</th>
                            <th>Filière</th>
                            <th>Module</th>
                            <th>Coefficient</th>
                            <th class="note-column">Note / 20</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $current_filiere = '';
                        $current_module = '';
                        foreach($notes_list as $note): 
                            
                            // Insert Filière grouping header
                            if ($note['CodeFilière'] !== $current_filiere):
                                $current_filiere = $note['CodeFilière'];
                                $current_module = ''; // Reset module when filiere changes
                                ?>
                                <tr class="group-header" style="background-color: #d8e6f3;">
                                    <td colspan="6">FILIÈRE : <?= htmlspecialchars($current_filiere ?: 'HORS FILIÈRE') ?></td>
                                </tr>
                                <?php
                            endif;

                            // Insert Module grouping header
                            if ($note['DésignationModule'] !== $current_module):
                                $current_module = $note['DésignationModule'];
                                ?>
                                <tr class="group-header" style="background-color: #e9f0f7;">
                                    <td colspan="6" style="text-align: left; padding-left: 40px;">MODULE : <?= htmlspecialchars($current_module) ?> (Coeff: <?= htmlspecialchars($note['Coefficient']) ?>)</td>
                                </tr>
                                <?php
                            endif;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($note['numero_etudiant']) ?></td>
                                <td><?= htmlspecialchars($note['nom_etudiant'] . ' ' . $note['prenom_etudiant']) ?></td>
                                <td><?= htmlspecialchars($note['CodeFilière'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($note['DésignationModule']) ?></td>
                                <td><?= htmlspecialchars($note['Coefficient']) ?></td>
                                <td class="note-column"><?= htmlspecialchars(number_format($note['Note'], 2)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #666; font-style: italic;">Aucune note n'a été enregistrée pour l'instant.</p>
        <?php endif; ?>

        <div style="margin-top: 30px; text-align: center;">
            <a href="menu_principal.php" style="background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;">
                ← Retour au menu principal
            </a>
            <a href="frmBulletins.php" style="background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin-left: 15px;">
                📝 Gérer les Notes
            </a>
        </div>
    </div>
</body>
</html>