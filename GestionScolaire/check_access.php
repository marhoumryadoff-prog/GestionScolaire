<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// وظيفة للتحقق من صلاحيات Admin
function requireAdmin() {
    if ($_SESSION['user_role'] !== 'Admin') {
        header("Location: menu_principal.php");
        exit();
    }
}

// وظيفة للتحقق من صلاحيات User
function requireUser() {
    if ($_SESSION['user_role'] !== 'User') {
        header("Location: menu_principal.php");
        exit();
    }
}
?>