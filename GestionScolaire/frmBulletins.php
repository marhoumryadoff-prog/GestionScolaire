<?php
require_once 'connexion_base.php';
$db = new ConnexionBase();

$message = "";
$type_message = '';
$etudiant_trouve = false;
$etudiant = ['Numéro' => '', 'Nom' => '', 'Prenom' => '', 'Civilité' => '', 'Filiere' => ''];
$note_data = ['Code_Module' => '', 'Coefficient' => '', 'Note' => ''];
$modules_list = [];
$filiere_id = null;

// Use $_REQUEST to handle GET (search/select) and POST (CRUD) variables
$numero_etudiant = isset($_REQUEST['numero']) ? trim($_REQUEST['numero']) : '';
$code_module_selectionne = isset($_REQUEST['module']) ? trim($_REQUEST['module']) : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Manage messages from redirection
if (isset($_GET['msg'])) {
    $parts = explode('|', $_GET['msg']);
    $message = htmlspecialchars($parts[1]);
    $type_message = htmlspecialchars($parts[0]);
}

// --- LOGIC: 1. Fetch Student Info and Filter Modules ---
if (!empty($numero_etudiant)) {
    $stmt_etudiant = $db->pdo->prepare("
        SELECT e.numero_etudiant, e.nom_etudiant, e.prenom_etudiant, e.civilite, f.CodeFilière, f.Id as FilièreId 
        FROM etudiants e
        LEFT JOIN filières f ON e.FilièreId = f.Id
        WHERE e.numero_etudiant = ?
    ");
    $stmt_etudiant->execute([$numero_etudiant]);
    $etudiant_db = $stmt_etudiant->fetch(PDO::FETCH_ASSOC);

    if ($etudiant_db) {
        $etudiant_trouve = true;
        $etudiant['Numéro'] = $etudiant_db['numero_etudiant'];
        $etudiant['Nom'] = $etudiant_db['nom_etudiant'];
        $etudiant['Prenom'] = $etudiant_db['prenom_etudiant'];
        $etudiant['Civilité'] = $etudiant_db['civilite'];
        $etudiant['Filiere'] = $etudiant_db['CodeFilière'];
        $filiere_id = $etudiant_db['FilièreId'];

        // Fetch Modules specific to this Filière
        if ($filiere_id !== null) {
            $sql_modules = "
                SELECT CodeModule, DésignationModule 
                FROM modules 
                WHERE FilièreId = ? 
                ORDER BY DésignationModule
            ";
            $stmt_modules = $db->pdo->prepare($sql_modules);
            $stmt_modules->execute([$filiere_id]);
            $modules_list = $stmt_modules->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $message = "Attention: L'étudiant n'est lié à aucune filière. Modules non affichés.";
            $type_message = 'info';
        }

        // --- LOGIC: 2. Fetch Module Details & Existing Note ---
        if (!empty($code_module_selectionne)) {
            // Get module details (Code and Coefficient)
            $stmt_module_details = $db->pdo->prepare("
                SELECT m.CodeModule, m.Coefficient, m.DésignationModule 
                FROM modules m 
                WHERE m.CodeModule = ? AND m.FilièreId = ?
            ");
            $stmt_module_details->execute([$code_module_selectionne, $filiere_id]);
            $module_details = $stmt_module_details->fetch(PDO::FETCH_ASSOC);

            if ($module_details) {
                $note_data['Code_Module'] = $module_details['CodeModule'];
                $note_data['Coefficient'] = $module_details['Coefficient'] ?? 'N/A';
            } else {
                $message = "Erreur: La matière sélectionnée n'est pas liée à ce programme!";
                $type_message = 'error';
                $code_module_selectionne = '';
            }
            
            // Fetch the existing Note
            if (!empty($code_module_selectionne)) {
                $stmt_note = $db->pdo->prepare("
                    SELECT n.Note 
                    FROM Notes n
                    WHERE n.Num_Etudiant = ? AND n.Code_Module = ?
                ");
                $stmt_note->execute([$numero_etudiant, $code_module_selectionne]);
                $note_db = $stmt_note->fetch(PDO::FETCH_ASSOC);
                
                if ($note_db) {
                    $note_data['Note'] = $note_db['Note'];
                    if(empty($message)) $message = "Note existante affichée. Prêt à Modifier/Supprimer.";
                    if(empty($type_message)) $type_message = 'info';
                } else {
                    $note_data['Note'] = '';
                    if(empty($message)) $message = "Note Inexistante pour ce module. Prêt à Enregistrer.";
                    if(empty($type_message)) $type_message = 'info';
                }
            }
        }
    } else {
        $message = "Erreur : Étudiant N° " . htmlspecialchars($numero_etudiant) . " non trouvé.";
        $type_message = 'error';
    }
}

// --- LOGIC: 3. HANDLE CRUD OPERATIONS (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $etudiant_trouve && !empty($code_module_selectionne)) {
    $note = isset($_POST['note']) ? trim($_POST['note']) : null;
    $note_valide = is_numeric($note) && $note >= 0 && $note <= 20;

    if (!$note_valide && $action !== 'supprimer') {
        $message = "Erreur: La note doit être un nombre entre 0.00 et 20.00.";
        $type_message = 'error';
    } else {
        try {
            $stmt_check = $db->pdo->prepare("SELECT 1 FROM Notes WHERE Num_Etudiant = ? AND Code_Module = ?");
            $stmt_check->execute([$numero_etudiant, $code_module_selectionne]);
            $note_exists = $stmt_check->fetch();

            switch ($action) {
                
                // --- ENREGISTRER (CREATE/INSERT) ---
                case 'enregistrer':
                    if ($note_exists) {
                        $message = "Erreur: Note déjà existante. Veuillez utiliser le bouton 'Modifier'.";
                        $type_message = 'error';
                    } else {
                        $stmt = $db->pdo->prepare("
                            INSERT INTO Notes (Num_Etudiant, Code_Module, Note) 
                            VALUES (?, ?, ?)
                        ");
                        $stmt->execute([$numero_etudiant, $code_module_selectionne, $note]);
                        $message = "Enregistrement de la note effectué avec succès.";
                        $type_message = 'success';
                    }
                    break;
                
                // --- MODIFIER (UPDATE) ---
                case 'modifier':
                    if (!$note_exists) {
                        $message = "Erreur : Note Inexistante pour ce module. Utilisez 'Enregistrer'.";
                        $type_message = 'error';
                    } else {
                        // FIX: Added robust try-catch for update execution
                        try {
                            $stmt = $db->pdo->prepare("
                                UPDATE Notes SET Note = ? 
                                WHERE Num_Etudiant = ? AND Code_Module = ?
                            ");
                            $stmt->execute([$note, $numero_etudiant, $code_module_selectionne]);
                            $message = "Modification de la note effectuée avec succès.";
                            $type_message = 'success';
                        } catch (PDOException $e) {
                            $message = "Erreur SQL Modification: " . $e->getMessage();
                            $type_message = 'error';
                        }
                    }
                    break;

                // --- SUPPRIMER (DELETE) ---
                case 'supprimer':
                    if (!$note_exists) {
                        $message = "Erreur : Note Inexistante pour ce module. Suppression impossible.";
                        $type_message = 'error';
                    } else {
                        $stmt = $db->pdo->prepare("
                            DELETE FROM Notes 
                            WHERE Num_Etudiant = ? AND Code_Module = ?
                        ");
                        $stmt->execute([$numero_etudiant, $code_module_selectionne]);
                        $message = "Suppression de la note effectuée avec succès.";
                        $type_message = 'success';
                    }
                    break;
            }
        } catch (Exception $e) {
            $message = "Erreur DB: " . $e->getMessage();
            $type_message = 'error';
        }
    }

    // Redirection après POST pour éviter le re-submit
    header("Location: frmBulletins.php?numero=" . urlencode($numero_etudiant) . "&module=" . urlencode($code_module_selectionne) . "&msg=" . urlencode($type_message . '|' . $message));
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Saisie Bulletin de Notes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 650px; margin: 0 auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #007bff; text-align: center; margin-bottom: 30px; }
        .form-row { display: flex; align-items: center; margin-bottom: 15px; }
        .form-row label { flex: 1; font-weight: bold; padding-right: 15px; text-align: right; color: #333; }
        .form-row input, .form-row select { flex: 2; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .recherche-group { display: flex; gap: 10px; align-items: center; }
        .recherche-group input { flex: 1; }
        .buttons { display: flex; justify-content: center; gap: 15px; margin-top: 30px; }
        .buttons button { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; color: white; font-weight: bold; }
        .btn-enregistrer { background: #28a745; }
        .btn-modifier { background: #ffc107; color: #333 !important; }
        .btn-supprimer { background: #dc3545; }
        .btn-rechercher { background: #007bff; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; text-align: center; font-weight: bold; }
        .info { background: #cce7ff; color: #004085; border: 1px solid #b3d7ff; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        
        /* New button styles for reporting */
        .report-links {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
        }
        .report-btn {
            display: inline-block;
            padding: 8px 15px;
            margin: 5px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Saisie Bulletin de Notes</h1>
        
        <?php if ($message): ?>
            <div class="message <?= $type_message ?>"><?= $message ?></div>
        <?php endif; ?>

        <form method="post" action="frmBulletins.php">
            <input type="hidden" name="numero" value="<?= htmlspecialchars($numero_etudiant) ?>">
            
            <div class="form-row">
                <label for="numero_get">Numéro Étudiant:</label>
                <div class="recherche-group">
                    <input type="text" id="numero_get" name="numero_get" 
                           value="<?= htmlspecialchars($numero_etudiant) ?>" required>
                    <button type="button" class="btn-rechercher" onclick="document.location.href = 'frmBulletins.php?numero=' + document.getElementById('numero_get').value;">
                        Rechercher
                    </button>
                </div>
            </div>

            <div class="form-row">
                <label>Civilité:</label>
                <div style="display: flex; gap: 15px; align-items: center; flex: 2; height: 38px;">
                    <?php 
                        $current_civilite = $etudiant_trouve ? $etudiant['Civilité'] : '';
                        $civilite_options = ['M' => 'Monsieur', 'Mme' => 'Madame', 'Mlle' => 'Mademoiselle'];
                    ?>
                    <?php foreach($civilite_options as $value => $label): ?>
                        <label style="width: auto; font-weight: normal; margin: 0; display: flex; align-items: center;">
                            <input type="radio" disabled 
                                   <?= ($current_civilite == $value) ? 'checked' : '' ?>
                                   style="width: auto; margin-right: 5px; height: auto;">
                            <?= $label ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-row">
                <label>Nom / Prénom:</label>
                <input type="text" 
                       value="<?= htmlspecialchars($etudiant['Nom'] . ' ' . $etudiant['Prenom']) ?>" 
                       readonly>
            </div>
            <div class="form-row">
                <label>Filière:</label>
                <input type="text" 
                       value="<?= htmlspecialchars($etudiant['Filiere']) ?>" 
                       readonly>
            </div>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

            <div class="form-row">
                <label for="module">Module:</label>
                <select id="module_select" name="module_select" 
                        onchange="document.location.href = 'frmBulletins.php?numero=<?= urlencode($numero_etudiant) ?>&module=' + this.value;"
                        <?= $etudiant_trouve ? '' : 'disabled' ?>>
                    <option value="">-- Sélectionner un module --</option>
                    <?php if ($filiere_id === null && $etudiant_trouve): ?>
                        <option disabled>L'étudiant n'est pas lié à une filière</option>
                    <?php endif; ?>
                    <?php foreach ($modules_list as $mod): ?>
                        <option value="<?= htmlspecialchars($mod['CodeModule']) ?>" 
                                <?php echo ($mod['CodeModule'] == $code_module_selectionne) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($mod['DésignationModule']) ?> (<?= $mod['CodeModule'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <input type="hidden" name="module" value="<?= htmlspecialchars($code_module_selectionne) ?>">

            <div class="form-row">
                <label>Code Module:</label>
                <input type="text" 
                       value="<?= htmlspecialchars($note_data['Code_Module']) ?>" 
                       readonly>
            </div>
            <div class="form-row">
                <label>Coefficient:</label>
                <input type="text" 
                       value="<?= htmlspecialchars($note_data['Coefficient']) ?>" 
                       readonly>
            </div>

            <div class="form-row">
                <label for="note">Note / 20:</label>
                <input type="number" step="0.01" min="0" max="20" id="note" name="note" 
                       value="<?= htmlspecialchars($note_data['Note']) ?>"
                       <?= !empty($code_module_selectionne) ? '' : 'disabled' ?>>
            </div>

            <div class="buttons">
                <button type="submit" name="action" value="enregistrer" class="btn-enregistrer"
                        <?= empty($code_module_selectionne) ? 'disabled' : '' ?>>Enregistrer</button>
                <button type="submit" name="action" value="modifier" class="btn-modifier" 
                        <?= !empty($note_data['Note']) ? '' : 'disabled' ?>
                        onclick="return confirm('Confirmer la modification de la note?')">
                    Modifier
                </button>
                <button type="submit" name="action" value="supprimer" class="btn-supprimer" 
                        <?= !empty($note_data['Note']) ? '' : 'disabled' ?>
                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette note?')">
                    Supprimer
                </button>
            </div>
        </form>
        
        <div class="report-links">
            <h4>Accès aux Rapports:</h4>
            
            <?php if ($etudiant_trouve): ?>
                <a href="bulletin_etudiant.php?numero=<?= urlencode($etudiant['Numéro']) ?>" 
                   class="report-btn" style="background: #008080; color: white;">
                    📋 Voir le Bulletin (<?= htmlspecialchars($etudiant['Numéro']) ?>)
                </a>
            <?php endif; ?>
            
            <a href="liste_note.php" class="report-btn" style="background: #1e3c72; color: white;">
                📊 Rapport Global des Notes
            </a>
        </div>
        
        <p style="text-align: center; margin-top: 15px;">
            <a href="menu_principal.php" style="color: #6c757d; text-decoration: none; font-weight: bold;">← Retour au Menu Principal</a>
        </p>
    </div>

</body>
</html>