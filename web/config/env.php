<?php
/**
 * Web Environment Configuration
 * eDonation Web Application
 * 
 * โหลด configuration จาก .env ที่ root level
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
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
        }
    }
}

// ===== Application Settings =====
if (!defined('APP_ENV')) define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
if (!defined('APP_DEBUG')) define('APP_DEBUG', ($_ENV['APP_DEBUG'] ?? 'false') === 'true');
if (!defined('APP_URL')) define('APP_URL', $_ENV['APP_URL'] ?? 'https://app.nurse.cmu.ac.th/edonation');
if (!defined('BASE_PATH')) define('BASE_PATH', $_ENV['BASE_PATH'] ?? '/edonation');

// ===== URLs ที่ใช้งาน =====
if (!defined('WEB_URL')) define('WEB_URL', APP_URL);
if (!defined('API_URL')) define('API_URL', APP_URL . '/api');
if (!defined('API_BASE')) define('API_BASE', BASE_PATH . '/api/v1');
if (!defined('ADMIN_URL')) define('ADMIN_URL', APP_URL . '/admin');
if (!defined('OFFICE_URL')) define('OFFICE_URL', APP_URL . '/office');

// ===== SCB PromptPay Configuration =====
if (!defined('SCB_BILLER_ID')) define('SCB_BILLER_ID', $_ENV['SCB_BILLER_ID'] ?? '');
if (!defined('SCB_API_KEY')) define('SCB_API_KEY', $_ENV['SCB_API_KEY'] ?? '');
if (!defined('SCB_API_SECRET')) define('SCB_API_SECRET', $_ENV['SCB_API_SECRET'] ?? '');

// SCB API URLs (Production)
if (!defined('SCB_BASE_URL')) define('SCB_BASE_URL', 'https://api.scb.co.th/partners/sandbox/v1');
if (!defined('SCB_OAUTH_URL')) define('SCB_OAUTH_URL', SCB_BASE_URL . '/oauth/token');
if (!defined('SCB_QR_CREATE_URL')) define('SCB_QR_CREATE_URL', SCB_BASE_URL . '/payment/qrcode/create');

// SCB QR Settings
if (!defined('SCB_QR_TYPE')) define('SCB_QR_TYPE', 'PP');
if (!defined('SCB_PP_TYPE')) define('SCB_PP_TYPE', 'BILLERID');
if (!defined('SCB_REF3')) define('SCB_REF3', 'EDN');
if (!defined('SCB_QR_NUMBER_OF_TIMES')) define('SCB_QR_NUMBER_OF_TIMES', 1);
if (!defined('SCB_QR_EXPIRY_MINUTES')) define('SCB_QR_EXPIRY_MINUTES', 30);

// ===== LINE Notify =====
if (!defined('LINE_TOKEN')) define('LINE_TOKEN', $_ENV['LINE_TOKEN'] ?? '');

// ===== Email Configuration =====
if (!defined('GMAIL_USER')) define('GMAIL_USER', $_ENV['GMAIL_USER'] ?? '');
if (!defined('GMAIL_PASS')) define('GMAIL_PASS', $_ENV['GMAIL_PASS'] ?? '');
