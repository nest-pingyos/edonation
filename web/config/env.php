<?php
/**
 * Web Environment Configuration
 * eDonation Web Application
 * 
 * โหลด configuration จาก .env ที่ root level
 * รองรับ API แยก domain
 * 
 * @version 2.0
 */

// ป้องกันการโหลดซ้ำ
if (defined('WEB_ENV_LOADED')) {
    return;
}
define('WEB_ENV_LOADED', true);

// ===== Load Environment Configuration =====
$envFile = dirname(__DIR__, 2) . '/.env';
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

        // Handle variable interpolation ${VAR}
        if (preg_match_all('/\$\{([^}]+)\}/', $value, $matches)) {
            foreach ($matches[1] as $varName) {
                if (isset($_ENV[$varName])) {
                    $value = str_replace('${' . $varName . '}', $_ENV[$varName], $value);
                }
            }
        }

        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
        }
    }
}

// ===== Domain & URL Configuration =====
// App Domain (Web & Admin)
if (!defined('APP_DOMAIN'))
    define('APP_DOMAIN', $_ENV['APP_DOMAIN'] ?? 'http://localhost');

// API Domain (can be separate from App Domain)
if (!defined('API_DOMAIN'))
    define('API_DOMAIN', $_ENV['API_DOMAIN'] ?? APP_DOMAIN);

// Base paths
if (!defined('BASE_PATH')) {
    // Detect BASE_PATH automatically (e.g., /appdev/edonation)
    $script_path = str_replace('\\', '/', dirname(__DIR__, 2));
    $root_path = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');

    if (empty($root_path)) {
        // Fallback for CLI
        $auto_base = $_ENV['BASE_PATH'] ?? '/edonation';
    } else {
        $auto_base = str_replace($root_path, '', $script_path);
        if ($auto_base && $auto_base[0] !== '/')
            $auto_base = '/' . $auto_base;
    }
    define('BASE_PATH', $auto_base ?: '/edonation');
}
if (!defined('API_BASE_PATH'))
    define('API_BASE_PATH', $_ENV['API_BASE_PATH'] ?? BASE_PATH . '/api');

// ===== Full URLs =====
if (!defined('APP_URL'))
    define('APP_URL', APP_DOMAIN . BASE_PATH);
if (!defined('API_URL'))
    define('API_URL', API_DOMAIN . API_BASE_PATH);
if (!defined('API_BASE'))
    define('API_BASE', API_URL . '/v1');

// Web section URLs
if (!defined('WEB_URL'))
    define('WEB_URL', APP_URL);
if (!defined('ADMIN_URL'))
    define('ADMIN_URL', APP_URL . '/admin');
if (!defined('OFFICE_URL'))
    define('OFFICE_URL', APP_URL . '/office');

// ===== Application Settings =====
if (!defined('APP_ENV'))
    define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
if (!defined('APP_DEBUG'))
    define('APP_DEBUG', ($_ENV['APP_DEBUG'] ?? 'false') === 'true');

// ===== SCB PromptPay Configuration =====
if (!defined('SCB_BILLER_ID'))
    define('SCB_BILLER_ID', $_ENV['SCB_BILLER_ID'] ?? '');
if (!defined('SCB_API_KEY'))
    define('SCB_API_KEY', $_ENV['SCB_API_KEY'] ?? '');
if (!defined('SCB_API_SECRET'))
    define('SCB_API_SECRET', $_ENV['SCB_API_SECRET'] ?? '');

// SCB API URLs (Production)
if (!defined('SCB_BASE_URL'))
    define('SCB_BASE_URL', 'https://api.scb.co.th/partners/sandbox/v1');
if (!defined('SCB_OAUTH_URL'))
    define('SCB_OAUTH_URL', SCB_BASE_URL . '/oauth/token');
if (!defined('SCB_QR_CREATE_URL'))
    define('SCB_QR_CREATE_URL', SCB_BASE_URL . '/payment/qrcode/create');

// SCB QR Settings
if (!defined('SCB_QR_TYPE'))
    define('SCB_QR_TYPE', 'PP');
if (!defined('SCB_PP_TYPE'))
    define('SCB_PP_TYPE', 'BILLERID');
if (!defined('SCB_REF3'))
    define('SCB_REF3', 'EDN');
if (!defined('SCB_QR_NUMBER_OF_TIMES'))
    define('SCB_QR_NUMBER_OF_TIMES', 1);
if (!defined('SCB_QR_EXPIRY_MINUTES'))
    define('SCB_QR_EXPIRY_MINUTES', 30);

// ===== LINE Notify =====
if (!defined('LINE_TOKEN'))
    define('LINE_TOKEN', $_ENV['LINE_TOKEN'] ?? '');

// ===== Email Configuration =====
if (!defined('GMAIL_USER'))
    define('GMAIL_USER', $_ENV['GMAIL_USER'] ?? '');
if (!defined('GMAIL_PASS'))
    define('GMAIL_PASS', $_ENV['GMAIL_PASS'] ?? '');

/**
 * Check if API is on separate domain
 * @return bool
 */
function isApiSeparateDomain(): bool
{
    return API_DOMAIN !== APP_DOMAIN;
}

/**
 * Get API endpoint URL
 * @param string $endpoint (e.g., '/projects', '/donations/1')
 * @return string Full API URL
 */
function getApiUrl(string $endpoint): string
{
    $endpoint = ltrim($endpoint, '/');
    return API_BASE . '/' . $endpoint;
}
