<?php
require_once 'connexion_base.php';
$db = new ConnexionBase();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $libelle_nationalite = trim($_POST['libelle_nationalite'] ?? '');
    $id_nationalite = $_POST['id_nationalite'] ?? null;
    
    if (empty($libelle_nationalite)) {
        header("Location: gestion_nationalites.php?erreur=Le nom de la nationalité ne peut pas être vide");
        exit();
    }
    
    try {
        // --- LOGIC FOR AJOUTER (CREATE) ---
        if ($action === 'ajouter') {
            // التحقق من عدم وجود الرياضة مسبقاً
            $verif = $db->pdo->prepare("SELECT id_nationalite FROM nationalites WHERE libelle_nationalite = ?");
            $verif->execute([$libelle_nationalite]);
            
            if ($verif->rowCount() > 0) {
                header("Location: gestion_nationalites.php?erreur=Nationalité déjà existante");
                exit();
            }
            
            $requete = $db->pdo->prepare("INSERT INTO nationalites (libelle_nationalite) VALUES (?)");
            $requete->execute([$libelle_nationalite]);
            
            header("Location: gestion_nationalites.php?success=Nationalité '{$libelle_nationalite}' ajoutée avec succès");
            exit();
        }

        // --- LOGIC FOR MODIFIER (UPDATE) ---
        if ($action === 'modifier' && $id_nationalite) {
            // Check if the new name already exists for *another* nationality
            $verif = $db->pdo->prepare("SELECT id_nationalite FROM nationalites WHERE libelle_nationalite = ? AND id_nationalite != ?");
            $verif->execute([$libelle_nationalite, $id_nationalite]);
            
            if ($verif->rowCount() > 0) {
                header("Location: gestion_nationalites.php?erreur=Modification échouée: Ce nom existe déjà.");
                exit();
            }
            
            $requete = $db->pdo->prepare("UPDATE nationalites SET libelle_nationalite = ? WHERE id_nationalite = ?");
            $requete->execute([$libelle_nationalite, $id_nationalite]);
            
            header("Location: gestion_nationalites.php?success=Nationalité modifiée avec succès");
            exit();
        }
        
    } catch (Exception $e) {
        header("Location: gestion_nationalites.php?erreur=Erreur lors de l'opération: " . $e->getMessage());
        exit();
    }
}

// --- LOGIC FOR SUPPRIMER (DELETE - unchanged but included for completeness) ---
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id_nationalite = intval($_GET['id']);
    
    // Check for associated students first
    $verif = $db->pdo->prepare("SELECT id_etudiant FROM etudiants WHERE id_nationalite = ?");
    $verif->execute([$id_nationalite]);
    
    if ($verif->rowCount() > 0) {
        header("Location: gestion_nationalites.php?erreur=Impossible de supprimer, des étudiants ont cette nationalité");
        exit();
    }
    
    // Proceed with deletion
    $requete = $db->pdo->prepare("DELETE FROM nationalites WHERE id_nationalite = ?");
    $requete->execute([$id_nationalite]);
    
    header("Location: gestion_nationalites.php?success=Nationalité supprimée");
    exit();
}

header("Location: gestion_nationalites.php");
exit();
?>