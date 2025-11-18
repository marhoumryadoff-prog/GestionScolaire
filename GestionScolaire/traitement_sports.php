<?php
require_once 'connexion_base.php';
$db = new ConnexionBase();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $libelle_sport = trim($_POST['libelle_sport'] ?? '');
    $id_sport = $_POST['id_sport'] ?? null;
    
    if (empty($libelle_sport)) {
        header("Location: gestion_sports.php?erreur=Le nom du sport ne peut pas être vide");
        exit();
    }
    
    try {
        // --- LOGIC FOR AJOUTER (CREATE) ---
        if ($action === 'ajouter') {
            // التحقق من عدم وجود الرياضة مسبقاً
            $verif = $db->pdo->prepare("SELECT id_sport FROM sports WHERE libelle_sport = ?");
            $verif->execute([$libelle_sport]);
            
            if ($verif->rowCount() > 0) {
                header("Location: gestion_sports.php?erreur=Ce sport existe déjà");
                exit();
            }
            
            // إضافة الرياضة
            $requete = $db->pdo->prepare("INSERT INTO sports (libelle_sport) VALUES (?)");
            $requete->execute([$libelle_sport]);
            
            header("Location: gestion_sports.php?success=Sport '{$libelle_sport}' ajouté avec succès");
            exit();
        }

        // --- LOGIC FOR MODIFIER (UPDATE) ---
        if ($action === 'modifier' && $id_sport) {
            // Check if the new name already exists for *another* sport
            $verif = $db->pdo->prepare("SELECT id_sport FROM sports WHERE libelle_sport = ? AND id_sport != ?");
            $verif->execute([$libelle_sport, $id_sport]);
            
            if ($verif->rowCount() > 0) {
                header("Location: gestion_sports.php?erreur=Modification échouée: Ce nom existe déjà.");
                exit();
            }
            
            $requete = $db->pdo->prepare("UPDATE sports SET libelle_sport = ? WHERE id_sport = ?");
            $requete->execute([$libelle_sport, $id_sport]);
            
            header("Location: gestion_sports.php?success=Sport modifié avec succès");
            exit();
        }
        
    } catch (Exception $e) {
        header("Location: gestion_sports.php?erreur=Erreur lors de l'opération: " . $e->getMessage());
        exit();
    }
}

// --- LOGIC FOR SUPPRIMER (DELETE - adjusted for error handling) ---
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id_sport = intval($_GET['id']);
    
    try {
        // 1. Check for associated students
        $verif = $db->pdo->prepare("SELECT id_etudiant FROM etudiant_sports WHERE id_sport = ?");
        $verif->execute([$id_sport]);
        
        if ($verif->rowCount() > 0) {
            header("Location: gestion_sports.php?erreur=Impossible de supprimer ce sport, des étudiants y sont inscrits");
            exit();
        }
        
        // 2. Delete the sport
        $requete = $db->pdo->prepare("DELETE FROM sports WHERE id_sport = ?");
        $requete->execute([$id_sport]);
        
        header("Location: gestion_sports.php?success=Sport supprimé avec succès");
        exit();
        
    } catch (Exception $e) {
        header("Location: gestion_sports.php?erreur=Erreur lors de la suppression: " . $e->getMessage());
        exit();
    }
}

header("Location: gestion_sports.php");
exit();
?>