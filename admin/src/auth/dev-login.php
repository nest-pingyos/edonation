<?php
/**
 * Developer Login Handler
 * Sets a mock session for development testing
 */
require_once __DIR__ . '/../services/session.php';

if (defined('APP_ENV') && APP_ENV === 'development') {
    $_SESSION['user'] = [
        'id' => 1,
        'email' => 'dev@edonation.internal',
        'name' => 'Developer Super Admin',
        'role' => 'super_admin'
    ];
    $_SESSION['_fingerprint'] = getSessionFingerprint();
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();

    header('Location: ../index.php');
    exit;
} else {
    die('Developer login is only available in development environment.');
}
