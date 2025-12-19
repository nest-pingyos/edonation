<?php
/**
 * Environment Configuration
 */

// Load .env file if exists
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0)
            continue;
        if (strpos($line, '=') === false)
            continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Default configurations
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', ($_ENV['APP_DEBUG'] ?? 'false') === 'true');
define('APP_URL', $_ENV['APP_URL'] ?? 'https://app.nurse.cmu.ac.th/appdev/edonation');

// Base Path สำหรับ URL
define('BASE_PATH', $_ENV['BASE_PATH'] ?? '/appdev/edonation');
define('OFFICE_URL', APP_URL . '/office');

define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'appdev');
define('DB_USER', $_ENV['DB_USER'] ?? 'dev0299');
define('DB_PASS', $_ENV['DB_PASS'] ?? 'dev@0299');

define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-this');
define('JWT_EXPIRE', 86400); // 24 hours

