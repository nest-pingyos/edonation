<?php
/**
 * Test Payment Callback with LINE Notification
 * จำลองการชำระเงินและตรวจสอบการแจ้งเตือน
 */

require_once __DIR__ . '/api/config/bootstrap.php';
require_once __DIR__ . '/api/services/LineNotificationService.php';

header('Content-Type: text/plain; charset=UTF-8');

echo "=== Test Payment Notification Flow ===\n\n";

$pdo = Database::getInstance();

// 1. ดึงข้อมูล donation ล่าสุดที่ completed
echo "1. ดึงข้อมูล donation ล่าสุด:\n";
$stmt = $pdo->query("SELECT * FROM donat_user WHERE status_donat = 'completed' ORDER BY updated_at DESC LIMIT 1");
$donation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$donation) {
    echo "   ❌ ไม่พบ donation ที่ completed\n";
    exit;
}

echo "   ID: {$donation['id']}\n";
echo "   Amount: {$donation['amount']}\n";
echo "   Project Number: {$donation['project_number']}\n";

// 2. ทดสอบ Query ดึงชื่อโครงการ
echo "\n2. ทดสอบ Query ดึงชื่อโครงการ:\n";
try {
    $getProject = $pdo->prepare("
        SELECT p.project_name 
        FROM donat_user d
        JOIN projects p ON d.project_number = p.project_number
        WHERE d.id = :id
    ");
    $getProject->execute([':id' => $donation['id']]);
    $project = $getProject->fetch();

    if ($project) {
        echo "   ✓ Project Name: {$project['project_name']}\n";
    } else {
        echo "   ❌ ไม่พบโครงการ (project_number ไม่ตรงกัน?)\n";

        // ตรวจสอบว่า project_number มีอยู่ใน projects หรือไม่
        $checkProject = $pdo->prepare("SELECT * FROM projects WHERE project_number = :pnum");
        $checkProject->execute([':pnum' => $donation['project_number']]);
        $projectExists = $checkProject->fetch();

        if ($projectExists) {
            echo "   Project มีอยู่: {$projectExists['project_name']}\n";
        } else {
            echo "   ❌ Project number {$donation['project_number']} ไม่มีในตาราง projects\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// 3. ทดสอบส่ง LINE Notification
echo "\n3. ทดสอบส่ง LINE Notification:\n";
try {
    $notifier = new LineNotificationService();

    $projectName = $project['project_name'] ?? 'ทดสอบโครงการ';
    $donorName = trim(($donation['first_name'] ?? '') . ' ' . ($donation['last_name'] ?? ''));

    echo "   Sending...\n";
    echo "   - Donation ID: {$donation['id']}\n";
    echo "   - Amount: {$donation['amount']}\n";
    echo "   - Project: $projectName\n";
    echo "   - Donor: " . ($donorName ?: 'ไม่ระบุ') . "\n\n";

    $result = $notifier->sendPaymentSuccessNotification(
        (int) $donation['id'],
        (float) $donation['amount'],
        $projectName,
        $donorName
    );

    echo "   Result:\n";
    echo "   - Success: " . ($result['success'] ? 'YES ✓' : 'NO ✗') . "\n";
    echo "   - Message: " . $result['message'] . "\n";

    if (!empty($result['results'])) {
        echo "   - Details:\n";
        foreach ($result['results'] as $r) {
            echo "     * CMU Account: {$r['cmu_account']}\n";
            echo "       Success: " . ($r['success'] ? 'Yes' : 'No') . "\n";
            echo "       Message: {$r['message']}\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   Stack: " . $e->getTraceAsString() . "\n";
}

// 4. ตรวจสอบ logs
echo "\n4. Notification Logs ล่าสุด:\n";
$logStmt = $pdo->query("SELECT * FROM notification_logs ORDER BY created_at DESC LIMIT 5");
$logs = $logStmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($logs)) {
    echo "   ไม่มี logs\n";
} else {
    foreach ($logs as $log) {
        echo "   [{$log['created_at']}] {$log['status']} | {$log['recipient_email']}\n";
    }
}

echo "\n=== จบการทดสอบ ===\n";
