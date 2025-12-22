<?php
/**
 * Logout Handler
 */
require_once 'partials/main.php';

// Destroy session
logoutSession();

// Redirect to login
header('Location: auth-signin.php');
exit();
