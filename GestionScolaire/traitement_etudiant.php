<?php
require_once 'connexion_base.php';
$db = new ConnexionBase();

// دالة لرفع الصورة
function uploadPhoto($file) {
    $target_dir = "uploads/";
    
    // تأكد من وجود مجلد uploads
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // إذا لم يكن هناك ملف مرفوع أو حدث خطأ
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ["success" => null]; // لا توجد صورة جديدة
    }
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // التحقق من أن الملف صورة
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ["error" => "Le fichier n'est pas une image."];
    }
    
    // التحقق من الحجم (2MB كحد أقصى)
    if ($file["size"] > 2000000) {
        return ["error" => "L'image est trop volumineuse (max 2MB)."];
    }
    
    // التحقق من الامتداد
    $allowed_extensions = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($file_extension, $allowed_extensions)) {
        return ["error" => "Seuls JPG, JPEG, PNG & GIF sont autorisés."];
    }
    
    // رفع الصورة
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ["success" => $new_filename];
    } else {
        return ["error" => "Erreur lors du téléchargement de l'image."];
    }
}

// دالة لحذف الصورة القديمة
function deleteOldPhoto($filename) {
    if (!empty($filename)) {
        $file_path = "uploads/" . $filename;
        if (file_exists($file_path)) {
            unlink($file_path);
            return true;
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $numero = trim($_POST['numero']);
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $date_naissance = $_POST['date_naissance'];
    $civilite = $_POST['civilite'];
    $adresse = trim($_POST['adresse']);
    $localisation = trim($_POST['localisation']);
    
    // MODIFICATION: Handle multi-select inputs (arrays)
    $platforms = isset($_POST['platforme']) ? (array)$_POST['platforme'] : [];
    $applications = isset($_POST['application']) ? (array)$_POST['application'] : [];
    
    // Convert arrays to comma-separated strings for database storage
    $platforme_str = implode(',', array_filter($platforms));
    $application_str = implode(',', array_filter($applications));
    // END MODIFICATION
    
    $nationalite = $_POST['nationalite'];
    $filiere = $_POST['filiere'];
    $sports = isset($_POST['sports']) ? $_POST['sports'] : [];

    if ($action === 'ajouter') {
        // التحقق من عدم وجود الرقم مسبقاً
        $verif = $db->pdo->prepare("SELECT id_etudiant FROM etudiants WHERE numero_etudiant = ?");
        $verif->execute([$numero]);
        
        if ($verif->rowCount() > 0) {
            header("Location: formulaire_principal.php?erreur=Numéro étudiant déjà existant");
            exit();
        }

        // معالجة رفع الصورة
        $photo_filename = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_result = uploadPhoto($_FILES['photo']);
            if (isset($upload_result['error'])) {
                header("Location: formulaire_principal.php?erreur=" . $upload_result['error']);
                exit();
            }
            $photo_filename = $upload_result['success'];
        }

        // إضافة طالب جديد
        try {
            $db->pdo->beginTransaction();
            
            // MODIFICATION: Use platforme_str and application_str
            $requete_etudiant = $db->pdo->prepare("INSERT INTO etudiants (numero_etudiant, nom_etudiant, prenom_etudiant, civilite, adresse, localisation, platforme, application, photo, date_naissance, id_nationalite, FilièreId) 
                                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $requete_etudiant->execute([$numero, $nom, $prenom, $civilite, $adresse, $localisation, $platforme_str, $application_str, $photo_filename, $date_naissance, $nationalite, $filiere]);
            $id_etudiant = $db->pdo->lastInsertId();
            
            // إضافة الرياضات
            if (!empty($sports)) {
                $requete_sports = $db->pdo->prepare("INSERT INTO etudiant_sports (id_etudiant, id_sport) VALUES (?, ?)");
                foreach ($sports as $id_sport) {
                    $requete_sports->execute([$id_etudiant, $id_sport]);
                }
            }
            
            $db->pdo->commit();
            header("Location: formulaire_principal.php?success=Étudiant ajouté avec succès");
            exit();
            
        } catch (Exception $e) {
            $db->pdo->rollBack();
            header("Location: formulaire_principal.php?erreur=Erreur lors de l'ajout: " . $e->getMessage());
            exit();
        }
    }
    
    elseif ($action === 'modifier' && isset($_POST['id_etudiant'])) {
        $id_etudiant = $_POST['id_etudiant'];
        $photo_actuelle = $_POST['photo_actuelle'] ?? null;
        
        try {
            $db->pdo->beginTransaction();
            
            // معالجة الصورة
            $photo_filename = $photo_actuelle;
            
            if (isset($_POST['supprimer_photo']) && $_POST['supprimer_photo'] == '1') {
                // حذف الصورة الحالية
                deleteOldPhoto($photo_actuelle);
                $photo_filename = null;
            } elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                // رفع صورة جديدة وحذف القديمة
                $upload_result = uploadPhoto($_FILES['photo']);
                if (isset($upload_result['error'])) {
                    throw new Exception($upload_result['error']);
                }
                // حذف الصورة القديمة إذا كانت موجودة
                if (!empty($photo_actuelle)) {
                    deleteOldPhoto($photo_actuelle);
                }
                $photo_filename = $upload_result['success'];
            }
            
            // تحديث بيانات الطالب
            // MODIFICATION: Use platforme_str and application_str
            $requete_etudiant = $db->pdo->prepare("UPDATE etudiants SET nom_etudiant = ?, prenom_etudiant = ?, civilite = ?, adresse = ?, localisation = ?, platforme = ?, application = ?, photo = ?, date_naissance = ?, id_nationalite = ?, FilièreId = ? WHERE id_etudiant = ?");
            
            if (!$requete_etudiant->execute([$nom, $prenom, $civilite, $adresse, $localisation, $platforme_str, $application_str, $photo_filename, $date_naissance, $nationalite, $filiere, $id_etudiant])) {
                throw new Exception("Erreur lors de la mise à jour de l'étudiant");
            }
            
            // حذف الرياضات القديمة وإضافة الجديدة
            $delete_sports = $db->pdo->prepare("DELETE FROM etudiant_sports WHERE id_etudiant = ?");
            if (!$delete_sports->execute([$id_etudiant])) {
                throw new Exception("Erreur lors de la suppression des anciens sports");
            }
            
            if (!empty($sports)) {
                $requete_sports = $db->pdo->prepare("INSERT INTO etudiant_sports (id_etudiant, id_sport) VALUES (?, ?)");
                foreach ($sports as $id_sport) {
                    if (!$requete_sports->execute([$id_etudiant, $id_sport])) {
                        throw new Exception("Erreur lors de l'ajout des sports");
                    }
                }
            }
            
            $db->pdo->commit();
            header("Location: formulaire_principal.php?success=Étudiant modifié avec succès&recherche_numero=" . urlencode($numero));
            exit();
            
        } catch (Exception $e) {
            $db->pdo->rollBack();
            header("Location: formulaire_principal.php?erreur=Erreur lors de la modification: " . $e->getMessage() . "&recherche_numero=" . urlencode($numero));
            exit();
        }
    }
    
    elseif ($action === 'supprimer' && isset($_POST['id_etudiant'])) {
        $id_etudiant = $_POST['id_etudiant'];
        $photo_actuelle = $_POST['photo_actuelle'] ?? null;
        
        try {
            $db->pdo->beginTransaction();
            
            // حذف الصورة إذا كانت موجودة
            if (!empty($photo_actuelle)) {
                deleteOldPhoto($photo_actuelle);
            }
            
            // حذف الرياضات أولاً
            $delete_sports = $db->pdo->prepare("DELETE FROM etudiant_sports WHERE id_etudiant = ?");
            if (!$delete_sports->execute([$id_etudiant])) {
                throw new Exception("Erreur lors de la suppression des sports");
            }
            
            // ثم حذف الطالب
            $delete_etudiant = $db->pdo->prepare("DELETE FROM etudiants WHERE id_etudiant = ?");
            if (!$delete_etudiant->execute([$id_etudiant])) {
                throw new Exception("Erreur lors de la suppression de l'étudiant");
            }
            
            $db->pdo->commit();
            header("Location: formulaire_principal.php?success=Étudiant supprimé avec succès");
            exit();
            
        } catch (Exception $e) {
            $db->pdo->rollBack();
            header("Location: formulaire_principal.php?erreur=Erreur lors de la suppression: " . $e->getMessage());
            exit();
        }
    }
}

header("Location: formulaire_principal.php");
exit();
?>