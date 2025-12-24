<?php
/**
 * QR Code Generator API (SCB Dynamic QR)
 */

// Start output buffering to prevent "headers already sent"
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load web config and shared service
try {
    require_once __DIR__ . '/../config/env.php';
    require_once dirname(__DIR__, 2) . '/shared/services/SCBPaymentService.php';
} catch (Throwable $e) {
    ob_end_clean();
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Initialization failed', 'message' => $e->getMessage()]);
    exit;
}

// รับ parameters
$donationId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$ref1 = isset($_GET['ref']) ? trim($_GET['ref']) : '';
$ref2 = isset($_GET['ref2']) ? trim($_GET['ref2']) : '';
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;

// Validate basic parameters
if (empty($ref1) || $amount <= 0 || $donationId <= 0) {
    ob_end_clean();
    header('HTTP/1.1 400 Bad Request');
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Missing required parameters',
        'id' => $donationId,
        'ref' => $ref1,
        'amount' => $amount
    ]);
    exit;
}

// ===== Security: ตรวจสอบว่า Donation มีอยู่จริง =====
try {
    require_once __DIR__ . '/../config/database.php';

    // Ensure $pdo is available (it should be from database.php)
    if (!isset($pdo)) {
        throw new Exception("Database connection variable \$pdo not found");
    }

    $stmt = $pdo->prepare("SELECT id, amount, billPaymentRef1, status_donat FROM edonation_donat_user WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $donationId]);
    $donation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$donation) {
        throw new Exception("Donation record not found (ID: $donationId)");
    }

    // Check ref and amount match
    // Use epsilon-friendly comparison for float if needed, but here simple comparison usually works for currency
    if ($donation['billPaymentRef1'] !== $ref1 || round(floatval($donation['amount']), 2) !== round($amount, 2)) {
        throw new Exception("Parameters mismatch for Donation #$donationId");
    }

    if ($donation['status_donat'] === 'completed') {
        throw new Exception("Payment already completed for Donation #$donationId");
    }

} catch (Throwable $e) {
    ob_end_clean();
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Validation failed',
        'message' => $e->getMessage()
    ]);
    exit;
}

// Log request
error_log("QR Request: id=$donationId, ref1=$ref1, amount=$amount");

// สร้าง QR Code ผ่าน SCB API
try {
    $scb = new SCBPaymentService();
    $result = $scb->createQRCode($amount, $ref1, $ref2);

    if ($result && $result['success'] && isset($result['qr_image'])) {
        $imageData = base64_decode($result['qr_image']);
        ob_end_clean();
        header('Content-Type: image/png');
        header('Content-Length: ' . strlen($imageData));
        echo $imageData;
        exit;
    }
} catch (Throwable $e) {
    error_log("SCB API Error: " . $e->getMessage());
}

// Fallback: ใช้ระบบเดิม (Manual EMVCo) กรณี SCB API ล้มเหลว
try {
    error_log("QR Fallback: Using manual EMVCo generation for Donation #$donationId");

    if (file_exists(__DIR__ . '/lib-crc16.inc.php')) {
        require_once __DIR__ . '/lib-crc16.inc.php';
    } else {
        throw new Exception("lib-crc16.inc.php not found");
    }

    if (!function_exists('f')) {
        function f($tag, $value)
        {
            return $tag . sprintf('%02d', strlen($value)) . $value;
        }
    }

    $amountFormatted = number_format($amount, 2, '.', '');
    $billerId = defined('SCB_BILLER_ID') ? SCB_BILLER_ID : '';

    $tag30 = f('00', 'A000000677010112') .
        f('01', $billerId) .
        f('02', $ref1) .
        f('03', $ref2 ?: '0');

    $payload = f('00', '01') .
        f('01', '12') .
        f('30', $tag30) .
        f('53', '764') .
        f('54', $amountFormatted) .
        f('58', 'TH') .
        f('62', f('07', 'SCB001')) .
        '6304';

    if (function_exists('CRC16HexDigest')) {
        $checksum = CRC16HexDigest($payload);
        $qrcodeFull = $payload . $checksum;

        ob_end_clean();
        header('Location: https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrcodeFull));
        exit;
    } else {
        throw new Exception("CRC16HexDigest function not found");
    }
} catch (Throwable $e) {
    ob_end_clean();
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json');
    echo json_encode(['error' => 'QR generation failed', 'message' => $e->getMessage()]);
    exit;
}

