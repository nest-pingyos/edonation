<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Check for Dev Login
if (isset($_GET['dev_login']) && defined('APP_ENV') && APP_ENV === 'development') {
    require_once __DIR__ . '/../services/database.php';

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
            'name_th' => 'ผู้ดูแล',
            'name_en' => 'Developer',
            'organization' => 'Local Development',
            'role' => $authorizedUser['role'],
            'logged_in' => true,
            'login_time' => date('Y-m-d H:i:s')
        ];

        header("Location: ../index.php");
        exit;
    } else {
        $_SESSION['auth_error'] = 'ไม่สามารถสร้างบัญชี Dev ได้';
        header("Location: login.php");
        exit;
    }
}

$client_id = '9ff50902-00e4-482f-b3d0-f0d59d31c999';
$client_secret = '4gI8Q~qObbh7QxvOW5g3LIVkQRY.vpx71LlA1aJp';
$redirect_uri = 'https://app.nurse.cmu.ac.th/edonation/admin/src/auth/callback.php';

$tenant_id = 'cf81f1df-de59-4c29-91da-a2dfd04aa751';

$oauth_scope = "api://cmu/Mis.Account.Read.Me.Basicinfo";
$oauth_auth_url = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/authorize";
$oauth_token_url = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/token";
$basicinfo_url = "https://api.cmu.ac.th/mis/cmuaccount/prod/v3/me/basicinfo";

if (isset($_GET['error'])) {
    echo "Error: " . htmlspecialchars($_GET['error']) . "<br>";
    echo "Description: " . htmlspecialchars($_GET['error_description']);
    exit;
}

if (isset($_GET['code'])) {
    $accessToken = get_oauth_token($_GET['code']);
    if (!$accessToken) {
        $_SESSION['auth_error'] = 'ไม่สามารถรับ access_token ได้';
        header("Location: login.php");
        exit;
    }

    $user = call_cmu_api($accessToken);
    if (!$user || !isset($user['cmuitaccount'])) {
        $_SESSION['auth_error'] = 'ไม่สามารถดึงข้อมูลผู้ใช้จาก CMU API ได้';
        header("Location: login.php");
        exit;
    }

    // Check if user is authorized in database
    require_once __DIR__ . '/../services/database.php';

    $email = $user['cmuitaccount'];
    $authorizedUser = DatabaseService::authenticateCMUUser($email);

    if (!$authorizedUser) {
        // User not authorized
        $_SESSION['auth_error'] = 'คุณไม่มีสิทธิ์เข้าใช้งานระบบ Admin (' . $email . ')';
        header("Location: login.php");
        exit;
    }

    // Store user info with database role
    $_SESSION['backend_user'] = [
        'id' => $authorizedUser['id'],
        'email' => $user['cmuitaccount'],
        'name_th' => $user['firstname_TH'] . ' ' . $user['lastname_TH'],
        'name_en' => $user['firstname_EN'] . ' ' . $user['lastname_EN'],
        'organization' => $user['organization_name_TH'] ?? '',
        'role' => $authorizedUser['role'],
        'logged_in' => true,
        'login_time' => date('Y-m-d H:i:s')
    ];

    header("Location: ../index.php");
    exit;
}

// Redirect to OAuth login
$auth_url = $oauth_auth_url . '?' . http_build_query([
    'client_id' => $client_id,
    'response_type' => 'code',
    'redirect_uri' => $redirect_uri,
    'response_mode' => 'query',
    'scope' => $oauth_scope
]);

header("Location: $auth_url");
exit;

function get_oauth_token($code)
{
    global $client_id, $client_secret, $redirect_uri, $oauth_token_url;

    $data = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $code,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init($oauth_token_url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log("cURL error: " . curl_error($ch));
        return null;
    }
    curl_close($ch);

    $json = json_decode($response, true);
    return $json['access_token'] ?? null;
}

function call_cmu_api($accessToken)
{
    global $basicinfo_url;
    $ch = curl_init($basicinfo_url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $accessToken"
        ],
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}