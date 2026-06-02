<?php
// Debugging
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 1);

// Root Path Calculation
$rootPath = dirname(dirname(__DIR__)); // /Applications/XAMPP/xamppfiles/htdocs/edonation
$adminSrcPath = dirname(__FILE__);     // /Applications/XAMPP/xamppfiles/htdocs/edonation/admin/src

// 1. Config & Admin Session
require_once $adminSrcPath . '/services/session.php';

// 2. API Dependencies (Minimal set to avoid constant conflicts)
require_once $rootPath . '/api/config/env.php';
require_once $rootPath . '/api/config/database.php';
require_once $rootPath . '/api/helpers/Response.php';

// 3. Controller
require_once $rootPath . '/api/controllers/MemberController.php';

// Check Admin Authentication
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Method Not Allowed');
}

$ids = $_POST['ids'] ?? '';
if (empty($ids)) {
    die('No IDs selected');
}

$idsArray = explode(',', $ids);

try {
    // ใช้ Controller ดึงข้อมูลแทนการ Query เอง
    $memberController = new MemberController();
    $transactions = $memberController->getTransactionsForExport($idsArray);

    if (empty($transactions)) {
        die('ไม่พบข้อมูลสำหรับรายการที่เลือก');
    }

    // Group data by id_members
    // แต่ละ row ที่มี receipt_number = NULL คือสมาชิกที่ไม่มีใบเสร็จ (LEFT JOIN ไม่ match)
    $groupedData = [];
    foreach ($transactions as $t) {
        $id = $t['id_members'];
        if (!isset($groupedData[$id])) {
            $groupedData[$id] = [
                'info'  => $t,
                'items' => []
            ];
        }
        // เพิ่มเฉพาะ row ที่มีใบเสร็จจริง
        if (!empty($t['receipt_number'])) {
            $groupedData[$id]['items'][] = $t;
        }
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

        // Name: ใช้ full_name จาก edonation_members เป็นหลัก
        $name = $m['full_name'] ?: trim(($m['title'] ?? '') . ' ' . ($m['first_name'] ?? '') . ' ' . ($m['last_name'] ?? ''));
        if (empty(trim($name))) $name = $m['receipt_name'] ?? '-';

        // Address: ใช้ full_address จาก edonation_members เป็นหลัก
        $addr = $m['address_full'];
        if (empty($addr)) {
            $parts = [];
            if (!empty($m['address_line'])) $parts[] = $m['address_line'];
            if (!empty($m['district']))     $parts[] = 'ต.' . $m['district'];
            if (!empty($m['amphure']))      $parts[] = 'อ.' . $m['amphure'];
            if (!empty($m['province']))     $parts[] = 'จ.' . $m['province'];
            if (!empty($m['zip_code']))     $parts[] = $m['zip_code'];
            $addr = implode(' ', $parts);
        }

        // Write Member Info Block
        fputcsv($output, ['ข้อมูลสมาชิก']);
        fputcsv($output, ['รหัสสมาชิก:',      $m['id_members'] . "\t"]);
        fputcsv($output, ['ชื่อ-นามสกุล:',    $name]);
        fputcsv($output, ['เลขบัตรประชาชน:', $m['id_card'] . "\t"]);
        fputcsv($output, ['เบอร์โทรศัพท์:',  $m['phone'] . "\t"]);
        fputcsv($output, ['อีเมล:',           $m['email'] ?? '-']);
        fputcsv($output, ['ที่อยู่:',          $addr]);
        fputcsv($output, ['อาชีพ:',           $m['occupation'] ?? '-']);

        // Summary for this member
        $totalAmount = array_sum(array_filter(array_column($items, 'amount')));
        fputcsv($output, ['จำนวนครั้งบริจาค:', count($items) . ' ครั้ง']);
        fputcsv($output, ['ยอดบริจาครวม:',     number_format($totalAmount, 2) . ' บาท']);
        fputcsv($output, []); // Blank line

        // --- 2. Donation History Section (Table) ---
        if (!empty($items)) {
            fputcsv($output, ['ประวัติการบริจาค']);
            fputcsv($output, [
                'ลำดับ',
                'วันที่',
                'เวลา',
                'เลขที่ใบเสร็จ',
                'โครงการ',
                'ชื่อผู้บริจาค (หน้าใบเสร็จ)',
                'จำนวนเงิน (บาท)'
            ]);

            foreach ($items as $index => $t) {
                $date = '';
                $time = '';
                if (!empty($t['issued_at'])) {
                    $dt   = new DateTime($t['issued_at']);
                    $date = $dt->format('Y-m-d');
                    $time = $dt->format('H:i:s');
                }

                fputcsv($output, [
                    $index + 1,
                    $date,
                    $time,
                    $t['receipt_number'],
                    $t['project_name'] ?? '-',
                    $t['receipt_name'] ?? '-',
                    number_format((float) $t['amount'], 2)
                ]);
            }
        } else {
            fputcsv($output, ['ประวัติการบริจาค', 'ไม่พบข้อมูลใบเสร็จ']);
        }

        // End of Member Block - Separator
        fputcsv($output, []);
        fputcsv($output, ['--------------------------------------------------']);
        fputcsv($output, []);
    }

    fclose($output);
    exit;

} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
