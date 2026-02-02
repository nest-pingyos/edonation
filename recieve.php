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

function logCallback($message)
{
    $logFile = __DIR__ . '/logs/bank_callback.log';
    if (!is_dir(dirname($logFile))) {
        @mkdir(dirname($logFile), 0777, true);
    }
    @file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
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

// Determine API URL - inside Docker, use localhost:80 (internal Apache)
// Outside Docker (with port 8080), the HTTP_HOST will include the port
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
// If running inside container, call internal API on port 80
if (strpos($host, ':') !== false) {
    // External call with port - use internal localhost
    $apiUrl = 'http://localhost' . $basePath . '/api/v1/payments/callback';
} else {
    // Normal call without port
    $apiUrl = 'http://' . $host . $basePath . '/api/v1/payments/callback';
}

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
