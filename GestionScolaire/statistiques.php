<?php
require_once 'check_access.php';
require_once 'connexion_base.php';
$db = new ConnexionBase();

// =======================
// GENDER (Homme / Femme)
// =======================
$sql_sexe = "SELECT 
    CASE 
        WHEN civilite = 'M' THEN 'Homme'
        WHEN civilite IN ('Mme', 'Mlle') THEN 'Femme'
        ELSE NULL
    END as civilite,
    COUNT(*) as count 
    FROM etudiants 
    WHERE civilite IN ('M', 'Mme', 'Mlle')
    GROUP BY 
    CASE 
        WHEN civilite = 'M' THEN 'Homme'
        WHEN civilite IN ('Mme', 'Mlle') THEN 'Femme'
        ELSE NULL
    END";

$stats_sexe = $db->pdo->query($sql_sexe)->fetchAll(PDO::FETCH_ASSOC);
$filtered_stats = array_values($stats_sexe);

$sql_total = "SELECT COUNT(*) as total FROM etudiants WHERE civilite IN ('M', 'Mme', 'Mlle')";
$total_etudiants = $db->pdo->query($sql_total)->fetch(PDO::FETCH_ASSOC)['total'];

$labels_sexe = [];
$data_sexe = [];
$pourcentages_sexe = [];
$colors_sexe = ['#667eea', '#764ba2'];

foreach($filtered_stats as $stat) {
    $labels_sexe[] = $stat['civilite'];
    $data_sexe[] = (int)$stat['count'];
    $pourcentage = $total_etudiants > 0 ? round((($stat['count']) / $total_etudiants) * 100, 2) : 0;
    $pourcentages_sexe[] = $pourcentage;
}
$total_percentage = array_sum($pourcentages_sexe);
if (abs($total_percentage - 100) > 0.01 && !empty($pourcentages_sexe)) {
    $correction = 100 - $total_percentage;
    $pourcentages_sexe[0] += $correction;
    $pourcentages_sexe[0] = round($pourcentages_sexe[0], 2);
}

// ============================================
// ROLES (Admin / User) DISTRIBUTION
// ============================================
$sql_roles = "SELECT role, COUNT(*) as count FROM user GROUP BY role ORDER BY count DESC";
$stats_roles = [];
try {
    $stats_roles = $db->pdo->query($sql_roles)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // If table doesn't exist or query fails, keep empty
    $stats_roles = [];
}

$sql_total_users = "SELECT COUNT(*) as total FROM user";
$total_users = 0;
try {
    $total_users = (int)$db->pdo->query($sql_total_users)->fetch(PDO::FETCH_ASSOC)['total'];
} catch (Exception $e) {
    $total_users = 0;
}

$labels_roles = [];
$data_roles = [];
$pourcentages_roles = [];
$colors_roles = ['#FF6B6B', '#4ECDC4', '#ffd166', '#845ef7']; // fallback palette

foreach($stats_roles as $stat) {
    $labels_roles[] = $stat['role'];
    $data_roles[] = (int)$stat['count'];
    $pourcentage = $total_users > 0 ? round((($stat['count']) / $total_users) * 100, 2) : 0;
    $pourcentages_roles[] = $pourcentage;
}
$total_percentage_roles = array_sum($pourcentages_roles);
if (abs($total_percentage_roles - 100) > 0.01 && !empty($pourcentages_roles)) {
    $correction = 100 - $total_percentage_roles;
    $pourcentages_roles[0] += $correction;
    $pourcentages_roles[0] = round($pourcentages_roles[0], 2);
}

// ============================================
// Overall summary (students & users quick counts)
// ============================================
$sql_overall = "SELECT 
    (SELECT COUNT(*) FROM etudiants) as total_etudiants,
    (SELECT COUNT(*) FROM user) as total_users
";
$overall_stats = [];
try {
    $overall_stats = $db->pdo->query($sql_overall)->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $overall_stats = ['total_etudiants' => 0, 'total_users' => 0];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques - Répartition Sexe & Rôles</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --light: #f8f9fa;
            --dark: #2d3748;
            --border-radius: 12px;
            --box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;background:linear-gradient(135deg,#f4f7ff 0%, #f8fafc 100%);padding:24px}
        .container{max-width:1200px;margin:0 auto;background:white;padding:24px;border-radius:12px;box-shadow:var(--box-shadow)}
        .header{text-align:center;margin-bottom:20px}
        h1{color:var(--dark);margin-bottom:6px}
        p.lead{color:#6b7280;margin-bottom:18px}
        .summary-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:20px}
        .card{background:linear-gradient(135deg,#ffffff,#f8fafc);padding:16px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.05);text-align:center}
        .card .value{font-size:22px;font-weight:700;color:var(--primary)}
        .card .label{color:#6b7280;margin-top:6px}
        .charts{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-top:8px}
        .chart-box{background:var(--light);padding:16px;border-radius:10px;height:420px;display:flex;flex-direction:column}
        .chart-title{text-align:center;font-weight:700;color:var(--dark);margin-bottom:12px}
        .stat-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-top:14px}
        .stat-card{background:white;padding:12px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.06);text-align:center}
        .stat-number{font-size:20px;font-weight:700;color:var(--primary)}
        .stat-pct{font-weight:600;color:#0ea5a5}
        .navigation{margin-top:18px;text-align:center}
        .btn{display:inline-block;padding:10px 16px;border-radius:8px;text-decoration:none;background:linear-gradient(90deg,var(--primary),var(--secondary));color:white}
        @media (max-width:900px){.charts{grid-template-columns:1fr}}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Statistiques</h1>
            <p class="lead">Répartition des étudiants par sexe et répartition des utilisateurs par rôle (Admin / User)</p>
        </div>

        <div class="summary-grid">
            <div class="card">
                <div class="value"><?= intval($overall_stats['total_etudiants'] ?? 0) ?></div>
                <div class="label">Étudiants</div>
            </div>
            <div class="card">
                <div class="value"><?= intval($overall_stats['total_users'] ?? 0) ?></div>
                <div class="label">Utilisateurs</div>
            </div>
            <div class="card">
                <div class="value"><?= array_sum($data_sexe) ?: 0 ?></div>
                <div class="label">Étudiants (comptés sexe)</div>
            </div>
            <div class="card">
                <div class="value"><?= array_sum($data_roles) ?: 0 ?></div>
                <div class="label">Utilisateurs (par rôle)</div>
            </div>
        </div>

        <!-- Charts: Sexe & Roles -->
        <div class="charts">
            <div class="chart-box">
                <div class="chart-title">👥 Répartition par Sexe (Étudiants)</div>
                <div style="flex:1">
                    <canvas id="histogramSexe"></canvas>
                </div>
                <div class="stat-cards" style="margin-top:12px">
                    <?php foreach($filtered_stats as $i => $s): ?>
                        <div class="stat-card">
                            <div class="stat-number"><?= htmlspecialchars($s['count']) ?></div>
                            <div class="stat-pct"><?= number_format($pourcentages_sexe[$i] ?? 0, 2) ?>%</div>
                            <div class="stat-label"><?= htmlspecialchars($s['civilite']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="chart-box">
                <div class="chart-title">🔐 Répartition par Rôle (Utilisateurs)</div>
                <div style="flex:1">
                    <canvas id="histogramRoles"></canvas>
                </div>
                <div class="stat-cards" style="margin-top:12px">
                    <?php if (!empty($stats_roles)): ?>
                        <?php foreach($stats_roles as $i => $r): ?>
                            <div class="stat-card">
                                <div class="stat-number"><?= htmlspecialchars($r['count']) ?></div>
                                <div class="stat-pct"><?= number_format($pourcentages_roles[$i] ?? 0, 2) ?>%</div>
                                <div class="stat-label"><?= htmlspecialchars($r['role']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="stat-card">
                            <div class="stat-number">0</div>
                            <div class="stat-pct">0.00%</div>
                            <div class="stat-label">Pas de données</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="navigation">
            <button class="btn" onclick="window.print()">🖨️ Imprimer</button>
            <a href="menu_principal.php" class="btn" style="margin-left:12px">🏠 Menu Principal</a>
        </div>
    </div>

    <script>
        // Sexe charts data
        const labelsSexe = <?= json_encode($labels_sexe) ?>;
        const dataSexe = <?= json_encode($data_sexe) ?>;
        const colorsSexe = <?= json_encode($colors_sexe) ?>;
        const pctSexe = <?= json_encode($pourcentages_sexe) ?>;

        // Roles charts data
        const labelsRoles = <?= json_encode($labels_roles) ?>;
        const dataRoles = <?= json_encode($data_roles) ?>;
        const colorsRoles = <?= json_encode($colors_roles) ?>;
        const pctRoles = <?= json_encode($pourcentages_roles) ?>;

        // Histogramme Sexe
        new Chart(document.getElementById('histogramSexe').getContext('2d'), {
            type: 'bar',
            data: {
                labels: labelsSexe.map((l,i) => `${l} — ${pctSexe[i] ?? 0}%`),
                datasets: [{
                    label: "Nombre d'étudiants",
                    data: dataSexe,
                    backgroundColor: colorsSexe,
                    borderColor: colorsSexe,
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero:true, ticks:{stepSize:1} } }
            }
        });

        // Histogramme Roles
        new Chart(document.getElementById('histogramRoles').getContext('2d'), {
            type: 'bar',
            data: {
                labels: labelsRoles.map((l,i) => `${l} — ${pctRoles[i] ?? 0}%`),
                datasets: [{
                    label: "Nombre d'utilisateurs",
                    data: dataRoles,
                    backgroundColor: colorsRoles,
                    borderColor: colorsRoles,
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero:true, ticks:{stepSize:1} } }
            }
        });
    </script>
</body>
</html>