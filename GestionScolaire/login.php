
<?php
session_start();
require_once 'connexion_base.php';
$db = new ConnexionBase();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mdp = trim($_POST['mdp']);
    
    if (!empty($email) && !empty($mdp)) {
        $requete = $db->pdo->prepare("SELECT * FROM user WHERE email = ?");
        $requete->execute([$email]);
        $user = $requete->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($mdp, $user['mdp'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            header("Location: menu_principal.php");
            exit();
        } else {
            $message = "❌ User Inexistant. Inscrivez vous";
        }
    } else {
        $message = "❌ Veuillez remplir tous les champs";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Gestion Scolarité</title>
    <style>
        body { font-family: Arial; margin: 0; padding: 0; background: #f5f5f5; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-container { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h1 { color: #333; text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #333; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn-login { background: #007bff; color: white; border: none; padding: 12px; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; }
        .btn-inscription { background: #28a745; color: white; border: none; padding: 12px; border-radius: 4px; cursor: pointer; width: 100%; margin-top: 10px; text-decoration: none; display: block; text-align: center; }
        .message { padding: 10px; margin: 15px 0; border-radius: 4px; text-align: center; font-weight: bold; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .test-accounts { background: #e9ecef; padding: 15px; border-radius: 4px; margin-top: 20px; font-size: 0.9em; text-align: center; }
        .quick-select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-top: 10px; box-sizing: border-box; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>🔐 Connexion</h1>
        
        <?php if ($message): ?>
            <div class="message error"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">📧 Email:</label>
                <input type="email" id="email" name="email" required placeholder="votre@email.com">
            </div>
            
            <div class="form-group">
                <label for="mdp">🔒 Mot de passe:</label>
                <input type="password" id="mdp" name="mdp" required placeholder="Votre mot de passe">
            </div>
            
            <button type="submit" class="btn-login">🚀 Se Connecter</button>
        </form>
        
        <a href="inscription.php" class="btn-inscription">📝 Inscription</a>
    </div>
</body>
</html>
