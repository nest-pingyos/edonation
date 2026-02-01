<?php
/**
 * eDonation System - Local Environment Test
 * 
 * ไฟล์ทดสอบสำหรับ Local Development (XAMPP)
 * 
 * วิธีใช้: http://localhost/edonation/local_test.php
 * 
 * @version 2.0
 * @date 2026-02-01
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test results storage
$results = [];
$passed = 0;
$failed = 0;

function addTest($category, $name, $status, $message = '')
{
    global $results, $passed, $failed;
    $results[$category][] = [
        'name' => $name,
        'status' => $status,
        'message' => $message
    ];
    $status ? $passed++ : $failed++;
}

function testApi($url)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => ['Accept: application/json']
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error)
        return ['success' => false, 'message' => "Error: {$error}"];
    return [
        'success' => $httpCode >= 200 && $httpCode < 400,
        'message' => "HTTP {$httpCode}"
    ];
}

// =============================================
// Run Tests
// =============================================

// 1. Environment
$envFile = __DIR__ . '/.env';
addTest('environment', 'ไฟล์ .env', file_exists($envFile), file_exists($envFile) ? 'พบไฟล์' : 'ไม่พบ');

if (file_exists($envFile)) {
    require_once __DIR__ . '/config/env.php';
}

addTest('environment', 'APP_ENV', defined('APP_ENV') && APP_ENV === 'development', defined('APP_ENV') ? APP_ENV : 'N/A');
addTest('environment', 'APP_DEBUG', defined('APP_DEBUG') && APP_DEBUG === true, defined('APP_DEBUG') ? (APP_DEBUG ? 'true' : 'false') : 'N/A');
addTest('environment', 'BASE_PATH', defined('BASE_PATH'), defined('BASE_PATH') ? BASE_PATH : 'N/A');
addTest('environment', 'APP_URL', defined('APP_URL'), defined('APP_URL') ? APP_URL : 'N/A');

// 2. Database
$dbConnected = false;
$dbError = '';
try {
    require_once __DIR__ . '/config/database.php';
    $pdo->query('SELECT 1');
    $dbConnected = true;
} catch (Exception $e) {
    $dbError = $e->getMessage();
}

$dbHostValue = defined('DB_HOST') ? DB_HOST : 'N/A';
$dbNameValue = defined('DB_NAME') ? DB_NAME : 'N/A';
addTest('database', 'Database Config', defined('DB_HOST') && defined('DB_NAME'), "Host: {$dbHostValue}, DB: {$dbNameValue}");
addTest('database', 'PDO Connection', $dbConnected, $dbConnected ? 'เชื่อมต่อสำเร็จ' : 'ล้มเหลว');

if ($dbConnected) {
    $mysqlCheck = isset($mysqli) && @mysqli_ping($mysqli);
    addTest('database', 'MySQLi Connection', $mysqlCheck, $mysqlCheck ? 'เชื่อมต่อสำเร็จ' : 'ล้มเหลว');
}

// 3. Tables
$tables = ['edonation_projects', 'edonation_receipts', 'edonation_members', 'edonation_admin_users', 'edonation_news'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        if ($exists) {
            $cnt = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            addTest('tables', $table, true, "{$cnt} rows");
        } else {
            addTest('tables', $table, false, 'ไม่พบตาราง');
        }
    } catch (Exception $e) {
        addTest('tables', $table, false, 'Error');
    }
}

// 4. API
$apiBase = defined('API_URL') ? API_URL : 'http://localhost/edonation/api';
$endpoints = ['/v1' => 'API Base', '/v1/projects?limit=3' => 'Projects', '/v1/news?limit=3' => 'News'];
foreach ($endpoints as $ep => $name) {
    $res = testApi($apiBase . $ep);
    addTest('api', $name, $res['success'], $res['message']);
}

// 5. Security
addTest('security', 'PHP Session', session_status() === PHP_SESSION_ACTIVE, session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive');
$jwtOk = defined('JWT_SECRET') || isset($_ENV['JWT_SECRET']);
addTest('security', 'JWT Secret', $jwtOk, $jwtOk ? 'กำหนดแล้ว' : 'ไม่ได้กำหนด');
$cmuOk = isset($_ENV['CMU_OAUTH_CLIENT_ID']) && !empty($_ENV['CMU_OAUTH_CLIENT_ID']);
addTest('security', 'CMU OAuth', $cmuOk, $cmuOk ? 'กำหนดแล้ว' : 'ไม่ได้กำหนด');

// 6. PHP
$phpOk = version_compare(phpversion(), '7.4', '>=');
addTest('php', 'PHP Version', $phpOk, 'PHP ' . phpversion());
$exts = ['pdo', 'pdo_mysql', 'curl', 'json', 'mbstring', 'gd'];
foreach ($exts as $ext) {
    addTest('php', "ext-{$ext}", extension_loaded($ext), extension_loaded($ext) ? 'Loaded' : 'Missing');
}

// 7. Files
$files = [
    __DIR__ . '/admin/src/index.php' => 'Admin Dashboard',
    __DIR__ . '/config/database.php' => 'Database Config',
    __DIR__ . '/api/index.php' => 'API Router'
];
foreach ($files as $path => $name) {
    addTest('files', $name, file_exists($path), file_exists($path) ? 'พบ' : 'ไม่พบ');
}

$total = $passed + $failed;
$percentage = $total > 0 ? round(($passed / $total) * 100) : 0;

$categoryNames = [
    'environment' => 'Environment',
    'database' => 'Database',
    'tables' => 'Tables',
    'api' => 'API',
    'security' => 'Security',
    'php' => 'PHP',
    'files' => 'Files'
];
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eDonation - Local Test</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            color: #333;
            line-height: 1.5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .header .badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            margin-top: 10px;
        }

        /* Summary Cards */
        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .card .number {
            font-size: 2rem;
            font-weight: 700;
        }

        .card .label {
            font-size: 0.8rem;
            color: #666;
            margin-top: 5px;
        }

        .card.success .number {
            color: #10B981;
        }

        .card.danger .number {
            color: #EF4444;
        }

        .card.info .number {
            color: #4F46E5;
        }

        /* Status Banner */
        .status-banner {
            padding: 15px 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .status-banner.success {
            background: #D1FAE5;
            color: #065F46;
        }

        .status-banner.warning {
            background: #FEF3C7;
            color: #92400E;
        }

        .status-banner.danger {
            background: #FEE2E2;
            color: #991B1B;
        }

        /* Test Section */
        .section {
            background: white;
            border-radius: 10px;
            margin-bottom: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            padding: 12px 16px;
            background: #F9FAFB;
            border-bottom: 1px solid #E5E7EB;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-header .count {
            font-size: 0.75rem;
            color: #666;
            font-weight: 400;
        }

        .test-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 16px;
            border-bottom: 1px solid #F3F4F6;
            font-size: 0.85rem;
        }

        .test-item:last-child {
            border-bottom: none;
        }

        .test-item .name {
            color: #374151;
        }

        .test-item .status {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .test-item .message {
            color: #9CA3AF;
            font-size: 0.8rem;
        }

        .test-item .icon {
            font-size: 1rem;
        }

        .test-item .icon.pass {
            color: #10B981;
        }

        .test-item .icon.fail {
            color: #EF4444;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            color: #9CA3AF;
            font-size: 0.75rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🔧 eDonation System Test</h1>
            <p>Local Development Environment</p>
            <span class="badge">XAMPP / localhost</span>
        </div>

        <div class="summary">
            <div class="card success">
                <div class="number"><?= $passed ?></div>
                <div class="label">Passed</div>
            </div>
            <div class="card danger">
                <div class="number"><?= $failed ?></div>
                <div class="label">Failed</div>
            </div>
            <div class="card info">
                <div class="number"><?= $percentage ?>%</div>
                <div class="label">Success</div>
            </div>
        </div>

        <?php if ($percentage === 100): ?>
            <div class="status-banner success">✓ ระบบพร้อมใช้งาน - ทุกการทดสอบผ่าน</div>
        <?php elseif ($percentage >= 70): ?>
            <div class="status-banner warning">⚠ ระบบพร้อมใช้งานบางส่วน - กรุณาตรวจสอบรายการที่ไม่ผ่าน</div>
        <?php else: ?>
            <div class="status-banner danger">✕ พบปัญหาหลายรายการ - กรุณาแก้ไขก่อนใช้งาน</div>
        <?php endif; ?>

        <?php foreach ($results as $category => $tests): ?>
            <?php
            $catPassed = count(array_filter($tests, fn($t) => $t['status']));
            $catTotal = count($tests);
            ?>
            <div class="section">
                <div class="section-header">
                    <span><?= $categoryNames[$category] ?? ucfirst($category) ?></span>
                    <span class="count"><?= $catPassed ?>/<?= $catTotal ?> passed</span>
                </div>
                <?php foreach ($tests as $test): ?>
                    <div class="test-item">
                        <span class="name"><?= htmlspecialchars($test['name']) ?></span>
                        <span class="status">
                            <span class="message"><?= htmlspecialchars($test['message']) ?></span>
                            <span
                                class="icon <?= $test['status'] ? 'pass' : 'fail' ?>"><?= $test['status'] ? '✓' : '✕' ?></span>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <div class="footer">
            Tested at <?= date('d/m/Y H:i:s') ?> • PHP <?= phpversion() ?> • <?= php_uname('n') ?>
        </div>
    </div>
</body>

</html>