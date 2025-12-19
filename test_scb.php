<?php
/**
 * SCB API Test Script
 * ทดสอบการเชื่อมต่อ SCB API สำหรับสร้าง QR Code
 * 
 * URL: https://app.nurse.cmu.ac.th/appdev/edonation/test_scb.php
 */

header('Content-Type: text/html; charset=UTF-8');

// Load dependencies
require_once __DIR__ . '/api/config/bootstrap.php';
require_once __DIR__ . '/api/services/SCBPaymentService.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>SCB API Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; }
        .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; }
        .info { color: #1565c0; background: #e3f2fd; padding: 10px; border-radius: 5px; }
        pre { background: #f5f5f5; padding: 15px; overflow-x: auto; border-radius: 5px; }
        h2 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        .qr-container { text-align: center; margin: 20px 0; }
        .qr-container img { border: 2px solid #ddd; padding: 10px; background: white; }
    </style>
</head>
<body>
    <h1>🧪 SCB API Test</h1>
    <p>ทดสอบการเชื่อมต่อ SCB Open Banking API</p>
    <hr>";

// Test 1: Check Config
echo "<h2>1. ตรวจสอบ Config</h2>";

if (defined('SCB_API_KEY') && !empty(SCB_API_KEY)) {
    echo "<div class='success'>✅ SCB_API_KEY: ตั้งค่าแล้ว (" . substr(SCB_API_KEY, 0, 10) . "...)</div>";
} else {
    echo "<div class='error'>❌ SCB_API_KEY: ไม่ได้ตั้งค่า</div>";
}

if (defined('SCB_BILLER_ID') && !empty(SCB_BILLER_ID)) {
    echo "<div class='success'>✅ SCB_BILLER_ID: " . SCB_BILLER_ID . "</div>";
} else {
    echo "<div class='error'>❌ SCB_BILLER_ID: ไม่ได้ตั้งค่า</div>";
}

// Test 2: OAuth Token
echo "<h2>2. ทดสอบ OAuth Token</h2>";

try {
    $scb = new SCBPaymentService();
    $token = $scb->getAccessToken();

    if ($token) {
        echo "<div class='success'>✅ OAuth Token: สำเร็จ</div>";
        echo "<pre>Token: " . substr($token, 0, 50) . "...</pre>";
    } else {
        echo "<div class='error'>❌ ไม่สามารถรับ OAuth Token ได้</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    $token = null;
}

// Test 3: Create QR Code
echo "<h2>3. ทดสอบสร้าง QR Code</h2>";

if ($token) {
    try {
        // Test data
        $testAmount = 1.00; // ทดสอบ 1 บาท
        $testRef1 = 'TEST' . date('YmdHis');
        $testRef2 = '1234567890123';

        echo "<div class='info'>📝 ข้อมูลทดสอบ:</div>";
        echo "<pre>";
        echo "Amount: " . number_format($testAmount, 2) . " บาท\n";
        echo "Ref1: " . $testRef1 . "\n";
        echo "Ref2: " . $testRef2 . "\n";
        echo "</pre>";

        $result = $scb->createQRCode($testAmount, $testRef1, $testRef2);

        if ($result && isset($result['success']) && $result['success']) {
            echo "<div class='success'>✅ สร้าง QR Code สำเร็จ!</div>";

            if (isset($result['qr_image'])) {
                echo "<div class='qr-container'>";
                echo "<img src='data:image/png;base64," . $result['qr_image'] . "' alt='QR Code'>";
                echo "<p><strong>⚠️ นี่คือ QR ทดสอบ - อย่าสแกนจ่ายจริง!</strong></p>";
                echo "</div>";
            }

            echo "<pre>";
            echo "Transaction ID: " . ($result['transaction_id'] ?? 'N/A') . "\n";
            echo "Expiry: " . ($result['expiry_date'] ?? 'N/A') . "\n";
            echo "</pre>";

        } else {
            echo "<div class='error'>❌ สร้าง QR Code ไม่สำเร็จ</div>";
            echo "<pre>" . print_r($result, true) . "</pre>";
        }

    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
} else {
    echo "<div class='error'>❌ ไม่สามารถทดสอบได้ เนื่องจากไม่มี Token</div>";
}

// Test 4: Network Check
echo "<h2>4. ตรวจสอบ Network</h2>";

$scbHost = 'api.scb.co.th';
$fp = @fsockopen($scbHost, 443, $errno, $errstr, 5);
if ($fp) {
    echo "<div class='success'>✅ เชื่อมต่อ $scbHost ได้</div>";
    fclose($fp);
} else {
    echo "<div class='error'>❌ ไม่สามารถเชื่อมต่อ $scbHost: $errstr ($errno)</div>";
}

echo "
    <hr>
    <p><strong>หมายเหตุ:</strong> ลบไฟล์นี้หลังทดสอบเสร็จ!</p>
    <p><a href='home/'>← กลับหน้าหลัก</a></p>
</body>
</html>";
