<?php
require_once 'connexion_base.php';
$db = new ConnexionBase();

// جلب بيانات الشعب أولاً
$filieres = $db->pdo->query("SELECT * FROM filières ORDER BY CodeFilière")->fetchAll(PDO::FETCH_ASSOC);

// البحث عن وحدة
$module_courant = null;
if (isset($_GET['recherche_code']) && !empty($_GET['recherche_code'])) {
    $code_recherche = $_GET['recherche_code'];
    try {
        // Query updated to select Coefficient
        $requete_module = $db->pdo->prepare("
            SELECT m.*, f.CodeFilière, f.Désignation as FiliereDesignation 
            FROM modules m 
            LEFT JOIN filières f ON m.FilièreId = f.Id 
            WHERE m.CodeModule = ?
        ");
        $requete_module->execute([$code_recherche]);
        $module_courant = $requete_module->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $erreur_recherche = "Erreur recherche: " . $e->getMessage();
    }
}

// التحقق إذا كان يجب عرض القائمة
$afficher_liste = isset($_GET['afficher_liste']) && $_GET['afficher_liste'] == '1';
$filiere_filter = isset($_GET['filiere_filter']) ? trim($_GET['filiere_filter']) : '';

// معالجة العمليات
$message = '';
$type_message = '';

if ($_POST) {
    $id = $_POST['id'] ?? '';
    $code = $_POST['CodeModule'] ?? '';
    $designation = $_POST['Designation'] ?? '';
    $filiereId = $_POST['FiliereId'] ?? '';
    $coefficient = $_POST['Coefficient'] ?? null; // NEW: Get Coefficient
    
    // Convert coefficient to float for DB integrity
    if ($coefficient !== null && $coefficient !== '') {
        $coefficient = (float)$coefficient;
    } else {
        $coefficient = null;
    }
    
    if (isset($_POST['Enregistrer'])) {
        try {
            // التحقق من عدم التكرار
            $verif = $db->pdo->prepare("SELECT Id FROM modules WHERE CodeModule = ?");
            $verif->execute([$code]);
            
            if ($verif->rowCount() > 0) {
                $message = "Code module déjà existant";
                $type_message = 'error';
            } else {
                // UPDATE INSERT SQL: Add Coefficient
                $sql = "INSERT INTO modules (CodeModule, DésignationModule, FilièreId, Coefficient) VALUES (?, ?, ?, ?)";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute([$code, $designation, $filiereId, $coefficient]);
                
                $message = "Module enregistré avec succès";
                $type_message = 'success';
                $module_courant = null;
            }
        } catch (PDOException $e) {
            $message = "Erreur: " . $e->getMessage();
            $type_message = 'error';
        }
    }
    
    if (isset($_POST['Modifier'])) {
        if (empty($id)) {
            $message = "Veuillez sélectionner un module à modifier";
            $type_message = 'error';
        } else {
            try {
                // التحقق إذا كان الكود الجديد غير مستخدم من قبل modules أخرى
                $verif = $db->pdo->prepare("SELECT Id FROM modules WHERE CodeModule = ? AND Id != ?");
                $verif->execute([$code, $id]);
                
                if ($verif->rowCount() > 0) {
                    $message = "Code module déjà utilisé par un autre module";
                    $type_message = 'error';
                } else {
                    // UPDATE MODIFIER SQL: Add Coefficient
                    $sql = "UPDATE modules SET CodeModule = ?, DésignationModule = ?, FilièreId = ?, Coefficient = ? WHERE Id = ?";
                    $stmt = $db->pdo->prepare($sql);
                    $stmt->execute([$code, $designation, $filiereId, $coefficient, $id]);
                    
                    $message = "Module modifié avec succès";
                    $type_message = 'success';
                    
                    // تحديث البيانات الحالية بعد التعديل
                    $requete_module = $db->pdo->prepare("
                        SELECT m.*, f.CodeFilière, f.Désignation as FiliereDesignation 
                        FROM modules m 
                        LEFT JOIN filières f ON m.FilièreId = f.Id 
                        WHERE m.Id = ?
                    ");
                    $requete_module->execute([$id]);
                    $module_courant = $requete_module->fetch(PDO::FETCH_ASSOC);
                }
                
            } catch (PDOException $e) {
                $message = "Erreur: " . $e->getMessage();
                $type_message = 'error';
            }
        }
    }
    
    if (isset($_POST['Supprimer'])) {
        if (empty($id)) {
            $message = "Veuillez sélectionner un module à supprimer";
            $type_message = 'error';
        } else {
            try {
                $sql = "DELETE FROM modules WHERE Id = ?";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute([$id]);
                
                $message = "Module supprimé avec succès";
                $type_message = 'success';
                $module_courant = null;
            } catch (PDOException $e) {
                $message = "Erreur: " . $e->getMessage();
                $type_message = 'error';
            }
        }
    }
}

// جلب البيانات فقط إذا طلبنا عرض القائمة
if ($afficher_liste) {
    try {
        if (!empty($filiere_filter)) {
            $stmtMods = $db->pdo->prepare("\n                SELECT m.*, f.CodeFilière, f.Désignation as FiliereDesignation \n                FROM modules m \n                LEFT JOIN filières f ON m.FilièreId = f.Id \n                WHERE m.FilièreId = ? \n                ORDER BY m.CodeModule\n            ");
            $stmtMods->execute([$filiere_filter]);
            $modules = $stmtMods->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $modules = $db->pdo->query("\n                SELECT m.*, f.CodeFilière, f.Désignation as FiliereDesignation \n                FROM modules m \n                LEFT JOIN filières f ON m.FilièreId = f.Id \n                ORDER BY m.CodeModule\n            ")->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $modules = [];
        if (empty($message)) {
            $message = "Erreur chargement: " . $e->getMessage();
            $type_message = 'error';
        }
    }
} else {
    $modules = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Modules</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .recherche-group { background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .form-container { background: #f8f9fa; padding: 25px; border-radius: 8px; margin-bottom: 25px; border: 2px solid #e9ecef; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #dee2e6; padding: 12px; text-align: left; }
        th { background-color: #2c3e50; color: white; font-weight: bold; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #2c3e50; font-size: 14px; }
        input, select { padding: 10px; width: 100%; max-width: 400px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px; }
        button { padding: 12px 25px; margin: 8px; border: none; border-radius: 5px; cursor: pointer; color: white; font-weight: bold; font-size: 14px; }
        .btn-enregistrer { background: #28a745; }
        .btn-modifier { background: #ffc107; color: black; }
        .btn-supprimer { background: #dc3545; }
        .btn-afficher { background: #17a2b8; }
        .btn-rechercher { background: #6f42c1; }
        .message { padding: 15px; margin: 15px 0; border-radius: 5px; text-align: center; font-weight: bold; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        h2 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 15px; text-align: center; }
        h3 { color: #34495e; margin-top: 30px; padding-bottom: 10px; border-bottom: 2px solid #bdc3c7; }
        .buttons-container { text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #dee2e6; }
        .liste-container { margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Gestion des Modules</h2>
        
        <?php if ($message): ?>
            <div class="message <?= $type_message ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <div class="recherche-group">
            <form method="GET" action="frmModules.php" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <label for="recherche_immediate" style="width: auto; margin: 0; font-weight: bold; color: #2c3e50;">
                    🔍 Rechercher un module par code:
                </label>
                <input type="text" id="recherche_immediate" name="recherche_code" 
                       placeholder="Entrez le code module" 
                       value="<?= isset($_GET['recherche_code']) ? htmlspecialchars($_GET['recherche_code']) : '' ?>"
                       style="width: 250px; padding: 10px; border: 1px solid #ced4da; border-radius: 4px;">

                <!-- Filtre par Filière -->
                <select id="filterFiliere" name="filiere_filter" style="width: 300px; padding: 10px; border: 1px solid #ced4da; border-radius: 4px;">
                    <option value="">Filtrer par Filière (optionnel)</option>
                    <?php foreach($filieres as $f): ?>
                        <option value="<?= $f['Id'] ?>" <?= (isset($_GET['filiere_filter']) && $_GET['filiere_filter'] == $f['Id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['CodeFilière']) ?> - <?= htmlspecialchars($f['Désignation']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn-rechercher">Rechercher</button>
                
                <?php if ($module_courant): ?>
                    <span style="color: #28a745; font-weight: bold; font-size: 14px;">
                        ✓ Module trouvé: <?= htmlspecialchars($module_courant['CodeModule']) ?> - <?= htmlspecialchars($module_courant['DésignationModule']) ?>
                    </span>
                <?php elseif (isset($_GET['recherche_code']) && !empty($_GET['recherche_code']) && !$module_courant): ?>
                    <span style="color: #dc3545; font-weight: bold; font-size: 14px;">
                        ✗ Aucun module trouvé avec ce code
                    </span>
                <?php endif; ?>
            </form>
        </div>

        <div class="form-container">
            <form method="post">
                <input type="hidden" name="id" id="idField" value="<?= $module_courant ? $module_courant['Id'] : '' ?>">
                
                <div class="form-group">
                    <label for="CodeModule">Code Module:</label>
                    <input type="text" id="CodeModule" name="CodeModule" required 
                           value="<?= $module_courant ? htmlspecialchars($module_courant['CodeModule']) : '' ?>"
                           placeholder="Ex: M001, MATH101">
                </div>
                
                <div class="form-group">
                    <label for="Designation">Désignation:</label>
                    <input type="text" id="Designation" name="Designation" required 
                           value="<?= $module_courant ? htmlspecialchars($module_courant['DésignationModule']) : '' ?>"
                           placeholder="Ex: Mathématiques, Programmation">
                </div>
                
                <div class="form-group">
                    <label for="Coefficient">Coefficient (Poids):</label>
                    <input type="number" step="0.01" min="0.1" id="Coefficient" name="Coefficient" 
                           value="<?= $module_courant ? htmlspecialchars($module_courant['Coefficient']) : '' ?>"
                           placeholder="Ex: 3.5, 4.0">
                </div>
                <div class="form-group">
                    <label for="FiliereId">Filière:</label>
                    <select id="FiliereId" name="FiliereId" required>
                        <option value="">Sélectionner une filière</option>
                        <?php foreach($filieres as $filiere): ?>
                            <option value="<?= $filiere['Id'] ?>" 
                                <?= ($module_courant && $module_courant['FilièreId'] == $filiere['Id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($filiere['CodeFilière']) ?> - <?= htmlspecialchars($filiere['Désignation']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="buttons-container">
                    <button type="submit" name="Enregistrer" class="btn-enregistrer">Enregistrer</button>
                    <button type="submit" name="Modifier" class="btn-modifier">Modifier</button>
                    <button type="submit" name="Supprimer" class="btn-supprimer" onclick="return confirm('Êtes-vous sûr de supprimer ce module?')">Supprimer</button>
                    <button type="button" class="btn-afficher" onclick="afficherListe()">Afficher Liste</button>
                </div>
            </form>
        </div>

        <?php if ($afficher_liste): ?>
        <div class="liste-container">
            <h3>📋 Liste des Modules</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code Module</th>
                        <th>Désignation</th>
                        <th>Coefficient</th> <th>Filière</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($modules) > 0): ?>
                        <?php foreach($modules as $module): ?>
                        <tr>
                            <td><?= $module['Id'] ?></td>
                            <td><?= htmlspecialchars($module['CodeModule']) ?></td>
                            <td><?= htmlspecialchars($module['DésignationModule']) ?></td>
                            <td><?= htmlspecialchars($module['Coefficient'] ?: 'N/A') ?></td> <td><?= htmlspecialchars($module['CodeFilière'] . ' - ' . $module['FiliereDesignation']) ?></td>
                            <td>
                                <button onclick="editModule(<?= $module['Id'] ?>, '<?= $module['CodeModule'] ?>', '<?= $module['DésignationModule'] ?>', '<?= $module['Coefficient'] ?>', <?= $module['FilièreId'] ?>)" 
                                        style="background: #3498db; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">
                                    Sélectionner
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #666; font-style: italic; padding: 20px;">
                                Aucun module enregistré
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <div style="margin-top: 25px; text-align: center;">
            <a href="menu_principal.php" style="background: #6c757d; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                ← Retour au menu principal
            </a>
        </div>
    </div>

    <script>
    // Selection function updated to accept Coefficient
    function editModule(id, code, designation, coefficient, filiereId) {
        document.getElementById('idField').value = id;
        document.getElementById('CodeModule').value = code;
        document.getElementById('Designation').value = designation;
        document.getElementById('Coefficient').value = coefficient; // NEW ASSIGNMENT
        document.getElementById('FiliereId').value = filiereId;
        document.querySelector('.form-container').scrollIntoView({ behavior: 'smooth' });
    }
    
    function afficherListe() {
        var fil = '';
        var el = document.getElementById('filterFiliere');
        if (el) fil = el.value;
        var url = 'frmModules.php?afficher_liste=1';
        if (fil) url += '&filiere_filter=' + encodeURIComponent(fil);
        window.location.href = url;
    }
    </script>
</body>
</html>