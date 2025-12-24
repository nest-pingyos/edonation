<?php
echo "START_VALIDATION\n";
define('CLI_MODE', true);

// 1. Web Env
try {
    include_once __DIR__ . '/web/config/env.php';
    echo "WEB_ENV: OK\n";
    echo "BASE_PATH: " . (defined('BASE_PATH') ? BASE_PATH : 'ERR') . "\n";
} catch (Throwable $e) {
    echo "WEB_ENV_ERR: " . $e->getMessage() . "\n";
}

// 2. Web DB
try {
    include_once __DIR__ . '/web/config/database.php';
    if (isset($pdo)) {
        echo "WEB_DB: OK\n";
    } else {
        echo "WEB_DB: FAILED\n";
    }
} catch (Throwable $e) {
    echo "WEB_DB_ERR: " . $e->getMessage() . "\n";
}

// 3. API Bootstrap (Skip session_start if possible)
try {
    // Just Load API Env manually to avoid bootstrap session_start
    include_once __DIR__ . '/api/config/env.php';
    echo "API_ENV: OK\n";

    // Test API DB class
    require_once __DIR__ . '/api/config/database.php';
    $apiPdo = Database::getInstance();
    if ($apiPdo) {
        echo "API_DB: OK\n";
    }
} catch (Throwable $e) {
    echo "API_DB_ERR: " . $e->getMessage() . "\n";
}

// 4. Logo check
if (file_exists(__DIR__ . '/web/assets/images/logo/logo.svg')) {
    echo "LOGO_FILE: OK\n";
} else {
    echo "LOGO_FILE: MISSING\n";
}

echo "END_VALIDATION\n";
