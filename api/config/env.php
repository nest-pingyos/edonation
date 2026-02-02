<?php
/**
 * Environment Configuration
 * eDonation API
 * 
 * รองรับ API แยก domain พร้อม CORS
 * 
 * @version 2.0
 */

// Load .env file from project root
$envFile = dirname(__DIR__, 2) . '/.env';
$envVars = [];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $envVars[$key] = $value;
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
        }
    }
}

// Handle variable interpolation
foreach ($envVars as $key => $value) {
    if (preg_match_all('/\$\{([^}]+)\}/', $value, $matches)) {
        foreach ($matches[1] as $varName) {
            if (isset($envVars[$varName])) {
                $value = str_replace('${' . $varName . '}', $envVars[$varName], $value);
            }
        }
        $_ENV[$key] = $value;
    }
}

// ===========================================
// Domain & URL Configuration
// ===========================================
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isDocker = isset($_ENV['BASE_PATH']) && $_ENV['BASE_PATH'] === '';

define('APP_DOMAIN', $_ENV['APP_DOMAIN'] ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $host);
define('BASE_PATH', $_ENV['BASE_PATH'] ?? '/edonation');
define('API_BASE_PATH', $_ENV['API_BASE_PATH'] ?? BASE_PATH . '/api');

// Full URLs
define('APP_URL', APP_DOMAIN . BASE_PATH);
define('API_URL', APP_DOMAIN . API_BASE_PATH);
define('WEB_URL', APP_URL);
define('ADMIN_URL', APP_URL . '/admin');

// ===========================================
// CORS Configuration
// ===========================================
// Allowed origins for CORS (comma-separated in .env)
$allowedOrigins = $_ENV['CORS_ALLOWED_ORIGINS'] ?? APP_DOMAIN;
define('CORS_ALLOWED_ORIGINS', explode(',', $allowedOrigins));

// ===========================================
// Application Settings
// ===========================================
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', ($_ENV['APP_DEBUG'] ?? 'false') === 'true');

// ===========================================
// Database Configuration
// ===========================================
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'edonation');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// ===========================================
// JWT Configuration
// ===========================================
define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-this');
define('JWT_EXPIRE', intval($_ENV['JWT_EXPIRE'] ?? 86400)); // 24 hours

// ===========================================
// External Services
// ===========================================
define('GMAIL_USER', $_ENV['GMAIL_USER'] ?? '');
define('GMAIL_PASS', $_ENV['GMAIL_PASS'] ?? '');

// ===========================================
// SCB PromptPay Configuration  
// ===========================================
define('SCB_BILLER_ID', $_ENV['SCB_BILLER_ID'] ?? '');
define('SCB_API_KEY', $_ENV['SCB_API_KEY'] ?? '');
define('SCB_API_SECRET', $_ENV['SCB_API_SECRET'] ?? '');

/**
 * Handle CORS headers
 * เรียกใช้ function นี้ก่อน output ใดๆ
 */
function handleCors(): void
{
    // Get origin
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    // Check if origin is allowed
    $allowed = false;
    foreach (CORS_ALLOWED_ORIGINS as $allowedOrigin) {
        $allowedOrigin = trim($allowedOrigin);
        if ($origin === $allowedOrigin || $allowedOrigin === '*') {
            $allowed = true;
            break;
        }
    }

    // If development, allow localhost
    if (APP_ENV === 'development' && strpos($origin, 'localhost') !== false) {
        $allowed = true;
    }

    if ($allowed && $origin) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
    } elseif (empty($origin)) {
        // Same-origin request
        header('Access-Control-Allow-Origin: *');
    }

    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Max-Age: 86400'); // 24 hours cache for preflight
}
