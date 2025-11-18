<?php
require_once 'connexion_base.php';
// require_once 'check_access.php'; // Include if using the access control system
$db = new ConnexionBase();

// Fetch reference data
$requete_nationalites = $db->pdo->query("SELECT * FROM nationalites ORDER BY libelle_nationalite");
$nationalites = $requete_nationalites->fetchAll(PDO::FETCH_ASSOC);

$requete_sports = $db->pdo->query("SELECT * FROM sports ORDER BY libelle_sport");
$sports = $requete_sports->fetchAll(PDO::FETCH_ASSOC);

$requete_filieres = $db->pdo->query("SELECT * FROM filières ORDER BY CodeFilière");
$filieres = $requete_filieres->fetchAll(PDO::FETCH_ASSOC);

// Fixed lists for multi-selects
$platformes = ['Windows', 'Linux', 'macOS', 'Android', 'iOS', 'Web'];
$applications = ['Word', 'Excel', 'PowerPoint', 'Photoshop', 'Visual Studio', 'Eclipse', 'NetBeans', 'Autres'];

// Initialize student data and multi-select arrays
$etudiant_courant = null;
$platforms_etudiant = [];
$applications_etudiant = [];

if (isset($_GET['recherche_numero'])) {
    $numero_recherche = $_GET['recherche_numero'];
    $requete_etudiant = $db->pdo->prepare("
        SELECT e.*, n.libelle_nationalite, f.CodeFilière, f.Désignation as NomFiliere
        FROM etudiants e 
        LEFT JOIN nationalites n ON e.id_nationalite = n.id_nationalite 
        LEFT JOIN filières f ON e.FilièreId = f.Id
        WHERE e.numero_etudiant = ?
    ");
    $requete_etudiant->execute([$numero_recherche]);
    $etudiant_courant = $requete_etudiant->fetch(PDO::FETCH_ASSOC);
    
    if ($etudiant_courant) {
        if (!empty($etudiant_courant['platforme'])) {
            $platforms_etudiant = explode(',', $etudiant_courant['platforme']);
        }
        if (!empty($etudiant_courant['application'])) {
            $applications_etudiant = explode(',', $etudiant_courant['application']);
        }
        
        $requete_sports_etudiant = $db->pdo->prepare("
            SELECT s.id_sport 
            FROM etudiant_sports es 
            JOIN sports s ON es.id_sport = s.id_sport 
            WHERE es.id_etudiant = ?
        ");
        $requete_sports_etudiant->execute([$etudiant_courant['id_etudiant']]);
        $sports_etudiant = $requete_sports_etudiant->fetchAll(PDO::FETCH_COLUMN);
    }
}

// Logic for pre-selecting 'Française' if no student is loaded
$default_nationalite_id = null;
if (!$etudiant_courant) {
    foreach ($nationalites as $nat) {
        if (trim(mb_strtolower($nat['libelle_nationalite'], 'UTF-8')) === 'française') {
            $default_nationalite_id = $nat['id_nationalite'];
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Étudiants - TP02</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; margin-bottom: 20px; }
        .recherche-group { background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: inline-block; width: 150px; font-weight: bold; }
        input, select, textarea { padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px; }
        textarea { height: 60px; resize: vertical; }
        .buttons { margin-top: 20px; text-align: center; }
        button { padding: 10px 15px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; color: white; }
        .btn-ajouter { background: #28a745; }
        .btn-modifier { background: #ffc107; color: black !important; }
        .btn-supprimer { background: #dc3545; }
        .btn-rechercher { background: #17a2b8; }
        .btn-liste { background: #6c757d; }
        .btn-nouveau { background: #007bff; }
        .liens { text-align: center; margin-top: 20px; }
        .liens a { margin: 0 10px; text-decoration: none; color: #007bff; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; text-align: center; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .erreur { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #cce7ff; color: #004085; border: 1px solid #b3d7ff; }
        
        /* Styles for Checkbox Groups */
        .checkbox-group-container { 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            padding: 10px; 
            width: 300px;
            display: inline-block;
        }
        .checkbox-group-container label { 
            display: block; 
            width: auto; 
            font-weight: normal; 
            margin-bottom: 5px;
        }
        .checkbox-group-container input[type="checkbox"] { 
            width: auto; 
            margin-right: 8px;
        }
    </style>
</head>
<body>
    
    <div class="container">
        <h1>Gestion des Étudiants - TP_PAW</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="message success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['erreur'])): ?>
            <div class="message erreur"><?= htmlspecialchars($_GET['erreur']) ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['info'])): ?>
            <div class="message info"><?= htmlspecialchars($_GET['info']) ?></div>
        <?php endif; ?>
        
        <div class="recherche-group">
            <form method="GET" action="formulaire_principal.php" style="display: flex; align-items: center; gap: 10px;">
                <label for="recherche_immediate" style="width: auto; margin: 0;">Rechercher un étudiant par numéro:</label>
                <input type="text" id="recherche_immediate" name="recherche_numero" placeholder="Entrez le numéro" style="width: 200px;">
                <button type="submit" style="background: #17a2b8;">🔍 Rechercher</button>
                <?php if ($etudiant_courant): ?>
                    <span style="color: #28a745; font-weight: bold;">✓ Étudiant trouvé: <?= htmlspecialchars($etudiant_courant['nom_etudiant']) ?> <?= htmlspecialchars($etudiant_courant['prenom_etudiant']) ?></span>
                <?php elseif (isset($_GET['recherche_numero']) && !$etudiant_courant): ?>
                    <span style="color: #dc3545; font-weight: bold;">✗ Aucun étudiant trouvé avec ce numéro</span>
                <?php endif; ?>
            </form>
        </div>
        
        <form method="POST" action="traitement_etudiant.php" enctype="multipart/form-data">
            
            <?php if ($etudiant_courant): ?>
                <input type="hidden" name="id_etudiant" value="<?= $etudiant_courant['id_etudiant'] ?>">
                <?php if (!empty($etudiant_courant['photo'])): ?>
                    <input type="hidden" name="photo_actuelle" value="<?= htmlspecialchars($etudiant_courant['photo']) ?>">
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="numero">Numéro:</label>
                <input type="text" id="numero" name="numero" required 
                       value="<?= $etudiant_courant ? htmlspecialchars($etudiant_courant['numero_etudiant']) : '' ?>"
                       <?= $etudiant_courant ? 'readonly' : 'autofocus' ?>>
            </div>
            
            <div class="form-group">
                <label for="nom">Nom:</label>
                <input type="text" id="nom" name="nom" required 
                       value="<?= $etudiant_courant ? htmlspecialchars($etudiant_courant['nom_etudiant']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="prenom">Prénom:</label>
                <input type="text" id="prenom" name="prenom" required 
                       value="<?= $etudiant_courant ? htmlspecialchars($etudiant_courant['prenom_etudiant']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label>Civilité:</label>
                <div style="display: flex; gap: 15px; align-items: center; max-width: 400px; height: 38px;">
                    <?php 
                        $current_civilite = $etudiant_courant ? $etudiant_courant['civilite'] : '';
                        $civilite_options = [
                            'M' => 'Monsieur', 
                            'Mme' => 'Madame', 
                            'Mlle' => 'Mademoiselle' 
                        ];
                    ?>
                    <?php foreach($civilite_options as $value => $label): ?>
                        <label style="width: auto; font-weight: normal; margin: 0; display: flex; align-items: center;">
                            <input type="radio" name="civilite" value="<?= $value ?>" 
                                   <?= ($current_civilite == $value) ? 'checked' : '' ?>
                                   style="width: auto; margin-right: 5px; height: auto;">
                            <?= $label ?>
                        </label>
                    <?php endforeach; ?>
                    
                    <?php if (empty($current_civilite) || !in_array($current_civilite, array_keys($civilite_options))): ?>
                        <input type="radio" name="civilite" value="" style="display: none;" checked>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="date_naissance">Date Naissance:</label>
                <input type="date" id="date_naissance" name="date_naissance" 
                       value="<?= $etudiant_courant ? $etudiant_courant['date_naissance'] : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="adresse">Adresse:</label>
                <textarea id="adresse" name="adresse"><?= $etudiant_courant ? htmlspecialchars($etudiant_courant['adresse']) : '' ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="localisation">Localisation:</label>
                <input type="text" id="localisation" name="localisation" 
                       value="<?= $etudiant_courant ? htmlspecialchars($etudiant_courant['localisation']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label>Plateforme:</label>
                <div class="checkbox-group-container">
                    <?php foreach($platformes as $platforme): ?>
                        <label>
                            <input type="checkbox" name="platforme[]" value="<?= $platforme ?>" 
                                <?= in_array($platforme, $platforms_etudiant) ? 'checked' : '' ?>>
                            <?= $platforme ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <small style="display: block; color: #666;">Cochez une ou plusieurs plateformes</small>
            </div>
            
            <div class="form-group">
                <label>Application:</label>
                <div class="checkbox-group-container">
                    <?php foreach($applications as $application): ?>
                        <label>
                            <input type="checkbox" name="application[]" value="<?= $application ?>" 
                                <?= in_array($application, $applications_etudiant) ? 'checked' : '' ?>>
                            <?= $application ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <small style="display: block; color: #666;">Cochez une ou plusieurs applications</small>
            </div>
         
            <div class="form-group">
                <label for="photo">Photo de l'étudiant:</label>
                <input type="file" id="photo" name="photo" accept="image/*" onchange="previewImage(this)">
                <div id="new-photo-preview" style="display: none; margin-top: 10px; text-align: center; background: #f8f9fa; padding: 15px; border-radius: 10px;">
                    <h4 style="color: #17a2b8;">🔄 Nouvelle photo sélectionnée</h4>
                    <img id="photo-preview" src="" alt="Aperçu de la nouvelle photo" width="200" height="200" style="border-radius: 10px; border: 3px solid #17a2b8; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                    <br>
                    <small style="color: #17a2b8; font-weight: bold;">Aperçu de la nouvelle photo</small>
                </div>
                
                <?php if ($etudiant_courant && !empty($etudiant_courant['photo'])): ?>
                <div id="current-photo" style="margin-top: 10px; text-align: center; background: #f8f9fa; padding: 15px; border-radius: 10px;">
                    <h4 style="color: #28a745;">✅ Photo actuelle de l'étudiant</h4>
                    <img src="uploads/<?= htmlspecialchars($etudiant_courant['photo']) ?>" alt="Photo actuelle" width="200" height="200" style="border-radius: 10px; border: 3px solid #28a745; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                    <br>
                    <small style="color: #28a745; font-weight: bold;">Photo de <?= htmlspecialchars($etudiant_courant['prenom_etudiant']) ?> <?= htmlspecialchars($etudiant_courant['nom_etudiant']) ?></small>
                    <br>
                    <button type="button" onclick="removeCurrentPhoto()" style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; margin-top: 5px; cursor: pointer;">❌ Supprimer cette photo</button>
                </div>
                <?php endif; ?>
            </div>

            <script>
            // JavaScript for image preview and removal logic (omitted for brevity, assume it's complete)
            function previewImage(input) {
                const preview = document.getElementById('photo-preview');
                const previewContainer = document.getElementById('new-photo-preview');
                const currentPhoto = document.getElementById('current-photo');
                
                if (input.files && input.files[0]) {
                    const file = input.files[0];
                    if (file.size > 2 * 1024 * 1024) {
                        alert('❌ Le fichier est trop volumineux. Taille maximale: 2MB');
                        input.value = '';
                        previewContainer.style.display = 'none';
                        return;
                    }
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    if (!validTypes.includes(file.type)) {
                        alert('❌ Format de fichier non supporté. Utilisez JPG, PNG ou GIF.');
                        input.value = '';
                        previewContainer.style.display = 'none';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        previewContainer.style.display = 'block';
                        if (currentPhoto) {
                            currentPhoto.style.display = 'none';
                        }
                    }
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.style.display = 'none';
                }
            }

            function removeCurrentPhoto() {
                if (confirm('Êtes-vous sûr de vouloir supprimer la photo actuelle ?')) {
                    const currentPhoto = document.getElementById('current-photo');
                    const photoInput = document.getElementById('photo');
                    if (!document.querySelector('input[name="supprimer_photo"]')) {
                        const deleteInput = document.createElement('input');
                        deleteInput.type = 'hidden';
                        deleteInput.name = 'supprimer_photo';
                        deleteInput.value = '1';
                        photoInput.parentNode.appendChild(deleteInput);
                    }
                    if (currentPhoto) {
                        currentPhoto.style.display = 'none';
                    }
                    photoInput.disabled = false;
                    alert('✅ La photo sera supprimée lors de l\'enregistrement');
                }
            }

            document.querySelector('form').addEventListener('submit', function(e) {
                const photoInput = document.getElementById('photo');
                if (photoInput.files.length > 0) {
                    const file = photoInput.files[0];
                    if (file.size > 2 * 1024 * 1024) {
                        e.preventDefault();
                        alert('❌ La photo est trop volumineuse. Taille maximale: 2MB');
                        return false;
                    }
                }
            });
            </script>
            
            <div class="form-group">
                <label for="nationalite">Nationalité:</label>
                <select id="nationalite" name="nationalite">
                    <option value="">Sélectionner</option>
                    <?php foreach($nationalites as $nat): ?>
                        <option value="<?= $nat['id_nationalite'] ?>" 
                            <?php 
                                $selected = '';
                                if ($etudiant_courant) {
                                    if ($etudiant_courant['id_nationalite'] == $nat['id_nationalite']) {
                                        $selected = 'selected';
                                    }
                                } elseif ($default_nationalite_id !== null && $nat['id_nationalite'] == $default_nationalite_id) {
                                    $selected = 'selected';
                                }
                            ?>
                            <?= $selected ?>>
                            <?= $nat['libelle_nationalite'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="filiere">Filière d'inscription:</label>
                <select id="filiere" name="filiere">
                    <option value="">Sélectionner une filière</option>
                    <?php
                    foreach($filieres as $filiere): ?>
                        <option value="<?= $filiere['Id'] ?>" 
                            <?= ($etudiant_courant && $etudiant_courant['FilièreId'] == $filiere['Id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($filiere['CodeFilière']) ?> - <?= htmlspecialchars($filiere['Désignation']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="sports">Sports:</label>
                <select id="sports" name="sports[]" multiple size="5" style="height: 120px;">
                    <?php foreach($sports as $sport): ?>
                        <option value="<?= $sport['id_sport'] ?>"
                            <?= (isset($sports_etudiant) && in_array($sport['id_sport'], $sports_etudiant)) ? 'selected' : '' ?>>
                            <?= $sport['libelle_sport'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div style="font-size: 12px; color: #666; margin-top: 5px;">
                    ⓘ Maintenez la touche Ctrl (Windows) ou Cmd (Mac) pour sélectionner plusieurs sports
                </div>
            </div>
            
            <div class="buttons">
                <button type="submit" name="action" value="ajouter" class="btn-ajouter">Enregistrer</button>
                <?php if ($etudiant_courant): ?>
                    <button type="submit" name="action" value="modifier" class="btn-modifier">Modifier</button>
                    <button type="submit" name="action" value="supprimer" class="btn-supprimer" 
                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet étudiant?')">Supprimer</button>
                    
                    <button type="button" 
                            onclick="window.location.href='bulletin_etudiant.php?numero=<?= urlencode($etudiant_courant['numero_etudiant']) ?>'" 
                            style="background: #008080; color: white; padding: 10px 15px; border-radius: 4px; border: none; cursor: pointer;">
                        📋 Voir le Bulletin
                    </button>
                <?php endif; ?>
                
                <button type="button" onclick="afficherListe()" class="btn-liste">🧑‍🎓 Afficher Liste</button>
                <button type="button" onclick="initialiserFormulaire()" class="btn-nouveau">Nouveau</button>
            </div>
        </form>

        <div class="liens">
            <a href="menu_principal.php">Retour menu</a>
            <a href="gestion_sports.php">Gérer les sports</a>
            <a href="gestion_nationalites.php">Gérer les nationalités</a>
            <a href="frmFilières.php">Gérer les filières</a>
            <a href="liste_etudiants.php">Voir la liste des étudiants</a>
        </div>
    </div>

    <script>
        function afficherListe() {
            window.location.href = 'liste_etudiants.php';
        }

        function initialiserFormulaire() {
            window.location.href = 'formulaire_principal.php';
        }
    </script>
</body>
</html>