<?php
// =====================================================
// config.php - Global Configuration & Auto-Protection
// =====================================================
// This file should be included at the TOP of EVERY PHP file
// It provides database connection AND enforces login protection
// =====================================================

// Start session if not already running
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================
// DATABASE CONNECTION
// =====================================================
require_once 'connexion_base.php';

// =====================================================
// AUTO LOGIN PROTECTION
// =====================================================

// List of files that can be accessed WITHOUT login
define('PUBLIC_FILES', array(
    'login.php',
    'logout.php',
    'inscription.php',
    'index.php'
));

// Get current file name
$current_file = basename($_SERVER['PHP_SELF']);

// If user is NOT logged in AND current file is NOT public
if (!isset($_SESSION['user_id']) && !in_array($current_file, PUBLIC_FILES)) {
    // Redirect to login
    header("Location: login.php");
    exit();
}

// =====================================================
// SET USER VARIABLES (available in all pages)
// =====================================================
$user_id = $_SESSION['user_id'] ?? null;
$user_email = $_SESSION['user_email'] ?? null;
$user_role = $_SESSION['user_role'] ?? 'User';
$is_admin = ($user_role === 'Admin');
$is_user = ($user_role === 'User');
$is_logged_in = isset($_SESSION['user_id']);

// =====================================================
// HELPER FUNCTIONS
// =====================================================

/**
 * Require Admin access
 */
function requireAdmin() {
    global $is_admin;
    if (!$is_admin) {
        header("Location: menu_principal.php");
        exit();
    }
}

/**
 * Require User access
 */
function requireUser() {
    global $is_logged_in;
    if (!$is_logged_in) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Redirect to login
 */
function redirectToLogin() {
    header("Location: login.php");
    exit();
}

/**
 * Redirect to menu
 */
function redirectToMenu() {
    header("Location: menu_principal.php");
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get user role
 */
function getUserRole() {
    return $_SESSION['user_role'] ?? 'User';
}

/**
 * Get user email
 */
function getUserEmail() {
    return $_SESSION['user_email'] ?? null;
}

?>
