<?php
/**
 * Receive Payment Callback (Bank Callback)
 * ตรวจสอบการชำระเงินจากธนาคาร
 */

error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't show errors to the bank response

// Log for debugging
$logFile = __DIR__ . '/payment.log';
function logP($msg) {
    global $logFile;
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, FILE_APPEND);
}

// 1. Database Connection
require_once __DIR__ . '/../config/database.php';

// 2. Get Input Data
$rawData = file_get_contents('php://input');
logP("Incoming Request: " . $rawData);

$data = json_decode($rawData, true);
if (!$data) {
    // If not JSON, try POST
    $data = $_POST;
    if (empty($data)) {
        // Try GET for testing
        $data = $_GET;
    }
}

// 3. Extract Fields
// Assumed fields from Bank or Standard implementation
// Adjust these keys based on actual Bank API docs
$ref1 = $data['billPaymentRef1'] ?? $data['ref1'] ?? ''; 
$amount = $data['amount'] ?? 0;
// date format example: 20251215 or 2025-12-15T15:00:00
$txnDate = $data['transactionDate'] ?? $data['date'] ?? date('Y-m-d H:i:s'); 

logP("Processing Ref1: $ref1, Amount: $amount, Date: $txnDate");

if (empty($ref1) || empty($amount)) {
    logP("Error: Missing required parameters");
    http_response_code(400);
    echo json_encode(['resCode' => '99', 'resDesc' => 'Missing parameters']);
    exit;
}

// 4. Verify & Update
try {
    // Check local donation record
    $stmt = $pdo->prepare("SELECT * FROM donat_user WHERE billPaymentRef1 = :ref1 LIMIT 1");
    $stmt->execute([':ref1' => $ref1]);
    $donation = $stmt->fetch();

    if (!$donation) {
        logP("Error: Ref1 not found");
        http_response_code(404);
        echo json_encode(['resCode' => '02', 'resDesc' => 'Ref1 not found']);
        exit;
    }

    // Already Paid?
    if ($donation['status_donat'] === 'completed') {
        logP("Info: Already paid");
        echo json_encode(['resCode' => '00', 'resDesc' => 'Success (Already Paid)']);
        exit;
    }

    // Verify Amount (allow small float diff?)
    $pkgAmount = floatval($donation['amount']);
    $payAmount = floatval($amount);

    if (abs($pkgAmount - $payAmount) > 0.01) {
        logP("Error: Amount mismatch (Exp: $pkgAmount, Act: $payAmount)");
        http_response_code(400);
        echo json_encode(['resCode' => '03', 'resDesc' => 'Amount mismatch']);
        exit;
    }
    
    // Verify Date (Check if transaction date is valid / not too old?)
    // In this scope, we just accept it if amount and ref match.
    // If the bank sends date, we might want to store it.

    // 5. Update Status
    $updateStmt = $pdo->prepare("
        UPDATE donat_user 
        SET status_donat = 'completed', 
            updated_at = NOW() 
        WHERE id = :id
    ");
    $updateStmt->execute([':id' => $donation['id']]);

    logP("Success: Payment updated for donation ID " . $donation['id']);
    
    // Response to Bank
    echo json_encode(['resCode' => '00', 'resDesc' => 'Success']);

} catch (Exception $e) {
    logP("Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['resCode' => '99', 'resDesc' => 'Internal Error']);
}
