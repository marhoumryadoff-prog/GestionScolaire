<?php
session_start();
require_once 'connexion_base.php';
$db = new ConnexionBase();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mdp = trim($_POST['mdp']);
    $confirmation_mdp = trim($_POST['confirmation_mdp']);
    $role = trim($_POST['role']);

    if (empty($email) || empty($mdp) || empty($confirmation_mdp) || empty($role)) {
        $message = "❌ Veuillez remplir tous les champs";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "❌ Format d'email invalide";
    } elseif ($mdp !== $confirmation_mdp) {
        $message = "❌ Les mots de passe ne correspondent pas";
    } elseif (strlen($mdp) < 6) {
        $message = "❌ Le mot de passe doit contenir au moins 6 caractères";
    } else {
        try {
            $verif = $db->pdo->prepare("SELECT id FROM user WHERE email = ?");
            $verif->execute([$email]);
            
            if ($verif->rowCount() > 0) {
                $message = "❌ Cet email est déjà utilisé";
            } else {
                $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);
                $requete = $db->pdo->prepare("INSERT INTO user (email, mdp, role) VALUES (?, ?, ?)");
                $requete->execute([$email, $mdp_hash, $role]);
                
                $message = "✅ Inscription réussie! Vous pouvez maintenant vous connecter";
                $email = $mdp = $confirmation_mdp = '';
            }
        } catch (Exception $e) {
            $message = "❌ Erreur: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Gestion Scolarité</title>
    <style>
        body { font-family: Arial; margin: 0; padding: 0; background: #f5f5f5; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .inscription-container { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h1 { color: #333; text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #333; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn-inscrire { background: #28a745; color: white; border: none; padding: 12px; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; }
        .btn-connexion { background: #6c757d; color: white; border: none; padding: 12px; border-radius: 4px; cursor: pointer; width: 100%; margin-top: 10px; text-decoration: none; display: block; text-align: center; }
        .message { padding: 10px; margin: 15px 0; border-radius: 4px; text-align: center; font-weight: bold; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <div class="inscription-container">
        <h1>📝 Inscription</h1>
        
        <?php if ($message): ?>
            <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="inscription.php">
            <div class="form-group">
                <label for="email">📧 Email:</label>
                <input type="email" id="email" name="email" required placeholder="votre@email.com" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" autocomplete="off">
            </div>
            
            <div class="form-group">
                <label for="mdp">🔒 Mot de passe:</label>
                <input type="password" id="mdp" name="mdp" required placeholder="6 caractères minimum" autocomplete="new-password">
            </div>
            
            <div class="form-group">
                <label for="confirmation_mdp">🔁 Confirmation:</label>
                <input type="password" id="confirmation_mdp" name="confirmation_mdp" required placeholder="Répétez le mot de passe" autocomplete="new-password">
            </div>
            
            <div class="form-group">
                <label for="role">👤 Rôle:</label>
                <select id="role" name="role" required>
                    <option value="">Sélectionner un rôle</option>
                    <option value="User">User (Étudiant)</option>
                    <option value="Admin">Admin (Administrateur)</option>
                </select>
            </div>
            
            <button type="submit" class="btn-inscrire">📝 Créer un compte</button>
        </form>
        
        <a href="login.php" class="btn-connexion">🔐 Déjà un compte? Se connecter</a>
    </div>
</body>
</html>