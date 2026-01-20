<?php
// Debugging (Suppress Warnings about duplicate constants)
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 1);

// Root Path Calculation
$rootPath = dirname(dirname(__DIR__)); // /Applications/XAMPP/xamppfiles/htdocs/edonation
$adminSrcPath = dirname(__FILE__);     // /Applications/XAMPP/xamppfiles/htdocs/edonation/admin/src

// 1. Include Admin Auth Middleware FIRST (loads admin config & session)
require_once $adminSrcPath . '/auth/middleware.php';

// 2. Include Database Config (safe to include after admin config due to checks inside datebase.php)
require_once $rootPath . '/config/database.php';

// Start Session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check Admin Authentication
requireAuthentication();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Method Not Allowed');
}

$ids = $_POST['ids'] ?? '';
if (empty($ids)) {
    die('No IDs selected');
}

$idsArray = explode(',', $ids);

try {
    // Check if $pdo exists (from database.php)
    if (!isset($pdo) || !$pdo) {
        throw new Exception("Database connection failed or not initialized.");
    }

    // Use global $pdo directly
    $placeholders = implode(',', array_fill(0, count($idsArray), '?'));

    // Query: Get ALL receipts for selected members (Transaction-based)
    $sql = "SELECT 
                r.issued_at,
                r.receipt_no as receipt_number,
                r.amount,
                du.project_name,
                r.id_members,
                r.id_card,
                r.payer_name as receipt_name,
                du.first_name,
                du.last_name,
                du.title,
                du.phone,
                du.occupation,
                du.receipt_address as address_full,
                du.address_line,
                du.province,
                du.amphure,
                du.district,
                du.zip_code
            FROM edonation_receipts r
            LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
            WHERE r.id_members IN ($placeholders)
            ORDER BY r.id_members ASC, r.issued_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($idsArray);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group data by id_members
    $groupedData = [];
    foreach ($transactions as $t) {
        $id = $t['id_members'];
        if (!isset($groupedData[$id])) {
            $groupedData[$id] = [
                'info' => $t, // Store first record as member info representative
                'items' => []
            ];
        }
        $groupedData[$id]['items'][] = $t;
    }

    // Set Headers for Download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=members_report_' . date('Ymd_His') . '.csv');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // BOM for Excel
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

    foreach ($groupedData as $memberId => $group) {
        $m = $group['info'];
        $items = $group['items'];

        // --- 1. Member Detail Section (Header) ---

        // Name Logic
        $name = trim(($m['title'] ?? '') . ' ' . $m['first_name'] . ' ' . ($m['last_name'] ?? ''));
        if (empty($name))
            $name = $m['receipt_name'];

        // Address Logic
        $addr = $m['address_full'];
        if (empty($addr)) {
            $parts = [];
            if ($m['address_line'])
                $parts[] = $m['address_line'];
            if ($m['district'])
                $parts[] = 'ต.' . $m['district'];
            if ($m['amphure'])
                $parts[] = 'อ.' . $m['amphure'];
            if ($m['province'])
                $parts[] = 'จ.' . $m['province'];
            if ($m['zip_code'])
                $parts[] = $m['zip_code'];
            $addr = implode(' ', $parts);
        }

        // Write Member Info Block
        fputcsv($output, ['ข้อมูลสมาชิก']);
        fputcsv($output, ['รหัสสมาชิก:', $m['id_members'] . "\t"]); // Tab for text format
        fputcsv($output, ['ชื่อ-นามสกุล:', $name]);
        fputcsv($output, ['เลขบัตรประชาชน:', $m['id_card'] . "\t"]);
        fputcsv($output, ['เบอร์โทรศัพท์:', $m['phone'] . "\t"]);
        fputcsv($output, ['ที่อยู่:', $addr]);
        fputcsv($output, ['อาชีพ:', $m['occupation'] ?? '-']);

        // Summary for this member
        $totalAmount = array_sum(array_column($items, 'amount'));
        fputcsv($output, ['ยอดบริจาครวม:', number_format($totalAmount, 2)]);
        fputcsv($output, []); // Blank line

        // --- 2. Donation History Section (Table) ---
        fputcsv($output, ['ประวัติการบริจาค']);
        // Table Header
        fputcsv($output, [
            'ลำดับ',
            'วันที่',
            'เวลา',
            'เลขที่ใบเสร็จ',
            'โครงการ',
            'ชื่อผู้บริจาค (หน้าใบเสร็จ)',
            'จำนวนเงิน'
        ]);

        // Transaction Rows
        foreach ($items as $index => $t) {
            // DateTime Logic
            $date = '';
            $time = '';
            if (!empty($t['issued_at'])) {
                $dt = new DateTime($t['issued_at']);
                $date = $dt->format('Y-m-d');
                $time = $dt->format('H:i:s');
            }

            fputcsv($output, [
                $index + 1,
                $date,
                $time,
                $t['receipt_number'],
                $t['project_name'] ?? '-',
                $t['receipt_name'],
                number_format($t['amount'], 2)
            ]);
        }

        // End of Member Block - Separator
        fputcsv($output, []);
        fputcsv($output, ['--------------------------------------------------']);
        fputcsv($output, []);
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    die('Database Error: ' . $e->getMessage());
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
