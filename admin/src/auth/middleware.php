<?php
/**
 * Auth Middleware
 * 
 * ตรวจสอบการ login ก่อนเข้าหน้า admin
 * Include ไฟล์นี้ในทุกหน้าที่ต้องการ authentication
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is authenticated via CMU OAuth or traditional login
 */
function isAuthenticated(): bool
{
    // Check CMU OAuth login
    if (isset($_SESSION['backend_user']) && $_SESSION['backend_user']['logged_in'] === true) {
        return true;
    }

    // Check traditional login
    if (isset($_SESSION['user']) && $_SESSION['user'] !== null && isset($_SESSION['user']['id'])) {
        return true;
    }

    return false;
}

/**
 * Check if session has expired (8 hours)
 */
function isAuthSessionExpired(): bool
{
    $sessionLifetime = 8 * 60 * 60; // 8 hours

    // Check CMU OAuth login time
    if (isset($_SESSION['backend_user']['login_time'])) {
        $loginTime = strtotime($_SESSION['backend_user']['login_time']);
        return (time() - $loginTime) > $sessionLifetime;
    }

    // Check traditional login time
    if (isset($_SESSION['login_time'])) {
        return (time() - $_SESSION['login_time']) > $sessionLifetime;
    }

    return true;
}

/**
 * Get current authenticated user
 */
function getAuthUser(): ?array
{
    // CMU OAuth user
    if (isset($_SESSION['backend_user']) && $_SESSION['backend_user']['logged_in'] === true) {
        return [
            'id' => $_SESSION['backend_user']['id'] ?? 0,
            'email' => $_SESSION['backend_user']['email'],
            'name' => $_SESSION['backend_user']['name_th'],
            'role' => $_SESSION['backend_user']['role'],
            'organization' => $_SESSION['backend_user']['organization'] ?? '',
            'auth_type' => 'cmu_oauth'
        ];
    }

    // Traditional user
    if (isset($_SESSION['user']) && $_SESSION['user'] !== null) {
        return array_merge($_SESSION['user'], ['auth_type' => 'traditional']);
    }

    return null;
}

/**
 * Require authentication - redirect if not logged in
 */
function requireAuthentication(): void
{
    if (!isAuthenticated() || isAuthSessionExpired()) {
        // Clear session
        $_SESSION = [];
        session_destroy();

        // Redirect to login
        header('Location: auth/login.php');
        exit();
    }
}

/**
 * Check if user has required role
 */
function hasAuthRole(string $requiredRole): bool
{
    $user = getAuthUser();
    if (!$user)
        return false;

    $roleHierarchy = [
        'super_admin' => 100,
        'admin' => 50,
        'editor' => 25,
        'viewer' => 10
    ];

    $userLevel = $roleHierarchy[$user['role']] ?? 0;
    $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;

    return $userLevel >= $requiredLevel;
}

/**
 * Require specific role - redirect if not authorized
 */
function requireRole(string $role): void
{
    requireAuthentication();

    if (!hasAuthRole($role)) {
        header('HTTP/1.1 403 Forbidden');
        echo '<h1>403 - ไม่มีสิทธิ์เข้าถึง</h1>';
        echo '<p>คุณไม่มีสิทธิ์เข้าถึงหน้านี้</p>';
        echo '<a href="index.php">กลับหน้าหลัก</a>';
        exit();
    }
}
