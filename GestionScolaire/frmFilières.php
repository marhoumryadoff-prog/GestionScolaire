
<?php
require_once 'connexion_base.php';
$db = new ConnexionBase();

// البحث عن شعبة
$filiere_courante = null;
if (isset($_GET['recherche_code']) && !empty($_GET['recherche_code'])) {
    $code_recherche = $_GET['recherche_code'];
    try {
        $requete_filiere = $db->pdo->prepare("SELECT * FROM filières WHERE CodeFilière = ?");
        $requete_filiere->execute([$code_recherche]);
        $filiere_courante = $requete_filiere->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $erreur_recherche = "Erreur recherche: " . $e->getMessage();
    }
}

// التحقق إذا كان يجب عرض القائمة
$afficher_liste = isset($_GET['afficher_liste']) && $_GET['afficher_liste'] == '1';

// معالجة العمليات
$message = '';
$type_message = '';

if ($_POST) {
    $id = $_POST['id'] ?? '';
    $code = $_POST['CodeFiliere'] ?? '';
    $designation = $_POST['Designation'] ?? '';
    
    if (isset($_POST['Enregistrer'])) {
        try {
            // التحقق من عدم التكرار
            $verif = $db->pdo->prepare("SELECT Id FROM filières WHERE CodeFilière = ?");
            $verif->execute([$code]);
            
            if ($verif->rowCount() > 0) {
                $message = "Code filière déjà existant";
                $type_message = 'error';
            } else {
                $sql = "INSERT INTO filières (CodeFilière, Désignation) VALUES (?, ?)";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute([$code, $designation]);
                
                $message = "Filière enregistrée avec succès";
                $type_message = 'success';
                $filiere_courante = null;
            }
        } catch (PDOException $e) {
            $message = "Erreur: " . $e->getMessage();
            $type_message = 'error';
        }
    }
    
    if (isset($_POST['Modifier'])) {
        if (empty($id)) {
            $message = "Veuillez sélectionner une filière à modifier";
            $type_message = 'error';
        } else {
            try {
                $sql = "UPDATE filières SET CodeFilière = ?, Désignation = ? WHERE Id = ?";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute([$code, $designation, $id]);
                
                $message = "Filière modifiée avec succès";
                $type_message = 'success';
            } catch (PDOException $e) {
                $message = "Erreur: " . $e->getMessage();
                $type_message = 'error';
            }
        }
    }
    
    if (isset($_POST['Supprimer'])) {
        if (empty($id)) {
            $message = "Veuillez sélectionner une filière à supprimer";
            $type_message = 'error';
        } else {
            try {
                // التحقق من عدم وجود طلاب مرتبطين
                $verif_etudiants = $db->pdo->prepare("SELECT id_etudiant FROM etudiants WHERE FilièreId = ?");
                $verif_etudiants->execute([$id]);
                
                if ($verif_etudiants->rowCount() > 0) {
                    $message = "Impossible de supprimer: des étudiants sont inscrits dans cette filière";
                    $type_message = 'error';
                } else {
                    $sql = "DELETE FROM filières WHERE Id = ?";
                    $stmt = $db->pdo->prepare($sql);
                    $stmt->execute([$id]);
                    
                    $message = "Filière supprimée avec succès";
                    $type_message = 'success';
                    $filiere_courante = null;
                }
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
        $filieres = $db->pdo->query("SELECT * FROM filières ORDER BY CodeFilière")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $filieres = [];
        if (empty($message)) {
            $message = "Erreur chargement: " . $e->getMessage();
            $type_message = 'error';
        }
    }
} else {
    $filieres = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Filières</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .recherche-group { background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .form-container { background: #f8f9fa; padding: 25px; border-radius: 8px; margin-bottom: 25px; border: 2px solid #e9ecef; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #dee2e6; padding: 12px; text-align: left; }
        th { background-color: #2c3e50; color: white; font-weight: bold; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #2c3e50; font-size: 14px; }
        input { padding: 10px; width: 100%; max-width: 400px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px; }
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
        <h2>Gestion des Filières</h2>
        
        <?php if ($message): ?>
            <div class="message <?= $type_message ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <div class="recherche-group">
            <form method="GET" action="frmFilières.php" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <label for="recherche_immediate" style="width: auto; margin: 0; font-weight: bold; color: #2c3e50;">
                    🔍 Rechercher une filière par code:
                </label>
                <input type="text" id="recherche_immediate" name="recherche_code" 
                       placeholder="Entrez le code filière" 
                       value="<?= isset($_GET['recherche_code']) ? htmlspecialchars($_GET['recherche_code']) : '' ?>"
                       style="width: 250px; padding: 10px; border: 1px solid #ced4da; border-radius: 4px;">
                <button type="submit" class="btn-rechercher">Rechercher</button>
                
                <?php if ($filiere_courante): ?>
                    <span style="color: #28a745; font-weight: bold; font-size: 14px;">
                        ✓ Filière trouvée: <?= htmlspecialchars($filiere_courante['CodeFilière']) ?> - <?= htmlspecialchars($filiere_courante['Désignation']) ?>
                    </span>
                <?php elseif (isset($_GET['recherche_code']) && !empty($_GET['recherche_code']) && !$filiere_courante): ?>
                    <span style="color: #dc3545; font-weight: bold; font-size: 14px;">
                        ✗ Aucune filière trouvée avec ce code
                    </span>
                <?php endif; ?>
            </form>
        </div>

        <div class="form-container">
            <form method="post">
                <input type="hidden" name="id" id="idField" value="<?= $filiere_courante ? $filiere_courante['Id'] : '' ?>">
                
                <div class="form-group">
                    <label for="CodeFiliere">Code Filière:</label>
                    <input type="text" id="CodeFiliere" name="CodeFiliere" required 
                           value="<?= $filiere_courante ? htmlspecialchars($filiere_courante['CodeFilière']) : '' ?>"
                           placeholder="Ex: TC, 2SC, 3ISIL">
                </div>
                
                <div class="form-group">
                    <label for="Designation">Désignation:</label>
                    <input type="text" id="Designation" name="Designation" required 
                           value="<?= $filiere_courante ? htmlspecialchars($filiere_courante['Désignation']) : '' ?>"
                           placeholder="Ex: Technologie, 2ème Science">
                </div>
                
                <div class="buttons-container">
                    <button type="submit" name="Enregistrer" class="btn-enregistrer">Enregistrer</button>
                    <button type="submit" name="Modifier" class="btn-modifier">Modifier</button>
                    <button type="submit" name="Supprimer" class="btn-supprimer" onclick="return confirm('Êtes-vous sûr de supprimer cette filière?')">Supprimer</button>
                    <button type="button" class="btn-afficher" onclick="afficherListe()">Afficher Liste</button>
                </div>
            </form>
        </div>

        <!-- القائمة تظهر فقط عند الضغط على زر "Afficher Liste" -->
        <?php if ($afficher_liste): ?>
        <div class="liste-container">
            <h3>📋 Liste des Filières</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code Filière</th>
                        <th>Désignation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($filieres) > 0): ?>
                        <?php foreach($filieres as $filiere): ?>
                        <tr>
                            <td><?= $filiere['Id'] ?></td>
                            <td><?= htmlspecialchars($filiere['CodeFilière']) ?></td>
                            <td><?= htmlspecialchars($filiere['Désignation']) ?></td>
                            <td>
                                <button onclick="editFiliere(<?= $filiere['Id'] ?>, '<?= $filiere['CodeFilière'] ?>', '<?= $filiere['Désignation'] ?>')" 
                                        style="background: #3498db; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">
                                    Sélectionner
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #666; font-style: italic; padding: 20px;">
                                Aucune filière enregistrée
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
    function editFiliere(id, code, designation) {
        document.getElementById('idField').value = id;
        document.getElementById('CodeFiliere').value = code;
        document.getElementById('Designation').value = designation;
        document.querySelector('.form-container').scrollIntoView({ behavior: 'smooth' });
    }
    
    function afficherListe() {
        window.location.href = 'frmFilières.php?afficher_liste=1';
    }
    </script>
</body>
</html>
