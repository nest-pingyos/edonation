<?php
/**
 * E2E System Verification Tool
 * This script tests the core logic and connectivity without requiring a browser.
 */

header('Content-Type: text/plain; charset=UTF-8');

function log_test($msg, $success = true)
{
    echo ($success ? "[OK] " : "[FAIL] ") . $msg . "\n";
}

echo "--- eDonation E2E Verification Start ---\n\n";

// 1. Check Root .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    log_test(".env file found at root");
} else {
    log_test(".env file NOT found at root", false);
}

// 2. Test WEB System Connectivity
echo "\n[Testing WEB System]\n";
try {
    require_once __DIR__ . '/web/config/env.php';
    log_test("web/config/env.php loaded successfully");
    log_test("BASE_PATH detected as: " . BASE_PATH);

    require_once __DIR__ . '/web/config/database.php';
    if (isset($pdo) && $pdo instanceof PDO) {
        log_test("WEB Database (PDO) connected successfully");
        $stmt = $pdo->query("SELECT COUNT(*) FROM edonation_donat_user");
        $count = $stmt->fetchColumn();
        log_test("Total donations in edonation_donat_user: " . $count);
    } else {
        log_test("WEB Database connected failed", false);
    }
} catch (Throwable $e) {
    log_test("WEB System Error: " . $e->getMessage(), false);
}

// 3. Test API System Connectivity
echo "\n[Testing API System]\n";
try {
    require_once __DIR__ . '/api/config/bootstrap.php';
    log_test("api/config/bootstrap.php loaded successfully");

    if (isset($pdo) && $pdo instanceof PDO) {
        log_test("API Database (PDO) connected successfully");
    } else {
        log_test("API Database connection failed", false);
    }
} catch (Throwable $e) {
    log_test("API System Error: " . $e->getMessage(), false);
}

// 4. Test Shared Services (SCB)
echo "\n[Testing Shared Services]\n";
try {
    require_once __DIR__ . '/shared/services/SCBPaymentService.php';
    $scb = new SCBPaymentService();
    log_test("SCBPaymentService instantiated successfully");

    if (defined('SCB_BILLER_ID') && !empty(SCB_BILLER_ID)) {
        log_test("SCB_BILLER_ID is configured: " . SCB_BILLER_ID);
    } else {
        log_test("SCB_BILLER_ID is NOT configured", false);
    }
} catch (Throwable $e) {
    log_test("Shared Services Error: " . $e->getMessage(), false);
}

// 5. Test QR Code Logic (Mock)
echo "\n[Testing QR Code API Logic]\n";
try {
    $_GET['id'] = 1; // Test ID
    $_GET['ref'] = 'TESTREF';
    $_GET['amount'] = 100.00;

    // We can't easily require the qrcode_api.php because it might exit or send headers
    // But we tested the components it uses. Let's do a logic check.
    if (file_exists(__DIR__ . '/web/donat/qrcode_api.php')) {
        log_test("web/donat/qrcode_api.php file exists");

        // Check for common syntax errors
        $output = shell_exec("php -l web/donat/qrcode_api.php");
        if (strpos($output, 'No syntax errors detected') !== false) {
            log_test("qrcode_api.php syntax check passed");
        } else {
            log_test("qrcode_api.php syntax check failed: " . $output, false);
        }
    }
} catch (Throwable $e) {
    log_test("QR Logic Error: " . $e->getMessage(), false);
}

echo "\n--- Verification Complete ---\n";
