<?php
/**
 * CMU Microsoft Entra ID Auth Callback Handler
 * 
 * This file handles the redirect back from Microsoft Entra ID (Azure AD),
 * exchanges the authorization code for tokens, and verifies user authorization
 * against the local database.
 */

// 1. Initial configuration and services
require_once __DIR__ . '/services/session.php';
require_once __DIR__ . '/config/oauth.php';
require_once __DIR__ . '/services/database.php';

// Safe redirect helper
function redirectError($message)
{
    $_SESSION['auth_error'] = $message;
    header('Location: login.php');
    exit;
}

// 2. Extract Authorization Code from query string
$code = $_GET['code'] ?? null;
$error = $_GET['error'] ?? null;

if ($error)
    redirectError("Microsoft Error: " . htmlspecialchars($error));
if (!$code)
    redirectError("ไม่ได้รับรหัสยืนยันตัวตน (No code provided)");

try {
    // 3. Exchange Authorization Code for Access Token
    // USES: getCmuOAuthAccessToken() in config/oauth.php
    $accessToken = getCmuOAuthAccessToken($code);
    if (!$accessToken) {
        throw new Exception("ยืนยันตัวตนกับ Microsoft ไม่สำเร็จกรุณาลองใหม่อีกครั้ง");
    }

    // 4. Retrieve User Basic Information from CMU API
    // USES: getCmuUserInfo() in config/oauth.php
    $userInfo = getCmuUserInfo($accessToken);
    $email = $userInfo['cmuitaccount_email'] ?? null;
    $displayName = $userInfo['cmuitaccount_name'] ?? 'CMU User';

    if (!$email) {
        throw new Exception("ไม่พบข้อมูลอีเมล (cmuitaccount_email) ในบัญชีของคุณ");
    }

    // 5. Query Local Database for Authorized Admins
    // REQUIREMENTS: Only 'active' status users can login
    $pdo = DatabaseService::getInstance();
    $stmt = $pdo->prepare("
        SELECT id, name, role, status 
        FROM edonation_admin_users 
        WHERE email = :email AND status = 'active'
        LIMIT 1
    ");
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch();

    if (!$admin) {
        error_log("AUTH DENIED: User $email not found in edonation_admin_users or is inactive.");
        throw new Exception("คุณไม่มีสิทธิ์เข้าใช้งานระบบจัดการนี้ หรือบัญชีถูกระงับ");
    }

    // 6. Authorization Success - Setup Session
    // We store minimal required info to avoid stale data
    $_SESSION['backend_user'] = [
        'id' => (int) $admin['id'],
        'email' => $email,
        'name' => $admin['name'] ?: $displayName,
        'role' => $admin['role'],
        'logged_in' => true,
        'login_time' => date('Y-m-d H:i:s')
    ];

    // For compatibility with getCurrentUser() helper
    $_SESSION['user'] = $_SESSION['backend_user'];

    // 7. Update Last Login Activity
    DatabaseService::updateLastLogin((int) $admin['id']);

    // Log success
    error_log("AUTH SUCCESS: $email (ID: {$admin['id']}) logged in as {$admin['role']}");

    // 8. Redirect to internal landing page
    header('Location: index.php');
    exit;

} catch (Exception $e) {
    // Clear any partial session and redirect with error
    logoutSession();
    redirectError($e->getMessage());
}
