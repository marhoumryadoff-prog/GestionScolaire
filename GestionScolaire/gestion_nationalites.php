<?php
require_once 'connexion_base.php';
$db = new ConnexionBase();

// جلب كل الجنسيات الموجودة
$requete_nationalites = $db->pdo->query("SELECT * FROM nationalites ORDER BY libelle_nationalite");
$toutes_nationalites = $requete_nationalites->fetchAll(PDO::FETCH_ASSOC);

// البحث عن جنسية بالرقم (للتعديل)
$nationalite_a_modifier = null;
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $requete = $db->pdo->prepare("SELECT * FROM nationalites WHERE id_nationalite = ?");
    $requete->execute([$id]);
    $nationalite_a_modifier = $requete->fetch(PDO::FETCH_ASSOC);
    if (!$nationalite_a_modifier) {
        header("Location: gestion_nationalites.php?erreur=Nationalité non trouvée.");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion Nationalités</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-section { background: #e7f3ff; padding: 20px; margin: 15px 0; border-radius: 5px; }
        .liste-nationalites { background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .nationalite-item { padding: 8px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; }
        .form-group { margin-bottom: 10px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"] { padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 8px 15px; margin-right: 5px; border: none; border-radius: 4px; cursor: pointer; color: white; }
        .btn-ajouter { background: #28a745; }
        .btn-modifier { background: #ffc107; color: black; }
        .btn-supprimer { background: #dc3545; }
        .btn-selectionner { background: #007bff; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; font-weight: bold; }
        .success { background: #d4edda; color: #155724; }
        .erreur { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestion des Nationalités</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="message success">✅ <?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['erreur'])): ?>
            <div class="message erreur">❌ <?= htmlspecialchars($_GET['erreur']) ?></div>
        <?php endif; ?>

        <div class="form-section">
            <h3><?= $nationalite_a_modifier ? '✏️ Modifier la Nationalité' : '➕ Ajouter une nouvelle Nationalité' ?></h3>
            <form method="POST" action="traitement_nationalites.php">
                <input type="hidden" name="id_nationalite" value="<?= $nationalite_a_modifier['id_nationalite'] ?? '' ?>">
                
                <div class="form-group">
                    <label for="libelle_nationalite">Nom de la nationalité:</label>
                    <input type="text" id="libelle_nationalite" name="libelle_nationalite" 
                           placeholder="Ex: Française, Marocaine" required
                           value="<?= htmlspecialchars($nationalite_a_modifier['libelle_nationalite'] ?? '') ?>">
                </div>
                
                <?php if ($nationalite_a_modifier): ?>
                    <button type="submit" name="action" value="modifier" class="btn-modifier">Modifier Nationalité</button>
                    <a href="gestion_nationalites.php" class="btn-ajouter" style="text-decoration: none;">Nouvelle Saisie</a>
                <?php else: ?>
                    <button type="submit" name="action" value="ajouter" class="btn-ajouter">Ajouter Nationalité</button>
                <?php endif; ?>
            </form>
        </div>

        <div class="liste-nationalites">
            <h3>📋 Liste des Nationalités existantes:</h3>
            <?php if (empty($toutes_nationalites)): ?>
                <p>Aucune nationalité trouvée</p>
            <?php else: ?>
                <?php foreach($toutes_nationalites as $nat): ?>
                    <div class="nationalite-item">
                        <span><strong>ID <?= $nat['id_nationalite'] ?>:</strong> <?= htmlspecialchars($nat['libelle_nationalite']) ?></span>
                        <div>
                            <a href="gestion_nationalites.php?id=<?= $nat['id_nationalite'] ?>" class="btn-selectionner" style="text-decoration: none; padding: 5px 10px;">
                                Sélectionner
                            </a>
                            <a href="traitement_nationalites.php?action=supprimer&id=<?= $nat['id_nationalite'] ?>" 
                               class="btn-supprimer"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer la nationalité : <?= $nat['libelle_nationalite'] ?> ?')"
                               style="text-decoration: none; padding: 5px 10px;">
                                Supprimer
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <br>
        <a href="menu_principal.php" style="background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;">← Retour au menu principal</a>
    </div>
</body>
</html>