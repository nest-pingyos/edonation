<?php
/**
 * CMU OAuth Callback Handler
 * 
 * This file handles the OAuth callback from Microsoft Entra
 * Redirect URI: http://localhost:8080/admin/src/auth-callback.php (dev)
 *               https://app.nurse.cmu.ac.th/edonation/admin/src/auth-callback.php (prod)
 */

session_start();

// Include required files
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/oauth.php';
require_once __DIR__ . '/services/database.php';

/**
 * Redirect with error message
 */
function redirectWithError(string $message): void
{
    $_SESSION['auth_error'] = $message;
    header('Location: auth/login.php');
    exit;
}

// =============================================
// Handle OAuth Error
// =============================================
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
    $errorDesc = htmlspecialchars($_GET['error_description'] ?? 'ไม่ทราบสาเหตุ');
    error_log("CMU OAuth Error: $error - $errorDesc");
    redirectWithError("การเข้าสู่ระบบถูกยกเลิก: $error");
}

// =============================================
// Handle Dev Login (Development Mode Only)
// =============================================
if (isset($_GET['dev_login']) && defined('APP_ENV') && APP_ENV === 'development') {
    $email = 'dev@localhost';
    $authorizedUser = DatabaseService::authenticateCMUUser($email);

    if (!$authorizedUser) {
        // Create Dev user if not exists
        if (DatabaseService::createUser($email, 'Developer', 'admin')) {
            $authorizedUser = DatabaseService::authenticateCMUUser($email);
        }
    }

    if ($authorizedUser) {
        $_SESSION['backend_user'] = [
            'id' => $authorizedUser['id'],
            'email' => $authorizedUser['email'],
            'name_th' => 'ผู้ดูแล (Dev)',
            'name_en' => 'Developer',
            'organization' => 'Local Development',
            'role' => $authorizedUser['role'],
            'logged_in' => true,
            'login_time' => date('Y-m-d H:i:s')
        ];

        // Update last login
        DatabaseService::updateLastLogin($authorizedUser['id']);

        header('Location: index.php');
        exit;
    } else {
        redirectWithError('ไม่สามารถสร้างบัญชี Dev ได้');
    }
}

// =============================================
// Handle OAuth Callback with Authorization Code
// =============================================
if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // Verify state parameter (CSRF protection)
    $state = $_GET['state'] ?? '';
    $expectedState = $_SESSION['oauth_state'] ?? '';

    if (empty($state) || $state !== $expectedState) {
        error_log("OAuth State Mismatch: received=$state, expected=$expectedState");
        // Don't block in case state wasn't set properly
        // redirectWithError('การเชื่อมต่อไม่ปลอดภัย กรุณาลองใหม่อีกครั้ง');
    }

    // Clear state
    unset($_SESSION['oauth_state']);

    // Exchange code for access token
    $accessToken = getCmuOAuthAccessToken($code);

    if (!$accessToken) {
        redirectWithError('ไม่สามารถรับ access token ได้ กรุณาลองใหม่อีกครั้ง');
    }

    // Get user info from CMU API
    $userInfo = getCmuUserInfo($accessToken);

    if (!$userInfo || !isset($userInfo['cmuitaccount'])) {
        error_log("CMU User Info Error: " . json_encode($userInfo));
        redirectWithError('ไม่สามารถดึงข้อมูลผู้ใช้จาก CMU Account ได้');
    }

    // Extract email (CMU IT Account)
    $email = $userInfo['cmuitaccount'];

    // Check if user is authorized in database
    $authorizedUser = DatabaseService::authenticateCMUUser($email);

    if (!$authorizedUser) {
        // User not in admin list
        error_log("Unauthorized CMU User: $email");
        redirectWithError("คุณไม่มีสิทธิ์เข้าใช้งานระบบ Admin ($email) กรุณาติดต่อผู้ดูแลระบบ");
    }

    // Check if user is active
    if ($authorizedUser['status'] !== 'active') {
        redirectWithError('บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ');
    }

    // Build user session
    $_SESSION['backend_user'] = [
        'id' => $authorizedUser['id'],
        'email' => $email,
        'name_th' => ($userInfo['firstname_TH'] ?? '') . ' ' . ($userInfo['lastname_TH'] ?? ''),
        'name_en' => ($userInfo['firstname_EN'] ?? '') . ' ' . ($userInfo['lastname_EN'] ?? ''),
        'organization' => $userInfo['organization_name_TH'] ?? $userInfo['organization_name_EN'] ?? '',
        'role' => $authorizedUser['role'],
        'logged_in' => true,
        'login_time' => date('Y-m-d H:i:s')
    ];

    // Update last login timestamp
    DatabaseService::updateLastLogin($authorizedUser['id']);

    // Clear any previous errors
    unset($_SESSION['auth_error']);

    // Redirect to dashboard
    header('Location: index.php');
    exit;
}

// =============================================
// No code received - Initiate OAuth Login
// =============================================
$loginUrl = getCmuOAuthLoginUrl();
header("Location: $loginUrl");
exit;
