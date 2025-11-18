<?php
require_once 'connexion_base.php';
$db = new ConnexionBase();

// --- If no filière is yet chosen (Initial screen) ---
if (!isset($_GET['filiere']) || empty($_GET['filiere'])) {
    // Fetch available filières
    $sql_filieres = "SELECT DISTINCT CodeFilière, Désignation FROM filières ORDER BY CodeFilière";
    $filieres = $db->pdo->query($sql_filieres)->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Choisir une filière</title>
        <style>
            :root {
                --primary-color: #2563eb;
                --secondary-color: #3b82f6;
                --accent-color: #60a5fa;
                --success-color: #22c55e;
                --warning-color: #f59e0b;
                --danger-color: #ef4444;
                --text-primary: #1e293b;
                --text-secondary: #475569;
                --bg-primary: #f8fafc;
                --bg-secondary: #f1f5f9;
                --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
                --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
                --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
            }

            body { 
                font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
                margin: 0;
                padding: 0;
                background: var(--bg-primary);
                color: var(--text-primary);
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .container { 
                width: 100%;
                max-width: 800px;
                margin: 2rem auto;
                padding: 2rem;
            }

            .header {
                text-align: center;
                margin-bottom: 3rem;
            }

            h2 { 
                color: var(--text-primary);
                font-size: 1.875rem;
                font-weight: 700;
                margin-bottom: 1rem;
            }

            .filiere-list { 
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1rem;
                margin: 2rem 0;
            }

            .filiere-btn { 
                background: white;
                padding: 1.5rem;
                border-radius: 0.75rem;
                text-decoration: none;
                color: var(--text-primary);
                box-shadow: var(--shadow-md);
                transition: all 0.2s;
                border: 1px solid var(--bg-secondary);
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .filiere-btn:hover { 
                transform: translateY(-2px);
                box-shadow: var(--shadow-lg);
                border-color: var(--primary-color);
            }

            .filiere-icon {
                background: var(--bg-secondary);
                width: 3rem;
                height: 3rem;
                border-radius: 0.5rem;
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--primary-color);
                font-size: 1.5rem;
            }

            .filiere-info {
                flex: 1;
            }

            .filiere-code {
                font-weight: 600;
                color: var(--text-primary);
                margin-bottom: 0.25rem;
            }

            .filiere-name {
                color: var(--text-secondary);
                font-size: 0.875rem;
            }

            .divider {
                text-align: center;
                margin: 2rem 0;
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .divider::before,
            .divider::after {
                content: "";
                flex: 1;
                height: 1px;
                background: var(--bg-secondary);
            }

            .divider span {
                color: var(--text-secondary);
                font-weight: 500;
                padding: 0 1rem;
            }

            .search-form {
                background: white;
                padding: 2rem;
                border-radius: 0.75rem;
                box-shadow: var(--shadow-md);
                max-width: 400px;
                margin: 0 auto;
            }

            .search-form label {
                display: block;
                margin-bottom: 0.5rem;
                color: var(--text-primary);
                font-weight: 500;
            }

            .search-form input[type="text"] {
                width: 100%;
                padding: 0.75rem;
                border: 1px solid var(--bg-secondary);
                border-radius: 0.5rem;
                margin-bottom: 1rem;
                font-size: 1rem;
                transition: all 0.2s;
            }

            .search-form input[type="text"]:focus {
                outline: none;
                border-color: var(--primary-color);
                box-shadow: 0 0 0 3px var(--accent-color);
            }

            .search-form input[type="submit"] {
                width: 100%;
                padding: 0.75rem;
                background: var(--primary-color);
                color: white;
                border: none;
                border-radius: 0.5rem;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s;
            }

            .search-form input[type="submit"]:hover {
                background: var(--secondary-color);
                transform: translateY(-2px);
            }

            .navigation {
                margin-top: 2rem;
                text-align: center;
            }

            .btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.75rem 1.5rem;
                border-radius: 0.5rem;
                text-decoration: none;
                font-weight: 500;
                transition: all 0.2s;
                color: var(--text-secondary);
            }

            .btn:hover {
                color: var(--primary-color);
            }

            @media (max-width: 640px) {
                .container {
                    padding: 1rem;
                }

                .filiere-list {
                    grid-template-columns: 1fr;
                }

                .search-form {
                    padding: 1.5rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>PV Global - Sélection de Filière</h2>
                <p style="color: var(--text-secondary);">Sélectionnez une filière pour voir le PV global des étudiants</p>
            </div>
            
            <div class="filiere-list">
                <?php foreach($filieres as $filiere): ?>
                    <a href="?filiere=<?= urlencode($filiere['CodeFilière']) ?>" class="filiere-btn">
                        <div class="filiere-icon">📚</div>
                        <div class="filiere-info">
                            <div class="filiere-code"><?= htmlspecialchars($filiere['CodeFilière']) ?></div>
                            <div class="filiere-name"><?= htmlspecialchars($filiere['Désignation']) ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="divider">
                <span>OU</span>
            </div>
            
            <form method="GET" class="search-form">
                <label>Rechercher une filière par code</label>
                <input type="text" name="filiere" placeholder="Ex: 3ISIL, L3, Master..." required>
                <input type="submit" value="Voir le PV Global">
            </form>
            
            <div class="navigation">
                <a href="menu_principal.php" class="btn">
                    <span>⬅</span>
                    <span>Retour au menu principal</span>
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// --- If the filière is chosen ---
$filiere = $_GET['filiere'];

// 🔹 Récupération des étudiants and their weighted average
$sql = "
    SELECT 
        e.numero_etudiant,
        e.nom_etudiant,
        e.prenom_etudiant,
        f.CodeFilière,
        f.Désignation as libelle_filiere,
        -- KEY FIX: Calculate the Weighted Average (SUM(Note * Coeff) / SUM(Coeff))
        ROUND(SUM(n.Note * m.Coefficient) / SUM(m.Coefficient), 2) AS moyenne_ponderee,
        SUM(m.Coefficient) AS total_coeff_notes
    FROM etudiants e
    INNER JOIN filières f ON e.FilièreId = f.Id
    LEFT JOIN Notes n ON e.numero_etudiant = n.Num_Etudiant
    -- NEW JOIN: Join to modules (m) to access the Coefficient
    LEFT JOIN modules m ON n.Code_Module = m.CodeModule
    -- Filter out rows where the note or coefficient is invalid/missing
    WHERE (f.CodeFilière = ? OR f.Désignation LIKE ?)
    GROUP BY e.numero_etudiant, e.nom_etudiant, e.prenom_etudiant, f.CodeFilière, f.Désignation
    ORDER BY moyenne_ponderee DESC
";

$stmt = $db->pdo->prepare($sql);
$stmt->execute([$filiere, "%$filiere%"]);
$etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Calcul des statistiques globales
// Use the calculated weighted average column for global stats
$moyennes = array_column($etudiants, 'moyenne_ponderee');

// Filter out NULL or non-numeric averages (where SUM(Coeff) was 0)
$validMoyennes = array_values(array_filter($moyennes, 'is_numeric'));
$moyenneGlobale = count($validMoyennes) ? round(array_sum($validMoyennes) / count($validMoyennes), 2) : 0;
$moyenneMin = count($validMoyennes) ? min($validMoyennes) : 0;
$moyenneMax = count($validMoyennes) ? max($validMoyennes) : 0;

// Fetch filière info for header display
$sql_filiere_info = "SELECT CodeFilière, Désignation FROM filières WHERE CodeFilière = ? OR Désignation LIKE ?";
$stmt_info = $db->pdo->prepare($sql_filiere_info);
$stmt_info->execute([$filiere, "%$filiere%"]);
$filiere_info = $stmt_info->fetch(PDO::FETCH_ASSOC);

$nom_filiere_affichage = $filiere_info ? $filiere_info['CodeFilière'] . ' - ' . $filiere_info['Désignation'] : $filiere;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>PV - <?= htmlspecialchars($filiere) ?></title>
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #3b82f6;
            --accent-color: #60a5fa;
            --success-color: #22c55e;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --text-primary: #1e293b;
            --text-secondary: #475569;
            --bg-primary: #f8fafc;
            --bg-secondary: #f1f5f9;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
        }

        body { 
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
            background: var(--bg-primary);
            color: var(--text-primary);
        }

        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 1.5rem;
        }

        /* Header Styles */
        .header { 
            background: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            text-align: center;
        }

        .header h1 {
            color: var(--text-primary);
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .header h2, .header h3 {
            color: var(--text-secondary);
            margin: 0.5rem 0;
            font-weight: 500;
        }

        /* Breadcrumbs */
        .breadcrumbs {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 0;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .breadcrumbs a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumbs a:hover {
            color: var(--secondary-color);
        }

        /* Filiere Info */
        .filiere-info {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            text-align: left;
            box-shadow: var(--shadow-md);
        }

        .filiere-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .filiere-icon {
            background: var(--primary-color);
            color: white;
            width: 3rem;
            height: 3rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .filiere-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        /* Stats Cards */
        .statistiques {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: var(--shadow-md);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 3rem;
            height: 3rem;
            background: var(--bg-secondary);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 0.75rem;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            margin: 2rem 0;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        th {
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--bg-secondary);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tbody tr {
            transition: background-color 0.2s;
        }

        tbody tr:hover {
            background-color: var(--bg-secondary);
        }

        /* Student Count */
        .student-count {
            background: white;
            padding: 1rem;
            border-radius: 0.75rem;
            margin: 1.5rem 0;
            text-align: center;
            box-shadow: var(--shadow-sm);
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .student-count strong {
            color: var(--text-primary);
            font-weight: 600;
        }

        /* Navigation */
        .navigation {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 0.75rem;
            box-shadow: var(--shadow-md);
        }

        .empty-state-icon {
            font-size: 3rem;
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }

        .empty-state h3 {
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: var(--text-secondary);
            max-width: 400px;
            margin: 0 auto;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .statistiques {
                grid-template-columns: 1fr;
            }

            .table-container {
                overflow-x: auto;
            }

            .navigation {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
    </head>
    <body>

        <header class="header">
            <div class="header-content">
                <h1>Faculté des Sciences Exactes</h1>
                <h2>Département d'Informatique</h2>
                <h3>Année Universitaire 2024/2025</h3>
            </div>
        </header>

        <div class="container">
            <div class="breadcrumbs">
                <a href="menu_principal.php">Menu Principal</a>
                <span>›</span>
                <a href="pv_global.php">PV Global</a>
                <?php if (isset($filiere_info)): ?>
                    <span>›</span>
                    <span><?= htmlspecialchars($filiere_info['CodeFilière']) ?></span>
                <?php endif; ?>
            </div>

            <div class="filiere-info">
                <div class="filiere-header">
                    <div class="filiere-icon">📚</div>
                    <div>
                        <div class="filiere-title">
                            <?php if (isset($filiere_info)): ?>
                                <?= htmlspecialchars($nom_filiere_affichage) ?>
                            <?php else: ?>
                                Sélectionnez une filière
                            <?php endif; ?>
                        </div>
                        <div style="color: var(--text-secondary);">Procès-Verbal Global</div>
                    </div>
                </div>
            </div>

            <?php if (count($etudiants) > 0): ?>
                <div class="student-count">
                    <strong>📊 <?= count($etudiants) ?></strong> étudiants au total | 
                    <strong>📝 <?= count($validMoyennes) ?></strong> étudiants avec notes
                </div>            <div class="statistiques">
                    <div class="stat-card">
                        <div class="stat-icon">📊</div>
                        <div class="stat-value"><?= number_format($moyenneGlobale, 2) ?></div>
                        <div class="stat-label">Moyenne Générale</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📉</div>
                        <div class="stat-value"><?= number_format($moyenneMin, 2) ?></div>
                        <div class="stat-label">Note Minimale</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📈</div>
                        <div class="stat-value"><?= number_format($moyenneMax, 2) ?></div>
                        <div class="stat-label">Note Maximale</div>
                    </div>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>N° Étudiant</th>
                                <th>Noms et Prénoms</th>
                                <th>Filière</th>
                                <th style="text-align: center;">Moyenne Générale</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($etudiants as $index => $etudiant): ?>
                            <tr>
                                <td><?= htmlspecialchars($etudiant['numero_etudiant']) ?></td>
                                <td>
                                    <div style="font-weight: 500;"><?= htmlspecialchars($etudiant['nom_etudiant']) ?></div>
                                    <div style="color: var(--text-secondary); font-size: 0.875rem;"><?= htmlspecialchars($etudiant['prenom_etudiant']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($etudiant['CodeFilière']) ?></td>
                                <td style="text-align: center;">
                                    <?php if ($etudiant['moyenne_ponderee'] !== NULL): ?>
                                        <span style="font-weight: 600; color: <?= $etudiant['moyenne_ponderee'] >= 10 ? 'var(--success-color)' : 'var(--danger-color)' ?>">
                                            <?= number_format($etudiant['moyenne_ponderee'], 2) ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--text-secondary);">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">🔍</div>
                <h3>Aucun étudiant trouvé</h3>
                <p>Aucun étudiant n'a été trouvé pour la filière "<?= htmlspecialchars($filiere) ?>". Vérifiez le code de la filière ou assurez-vous qu'il y a des étudiants inscrits.</p>
            </div>
        <?php endif; ?>

        <div class="navigation">
            <a href="?filiere=" class="btn btn-primary">
                <span>⬅</span>
                <span>Choisir une autre filière</span>
            </a>
            <a href="menu_principal.php" class="btn btn-secondary">
                <span>🏠</span>
                <span>Retour au menu principal</span>
            </a>
        </div>
    </div>

    </body>
    </html>