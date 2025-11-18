<?php
require_once 'connexion_base.php';
$db = new ConnexionBase();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Extract variables from the form
    $id = $_POST['id'] ?? '';
    $code = trim($_POST['CodeModule'] ?? '');
    $designation = trim($_POST['Designation'] ?? '');
    $filiereId = $_POST['FilièreId'] ?? null;
    $coefficient = $_POST['Coefficient'] ?? null; 
    
    // Ensure numeric conversion for safety
    if ($coefficient !== null && $coefficient !== '') {
        $coefficient = (float)$coefficient;
    } else {
        $coefficient = null;
    }
    
    $redirect_url = "frmModules.php";

    try {
        if ($action === 'Enregistrer') {
            // Check for duplicate CodeModule
            $verif = $db->pdo->prepare("SELECT Id FROM modules WHERE CodeModule = ?");
            $verif->execute([$code]);
            
            if ($verif->rowCount() > 0) {
                header("Location: {$redirect_url}?erreur=Code module déjà existant");
                exit();
            }
            
            $sql = "INSERT INTO modules (CodeModule, DésignationModule, FilièreId, Coefficient) VALUES (?, ?, ?, ?)";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute([$code, $designation, $filiereId, $coefficient]);
            
            header("Location: {$redirect_url}?success=Module enregistré avec succès");
            exit();
        }

        if ($action === 'Modifier') {
            if (empty($id)) {
                header("Location: {$redirect_url}?erreur=Veuillez sélectionner un module à modifier");
                exit();
            }
            
            // Check if the new CodeModule conflicts with another existing module
            $verif = $db->pdo->prepare("SELECT Id FROM modules WHERE CodeModule = ? AND Id != ?");
            $verif->execute([$code, $id]);
            
            if ($verif->rowCount() > 0) {
                header("Location: {$redirect_url}?erreur=Code module déjà utilisé par un autre module");
                exit();
            }

            $sql = "UPDATE modules SET CodeModule = ?, DésignationModule = ?, FilièreId = ?, Coefficient = ? WHERE Id = ?";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute([$code, $designation, $filiereId, $coefficient, $id]);
            
            // Redirect and load the modified module for confirmation
            header("Location: frmModules.php?recherche_code=" . urlencode($code) . "&success=Module modifié avec succès");
            exit();
        }
        
        if ($action === 'Supprimer') {
            if (empty($id)) {
                header("Location: {$redirect_url}?erreur=Veuillez sélectionner un module à supprimer");
                exit();
            }
            
            // The database handles the constraint check here
            $sql = "DELETE FROM modules WHERE Id = ?";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute([$id]);
            
            header("Location: {$redirect_url}?success=Module supprimé avec succès");
            exit();

        }
    } catch (PDOException $e) {
        // Handle Foreign Key Error (e.g., if notes still exist)
        if ($e->getCode() == '23000' || strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
            header("Location: {$redirect_url}?erreur=Impossible de supprimer/modifier (Clé étrangère active). Supprimez les notes liées d'abord.");
        } else {
            header("Location: {$redirect_url}?erreur=Erreur DB: " . $e->getMessage());
        }
        exit();
    }
}

// Default redirection
header("Location: frmModules.php");
exit();
?>