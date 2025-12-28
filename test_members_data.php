<?php
// Test script to verify members data availability
require_once __DIR__ . '/api/config/env.php';
require_once __DIR__ . '/api/config/database.php';

echo "=== E-Donation Members Data Verification ===\n";

try {
    $pdo = Database::getInstance();
    echo "[PASS] Database connected.\n\n";

    // 1. Verify ReportController::summary logic
    echo "1. Testing Stats (ReportController::summary logic)...\n";
    $statsSql = "SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM edonation_receipts";
    $stats = $pdo->query($statsSql)->fetch(PDO::FETCH_ASSOC);
    echo "   - Total Receipts: " . $stats['count'] . "\n";
    echo "   - Total Amount: " . number_format($stats['total'], 2) . "\n";

    if ($stats['count'] > 0) {
        echo "   [PASS] Found receipt data.\n";
    } else {
        echo "   [WARN] No receipt data found. Stats will be zero.\n";
    }
    echo "\n";

    // 2. Verify MemberController::search logic
    echo "2. Testing Search (MemberController::search logic)...\n";
    // Check edonation_donat_user
    $donatSql = "SELECT COUNT(*) as count FROM edonation_donat_user WHERE status_donat = 'completed'";
    $donatCount = $pdo->query($donatSql)->fetchColumn();
    echo "   - Completed Donations (edonation_donat_user): $donatCount\n";

    // Check edonation_bank_transactions
    $bankSql = "SELECT COUNT(*) as count FROM edonation_bank_transactions";
    $bankCount = $pdo->query($bankSql)->fetchColumn();
    echo "   - Bank Transactions: $bankCount\n";

    // Simulate search 'a' (common letter)
    echo "   - Simulating search for 'a'...\n";
    $sql = "SELECT 
                id,
                CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as name,
                amount,
                status_donat
            FROM edonation_donat_user 
            WHERE status_donat = 'completed' 
            AND CONCAT(first_name, ' ', last_name) LIKE '%a%'
            LIMIT 5";
    $results = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) > 0) {
        echo "   [PASS] Found " . count($results) . " matching records:\n";
        foreach ($results as $row) {
            echo "     * " . $row['name'] . " - " . $row['amount'] . "\n";
        }
    } else {
        echo "   [INFO] No records match 'a'. Trying to list any completed donation:\n";
        $sql = "SELECT first_name, last_name, amount FROM edonation_donat_user WHERE status_donat = 'completed' LIMIT 5";
        $any = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        if (count($any) > 0) {
            foreach ($any as $row) {
                echo "     * " . $row['first_name'] . " " . $row['last_name'] . " - " . $row['amount'] . "\n";
            }
        } else {
            echo "     [WARN] No completed donations found at all.\n";
        }
    }

} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}
echo "\n=== End of Verification ===\n";
