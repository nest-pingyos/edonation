<?php
// Include session service (this handles session_start and session_name)
require_once __DIR__ . '/../services/session.php';

// Securely logout and destroy session
logoutSession();

// Redirect to login page
header("Location: login.php");
exit;
