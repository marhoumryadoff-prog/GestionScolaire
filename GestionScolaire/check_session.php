<?php
// =====================================================
// check_session.php - Enforce Login for All Pages
// =====================================================
// This file is automatically included in every protected page
// It checks if the user is logged in, if not, redirects to login
// =====================================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// List of files that DON'T require login (public files)
$public_files = array(
    'login.php',
    'logout.php',
    'inscription.php',
    'connexion_base.php',
    'index.php'
);

// Get the current file being accessed
$current_file = basename($_SERVER['PHP_SELF']);

// Check if current file is protected
$is_public = in_array($current_file, $public_files);

// If file is NOT public AND user is NOT logged in, redirect to login
if (!$is_public && !isset($_SESSION['user_id'])) {
    // Redirect to login with return URL
    $return_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?redirect=" . $return_url);
    exit();
}
?>

