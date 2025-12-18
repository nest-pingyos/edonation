<?php
/**
 * Debug Payment Notification
 * ตรวจสอบปัญหาการแจ้งเตือนเมื่อชำระเงิน
 */

require_once __DIR__ . '/api/config/bootstrap.php';
require_once __DIR__ . '/api/services/LineNotificationService.php';

header('Content-Type: text/plain; charset=UTF-8');

echo "=== Debug Payment Notification ===\n\n";

$pdo = Database::getInstance();

// 1. ตรวจสอบ notification_recipients
echo "1. ตรวจสอบ notification_recipients:\n";
try {
    $stmt = $pdo->query("SELECT * FROM notification_recipients WHERE notification_type = 'payment_success' AND is_active = 1");
    $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($recipients)) {
        echo "   ❌ ไม่พบ recipients!\n";
    } else {
        foreach ($recipients as $r) {
            echo "   ✓ ID: {$r['id']}, cmu_account: " . ($r['cmu_account'] ?: 'NULL') . "\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// 2. ตรวจสอบ notification_logs ล่าสุด
echo "\n2. notification_logs ล่าสุด:\n";
try {
    $stmt = $pdo->query("SELECT * FROM notification_logs ORDER BY created_at DESC LIMIT 10");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($logs)) {
        echo "   ❌ ไม่มี logs\n";
    } else {
        foreach ($logs as $log) {
            echo "   [{$log['created_at']}] {$log['status']} | {$log['notification_type']} | {$log['recipient_email']}\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// 3. ตรวจสอบ bank_transactions ล่าสุด
echo "\n3. bank_transactions ล่าสุด (ชำระเงินล่าสุด):\n";
try {
    $stmt = $pdo->query("SELECT id, transactionId, billPaymentRef1, amount, confirmId, created_at FROM bank_transactions ORDER BY id DESC LIMIT 5");
    $txns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($txns)) {
        echo "   ❌ ไม่มี transactions\n";
    } else {
        foreach ($txns as $txn) {
            echo "   ID: {$txn['id']}, Amount: {$txn['amount']}, Ref1: {$txn['billPaymentRef1']}, ConfirmId: {$txn['confirmId']}\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// 4. ตรวจสอบ donat_user ที่ชำระเงินล่าสุด
echo "\n4. donat_user ที่ชำระเงินล่าสุด:\n";
try {
    $stmt = $pdo->query("SELECT id, first_name, last_name, project_number, amount, status_donat, updated_at FROM donat_user WHERE status_donat = 'completed' ORDER BY updated_at DESC LIMIT 5");
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($donations)) {
        echo "   ❌ ไม่มีรายการที่ชำระเงินแล้ว\n";
    } else {
        foreach ($donations as $d) {
            echo "   ID: {$d['id']}, {$d['first_name']} {$d['last_name']}, Amount: {$d['amount']}, Updated: {$d['updated_at']}\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// 5. ทดสอบ LineNotificationService โดยตรง
echo "\n5. ทดสอบส่งแจ้งเตือน:\n";
try {
    $notifier = new LineNotificationService();

    // ใช้ข้อมูลจริงจาก donation ล่าสุด
    if (!empty($donations)) {
        $lastDonation = $donations[0];

        // ดึงชื่อโครงการ
        $getProject = $pdo->prepare("SELECT project_name FROM project WHERE project_number = :pnum");
        $getProject->execute([':pnum' => $lastDonation['project_number']]);
        $project = $getProject->fetch();
        $projectName = $project['project_name'] ?? 'ไม่ระบุโครงการ';

        echo "   Sending notification for donation ID: {$lastDonation['id']}\n";
        echo "   Project: {$projectName}\n";
        echo "   Amount: {$lastDonation['amount']}\n";

        $result = $notifier->sendPaymentSuccessNotification(
            (int) $lastDonation['id'],
            (float) $lastDonation['amount'],
            $projectName,
            $lastDonation['first_name'] . ' ' . $lastDonation['last_name']
        );

        echo "   Result: " . ($result['success'] ? 'SUCCESS ✓' : 'FAILED ✗') . "\n";
        echo "   Message: " . $result['message'] . "\n";

        if (!empty($result['results'])) {
            foreach ($result['results'] as $r) {
                echo "   - {$r['cmu_account']}: " . ($r['success'] ? 'OK' : $r['message']) . "\n";
            }
        }
    } else {
        echo "   ❌ ไม่มีข้อมูล donation ให้ทดสอบ\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   Stack: " . $e->getTraceAsString() . "\n";
}

echo "\n=== จบการ Debug ===\n";
