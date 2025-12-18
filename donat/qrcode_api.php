<?php
/**
 * QR Code Generator API (SCB Dynamic QR)
 * 
 * ใช้ SCB Open Banking API สร้าง Dynamic QR Code
 * - Single-use (ใช้ได้ครั้งเดียว)
 * - หมดอายุใน 30 นาที
 * 
 * Parameters:
 * - ref1: เลขอ้างอิงการบริจาค (billPaymentRef1)
 * - ref2: เลขประจำตัวผู้เสียภาษี (Tax ID)
 * - amount: ยอดเงิน
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../api/config/bootstrap.php';
require_once __DIR__ . '/../api/services/SCBPaymentService.php';

// รับ parameters
$ref1 = isset($_GET['ref']) ? trim($_GET['ref']) : '';
$ref2 = isset($_GET['ref2']) ? trim($_GET['ref2']) : '';
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;

// Validate
if (empty($ref1) || $amount <= 0) {
    header('HTTP/1.1 400 Bad Request');
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Missing required parameters',
        'required' => ['ref', 'amount']
    ]);
    exit;
}

// Log request
error_log("QR Request: ref1=$ref1, ref2=$ref2, amount=$amount");

// สร้าง QR Code ผ่าน SCB API
$scb = new SCBPaymentService();
$result = $scb->createQRCode($amount, $ref1, $ref2);

if ($result && $result['success'] && isset($result['qr_image'])) {
    // ส่งกลับเป็นรูป PNG
    $imageData = base64_decode($result['qr_image']);

    header('Content-Type: image/png');
    header('Content-Length: ' . strlen($imageData));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo $imageData;
} else {
    // Fallback: ใช้ระบบเดิม (Manual EMVCo) กรณี SCB API ล้มเหลว
    error_log("QR Fallback: Using manual EMVCo generation");

    require_once __DIR__ . '/lib-crc16.inc.php';

    // TLV Helper
    function f($tag, $value)
    {
        return $tag . sprintf('%02d', strlen($value)) . $value;
    }

    $amountFormatted = number_format($amount, 2, '.', '');
    $billerId = SCB_BILLER_ID;

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

    $checksum = CRC16HexDigest($payload);
    $qrcodeFull = $payload . $checksum;

    // Redirect to QR Server API
    header('Location: https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrcodeFull));
}
exit;
