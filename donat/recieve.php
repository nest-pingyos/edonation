<?php
/**
 * Receive Payment Callback (Bank Callback Proxy)
 * 
 * รับ callback จากธนาคารแล้ว forward ไปยัง Payment API
 * ไฟล์นี้ทำหน้าที่เป็น proxy เพื่อความเข้ากันได้กับ Bank URL ที่ลงทะเบียนไว้
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

// Log file for debugging
$logFile = __DIR__ . '/payment.log';
function logP($msg)
{
    global $logFile;
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, FILE_APPEND);
}

// Get incoming data
$rawData = file_get_contents('php://input');
logP("Incoming Request: " . $rawData);

// Parse data
$data = json_decode($rawData, true);
if (!$data) {
    $data = $_POST;
    if (empty($data)) {
        $data = $_GET;
    }
}

// Forward to API
try {
    // Build API URL
    require_once __DIR__ . '/../config/env.php';
    $apiBase = defined('BASE_PATH') ? BASE_PATH : '/edonation';
    $apiUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
        . '://' . $_SERVER['HTTP_HOST']
        . $apiBase . '/api/v1/payments/callback';

    logP("Forwarding to API: " . $apiUrl);

    // Make API request
    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        logP("cURL Error: " . curl_error($ch));
        throw new Exception("API request failed: " . curl_error($ch));
    }

    curl_close($ch);

    logP("API Response ($httpCode): " . $response);

    // Return API response
    header('Content-Type: application/json');
    http_response_code($httpCode);
    echo $response;

} catch (Exception $e) {
    logP("Exception: " . $e->getMessage());

    // Fallback: Process locally if API fails
    logP("Fallback: Processing locally");

    try {
        require_once __DIR__ . '/../config/database.php';

        $ref1 = $data['billPaymentRef1'] ?? $data['ref1'] ?? '';
        $amount = $data['amount'] ?? 0;
        $transactionId = $data['transactionId'] ?? '';

        if (empty($ref1) || empty($amount)) {
            throw new Exception("Missing required parameters");
        }

        // Find donation
        $stmt = $pdo->prepare("SELECT * FROM edonation_donat_user WHERE billPaymentRef1 = :ref1 LIMIT 1");
        $stmt->execute([':ref1' => $ref1]);
        $donation = $stmt->fetch();

        if (!$donation) {
            throw new Exception("Ref1 not found");
        }

        // Already completed?
        if ($donation['status_donat'] === 'completed') {
            echo json_encode(['resCode' => '00', 'resDesc' => 'Success (Already Paid)']);
            exit;
        }

        // Update status
        $updateStmt = $pdo->prepare("
            UPDATE edonation_donat_user 
            SET status_donat = 'completed', updated_at = NOW() 
            WHERE id = :id
        ");
        $updateStmt->execute([':id' => $donation['id']]);

        logP("Fallback Success: Updated donation ID " . $donation['id']);

        echo json_encode([
            'resCode' => '00',
            'resDesc' => 'Success',
            'transactionId' => $transactionId,
            'confirmId' => $donation['id']
        ]);

    } catch (Exception $fallbackError) {
        logP("Fallback Error: " . $fallbackError->getMessage());
        http_response_code(500);
        echo json_encode(['resCode' => '99', 'resDesc' => 'Internal Error']);
    }
}
