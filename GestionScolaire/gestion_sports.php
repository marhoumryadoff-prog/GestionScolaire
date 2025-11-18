<?php
require_once 'connexion_base.php';
$db = new ConnexionBase();

// جلب كل الرياضات الموجودة
$requete_sports = $db->pdo->query("SELECT * FROM sports ORDER BY libelle_sport");
$tous_sports = $requete_sports->fetchAll(PDO::FETCH_ASSOC);

// البحث عن رياضة بالرقم (للتعديل)
$sport_a_modifier = null;
$recherche_id = $_GET['recherche_id'] ?? null;

if ($recherche_id) {
    $id = intval($recherche_id);
    $requete = $db->pdo->prepare("SELECT * FROM sports WHERE id_sport = ?");
    $requete->execute([$id]);
    $sport_a_modifier = $requete->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion Sports</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-section { background: #e7f3ff; padding: 20px; margin: 15px 0; border-radius: 5px; }
        .liste-sports { background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .sport-item { padding: 8px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; }
        .recherche-form { background: #e9ecef; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .resultat { background: #d4edda; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .form-group { margin-bottom: 10px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="number"] { padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 8px 15px; margin-right: 5px; border: none; border-radius: 4px; cursor: pointer; color: white; }
        .btn-ajouter { background: #28a745; }
        .btn-modifier { background: #ffc107; color: black; }
        .btn-supprimer { background: #dc3545; }
        .btn-rechercher { background: #6f42c1; }
        .btn-selectionner { background: #007bff; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; font-weight: bold; }
        .success { background: #d4edda; color: #155724; }
        .erreur { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestion des Sports</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="message success">✅ <?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['erreur'])): ?>
            <div class="message erreur">❌ <?= htmlspecialchars($_GET['erreur']) ?></div>
        <?php endif; ?>

        <div class="form-section">
            <h3><?= $sport_a_modifier ? '✏️ Modifier le Sport (ID: ' . $sport_a_modifier['id_sport'] . ')' : '➕ Ajouter un nouveau sport' ?></h3>
            <form method="POST" action="traitement_sports.php">
                <input type="hidden" name="id_sport" value="<?= $sport_a_modifier['id_sport'] ?? '' ?>">
                
                <div class="form-group">
                    <label for="libelle_sport">Nom du sport:</label>
                    <input type="text" id="libelle_sport" name="libelle_sport" 
                           placeholder="Ex: Basketball, Tennis" required
                           value="<?= htmlspecialchars($sport_a_modifier['libelle_sport'] ?? '') ?>">
                </div>
                
                <?php if ($sport_a_modifier): ?>
                    <button type="submit" name="action" value="modifier" class="btn-modifier">Modifier Sport</button>
                    <a href="gestion_sports.php" class="btn-ajouter" style="text-decoration: none;">Nouvelle Saisie</a>
                <?php else: ?>
                    <button type="submit" name="action" value="ajouter" class="btn-ajouter">Ajouter Sport</button>
                <?php endif; ?>
            </form>
        </div>

        <div class="recherche-form">
            <h3>🔍 Rechercher un sport par ID:</h3>
            <form method="GET" action="gestion_sports.php">
                <input type="number" name="recherche_id" required placeholder="Entrez l'ID du sport" 
                       value="<?= htmlspecialchars($recherche_id ?? '') ?>"
                       style="width: 200px;">
                <button type="submit" class="btn-rechercher">Rechercher</button>
            </form>
            <?php if ($recherche_id && !$sport_a_modifier): ?>
                <div class="message erreur" style="margin-top: 10px;">❌ Aucun sport trouvé avec l'ID: <?= htmlspecialchars($recherche_id) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="liste-sports">
            <h3>📋 Liste des sports existants:</h3>
            <?php if (empty($tous_sports)): ?>
                <p>Aucun sport trouvé</p>
            <?php else: ?>
                <?php foreach($tous_sports as $sport): ?>
                    <div class="sport-item">
                        <span><strong>ID <?= $sport['id_sport'] ?>:</strong> <?= htmlspecialchars($sport['libelle_sport']) ?></span>
                        <div>
                            <a href="gestion_sports.php?recherche_id=<?= $sport['id_sport'] ?>" class="btn-selectionner" style="text-decoration: none; padding: 5px 10px;">
                                Sélectionner
                            </a>
                            <a href="traitement_sports.php?action=supprimer&id=<?= $sport['id_sport'] ?>" 
                               class="btn-supprimer"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer le sport : <?= $sport['libelle_sport'] ?> ?')"
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