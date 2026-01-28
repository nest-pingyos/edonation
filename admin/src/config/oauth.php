<?php
/**
 * CMU OAuth Configuration
 * Microsoft Entra (Azure AD) for Chiang Mai University
 * 
 * SECURITY: Credentials are loaded from environment variables
 */

// Application credentials from environment
define('CMU_OAUTH_CLIENT_ID', getenv('CMU_OAUTH_CLIENT_ID') ?: '');
define('CMU_OAUTH_CLIENT_SECRET', getenv('CMU_OAUTH_CLIENT_SECRET') ?: '');
define('CMU_OAUTH_TENANT_ID', getenv('CMU_OAUTH_TENANT_ID') ?: 'cf81f1df-de59-4c29-91da-a2dfd04aa751');

// OAuth URLs
define('CMU_OAUTH_SCOPE', 'api://cmu/Mis.Account.Read.Me.Basicinfo');
define('CMU_OAUTH_AUTH_URL', 'https://login.microsoftonline.com/' . CMU_OAUTH_TENANT_ID . '/oauth2/v2.0/authorize');
define('CMU_OAUTH_TOKEN_URL', 'https://login.microsoftonline.com/' . CMU_OAUTH_TENANT_ID . '/oauth2/v2.0/token');
define('CMU_BASICINFO_URL', 'https://api.cmu.ac.th/mis/cmuaccount/prod/v3/me/basicinfo');

// Determine redirect URI based on environment
function getCmuOAuthRedirectUri(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Get BASE_PATH from config if available
    $basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';

    return $protocol . '://' . $host . $basePath . '/admin/src/auth-callback.php';
}

/**
 * Get OAuth authorization URL
 */
function getCmuOAuthLoginUrl(): string
{
    $params = [
        'client_id' => CMU_OAUTH_CLIENT_ID,
        'response_type' => 'code',
        'redirect_uri' => getCmuOAuthRedirectUri(),
        'response_mode' => 'query',
        'scope' => CMU_OAUTH_SCOPE,
        'state' => bin2hex(random_bytes(16)) // CSRF protection
    ];

    // Store state in session for verification
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['oauth_state'] = $params['state'];

    return CMU_OAUTH_AUTH_URL . '?' . http_build_query($params);
}

/**
 * Exchange authorization code for access token
 */
function getCmuOAuthAccessToken(string $code): ?string
{
    $data = [
        'client_id' => CMU_OAUTH_CLIENT_ID,
        'client_secret' => CMU_OAUTH_CLIENT_SECRET,
        'code' => $code,
        'redirect_uri' => getCmuOAuthRedirectUri(),
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init(CMU_OAUTH_TOKEN_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("CMU OAuth Token Error: $error");
        return null;
    }

    if ($httpCode !== 200) {
        error_log("CMU OAuth Token HTTP Error: $httpCode - $response");
        return null;
    }

    $json = json_decode($response, true);
    return $json['access_token'] ?? null;
}

/**
 * Get user info from CMU API
 */
function getCmuUserInfo(string $accessToken): ?array
{
    $ch = curl_init(CMU_BASICINFO_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $accessToken"
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("CMU API Error: $error");
        return null;
    }

    if ($httpCode !== 200) {
        error_log("CMU API HTTP Error: $httpCode - $response");
        return null;
    }

    return json_decode($response, true);
}
