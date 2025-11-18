<?php
require_once 'connexion_base.php';
// Include check_access if you are using the session redirection system
// require_once 'check_access.php'; 
$db = new ConnexionBase();

// جلب بيانات الدول أولاً
$pays = $db->pdo->query("SELECT * FROM nationalites ORDER BY libelle_nationalite")->fetchAll(PDO::FETCH_ASSOC);

// البحث عن أستاذ
$enseignant_courant = null;
$message = '';
$type_message = '';

$numero_recherche = $_GET['recherche_numero'] ?? '';

// Handle messages from redirection
if (isset($_GET['success'])) {
    $message = htmlspecialchars($_GET['success']);
    $type_message = 'success';
} elseif (isset($_GET['erreur'])) {
    $message = htmlspecialchars($_GET['erreur']);
    $type_message = 'error';
}

// Fetch teacher data if searching by number
if ($numero_recherche) {
    try {
        $requete_enseignant = $db->pdo->prepare("
            SELECT e.*, n.libelle_nationalite 
            FROM enseignants e 
            LEFT JOIN nationalites n ON e.PaysId = n.id_nationalite 
            WHERE e.Numéro = ?
        ");
        $requete_enseignant->execute([$numero_recherche]);
        $enseignant_courant = $requete_enseignant->fetch(PDO::FETCH_ASSOC);

        if (!$enseignant_courant) {
            $message = "✗ Aucun enseignant trouvé avec ce numéro.";
            $type_message = 'error';
        }
    } catch (PDOException $e) {
        $message = "Erreur de base de données: " . $e->getMessage();
        $type_message = 'error';
    }
}

// Define fixed lists for selection fields
$grades = ['Assistant', 'MAB', 'MAA', 'MCB', 'MCA', 'Professeur'];
$specialites = ['Informatique', 'Mathématiques', 'Anglais', 'autres'];

// Start of CRUD Processing (Modified to match the provided structure and use radio buttons)
$afficher_liste = isset($_GET['afficher_liste']) && $_GET['afficher_liste'] == '1';

if ($_POST) {
    // --- Variable extraction matching the new form names ---
    $id = $_POST['id'] ?? '';
    $numero = $_POST['txtNumero'] ?? '';
    // **KEY CHANGE:** Retrieve Civilité from the radio button name
    $civilite = $_POST['Civilité'] ?? ''; 
    $nom = $_POST['txtNom'] ?? '';
    $prenom = $_POST['txtPrenom'] ?? '';
    $adresse = $_POST['txtAdresse'] ?? '';
    $dateNaissance = $_POST['txtDateNaissance'] ?? '';
    $lieuNaissance = $_POST['txtLieuNaissance'] ?? '';
    $paysId = $_POST['cmbPays'] ?? '';
    $grade = $_POST['cmbGrade'] ?? '';
    $specialite = $_POST['cmbSpecialite'] ?? '';
    
    // --- CRUD Logic (remains consistent with your structure) ---
    if (isset($_POST['Enregistrer'])) {
        try {
            $verif = $db->pdo->prepare("SELECT Id FROM enseignants WHERE Numéro = ?");
            $verif->execute([$numero]);
            
            if ($verif->rowCount() > 0) {
                $message = "Numéro d'enseignant déjà existant";
                $type_message = 'error';
            } else {
                $sql = "INSERT INTO enseignants (Numéro, Civilité, Nom, Prénom, Adresse, DateNaissance, LieuNaissance, PaysId, Grade, Spécialité) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute([$numero, $civilite, $nom, $prenom, $adresse, $dateNaissance, $lieuNaissance, $paysId, $grade, $specialite]);
                
                $message = "Enseignant enregistré avec succès";
                $type_message = 'success';
                $enseignant_courant = null;
            }
        } catch (PDOException $e) {
            $message = "Erreur: " . $e->getMessage();
            $type_message = 'error';
        }
    }
    
    if (isset($_POST['Modifier'])) {
        if (empty($id)) {
            $message = "Veuillez sélectionner un enseignant à modifier";
            $type_message = 'error';
        } else {
            try {
                $sql = "UPDATE enseignants SET Civilité=?, Nom=?, Prénom=?, Adresse=?, DateNaissance=?, LieuNaissance=?, PaysId=?, Grade=?, Spécialité=? WHERE Id=?";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute([$civilite, $nom, $prenom, $adresse, $dateNaissance, $lieuNaissance, $paysId, $grade, $specialite, $id]);
                
                $message = "Enseignant modifié avec succès";
                $type_message = 'success';
            } catch (PDOException $e) {
                $message = "Erreur: " . $e->getMessage();
                $type_message = 'error';
            }
        }
    }
    
    if (isset($_POST['Supprimer'])) {
        if (empty($id)) {
            $message = "Veuillez sélectionner un enseignant à supprimer";
            $type_message = 'error';
        } else {
            try {
                $sql = "DELETE FROM enseignants WHERE Id = ?";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute([$id]);
                
                $message = "Enseignant supprimé avec succès";
                $type_message = 'success';
                $enseignant_courant = null;
            } catch (PDOException $e) {
                $message = "Erreur: " . $e->getMessage();
                $type_message = 'error';
            }
        }
    }
}

// Data fetching for 'Afficher Liste'
if ($afficher_liste) {
    try {
        $enseignants = $db->pdo->query("SELECT e.*, n.libelle_nationalite 
                                         FROM enseignants e 
                                         LEFT JOIN nationalites n ON e.PaysId = n.id_nationalite 
                                         ORDER BY e.Nom, e.Prénom")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $enseignants = [];
        if (empty($message)) {
            $message = "Erreur chargement: " . $e->getMessage();
            $type_message = 'error';
        }
    }
} else {
    $enseignants = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Enseignants</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .recherche-group { background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .form-container { background: #f8f9fa; padding: 25px; border-radius: 8px; margin-bottom: 25px; border: 2px solid #e9ecef; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #dee2e6; padding: 12px; text-align: left; }
        th { background-color: #2c3e50; color: white; font-weight: bold; }
        .form-group { margin-bottom: 20px; display: flex; align-items: center; }
        label { display: inline-block; width: 200px; font-weight: bold; color: #2c3e50; margin-right: 15px; }
        input[type="text"], input[type="date"], select, textarea { padding: 10px; width: 400px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px; }
        textarea { height: 80px; resize: vertical; }
        /* Style for radio button container */
        .radio-group { 
            display: flex; 
            gap: 15px; 
            align-items: center; 
            flex: 1; /* Allows it to occupy the remaining horizontal space */
        }
        .radio-group label {
             width: auto;
             font-weight: normal; 
             margin-right: 0;
        }
        .radio-group input[type="radio"] {
            width: auto;
            margin-right: 5px;
        }

        button { padding: 12px 25px; margin: 8px; border: none; border-radius: 5px; cursor: pointer; color: white; font-weight: bold; font-size: 14px; }
        .btn-enregistrer { background: #28a745; }
        .btn-modifier { background: #ffc107; color: black; }
        .btn-supprimer { background: #dc3545; }
        .btn-afficher { background: #17a2b8; }
        .btn-rechercher { background: #6f42c1; }
        .message { padding: 15px; margin: 15px 0; border-radius: 5px; text-align: center; font-weight: bold; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        h2 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 15px; text-align: center; }
        h3 { color: #34495e; margin-top: 30px; padding-bottom: 10px; border-bottom: 2px solid #bdc3c7; }
        .buttons-container { text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #dee2e6; }
        .liste-container { margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Gestion des Enseignants</h2>
        
        <?php if ($message): ?>
            <div class="message <?= $type_message ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <div class="recherche-group">
            <form method="GET" action="frmEnseignants.php" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <label for="recherche_immediate" style="width: auto; margin: 0; font-weight: bold; color: #2c3e50;">
                    🔍 Rechercher un enseignant par numéro:
                </label>
                <input type="text" id="recherche_immediate" name="recherche_numero" 
                        placeholder="Entrez le numéro enseignant" 
                        value="<?= isset($_GET['recherche_numero']) ? htmlspecialchars($_GET['recherche_numero']) : '' ?>"
                        style="width: 250px; padding: 10px; border: 1px solid #ced4da; border-radius: 4px;">
                <button type="submit" class="btn-rechercher">Rechercher</button>
                
                <?php if ($enseignant_courant): ?>
                    <span style="color: #28a745; font-weight: bold; font-size: 14px;">
                        ✓ Enseignant trouvé: <?= htmlspecialchars($enseignant_courant['Nom']) ?> <?= htmlspecialchars($enseignant_courant['Prénom']) ?>
                    </span>
                <?php elseif (isset($_GET['recherche_numero']) && !empty($_GET['recherche_numero']) && !$enseignant_courant): ?>
                    <span style="color: #dc3545; font-weight: bold; font-size: 14px;">
                        ✗ Aucun enseignant trouvé avec ce numéro
                    </span>
                <?php endif; ?>
            </form>
        </div>

        <div class="form-container">
            <form method="post">
                <input type="hidden" name="id" id="idField" value="<?= $enseignant_courant ? $enseignant_courant['Id'] : '' ?>">
                
                <div class="form-group">
                    <label for="txtNumero">Numéro:</label>
                    <input type="text" id="txtNumero" name="txtNumero" required 
                            value="<?= $enseignant_courant ? htmlspecialchars($enseignant_courant['Numéro']) : '' ?>"
                            <?= $enseignant_courant ? 'readonly' : '' ?>>
                </div>
                
                <div class="form-group">
                    <label>Civilité:</label>
                    <div class="radio-group">
                        <?php 
                            $current_civilite = $enseignant_courant['Civilité'] ?? '';
                            $civilite_options = [
                                'Mr' => 'Monsieur', 
                                'Mme' => 'Madame', 
                                'Mlle' => 'Mademoiselle' 
                            ];
                        ?>
                        <?php foreach($civilite_options as $value => $label): ?>
                            <label>
                                <input type="radio" name="Civilité" value="<?= $value ?>" 
                                    <?= ($current_civilite == $value) ? 'checked' : '' ?>
                                    required>
                                <?= $label ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="txtNom">Nom:</label>
                    <input type="text" id="txtNom" name="txtNom" required 
                            value="<?= $enseignant_courant ? htmlspecialchars($enseignant_courant['Nom']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="txtPrenom">Prénom:</label>
                    <input type="text" id="txtPrenom" name="txtPrenom" required 
                            value="<?= $enseignant_courant ? htmlspecialchars($enseignant_courant['Prénom']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="txtAdresse">Adresse:</label>
                    <textarea id="txtAdresse" name="txtAdresse"><?= $enseignant_courant ? htmlspecialchars($enseignant_courant['Adresse']) : '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="txtDateNaissance">Date Naissance:</label>
                    <input type="date" id="txtDateNaissance" name="txtDateNaissance" 
                            value="<?= $enseignant_courant ? $enseignant_courant['DateNaissance'] : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="txtLieuNaissance">Lieu Naissance:</label>
                    <input type="text" id="txtLieuNaissance" name="txtLieuNaissance" 
                            value="<?= $enseignant_courant ? htmlspecialchars($enseignant_courant['LieuNaissance']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="cmbPays">Pays:</label>
                    <select id="cmbPays" name="cmbPays" required>
                        <option value="">Sélectionner un pays</option>
                        <?php foreach($pays as $p): ?>
                            <option value="<?= $p['id_nationalite'] ?>" 
                                <?= ($enseignant_courant && $enseignant_courant['PaysId'] == $p['id_nationalite']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['libelle_nationalite']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="cmbGrade">Grade:</label>
                    <select id="cmbGrade" name="cmbGrade" required>
                        <option value="">Sélectionner</option>
                        <?php foreach($grades as $grade): ?>
                            <option value="<?= $grade ?>" 
                                <?= ($enseignant_courant && $enseignant_courant['Grade'] == $grade) ? 'selected' : '' ?>>
                                <?= $grade ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="cmbSpecialite">Spécialité:</label>
                    <select id="cmbSpecialite" name="cmbSpecialite" required>
                        <option value="">Sélectionner</option>
                        <?php foreach($specialites as $specialite): ?>
                            <option value="<?= $specialite ?>" 
                                <?= ($enseignant_courant && $enseignant_courant['Spécialité'] == $specialite) ? 'selected' : '' ?>>
                                <?= $specialite ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="buttons-container">
                    <button type="submit" name="Enregistrer" class="btn-enregistrer">Enregistrer</button>
                    <button type="submit" name="Modifier" class="btn-modifier">Modifier</button>
                    <button type="submit" name="Supprimer" class="btn-supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet enseignant?')">Supprimer</button>
                    <button type="button" class="btn-afficher" onclick="afficherListe()">Afficher Liste</button>
                </div>
            </form>
        </div>

        <?php if ($afficher_liste): ?>
        <div class="liste-container">
            <h3>📋 Liste des Enseignants</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Numéro</th>
                        <th>Civilité</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Grade</th>
                        <th>Spécialité</th>
                        <th>Pays</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($enseignants) > 0): ?>
                        <?php foreach($enseignants as $enseignant): ?>
                        <tr>
                            <td><?= $enseignant['Id'] ?></td>
                            <td><?= htmlspecialchars($enseignant['Numéro']) ?></td>
                            <td><?= htmlspecialchars($enseignant['Civilité']) ?></td>
                            <td><?= htmlspecialchars($enseignant['Nom']) ?></td>
                            <td><?= htmlspecialchars($enseignant['Prénom']) ?></td>
                            <td><?= htmlspecialchars($enseignant['Grade']) ?></td>
                            <td><?= htmlspecialchars($enseignant['Spécialité']) ?></td>
                            <td><?= htmlspecialchars($enseignant['libelle_nationalite']) ?></td>
                            <td>
                                <button onclick="editEnseignant(<?= $enseignant['Id'] ?>, '<?= $enseignant['Numéro'] ?>', '<?= $enseignant['Civilité'] ?>', '<?= $enseignant['Nom'] ?>', '<?= $enseignant['Prénom'] ?>', '<?= htmlspecialchars($enseignant['Adresse']) ?>', '<?= $enseignant['DateNaissance'] ?>', '<?= htmlspecialchars($enseignant['LieuNaissance']) ?>', <?= $enseignant['PaysId'] ?>, '<?= $enseignant['Grade'] ?>', '<?= $enseignant['Spécialité'] ?>')" 
                                        style="background: #3498db; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">
                                    Sélectionner
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; color: #666; font-style: italic; padding: 20px;">
                                Aucun enseignant enregistré
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <div style="margin-top: 25px; text-align: center;">
            <a href="menu_principal.php" style="background: #6c757d; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                ← Retour au menu principal
            </a>
        </div>
    </div>

    <script>
    function editEnseignant(id, numero, civilite, nom, prenom, adresse, dateNaissance, lieuNaissance, paysId, grade, specialite) {
        document.getElementById('idField').value = id;
        document.getElementById('txtNumero').value = numero;
        
        // **KEY CHANGE:** Select the correct radio button
        const civiliteRadios = document.getElementsByName('Civilité');
        for (let i = 0; i < civiliteRadios.length; i++) {
            if (civiliteRadios[i].value === civilite) {
                civiliteRadios[i].checked = true;
            }
        }

        document.getElementById('txtNom').value = nom;
        document.getElementById('txtPrenom').value = prenom;
        document.getElementById('txtAdresse').value = adresse || '';
        document.getElementById('txtDateNaissance').value = dateNaissance || '';
        document.getElementById('txtLieuNaissance').value = lieuNaissance || '';
        document.getElementById('cmbPays').value = paysId;
        document.getElementById('cmbGrade').value = grade;
        document.getElementById('cmbSpecialite').value = specialite;
        document.querySelector('.form-container').scrollIntoView({ behavior: 'smooth' });
    }
    
    function afficherListe() {
        window.location.href = 'frmEnseignants.php?afficher_liste=1';
    }
    </script>
</body>
</html>