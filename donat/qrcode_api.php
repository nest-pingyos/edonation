<?php
/**
 * QR Code Generator API
 * PromptPay QR Code using QR Server API
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/lib-crc16.inc.php';

// TLV Helper
function f($tag, $value) {
    return $tag . sprintf('%02d', strlen($value)) . $value;
}

$ref = isset($_GET['ref']) ? $_GET['ref'] : '';
$amount = isset($_GET['amount']) ? $_GET['amount'] : 0;

if (empty($ref) || $amount <= 0) die("Error");

// Build PromptPay Payload
$amountFormatted = number_format((float)$amount, 2, '.', '');
$billerId = '099400258783792'; 

$tag30 = f('00', 'A000000677010112') . 
         f('01', $billerId) . 
         f('02', $ref) . 
         f('03', '0');

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

// Redirect to QR Server API (Clean & Simple)
header('Location: https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrcodeFull));
exit;
