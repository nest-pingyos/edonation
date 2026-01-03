<?php
/**
 * Bank Payment Callback Receiver
 * รับ callback จากธนาคาร และส่งต่อไปยัง API
 * 
 * Endpoint: POST /recieve.php
 * Forwards to: POST /api/v1/payments/callback
 */

// Error handling - Don't expose errors to bank
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON response header
header('Content-Type: application/json; charset=UTF-8');

// Log file for debugging
$logFile = __DIR__ . '/logs/bank_callback.log';

function logCallback($message) {
    global $logFile;
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

// Get raw input
$rawInput = file_get_contents('php://input');
logCallback("Incoming: " . $rawInput);

// Validate JSON
$data = json_decode($rawInput, true);
if (!$data || !is_array($data)) {
    logCallback("Error: Invalid JSON");
    echo json_encode([
        'resCode' => '01',
        'resDesc' => 'Invalid JSON format',
        'transactionId' => null,
        'confirmId' => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Forward to API - use BASE_PATH from config
require_once __DIR__ . '/config/env.php';
$basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';
$apiUrl = 'http://' . $_SERVER['HTTP_HOST'] . $basePath . '/api/v1/payments/callback';

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $rawInput,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($rawInput)
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    logCallback("CURL Error: " . $error);
    echo json_encode([
        'resCode' => '99',
        'resDesc' => 'Internal error',
        'transactionId' => $data['transactionId'] ?? null,
        'confirmId' => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

logCallback("API Response (HTTP $httpCode): " . $response);

// Return API response directly
echo $response;
