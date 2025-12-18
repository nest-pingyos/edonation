<?php
/**
 * Test LINE Notification
 * ทดสอบการส่งแจ้งเตือน LINE
 * 
 * URL: http://localhost/appdev/edonation/test_line_notification.php
 */

require_once __DIR__ . '/api/config/bootstrap.php';
require_once __DIR__ . '/api/services/LineNotificationService.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    echo "=== ทดสอบ LINE Notification ===\n\n";

    // 1. ตรวจสอบข้อมูล recipients
    $pdo = Database::getInstance();
    $stmt = $pdo->query("SELECT * FROM notification_recipients WHERE notification_type = 'payment_success' AND is_active = 1");
    $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "1. ตรวจสอบ Recipients:\n";
    if (empty($recipients)) {
        echo "   ❌ ไม่พบ recipients สำหรับ payment_success\n";
        echo "   กรุณารัน SQL:\n";
        echo "   INSERT INTO notification_recipients (notification_type, recipient_email, cmu_account, is_active)\n";
        echo "   VALUES ('payment_success', 'your@cmu.ac.th', 'your.account', 1);\n\n";
        exit;
    }

    foreach ($recipients as $r) {
        echo "   - ID: {$r['id']}\n";
        echo "     CMU Account: " . ($r['cmu_account'] ?: '(NULL - ต้องใส่ค่า!)') . "\n";
        echo "     Active: " . ($r['is_active'] ? 'Yes' : 'No') . "\n";
    }

    // 2. ทดสอบส่งแจ้งเตือน
    echo "\n2. ทดสอบส่งแจ้งเตือน:\n";

    $notifier = new LineNotificationService();

    // ส่งทดสอบ payment_success
    $result = $notifier->sendPaymentSuccessNotification(
        999,                    // donationId (test)
        1000.00,                // amount
        'โครงการทดสอบระบบ',      // projectName
        'ผู้ทดสอบระบบ'          // donorName
    );

    echo "   Result:\n";
    echo "   - Success: " . ($result['success'] ? 'Yes ✅' : 'No ❌') . "\n";
    echo "   - Message: " . $result['message'] . "\n";

    if (!empty($result['results'])) {
        echo "   - Details:\n";
        foreach ($result['results'] as $r) {
            echo "     * CMU Account: {$r['cmu_account']}\n";
            echo "       Success: " . ($r['success'] ? 'Yes' : 'No') . "\n";
            echo "       Message: {$r['message']}\n";
        }
    }

    // 3. ตรวจสอบ logs
    echo "\n3. ตรวจสอบ Logs (ล่าสุด 5 รายการ):\n";
    $logStmt = $pdo->query("SELECT * FROM notification_logs ORDER BY created_at DESC LIMIT 5");
    $logs = $logStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($logs)) {
        echo "   ไม่มี logs\n";
    } else {
        foreach ($logs as $log) {
            echo "   - [{$log['created_at']}] {$log['status']} -> {$log['recipient_email']}\n";
        }
    }

    echo "\n=== จบการทดสอบ ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
