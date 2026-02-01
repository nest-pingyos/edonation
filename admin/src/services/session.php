<?php
/**
 * eDonation Admin - Session Management Service
 */

declare(strict_types=1);

// 1. Set Session Name FIRST (Must be before session_start)
if (!defined('SESSION_NAME')) {
    define('SESSION_NAME', 'edonation_admin');
}
session_name(SESSION_NAME);

// 2. Configure Session Cookies (Override system defaults)
$isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
$isLocal = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1' || strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost:') === 0);

$sessionConfig = [
    'cookie_httponly' => true,
    'cookie_secure' => $isHttps && !$isLocal, // Secure only if HTTPS and NOT localhost
    'cookie_samesite' => 'Lax',                // Use Lax to prevent redirect issues
    'use_strict_mode' => true,
    'use_only_cookies' => true,
    'gc_maxlifetime' => 28800,
];

foreach ($sessionConfig as $key => $value) {
    ini_set("session.{$key}", is_bool($value) ? ($value ? '1' : '0') : (string) $value);
}

// 3. Start Session
if (session_status() === PHP_SESSION_NONE) {
    if (!session_start()) {
        error_log("SESSION ERROR: Failed to start session.");
    }
}

// 4. Load Database (Now that session is safe)
require_once __DIR__ . '/database.php';

/**
 * Get Security Fingerprint
 */
function getSessionFingerprint(): string
{
    return hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'));
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool
{
    // Development Mock Check (ONLY if explicitly set, not automatic)
    if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'super_admin' && ($_SESSION['user']['email'] ?? '') === 'dev@edonation.internal') {
        return true;
    }

    // Check Fingerprint (Session Hijacking protection)
    if (isset($_SESSION['_fingerprint']) && $_SESSION['_fingerprint'] !== getSessionFingerprint()) {
        error_log("SESSION SECURITY: Fingerprint mismatch for session " . session_id());
        logoutSession();
        return false;
    }

    // Check Traditional Session
    if (isset($_SESSION['user']['id'])) {
        return true;
    }

    // Check CMU OAuth Session
    if (isset($_SESSION['backend_user']) && ($_SESSION['backend_user']['logged_in'] ?? false) === true) {
        return true;
    }

    return false;
}

/**
 * Get current user data
 */
function getCurrentUser(): ?array
{
    if (isset($_SESSION['user'])) {
        return $_SESSION['user'];
    }

    if (isset($_SESSION['backend_user']) && ($_SESSION['backend_user']['logged_in'] ?? false) === true) {
        return [
            'id' => $_SESSION['backend_user']['id'] ?? 0,
            'email' => $_SESSION['backend_user']['email'],
            'name' => $_SESSION['backend_user']['name_th'] ?? $_SESSION['backend_user']['name_en'] ?? 'Admin',
            'role' => $_SESSION['backend_user']['role']
        ];
    }

    return null;
}

/**
 * Check if session has expired
 */
function isSessionExpired(): bool
{
    if (isLoggedIn()) {
        $timeout = 28800; // 8 hours
        $lastActivity = $_SESSION['last_activity'] ?? time();
        $loginTime = $_SESSION['login_time'] ?? (isset($_SESSION['backend_user']['login_time']) ? strtotime($_SESSION['backend_user']['login_time']) : time());

        if (time() - $lastActivity > 3600)
            return true; // 1h idle
        if (time() - $loginTime > $timeout)
            return true; // 8h absolute
    }
    return false;
}

/**
 * Logout securely
 */
function logoutSession(): void
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Require Authentication (Unified Guard)
 */
function requireAuth(): void
{
    isLoggedIn();

    $currentPath = $_SERVER['PHP_SELF'] ?? '';
    $isLoginPage = (strpos($currentPath, 'login.php') !== false);

    if (!isLoggedIn() || isSessionExpired()) {
        if ($isLoginPage) {
            return;
        }

        error_log("AUTH REDIRECT: Redirecting to login because not logged in. Path: $currentPath");
        logoutSession();

        $loginBase = defined('BASE_PATH') ? BASE_PATH . '/admin/src/auth/login.php' : 'auth/login.php';

        // If we are already in the auth folder, use simple login.php
        if (strpos($currentPath, '/admin/src/auth/') !== false) {
            $redirectUrl = 'login.php';
        } else {
            $redirectUrl = $loginBase;
        }

        header("Location: $redirectUrl");
        exit();
    }

    // Refresh last activity
    $_SESSION['last_activity'] = time();
}

/**
 * Authentication check for CMU OAuth (Used by middleware.php)
 */
function isAuthenticated(): bool
{
    return isLoggedIn();
}

/**
 * Role Check
 */
function hasRole(string $role): bool
{
    $user = getCurrentUser();
    if (!$user)
        return false;

    $roleHierarchy = ['super_admin' => 100, 'admin' => 50, 'editor' => 25, 'viewer' => 10];
    $userLevel = $roleHierarchy[$user['role']] ?? 0;
    $requiredLevel = $roleHierarchy[$role] ?? 0;

    return $userLevel >= $requiredLevel;
}

function hasExactRole(string $role): bool
{
    $user = getCurrentUser();
    return $user && $user['role'] === $role;
}

/**
 * CSRF Protection
 */
function generateCSRFToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
