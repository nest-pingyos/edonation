<?php
// Include session service (this handles session_start and session_name)
require_once __DIR__ . '/services/session.php';

// Securely logout and destroy session
logoutSession();

// Microsoft Logout URL
$msLogoutURL = "https://login.microsoftonline.com/cf81f1df-de59-4c29-91da-a2dfd04aa751/oauth2/v2.0/logout";
$postLogoutRedirect = APP_URL . "/admin/src/login.php";
$finalUrl = "{$msLogoutURL}?post_logout_redirect_uri=" . urlencode($postLogoutRedirect);

// Redirect to Microsoft Logout
header("Location: {$finalUrl}");
exit;
