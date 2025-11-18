<?php
/**
 * Global Index File: Enforces login, then redirects to Main Menu
 * This ensures the application always requires authentication first
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // User not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// User is logged in, redirect to main menu
$target_file = 'menu_principal.php';

// Check if the target file exists to prevent a redirect loop error
if (file_exists($target_file)) {
    // Perform a server-side redirect to menu
    header("Location: " . $target_file);
    exit();
} else {
    // Fallback error message if the menu file is missing
    echo "Erreur: Le fichier menu_principal.php est introuvable. Veuillez vérifier l'installation.";
}
?>