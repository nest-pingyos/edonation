<?php
session_start();

// Clear CMU OAuth session
unset($_SESSION['backend_user']);

// Destroy entire session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
