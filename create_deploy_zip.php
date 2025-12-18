<?php
/**
 * Create Deploy Package
 * สร้างไฟล์ ZIP สำหรับ Deploy
 */

$zip = new ZipArchive();
$zipFile = __DIR__ . '/deploy_scb_integration.zip';

if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Cannot create ZIP file");
}

$files = [
    'api/config/scb.php',
    'api/services/SCBPaymentService.php',
    'api/services/LineNotificationService.php',
    'api/controllers/DonationController.php',
    'api/controllers/PaymentController.php',
    'donat/qrcode_api.php',
    'donat/qrgenerator.php',
];

foreach ($files as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        $zip->addFile($fullPath, $file);
        echo "Added: $file\n";
    } else {
        echo "NOT FOUND: $file\n";
    }
}

$zip->close();

echo "\n✅ ZIP created: $zipFile\n";
echo "File size: " . number_format(filesize($zipFile)) . " bytes\n";
