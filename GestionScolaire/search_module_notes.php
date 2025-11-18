<?php
require_once 'config.php';

// Strict: Only Users can access this page
if (!$is_user) {
    header("Location: menu_principal.php");
    exit();
}

$db = new ConnexionBase();
$message = '';
$message_type = 'info';
$module_info = null;
$module_id = '';

// Search by module ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['module_id'])) {
    $module_id = trim($_POST['module_id']);
    
    if (empty($module_id)) {
        $message = "❌ Veuillez entrer l'ID du module";
        $message_type = 'error';
    } else {
        // Get module details
        $requete_module = $db->pdo->prepare("
            SELECT 
                m.Id,
                m.CodeModule,
                m.DésignationModule,
                m.Coefficient,
                f.CodeFilière,
                f.Désignation as nom_filiere,
                COUNT(DISTINCT n.Num_Etudiant) as nombre_etudiants,
                AVG(n.Note) as moyenne_notes,
                MIN(n.Note) as note_min,
                MAX(n.Note) as note_max
            FROM modules m
            LEFT JOIN filières f ON m.FilièreId = f.Id
            LEFT JOIN Notes n ON m.CodeModule = n.Code_Module
            WHERE m.Id = ?
            GROUP BY m.Id, m.CodeModule, m.DésignationModule, m.Coefficient, f.CodeFilière, f.Désignation
        ");
        $requete_module->execute([$module_id]);
        $module_info = $requete_module->fetch(PDO::FETCH_ASSOC);
        
        if (!$module_info) {
            $message = "❌ Module non trouvé avec l'ID: " . htmlspecialchars($module_id);
            $message_type = 'error';
        } else {
            $message = "✅ Module trouvé avec succès";
            $message_type = 'success';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche Détails Module</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .nav-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .nav-links a:hover {
            color: #764ba2;
        }
        
        .logout-link {
            background: #ff6b6b;
            color: white !important;
            padding: 8px 16px;
            border-radius: 4px;
            margin: 0;
        }
        
        .logout-link:hover {
            background: #ff5252;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #1e3c72;
            text-align: center;
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .search-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin: 30px 0;
        }
        
        .search-section h2 {
            margin: 0 0 20px 0;
            font-size: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: white;
        }
        
        input {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        button {
            flex: 1;
            padding: 12px;
            background: white;
            color: #667eea;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        button:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
        }
        
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .message.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .message.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        /* Module Details Card */
        .module-details-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            overflow: hidden;
            margin: 30px 0;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .card-header h2 {
            margin: 0;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .card-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        
        .card-content {
            padding: 40px;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .detail-item {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-top: 4px solid #667eea;
        }
        
        .detail-label {
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        
        .detail-value {
            font-size: 24px;
            font-weight: 700;
            color: #1e3c72;
            word-break: break-word;
        }
        
        .detail-item.highlight {
            border-top: 4px solid #28a745;
            background: #d4edda;
        }
        
        .detail-item.highlight .detail-value {
            color: #28a745;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .divider {
            height: 1px;
            background: #e0e0e0;
            margin: 30px 0;
        }
        
        .stats-section {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .stats-section h3 {
            color: #1e3c72;
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
        }
        
        .stat-box {
            background: white;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            border-left: 4px solid #667eea;
        }
        
        .stat-number {
            font-size: 22px;
            font-weight: 700;
            color: #667eea;
            margin: 8px 0;
        }
        
        .stat-name {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 16px;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #999;
            font-size: 12px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            .card-content {
                padding: 20px;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 24px;
            }

            .card-header h2 {
                font-size: 20px;
            }
        }

        @media print {
            body {
                background: white;
            }
            
            .navbar {
                display: none;
            }
            
            .container {
                box-shadow: none;
            }

            .search-section {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <div class="navbar">
        <div class="nav-links">
            <a href="menu_principal.php">🏠 Accueil</a>
            <a href="student_bulletin.php">📋 Mon Bulletin</a>
            <a href="search_module_notes.php">🔍 Détails Module</a>
        </div>
        <a href="logout.php" class="logout-link">🚪 Déconnexion</a>
    </div>
    
    <!-- Main Container -->
    <div class="container">
        <h1>🔍 Détails du Module</h1>
        <p class="subtitle">Consultez les informations détaillées d'un module</p>
        
        <!-- Search Section -->
        <div class="search-section">
            <h2>Rechercher un Module par ID</h2>
            
            <form method="POST">
                <div class="form-group">
                    <label for="module_id">ID du Module (1, 2, 3, 4, ...):</label>
                    <input 
                        type="number" 
                        id="module_id" 
                        name="module_id" 
                        placeholder="Entrez l'ID du module" 
                        value="<?php echo htmlspecialchars($module_id); ?>"
                        min="1"
                        required
                    >
                </div>
                
                <div class="button-group">
                    <button type="submit">🔎 Rechercher</button>
                    <button type="reset">🔄 Réinitialiser</button>
                </div>
            </form>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($module_info): ?>
            <!-- Module Details Card -->
            <div class="module-details-card">
                <div class="card-header">
                    <h2>📚 <?php echo htmlspecialchars($module_info['DésignationModule']); ?></h2>
                    <p>Code: <?php echo htmlspecialchars($module_info['CodeModule']); ?></p>
                </div>
                
                <div class="card-content">
                    <!-- Main Details -->
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">ID Module</div>
                            <div class="detail-value"><?php echo htmlspecialchars($module_info['Id']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Code Module</div>
                            <div class="detail-value"><?php echo htmlspecialchars($module_info['CodeModule']); ?></div>
                        </div>
                        
                        <div class="detail-item highlight">
                            <div class="detail-label">Coefficient</div>
                            <div class="detail-value"><?php echo htmlspecialchars($module_info['Coefficient']); ?></div>
                        </div>
                        
                        <div class="detail-item full-width">
                            <div class="detail-label">Nom Complet du Module</div>
                            <div class="detail-value"><?php echo htmlspecialchars($module_info['DésignationModule']); ?></div>
                        </div>
                        
                        <div class="detail-item full-width">
                            <div class="detail-label">Filière Associée</div>
                            <div class="detail-value">
                                <?php 
                                if ($module_info['CodeFilière']) {
                                    echo htmlspecialchars($module_info['CodeFilière']) . ' - ' . htmlspecialchars($module_info['nom_filiere']);
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Statistics Section -->
                    <?php if ($module_info['nombre_etudiants'] > 0): ?>
                    <div class="divider"></div>
                    
                    <div class="stats-section">
                        <h3>📊 Statistiques</h3>
                        <div class="stats-grid">
                            <div class="stat-box">
                                <div class="stat-name">Nombre d'Étudiants</div>
                                <div class="stat-number"><?php echo htmlspecialchars($module_info['nombre_etudiants']); ?></div>
                            </div>
                            
                            <div class="stat-box">
                                <div class="stat-name">Moyenne des Notes</div>
                                <div class="stat-number"><?php echo number_format($module_info['moyenne_notes'], 2); ?></div>
                            </div>
                            
                            <div class="stat-box">
                                <div class="stat-name">Note Minimale</div>
                                <div class="stat-number"><?php echo number_format($module_info['note_min'], 2); ?></div>
                            </div>
                            
                            <div class="stat-box">
                                <div class="stat-name">Note Maximale</div>
                                <div class="stat-number"><?php echo number_format($module_info['note_max'], 2); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Print Button -->
                    <div style="text-align: center; margin-top: 30px;">
                        <button onclick="window.print();" style="background: #667eea; color: white; display: inline-block; width: auto; padding: 12px 30px;">
                            🖨️ Imprimer les Détails
                        </button>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <div class="no-data" style="background: #cfe9ff; color: #084298; padding: 30px; border-radius: 6px; margin-top: 20px;">
                <p>ℹ️ Entrez un ID de module pour consulter ses détails.</p>
                <p style="margin-top: 10px; font-size: 12px;">Les IDs disponibles sont numérotés de 1 à N selon les modules existants dans le système.</p>
            </div>
        <?php endif; ?>
        
        <!-- Footer -->
        <div class="footer">
            <p>Système de Gestion Scolaire © 2025 | Accès réservé aux utilisateurs connectés</p>
            <p>Consultation effectuée le <?php echo date('d/m/Y à H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>