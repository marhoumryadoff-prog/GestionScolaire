<?php
require_once 'check_access.php';
requireAdmin(); 
require_once 'connexion_base.php';
$db = new ConnexionBase();

// التحقق من أن المستخدم مسؤول
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header("Location: menu_principal.php");
    exit();
}

// جلب جميع المستخدمين
$requete_users = $db->pdo->query("SELECT * FROM user ORDER BY id");
$users = $requete_users->fetchAll(PDO::FETCH_ASSOC);

// معالجة العمليات
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $email = trim($_POST['email']);
    $mdp = trim($_POST['mdp']);
    $role = $_POST['role'];
    $id = $_POST['id'] ?? null;

    if ($action === 'ajouter') {
        if (empty($email) || empty($mdp) || empty($role)) {
            $message = "❌ Veuillez remplir tous les champs";
        } else {
            $verif = $db->pdo->prepare("SELECT id FROM user WHERE email = ?");
            $verif->execute([$email]);
            
            if ($verif->rowCount() > 0) {
                $message = "❌ Cet email existe déjà";
            } else {
                $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);
                $requete = $db->pdo->prepare("INSERT INTO user (email, mdp, role) VALUES (?, ?, ?)");
                $requete->execute([$email, $mdp_hash, $role]);
                $message = "✅ Utilisateur ajouté avec succès";
                header("Location: gestion_users.php");
                exit();
            }
        }
    } elseif ($action === 'modifier' && $id) {
        if (empty($email) || empty($role)) {
            $message = "❌ Veuillez remplir tous les champs";
        } else {
            $verif = $db->pdo->prepare("SELECT id FROM user WHERE email = ? AND id != ?");
            $verif->execute([$email, $id]);
            
            if ($verif->rowCount() > 0) {
                $message = "❌ Cet email est déjà utilisé par un autre utilisateur";
            } else {
                if (!empty($mdp)) {
                    $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);
                    $requete = $db->pdo->prepare("UPDATE user SET email = ?, mdp = ?, role = ? WHERE id = ?");
                    $requete->execute([$email, $mdp_hash, $role, $id]);
                } else {
                    $requete = $db->pdo->prepare("UPDATE user SET email = ?, role = ? WHERE id = ?");
                    $requete->execute([$email, $role, $id]);
                }
                $message = "✅ Utilisateur modifié avec succès";
                header("Location: gestion_users.php");
                exit();
            }
        }
    }
}

// حذف المستخدم
if (isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    if ($id != $_SESSION['user_id']) { // منع حذف المستخدم الحالي
        $requete = $db->pdo->prepare("DELETE FROM user WHERE id = ?");
        $requete->execute([$id]);
        $message = "✅ Utilisateur supprimé avec succès";
        header("Location: gestion_users.php");
        exit();
    } else {
        $message = "❌ Vous ne pouvez pas supprimer votre propre compte";
    }
}

// البحث عن مستخدم للتعديل
$user_a_modifier = null;
if (isset($_GET['modifier'])) {
    $id = intval($_GET['modifier']);
    $requete = $db->pdo->prepare("SELECT * FROM user WHERE id = ?");
    $requete->execute([$id]);
    $user_a_modifier = $requete->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion des Utilisateurs</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container { 
            max-width: 1000px; 
            margin: 0 auto; 
            background: white; 
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .form-section { 
            background: linear-gradient(135deg, #e7f3ff, #d4e7ff);
            padding: 25px; 
            margin: 20px 0; 
            border-radius: 10px;
            border: 2px solid #b3d7ff;
        }
        .liste-users { 
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 20px; 
            margin: 20px 0; 
            border-radius: 10px;
            border: 2px solid #dee2e6;
        }
        .user-item { 
            padding: 15px; 
            border-bottom: 1px solid #ddd; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            background: white;
            margin: 8px 0;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-group { margin-bottom: 15px; }
        label { 
            display: block; 
            font-weight: bold; 
            margin-bottom: 8px; 
            color: #2c3e50;
        }
        input, select { 
            padding: 10px; 
            width: 100%; 
            max-width: 400px;
            border: 2px solid #ddd; 
            border-radius: 6px; 
            font-size: 1em;
            transition: all 0.3s ease;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        button { 
            padding: 12px 20px; 
            margin-right: 10px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            color: white;
            font-weight: bold;
            font-size: 1em;
            transition: all 0.3s ease;
        }
        .btn-ajouter { background: linear-gradient(135deg, #28a745, #20c997); }
        .btn-modifier { background: linear-gradient(135deg, #ffc107, #fd7e14); color: black; }
        .btn-supprimer { background: linear-gradient(135deg, #dc3545, #c82333); }
        .btn-selectionner { background: linear-gradient(135deg, #007bff, #0056b3); }
        .btn-ajouter:hover, .btn-modifier:hover, .btn-supprimer:hover, .btn-selectionner:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .message { 
            padding: 15px; 
            margin: 15px 0; 
            border-radius: 8px; 
            font-weight: bold;
            text-align: center;
        }
        .success { 
            background: linear-gradient(135deg, #d4edda, #c3e6cb); 
            color: #155724; 
            border: 2px solid #c3e6cb;
        }
        .erreur { 
            background: linear-gradient(135deg, #f8d7da, #f5c6cb); 
            color: #721c24; 
            border: 2px solid #f5c6cb;
        }
        h1, h3 {
            color: #2c3e50;
            text-align: center;
        }
        .user-email {
            font-weight: bold;
            color: #2c3e50;
        }
        .user-role {
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.9em;
            margin-left: 10px;
        }
        .current-user {
            background: #fff3cd;
            border: 2px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>👥 Gestion des Utilisateurs</h1>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, '❌') !== false ? 'erreur' : 'success' ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="form-section">
            <h3><?= $user_a_modifier ? '✏️ Modifier l\'Utilisateur' : '➕ Ajouter un nouvel Utilisateur' ?></h3>
            <form method="POST" action="gestion_users.php">
                <input type="hidden" name="id" value="<?= $user_a_modifier['id'] ?? '' ?>">
                
                <div class="form-group">
                    <label for="email">📧 Email:</label>
                    <input type="email" id="email" name="email" required 
                           value="<?= htmlspecialchars($user_a_modifier['email'] ?? '') ?>"
                           placeholder="exemple@email.com">
                </div>
                
                <div class="form-group">
                    <label for="mdp">🔒 Mot de passe:</label>
                    <input type="password" id="mdp" name="mdp" 
                           <?= $user_a_modifier ? 'placeholder="Laisser vide pour ne pas changer"' : 'required placeholder="Mot de passe"' ?>>
                </div>
                
                <div class="form-group">
                    <label for="role">👤 Rôle:</label>
                    <select id="role" name="role" required>
                        <option value="">Sélectionner un rôle</option>
                        <option value="User" <?= ($user_a_modifier['role'] ?? '') === 'User' ? 'selected' : '' ?>>👨‍🎓 User (Étudiant)</option>
                        <option value="Admin" <?= ($user_a_modifier['role'] ?? '') === 'Admin' ? 'selected' : '' ?>>👨‍💼 Admin (Administrateur)</option>
                    </select>
                </div>
                
                <?php if ($user_a_modifier): ?>
                    <button type="submit" name="action" value="modifier" class="btn-modifier">✏️ Modifier Utilisateur</button>
                    <a href="gestion_users.php" style="background: #6c757d; color: white; padding: 12px 20px; text-decoration: none; border-radius: 6px; display: inline-block;">🔄 Nouvelle Saisie</a>
                <?php else: ?>
                    <button type="submit" name="action" value="ajouter" class="btn-ajouter">➕ Ajouter Utilisateur</button>
                <?php endif; ?>
            </form>
        </div>

        <div class="liste-users">
            <h3>📋 Liste des Utilisateurs (<?= count($users) ?> utilisateur(s))</h3>
            <?php if (empty($users)): ?>
                <p style="text-align: center; color: #6c757d; font-style: italic;">Aucun utilisateur trouvé</p>
            <?php else: ?>
                <?php foreach($users as $u): ?>
                    <div class="user-item <?= $u['id'] == $_SESSION['user_id'] ? 'current-user' : '' ?>">
                        <span>
                            <strong>ID <?= $u['id'] ?>:</strong> 
                            <span class="user-email"><?= htmlspecialchars($u['email']) ?></span>
                            <span class="user-role"><?= htmlspecialchars($u['role']) ?></span>
                            <?php if ($u['id'] == $_SESSION['user_id']): ?>
                                <span style="color: #856404; font-weight: bold;">(Vous)</span>
                            <?php endif; ?>
                        </span>
                        <div>
                            <a href="gestion_users.php?modifier=<?= $u['id'] ?>" class="btn-selectionner" style="text-decoration: none; padding: 8px 15px; display: inline-block;">📝 Sélectionner</a>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <a href="gestion_users.php?supprimer=<?= $u['id'] ?>" class="btn-supprimer" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer l\\'utilisateur : <?= $u['email'] ?> ?')" 
                                   style="text-decoration: none; padding: 8px 15px; display: inline-block;">🗑️ Supprimer</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <br>
        <div style="text-align: center;">
            <a href="menu_principal.php" style="background: #6c757d; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;">← Retour au menu principal</a>
        </div>
    </div>
</body>
</html>