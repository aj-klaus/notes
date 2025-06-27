<?php
// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the system's homepage (onss/index.php)
header('Location: /onss/index.php'); 
exit(); // Always exit after a header redirect
?>