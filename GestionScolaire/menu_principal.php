<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Menu Principal - Gestion Scolaire</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Base Reset & Typography */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        /* User Info Bar */
        .user-bar {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-badge {
            background: rgba(255,255,255,0.2);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .user-badge.admin {
            background: #ff6b6b;
        }
        
        .user-badge.user {
            background: #4ecdc4;
        }
        
        .logout-btn {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .logout-btn:hover {
            background: #ff5252;
        }
        
        /* Navigation Bar Styling */
        .navbar { 
            background: linear-gradient(135deg, #1e3c72, #2a5298); 
            padding: 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        
        .navbar ul {
            list-style: none;
            display: flex;
            flex-wrap: wrap;
        }
        
        .dropdown { 
            display: none; 
            position: absolute; 
            background: #ffffff;
            min-width: 250px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.25);
            border-radius: 0 0 6px 6px;
            z-index: 1000;
            top: 100%;
            left: 0;
            overflow: hidden;
            border-top: 3px solid #007bff;
            animation: slideDown 0.3s ease-out;
        }
        
        .navbar li { 
            position: relative; 
            margin-right: 0;
        }
        
        .navbar a { 
            color: white; 
            text-decoration: none; 
            padding: 15px 20px; 
            display: block;
            transition: background 0.3s ease, color 0.3s ease;
            font-weight: 600;
            font-size: 15px;
        }
        
        .navbar a:hover { 
            background: #4a6491;
            color: #f0f0f0;
        }
        
        .dropdown a { 
            color: #333; 
            padding: 12px 20px;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.2s ease;
            font-weight: 400;
        }
        
        .dropdown a:hover { 
            background: #e9ecef;
            color: #007bff;
            padding-left: 25px;
        }
        
        .dropdown a:last-child {
            border-bottom: none;
        }
        
        li:hover .dropdown { 
            display: block; 
        }
        
        /* Quick Access Cards */
        .quick-access-section {
            padding: 40px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            text-align: center;
            color: white;
        }
        
        .quick-access-section h2 {
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .quick-access-section p {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        
        .quick-access-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .quick-access-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            transition: all 0.3s ease;
            cursor: pointer;
            color: #333;
            text-decoration: none;
            display: block;
        }
        
        .quick-access-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.2);
        }
        
        .card-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .quick-access-card h3 {
            color: #1e3c72;
            margin-bottom: 10px;
            font-size: 20px;
        }
        
        .quick-access-card p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        
        /* Content Area */
        .content { 
            padding: 60px 20px; 
            text-align: center;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        h1 {
            color: #1e3c72;
            margin-bottom: 20px;
            font-weight: 700;
            font-size: 48px;
        }
        
        .content p {
            color: #666;
            font-size: 18px;
            line-height: 1.8;
            margin-bottom: 30px;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .admin-only, .user-only {
            display: none;
        }
        
        .admin-only.visible, .user-only.visible {
            display: block;
        }

        @media (max-width: 768px) {
            .navbar ul {
                flex-direction: column;
            }

            .quick-access-grid {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>
    <!-- User Info Bar -->
    <div class="user-bar">
        <div class="user-info">
            <div>
                <span style="font-weight: 600;">Connecté en tant que:</span>
                <span style="margin-left: 10px;"><?php echo htmlspecialchars($user_email); ?></span>
            </div>
            <div class="user-badge <?php echo strtolower($user_role); ?>">
                <?php echo htmlspecialchars($user_role); ?>
            </div>
        </div>
        <a href="logout.php" class="logout-btn">Déconnexion</a>
    </div>
    
    <!-- Navigation Bar -->
    <div class="navbar">
        <ul>
            <li>
                <a href="menu_principal.php">🏠 Accueil</a>
            </li>
            
            <?php if ($user_role === 'Admin'): ?>
            <li>
                <a href="#">📊 Gestion des Données</a>
                <ul class="dropdown">
                    <li><a href="formulaire_principal.php">👥 Étudiants</a></li>
                    <li><a href="frmEnseignants.php">👨‍🏫 Enseignants</a></li>
                    <li><a href="frmModules.php">📚 Modules</a></li>
                    <li><a href="frmBulletins.php">📋 Bulletins de Notes</a></li>
                </ul>
            </li>
            
            <li>
                <a href="#">⚙️ Tables de Référence</a>
                <ul class="dropdown">
                    <li><a href="gestion_nationalites.php">🌍 Nationalité</a></li>
                    <li><a href="gestion_sports.php">⚽ Sports</a></li>
                    <li><a href="frmFilières.php">🎓 Filières</a></li>
                </ul>
            </li>
            
            <li>
                <a href="liste_etudiants.php">📑 Liste des Étudiants</a>
            </li>
            
            <li>
                <a href="liste_note.php">📈 Liste des Notes</a>
            </li>
            
            <li>
                <a href="pv_global.php">📊 PV Global</a>
            </li>
            
            <li>
                <a href="#">🔐 Administration</a>
                <ul class="dropdown">
                    <li><a href="gestion_users.php">👤 Gestion des Utilisateurs</a></li>
                    <li><a href="statistiques.php">📈 Statistiques</a></li>
                </ul>
            </li>
            
            <?php else: ?>
            <li>
                <a href="student_bulletin.php">📋 Mon Bulletin</a>
            </li>
            <li>
                <a href="search_module_notes.php">🔍 Recherche par Module</a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
    
    <!-- Quick Access Section -->
    <div class="quick-access-section">
        <h2>Accès Rapide</h2>
        <p><?php echo ($user_role === 'Admin') ? 'Bienvenue Administrateur' : 'Bienvenue'; ?> - Utilisez les raccourcis ci-dessous pour un accès rapide</p>
        
        <div class="quick-access-grid">
            <?php if ($user_role === 'Admin'): ?>
            <!-- Admin Access Cards -->
            <a href="liste_etudiants.php" class="quick-access-card">
                <div class="card-icon">👥</div>
                <h3>Voir Étudiants</h3>
                <p>Consultez la liste complète des étudiants</p>
            </a>
            
            <a href="liste_note.php" class="quick-access-card">
                <div class="card-icon">📝</div>
                <h3>Gestion Notes</h3>
                <p>Gérez les notes et résultats</p>
            </a>
            
            <a href="formulaire_principal.php" class="quick-access-card">
                <div class="card-icon">✏️</div>
                <h3>Ajouter Étudiant</h3>
                <p>Enregistrer un nouvel étudiant</p>
            </a>
            
            <a href="gestion_users.php" class="quick-access-card">
                <div class="card-icon">🔐</div>
                <h3>Gestion Utilisateurs</h3>
                <p>Gérez les comptes administrateur</p>
            </a>
            
            <a href="frmEnseignants.php" class="quick-access-card">
                <div class="card-icon">👨‍🏫</div>
                <h3>Enseignants</h3>
                <p>Gérez les enseignants de l'établissement</p>
            </a>
            
            <a href="statistiques.php" class="quick-access-card">
                <div class="card-icon">📊</div>
                <h3>Statistiques</h3>
                <p>Consultez les statistiques du système</p>
            </a>
            
            <a href="pv_global.php" class="quick-access-card">
                <div class="card-icon">📋</div>
                <h3>PV Global</h3>
                <p>Procès-verbaux complets</p>
            </a>
            
            <a href="frmFilières.php" class="quick-access-card">
                <div class="card-icon">🎓</div>
                <h3>Filières</h3>
                <p>Gestion des filières scolaires</p>
            </a>
            
            <?php else: ?>
            <!-- User Access Cards -->
            <a href="student_bulletin.php" class="quick-access-card">
                <div class="card-icon">📋</div>
                <h3>Mon Bulletin</h3>
                <p>Consultez votre bulletin de notes complet</p>
            </a>
            
            <a href="search_module_notes.php" class="quick-access-card">
                <div class="card-icon">🔍</div>
                <h3>Recherche par Module</h3>
                <p>Trouvez votre note pour un module spécifique</p>
            </a>
            
            <a href="logout.php" class="quick-access-card" style="background: #ffe6e6;">
                <div class="card-icon">🚪</div>
                <h3>Déconnexion</h3>
                <p>Quitter votre session</p>
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="content">
        <h1>Système de Gestion Scolaire</h1>
        <p>
            Bienvenue dans votre espace de gestion scolaire. 
            Utilisez le menu de navigation ci-dessus ou les raccourcis d'accès rapide pour 
            <?php echo ($user_role === 'Admin') ? 'gérer les étudiants, les enseignants, les modules et consulter les bulletins de notes.' : 'consulter votre bulletin de notes et rechercher vos grades par module.'; ?>
        </p>
    </div>
</body>
</html>