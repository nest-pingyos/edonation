<?php
require_once __DIR__ . '/api/config/bootstrap.php';

$pdo = Database::getInstance();
$stmt = $pdo->query("SELECT id, notification_type, cmu_account, is_active FROM notification_recipients WHERE notification_type = 'payment_success'");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== notification_recipients (payment_success) ===\n\n";

if (empty($results)) {
    echo "❌ ไม่พบข้อมูล! กรุณาเพิ่มข้อมูลในตาราง notification_recipients\n";
} else {
    foreach ($results as $row) {
        echo "ID: " . $row['id'] . "\n";
        echo "Type: " . $row['notification_type'] . "\n";
        echo "CMU Account: " . ($row['cmu_account'] ?: '(NULL)') . "\n";
        echo "Active: " . ($row['is_active'] ? 'Yes' : 'No') . "\n";
        echo "---\n";
    }
}
