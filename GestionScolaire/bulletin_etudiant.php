<?php
require_once 'config.php';
$db = new ConnexionBase();

$student_number = $_GET['numero'] ?? ''; 
$etudiant_info = null;
$notes_list = [];
$message = '';

// Calculation variables
$total_weighted_score = 0.0; // Sum (Note * Coeff)
$total_coefficient = 0.0;    // Sum (Coeff)
$moyenne = 0.0;

if (empty($student_number)) {
    header('Location: formulaire_principal.php?erreur=Aucun numéro étudiant spécifié.');
    exit;
}

try {
    // 1. Fetch Student Header Information (Nom, Prénom, Filière)
    $stmt_info = $db->pdo->prepare("
        SELECT 
            e.nom_etudiant, e.prenom_etudiant, e.civilite, f.Désignation as NomFiliere, f.CodeFilière
        FROM etudiants e
        LEFT JOIN filières f ON e.FilièreId = f.Id
        WHERE e.numero_etudiant = ?
    ");
    $stmt_info->execute([$student_number]);
    $etudiant_info = $stmt_info->fetch(PDO::FETCH_ASSOC);

    if (!$etudiant_info) {
        $message = "Erreur: Étudiant N° " . htmlspecialchars($student_number) . " non trouvé.";
    } else {
        // 2. Fetch all Notes for this specific student, sorted by module
        // KEY CHANGE: Fetching CodeModule for the links
        $stmt_notes = $db->pdo->prepare("
            SELECT 
                n.Note,
                m.DésignationModule,
                m.Coefficient,
                m.CodeModule 
            FROM Notes n
            JOIN modules m ON n.Code_Module = m.CodeModule
            WHERE n.Num_Etudiant = ?
            ORDER BY m.DésignationModule
        ");
        $stmt_notes->execute([$student_number]);
        $notes_list = $stmt_notes->fetchAll(PDO::FETCH_ASSOC);

        // 3. Calculate Weighted Average (Moyenne)
        foreach ($notes_list as $index => $note) {
            $coeff = (float)($note['Coefficient'] ?? 0.0);
            $grade = (float)($note['Note'] ?? 0.0);
            $weighted_score = $grade * $coeff;

            // Store the calculated weighted score back into the array for display
            $notes_list[$index]['WeightedScore'] = $weighted_score; 
            $notes_list[$index]['CodeModule'] = $note['CodeModule']; // Ensure CodeModule is carried forward
            
            // Sum the totals
            if ($coeff > 0) {
                $total_weighted_score += $weighted_score;
                $total_coefficient += $coeff;
            }
        }

        if ($total_coefficient > 0) {
            $moyenne = $total_weighted_score / $total_coefficient;
        }
    }

} catch (PDOException $e) {
    $message = "Erreur de base de données: " . $e->getMessage();
}

$nom_complet = ($etudiant_info) ? htmlspecialchars($etudiant_info['nom_etudiant'] . ' ' . $etudiant_info['prenom_etudiant']) : 'Étudiant Inconnu';
$filiere = ($etudiant_info) ? htmlspecialchars($etudiant_info['CodeFilière'] . ' - ' . $etudiant_info['NomFiliere']) : 'N/A';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin de Notes pour <?= $nom_complet ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 900px; /* Increased max-width to accommodate new columns */ margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        h1 { color: #1e3c72; text-align: center; border-bottom: 2px solid #1e3c72; padding-bottom: 10px; }
        .student-header { margin-bottom: 25px; background: #f0f4f8; padding: 15px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
        .student-details p { margin: 5px 0; font-size: 1.1em; }
        .student-details strong { display: inline-block; width: 100px; color: #2a5298; }
        
        /* Moyenne Display Style */
        .moyenne-box {
            text-align: center;
            border: 3px solid <?= ($moyenne >= 10) ? '#28a745' : '#dc3545' ?>;
            background-color: <?= ($moyenne >= 10) ? '#e6ffe6' : '#fff0f0' ?>;
            border-radius: 8px;
            padding: 10px 20px;
            min-width: 170px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .moyenne-box .label {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 5px;
        }
        .moyenne-box .value {
            font-size: 2em;
            font-weight: 900;
            color: <?= ($moyenne >= 10) ? '#28a745' : '#dc3545' ?>;
        }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #007bff; color: white; }
        .note-column, .coef-column { text-align: center; }
        .total-row td { background-color: #f0f4f8; font-weight: bold; }
        .buttons { text-align: center; margin-top: 30px; }
        .buttons a { padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 0 10px; }
        .btn-return { background: #6c757d; color: white; }
        .btn-manage { background: #28a745; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bulletin de Notes Individuel</h1>
        
        <?php if ($message): ?>
            <div style="color: red; text-align: center; margin-bottom: 15px;"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($etudiant_info): ?>
            <div class="student-header">
                <div class="student-details">
                    <p><strong>Nom Complet:</strong> <?= $nom_complet ?></p>
                    <p><strong>N° Étudiant:</strong> <?= htmlspecialchars($student_number) ?></p>
                    <p><strong>Filière:</strong> <?= $filiere ?></p>
                </div>

                <div class="moyenne-box">
                    <div class="label">MOYENNE GÉNÉRALE</div>
                    <div class="value">
                        <?php if ($total_coefficient > 0): ?>
                            <?= htmlspecialchars(number_format($moyenne, 2)) ?> / 20
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </div>
                    <div class="label" style="color: black; font-weight: normal; font-size: 0.8em;">
                        (Total Coeff: <?= htmlspecialchars(number_format($total_coefficient, 1)) ?>)
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <h2>Détail des Notes</h2>
        
        <?php if (!empty($notes_list)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Module</th>
                        <th class="coef-column">Coefficient</th>
                        <th class="note-column">Note / 20</th>
                        <th class="coef-column">Note Pondérée (Note x Coeff)</th>
                        <th>Actions</th> </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach($notes_list as $note): 
                        $note_value = (float)($note['Note'] ?? 0.0);
                        $weighted_score_display = (float)($note['WeightedScore'] ?? 0.0);
                        $module_code = htmlspecialchars($note['CodeModule']); // The module code for the link
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($note['DésignationModule']) ?></td>
                            <td class="coef-column"><?= htmlspecialchars($note['Coefficient']) ?></td>
                            <td class="note-column" style="color: <?= ($note_value >= 10) ? '#28a745' : '#dc3545' ?>;">
                                <?= htmlspecialchars(number_format($note_value, 2)) ?>
                            </td>
                            <td class="coef-column"><?= htmlspecialchars(number_format($weighted_score_display, 2)) ?></td>
                            
                            <td>
                                <a href="frmBulletins.php?numero=<?= urlencode($student_number) ?>&module=<?= $module_code ?>" 
                                   style="background: #ffc107; color: black; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 0.9em;">
                                    Modifier
                                </a>
                                <a href="frmBulletins.php?numero=<?= urlencode($student_number) ?>&module=<?= $module_code ?>" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette note?');"
                                   style="background: #dc3545; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 0.9em;">
                                    Supprimer
                                </a>
                            </td>
                            </tr>
                    <?php endforeach; ?>
                    
                    <tr class="total-row">
                        <td colspan="2" style="text-align: right;">TOTAUX:</td>
                        <td></td>
                        <td class="coef-column"><?= htmlspecialchars(number_format($total_weighted_score, 2)) ?></td>
                        <td></td> </tr>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: #6c757d;">
                Aucune note enregistrée pour cet étudiant dans la base de données.
            </p>
        <?php endif; ?>

        <div class="buttons">
            <a href="formulaire_principal.php?recherche_numero=<?= urlencode($student_number) ?>" class="btn-return">
                ← Retour au Formulaire Étudiant
            </a>
            <a href="frmBulletins.php?numero=<?= urlencode($student_number) ?>" class="btn-manage">
                Gérer/Modifier les Notes
            </a>
        </div>
    </div>
</body>
</html>