<?php
// Start session if it hasn't been started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection (This line is kept for consistency if other scripts might use $dbh here,
// but for a pure session-based auth check, it's not strictly needed here if roles are trusted from session.)
require_once __DIR__ . '/../../includes/dbconnection.php';

// Check if a user is logged in AND their role is specifically 'admin'
// This relies on 'user/signin.php' correctly setting $_SESSION['uid'] and $_SESSION['role']
// upon successful login and redirecting non-admins away from admin pages.
if (!isset($_SESSION['uid']) || empty($_SESSION['uid']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // If not logged in, or if logged in but not as an admin,
    // destroy any incomplete/invalid session and redirect to the unified login page.
    session_destroy(); 
    // Redirect to the unified login page with an admin-specific unauthorized error message
    header('Location: /onss/user/signin.php?error=unauthorized_admin'); 
    exit();
}

// If execution reaches this point, the user is an authenticated administrator
// based on their session data (uid and role).
?>