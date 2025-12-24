<?php
/**
 * E2E System Health Check & Report
 */

set_time_limit(60);
error_reporting(E_ALL);
ini_set('display_errors', 1);

$report = [
    'timestamp' => date('Y-m-d H:i:s'),
    'status' => 'success',
    'web' => [],
    'api' => [],
    'shared' => [],
    'assets' => []
];

// 1. Check Root Environment
$report['env'] = [
    'root_dot_env' => file_exists(__DIR__ . '/.env'),
];

// 2. WEB System Check
try {
    // Mock SERVER variables for CLI
    $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2); // Simulating some root
    $_SERVER['HTTP_HOST'] = 'localhost';

    // Load Web Env
    require_once __DIR__ . '/web/config/env.php';
    require_once __DIR__ . '/web/config/database.php';

    $report['web'] = [
        'env_loaded' => defined('WEB_ENV_LOADED'),
        'base_path' => BASE_PATH,
        'db_connection' => (isset($pdo) && $pdo instanceof PDO),
        'tables' => []
    ];

    if ($report['web']['db_connection']) {
        $tables_to_check = ['edonation_donat_user', 'edonation_projects', 'edonation_receipts', 'edonation_bank_transactions'];
        foreach ($tables_to_check as $table) {
            try {
                $pdo->query("SELECT 1 FROM $table LIMIT 1");
                $report['web']['tables'][$table] = 'OK';
            } catch (Exception $e) {
                $report['web']['tables'][$table] = 'MISSING/ERROR: ' . $e->getMessage();
                $report['status'] = 'partial_failure';
            }
        }
    }
} catch (Throwable $e) {
    $report['web']['error'] = $e->getMessage();
    $report['status'] = 'failure';
}

// 3. API System Check
try {
    // API uses its own bootstrap
    require_once __DIR__ . '/api/config/bootstrap.php';

    $report['api'] = [
        'db_connection' => (isset($pdo) && $pdo instanceof PDO),
        'constants' => [
            'API_URL' => defined('API_URL') ? API_URL : null,
            'DB_HOST' => defined('DB_HOST') ? DB_HOST : null
        ]
    ];
} catch (Throwable $e) {
    $report['api']['error'] = $e->getMessage();
    $report['status'] = 'failure';
}

// 4. Assets & QR Logic Check
$report['assets']['logo'] = file_exists(__DIR__ . '/web/assets/images/logo/logo.svg');
$report['assets']['qrcode_api'] = file_exists(__DIR__ . '/web/donat/qrcode_api.php');

// 5. Service Check
try {
    require_once __DIR__ . '/shared/services/SCBPaymentService.php';
    $scb = new SCBPaymentService();
    $report['shared']['scb_service'] = 'INSTANTIATED';
    $report['shared']['scb_biller_id'] = defined('SCB_BILLER_ID') && !empty(SCB_BILLER_ID);
} catch (Throwable $e) {
    $report['shared']['scb_error'] = $e->getMessage();
}

// Final Output
file_put_contents('e2e_report.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Report generated at e2e_report.json\n";
