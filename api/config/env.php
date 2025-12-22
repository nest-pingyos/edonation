<?php
/**
 * Environment Configuration
 * eDonation API
 * 
 * Production URL: https://app.nurse.cmu.ac.th/edonation
 * Development URL: http://localhost/appdev/edonation
 */

// Load .env file from project root
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
        // ไม่ override ค่าที่มีอยู่แล้ว
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
        }
    }
}

// ===========================================
// Application Settings
// ===========================================
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', ($_ENV['APP_DEBUG'] ?? 'false') === 'true');

// Production URL ใหม่: https://app.nurse.cmu.ac.th/edonation
define('APP_URL', $_ENV['APP_URL'] ?? 'https://app.nurse.cmu.ac.th/edonation');
define('BASE_PATH', $_ENV['BASE_PATH'] ?? '/edonation');

// URLs ที่ใช้งาน
define('WEB_URL', APP_URL);
define('API_URL', APP_URL . '/api');
define('ADMIN_URL', APP_URL . '/admin');
define('OFFICE_URL', APP_URL . '/office');

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
define('LINE_TOKEN', $_ENV['LINE_TOKEN'] ?? '');
define('GMAIL_USER', $_ENV['GMAIL_USER'] ?? '');
define('GMAIL_PASS', $_ENV['GMAIL_PASS'] ?? '');

// ===========================================
// SCB PromptPay Configuration  
// ===========================================
define('SCB_BILLER_ID', $_ENV['SCB_BILLER_ID'] ?? '');
define('SCB_API_KEY', $_ENV['SCB_API_KEY'] ?? '');
define('SCB_API_SECRET', $_ENV['SCB_API_SECRET'] ?? '');

