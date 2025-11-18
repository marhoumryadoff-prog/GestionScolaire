<?php
require_once 'config.php';
$db = new ConnexionBase();

$requete = $db->pdo->query("
    SELECT e.*, n.libelle_nationalite, f.CodeFilière, f.Désignation as NomFiliere,
           GROUP_CONCAT(s.libelle_sport SEPARATOR ', ') as sports
    FROM etudiants e
    LEFT JOIN nationalites n ON e.id_nationalite = n.id_nationalite
    LEFT JOIN Filières f ON e.FilièreId = f.Id
    LEFT JOIN etudiant_sports es ON e.id_etudiant = es.id_etudiant
    LEFT JOIN sports s ON es.id_sport = s.id_sport
    GROUP BY e.id_etudiant
    ORDER BY e.nom_etudiant
");
$etudiants = $requete->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Étudiants</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1400px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: middle; }
        th { background: #007bff; color: white; }
        .liens { text-align: center; margin-top: 20px; }
        a { color: #007bff; text-decoration: none; margin: 0 10px; }
        /* Style for the photo display */
        .student-photo {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Liste des Étudiants - TP02</h1>
        
        <p><strong>Nombre d'étudiants:</strong> <?= count($etudiants) ?></p>

        <?php if (count($etudiants) > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Photo</th> <th>Numéro</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Civilité</th>
                            <th>Date Naiss.</th>
                            <th>Adresse</th>
                            <th>Localisation</th>
                            <th>Plateforme</th>
                            <th>Application</th>
                            <th>Nationalité</th>
                            <th>Filière</th>
                            <th>Sports</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($etudiants as $etudiant): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $photo_path = 'uploads/' . htmlspecialchars($etudiant['photo']);
                                    if (!empty($etudiant['photo']) && file_exists($photo_path)):
                                    ?>
                                        <img src="<?= $photo_path ?>" alt="Photo" class="student-photo">
                                    <?php else: ?>
                                        <span>N/A</span>
                                    <?php endif; ?>
                                </td> <td><?= htmlspecialchars($etudiant['numero_etudiant']) ?></td>
                                <td><?= htmlspecialchars($etudiant['nom_etudiant']) ?></td>
                                <td><?= htmlspecialchars($etudiant['prenom_etudiant']) ?></td>
                                <td><?= $etudiant['civilite'] ?: '-' ?></td>
                                <td><?= $etudiant['date_naissance'] ? date('d/m/Y', strtotime($etudiant['date_naissance'])) : '-' ?></td>
                                <td><?= htmlspecialchars($etudiant['adresse'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($etudiant['localisation'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($etudiant['platforme'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($etudiant['application'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($etudiant['libelle_nationalite']) ?></td>
                                <td><?= $etudiant['CodeFilière'] ? htmlspecialchars($etudiant['CodeFilière'] . ' - ' . $etudiant['NomFiliere']) : '-' ?></td>
                                <td><?= $etudiant['sports'] ?: 'Aucun' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #666; font-style: italic;">Aucun étudiant enregistré</p>
        <?php endif; ?>

        <div class="liens">
            <a href="formulaire_principal.php">← Retour au formulaire</a>
        </div>
    </div>
</body>
</html>