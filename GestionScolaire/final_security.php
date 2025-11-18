<?php
// تأمين جميع الملفات دفعة واحدة
$files_to_secure = [
    'formulaire_principal.php' => 'requireAdmin();',
    'frmEnseignants.php' => 'requireAdmin();',
    'frmModules.php' => 'requireAdmin();', 
    'frmBulletins.php' => 'requireAdmin();',
    'gestion_nationalites.php' => 'requireAdmin();',
    'gestion_sports.php' => 'requireAdmin();',
    'frmFilières.php' => 'requireAdmin();',
    'gestion_users.php' => 'requireAdmin();',
    'liste_etudiants.php' => '',
    'liste_notes_etudiants.php' => '',
    'bulletin_notes_etudiant.php' => '',
    'pv_global.php' => '',
    'statistiques.php' => '',
    'user_bulletin.php' => 'requireUser();'
];

foreach ($files_to_secure as $file => $access_type) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // إزالة أي session_start قديم
        $content = preg_replace('/<\?php\s*session_start\(\);\s*\?>/', '', $content);
        $content = preg_replace('/<\?php\s*session_start\(\);/', '', $content);
        
        // إضافة التحقق الجديد
        $security_code = "<?php\nrequire_once 'check_access.php';\n";
        if (!empty($access_type)) {
            $security_code .= $access_type . "\n";
        }
        $security_code .= "?>\n";
        
        file_put_contents($file, $security_code . $content);
        echo "✅ Sécurisé: $file\n";
    }
}

echo "🎉 Toutes les pages sont sécurisées avec succès!";
?>