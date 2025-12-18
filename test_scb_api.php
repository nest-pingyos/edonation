<?php
/**
 * Test SCB API Integration
 * ทดสอบการเชื่อมต่อ SCB Open Banking API
 * 
 * URL: http://localhost/appdev/edonation/test_scb_api.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/api/config/bootstrap.php';
require_once __DIR__ . '/api/services/SCBPaymentService.php';

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Test SCB API</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .success {
            color: #28a745;
        }

        .error {
            color: #dc3545;
        }

        .warning {
            color: #ffc107;
        }

        h1 {
            color: #1a3a5c;
        }

        h3 {
            color: #333;
            border-bottom: 2px solid #1a3a5c;
            padding-bottom: 10px;
        }

        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 12px;
        }

        .qr-image {
            max-width: 300px;
            border: 1px solid #ddd;
            border-radius: 10px;
            margin: 20px 0;
        }

        .info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }

        code {
            background: #eee;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <h1>🔧 Test SCB Open Banking API</h1>

    <?php
    $scb = new SCBPaymentService();

    // ========================================
    // Test 1: OAuth Token
    // ========================================
    echo '<div class="card">';
    echo '<h3>1. OAuth Token</h3>';

    $startTime = microtime(true);
    $token = $scb->getAccessToken();
    $elapsed = round((microtime(true) - $startTime) * 1000);

    if ($token) {
        echo '<p class="success">✅ ได้รับ Access Token สำเร็จ!</p>';
        echo '<p><strong>Token (บางส่วน):</strong> <code>' . substr($token, 0, 30) . '...</code></p>';
        echo '<p><strong>Response Time:</strong> ' . $elapsed . ' ms</p>';
    } else {
        echo '<p class="error">❌ ไม่สามารถรับ Access Token ได้</p>';
        echo '<p>ตรวจสอบ:</p>';
        echo '<ul>';
        echo '<li>API Key และ Secret ถูกต้องหรือไม่</li>';
        echo '<li>Network สามารถเข้าถึง api.scb.co.th ได้หรือไม่</li>';
        echo '<li>ดู PHP Error Log สำหรับรายละเอียด</li>';
        echo '</ul>';
    }
    echo '</div>';

    // ========================================
    // Test 2: Create QR Code
    // ========================================
    echo '<div class="card">';
    echo '<h3>2. Create Dynamic QR Code</h3>';

    if ($token) {
        // Test data
        $testAmount = 100.00;
        $testRef1 = '256812180001TEST';
        $testRef2 = '1234567890123';

        echo '<div class="info">';
        echo '<strong>Test Parameters:</strong><br>';
        echo "Amount: <code>$testAmount</code> บาท<br>";
        echo "Ref1: <code>$testRef1</code><br>";
        echo "Ref2 (Tax ID): <code>$testRef2</code>";
        echo '</div>';

        $startTime = microtime(true);
        $result = $scb->createQRCode($testAmount, $testRef1, $testRef2);
        $elapsed = round((microtime(true) - $startTime) * 1000);

        if ($result && $result['success']) {
            echo '<p class="success">✅ สร้าง QR Code สำเร็จ!</p>';
            echo '<p><strong>Response Time:</strong> ' . $elapsed . ' ms</p>';
            echo '<p><strong>Expiry:</strong> ' . ($result['expiry_date'] ?? 'N/A') . '</p>';

            if (isset($result['qr_image'])) {
                echo '<p><strong>QR Code:</strong></p>';
                echo '<img src="data:image/png;base64,' . $result['qr_image'] . '" alt="QR Code" class="qr-image">';
            }

            echo '<details><summary>📋 Full Response</summary>';
            echo '<pre>' . print_r($result, true) . '</pre>';
            echo '</details>';
        } else {
            echo '<p class="error">❌ ไม่สามารถสร้าง QR Code ได้</p>';

            if (isset($result['error_code'])) {
                echo '<p><strong>Error Code:</strong> <code>' . $result['error_code'] . '</code></p>';
                echo '<p><strong>Error Message:</strong> ' . ($result['error_message'] ?? 'Unknown') . '</p>';
            }

            echo '<p class="warning">⚠️ ระบบจะใช้ Fallback (Manual EMVCo) แทน</p>';
        }
    } else {
        echo '<p class="warning">⚠️ ข้ามการทดสอบ QR เนื่องจากไม่มี Token</p>';
    }
    echo '</div>';

    // ========================================
    // Test 3: Configuration Check
    // ========================================
    echo '<div class="card">';
    echo '<h3>3. Configuration</h3>';
    echo '<table style="width:100%; border-collapse: collapse;">';
    echo '<tr><td style="padding:8px; border-bottom:1px solid #eee;"><strong>API Key</strong></td><td style="padding:8px; border-bottom:1px solid #eee;"><code>' . substr(SCB_API_KEY, 0, 10) . '...</code></td></tr>';
    echo '<tr><td style="padding:8px; border-bottom:1px solid #eee;"><strong>Biller ID</strong></td><td style="padding:8px; border-bottom:1px solid #eee;"><code>' . SCB_BILLER_ID . '</code></td></tr>';
    echo '<tr><td style="padding:8px; border-bottom:1px solid #eee;"><strong>QR Expiry</strong></td><td style="padding:8px; border-bottom:1px solid #eee;">' . SCB_QR_EXPIRY_MINUTES . ' นาที</td></tr>';
    echo '<tr><td style="padding:8px; border-bottom:1px solid #eee;"><strong>Single-use</strong></td><td style="padding:8px; border-bottom:1px solid #eee;">' . (SCB_QR_NUMBER_OF_TIMES == 1 ? '✅ Yes' : '❌ No') . '</td></tr>';
    echo '<tr><td style="padding:8px; border-bottom:1px solid #eee;"><strong>Ref3 (หน่วยงาน)</strong></td><td style="padding:8px; border-bottom:1px solid #eee;"><code>' . SCB_REF3 . '</code></td></tr>';
    echo '</table>';
    echo '</div>';

    // ========================================
    // Test 4: Network Check
    // ========================================
    echo '<div class="card">';
    echo '<h3>4. Network Connectivity</h3>';

    $scbHost = 'api.scb.co.th';
    $port = 443;

    $connection = @fsockopen('ssl://' . $scbHost, $port, $errno, $errstr, 10);

    if ($connection) {
        fclose($connection);
        echo '<p class="success">✅ สามารถเชื่อมต่อ ' . $scbHost . ':' . $port . ' ได้</p>';
    } else {
        echo '<p class="error">❌ ไม่สามารถเชื่อมต่อ ' . $scbHost . ':' . $port . '</p>';
        echo '<p>Error: ' . $errstr . ' (' . $errno . ')</p>';
    }
    echo '</div>';
    ?>

    <div class="card">
        <h3>📌 Next Steps</h3>
        <ul>
            <li>ถ้า Token และ QR สำเร็จ → ระบบพร้อมใช้งานแล้ว!</li>
            <li>ถ้า Token ล้มเหลว → ตรวจสอบ API Key/Secret</li>
            <li>ถ้า QR ล้มเหลว → ระบบจะใช้ Fallback อัตโนมัติ</li>
        </ul>
        <p><a href="donat/qrgenerator.php?id=39">🧪 ทดสอบหน้า QR Generator จริง</a></p>
    </div>

</body>

</html>