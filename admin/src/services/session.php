<?php
/**
 * Session Management Service
 * 
 * จัดการ session สำหรับ admin
 */

require_once __DIR__ . '/database.php';

// Configure session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name(SESSION_NAME);
session_start();

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool
{
    // Bypass auth for development if needed
    if (defined('APP_ENV') && APP_ENV === 'development') {
        if (!isset($_SESSION['user'])) {
            $_SESSION['user'] = [
                'id' => 1,
                'email' => 'dev@edonation.internal',
                'name' => 'Developer Admin',
                'role' => 'super_admin'
            ];
        }
        return true;
    }
    return isset($_SESSION['user']) && $_SESSION['user'] !== null && isset($_SESSION['user']['id']);
}

/**
 * Get current user
 */
function getCurrentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Check authentication with email and password
 */
function checkAuth(string $email, string $password = ''): bool|string
{
    // If password provided, use full authentication
    if (!empty($password)) {
        $user = DatabaseService::authenticateUser($email, $password);
        if ($user) {
            setSession($user);
            return true;
        }
        return "อีเมลหรือรหัสผ่านไม่ถูกต้อง";
    }
    
    // Legacy: check if email exists (for demo)
    if (DatabaseService::checkUser($email)) {
        // For demo purposes only - should not use in production without password
        $pdo = DatabaseService::getInstance();
        $stmt = $pdo->prepare("SELECT id, email, name, role FROM edonation_admin_users WHERE email = :email AND status = 'active'");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        if ($user) {
            setSession($user);
            return true;
        }
    }
    return "อีเมลไม่ถูกต้อง";
}

/**
 * Set session with user data
 */
function setSession(array $user): void
{
    $_SESSION['user'] = $user;
    $_SESSION['login_time'] = time();
    
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
}

/**
 * Logout and destroy session
 */
function logoutSession(): void
{
    $_SESSION = [];
    
    // Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Check if session has expired
 */
function isSessionExpired(): bool
{
    if (defined('APP_ENV') && APP_ENV === 'development') return false;
    if (!isset($_SESSION['login_time'])) {
        return true;
    }
    return (time() - $_SESSION['login_time']) > SESSION_LIFETIME;
}

/**
 * Require authentication - redirect if not logged in
 */
function requireAuth(): void
{
    if (defined('APP_ENV') && APP_ENV === 'development') return;
    if (!isLoggedIn() || isSessionExpired()) {
        logoutSession();
        header('Location: auth-signin.php');
        exit();
    }
}

/**
 * Check user role
 */
function hasRole(string $role): bool
{
    $user = getCurrentUser();
    if (!$user) return false;
    
    $roleHierarchy = [
        'super_admin' => 100,
        'admin' => 50,
        'editor' => 25,
        'viewer' => 10
    ];
    
    $userLevel = $roleHierarchy[$user['role']] ?? 0;
    $requiredLevel = $roleHierarchy[$role] ?? 0;
    
    return $userLevel >= $requiredLevel;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken(): string
{
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken(string $token): bool
{
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}
