<?php
/**
 * eDonation System - Production Environment Test
 * 
 * ไฟล์ทดสอบสำหรับ Production Environment
 * 
 * วิธีใช้: https://your-domain.com/production_test.php?key=YOUR_KEY
 * 
 * ⚠️ ลบไฟล์นี้หลังทดสอบเสร็จ
 * 
 * @version 2.0
 * @date 2026-02-01
 */

// Security: Access Key Required
$accessKey = 'edonation_prod_test_2026';
if (($_GET['key'] ?? '') !== $accessKey) {
    http_response_code(403);
    die('Access Denied');
}

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Test results storage
$results = [];
$passed = 0;
$failed = 0;
$critical = 0;

function addTest($category, $name, $status, $message = '', $isCritical = false)
{
    global $results, $passed, $failed, $critical;
    $results[$category][] = [
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'critical' => $isCritical && !$status
    ];
    if ($status) {
        $passed++;
    } else {
        $failed++;
        if ($isCritical)
            $critical++;
    }
}

function testApi($url)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json']
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error)
        return ['success' => false, 'message' => "Error"];
    return [
        'success' => $httpCode >= 200 && $httpCode < 400,
        'message' => "HTTP {$httpCode}"
    ];
}

// =============================================
// Run Tests
// =============================================

// 1. Environment Security
$envFile = __DIR__ . '/.env';
addTest('security', 'ไฟล์ .env', file_exists($envFile), file_exists($envFile) ? 'พบ' : 'ไม่พบ', true);

if (file_exists($envFile)) {
    require_once __DIR__ . '/config/env.php';
}

addTest('security', 'APP_ENV = production', defined('APP_ENV') && APP_ENV === 'production', defined('APP_ENV') ? APP_ENV : 'N/A', true);
addTest('security', 'APP_DEBUG = false', defined('APP_DEBUG') && APP_DEBUG === false, defined('APP_DEBUG') ? (APP_DEBUG ? 'true!' : 'false') : 'N/A', true);

$isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
addTest('security', 'HTTPS', $isHttps, $isHttps ? 'Enabled' : 'Disabled', true);

$displayErrors = ini_get('display_errors');
$errorsOff = !$displayErrors || $displayErrors === '0' || $displayErrors === 'Off';
addTest('security', 'display_errors = Off', $errorsOff, $errorsOff ? 'Off' : 'On!', true);

// JWT Secret
$jwtSecret = defined('JWT_SECRET') ? JWT_SECRET : ($_ENV['JWT_SECRET'] ?? '');
$jwtBad = empty($jwtSecret) || strpos($jwtSecret, 'your') !== false || strpos($jwtSecret, 'CHANGE') !== false;
addTest('security', 'JWT Secret', !$jwtBad && strlen($jwtSecret) >= 32, $jwtBad ? 'ใช้ค่า default!' : 'OK', true);

// CMU OAuth
$cmuOk = isset($_ENV['CMU_OAUTH_CLIENT_ID']) &&
    !empty($_ENV['CMU_OAUTH_CLIENT_ID']) &&
    strpos($_ENV['CMU_OAUTH_CLIENT_ID'], 'CHANGE') === false;
addTest('security', 'CMU OAuth', $cmuOk, $cmuOk ? 'Configured' : 'Not set', true);

// 2. Database
$dbHostValue = defined('DB_HOST') ? DB_HOST : 'N/A';
addTest('database', 'Database Host', defined('DB_HOST'), $dbHostValue);

$dbPassOk = defined('DB_PASS') && !empty(DB_PASS) && strpos(DB_PASS, 'CHANGE') === false;
addTest('database', 'Database Password', $dbPassOk, $dbPassOk ? 'Set' : 'Weak/Empty!', true);

try {
    require_once __DIR__ . '/config/database.php';
    $pdo->query('SELECT 1');
    addTest('database', 'Connection', true, 'OK', true);
} catch (Exception $e) {
    addTest('database', 'Connection', false, 'Failed', true);
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
            addTest('tables', $table, false, 'Missing', true);
        }
    } catch (Exception $e) {
        addTest('tables', $table, false, 'Error', true);
    }
}

// Admin user exists
try {
    $adminCount = $pdo->query("SELECT COUNT(*) FROM edonation_admin_users WHERE status = 'active'")->fetchColumn();
    addTest('tables', 'Active Admins', $adminCount > 0, "{$adminCount} user(s)", true);
} catch (Exception $e) {
    addTest('tables', 'Active Admins', false, 'Error', true);
}

// 4. API
$apiBase = defined('API_URL') ? API_URL : '';
if ($apiBase) {
    $endpoints = ['/v1' => 'API Base', '/v1/projects?limit=3' => 'Projects'];
    foreach ($endpoints as $ep => $name) {
        $res = testApi($apiBase . $ep);
        addTest('api', $name, $res['success'], $res['message']);
    }
} else {
    addTest('api', 'API URL', false, 'Not defined');
}

// 5. File Security
$htaccessOk = file_exists(__DIR__ . '/.htaccess');
addTest('files', '.htaccess', $htaccessOk, $htaccessOk ? 'Present' : 'Missing');

// Check .env not accessible via HTTP
if (defined('APP_URL')) {
    $ch = curl_init(APP_URL . '/.env');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_NOBODY => true]);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $envProtected = $httpCode === 403 || $httpCode === 404 || $httpCode === 0;
    addTest('files', '.env Protected', $envProtected, $envProtected ? 'Blocked' : "Accessible (HTTP {$httpCode})!", !$envProtected);
}

// 6. PHP
$phpOk = version_compare(phpversion(), '7.4', '>=');
addTest('php', 'PHP Version', $phpOk, 'PHP ' . phpversion(), !$phpOk);

$memLimit = ini_get('memory_limit');
addTest('php', 'Memory Limit', true, $memLimit);

$exts = ['pdo', 'pdo_mysql', 'curl', 'json', 'mbstring', 'gd', 'openssl'];
foreach ($exts as $ext) {
    $loaded = extension_loaded($ext);
    addTest('php', "ext-{$ext}", $loaded, $loaded ? 'OK' : 'Missing', in_array($ext, ['pdo', 'pdo_mysql', 'openssl']));
}

// 7. Performance
$dbStart = microtime(true);
try {
    $pdo->query("SELECT 1");
    $dbTime = round((microtime(true) - $dbStart) * 1000, 2);
    addTest('performance', 'DB Response', $dbTime < 100, "{$dbTime}ms");
} catch (Exception $e) {
    addTest('performance', 'DB Response', false, 'Error');
}

$opcache = function_exists('opcache_get_status') && opcache_get_status() !== false;
addTest('performance', 'OPcache', $opcache, $opcache ? 'Enabled' : 'Disabled');

$total = $passed + $failed;
$percentage = $total > 0 ? round(($passed / $total) * 100) : 0;

$categoryNames = [
    'security' => 'Security',
    'database' => 'Database',
    'tables' => 'Tables',
    'api' => 'API',
    'files' => 'Files',
    'php' => 'PHP',
    'performance' => 'Performance'
];
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eDonation - Production Test</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #111827;
            color: #E5E7EB;
            line-height: 1.5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #DC2626, #991B1B);
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

        /* Warning */
        .warning {
            background: #FEF3C7;
            color: #92400E;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.85rem;
        }

        /* Summary Cards */
        .summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .card {
            background: #1F2937;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }

        .card .number {
            font-size: 2rem;
            font-weight: 700;
        }

        .card .label {
            font-size: 0.75rem;
            color: #9CA3AF;
            margin-top: 5px;
        }

        .card.success .number {
            color: #10B981;
        }

        .card.danger .number {
            color: #EF4444;
        }

        .card.critical .number {
            color: #F97316;
        }

        .card.info .number {
            color: #8B5CF6;
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
            background: #065F46;
            color: #D1FAE5;
        }

        .status-banner.warning {
            background: #92400E;
            color: #FEF3C7;
        }

        .status-banner.danger {
            background: #991B1B;
            color: #FEE2E2;
        }

        /* Test Section */
        .section {
            background: #1F2937;
            border-radius: 10px;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .section-header {
            padding: 12px 16px;
            background: #374151;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-header .count {
            font-size: 0.75rem;
            color: #9CA3AF;
            font-weight: 400;
        }

        .test-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 16px;
            border-bottom: 1px solid #374151;
            font-size: 0.85rem;
        }

        .test-item:last-child {
            border-bottom: none;
        }

        .test-item .name {
            color: #D1D5DB;
        }

        .test-item .status {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .test-item .message {
            color: #6B7280;
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

        .test-item.critical {
            background: rgba(239, 68, 68, 0.1);
        }

        .test-item.critical .name::after {
            content: 'CRITICAL';
            margin-left: 8px;
            font-size: 0.65rem;
            background: #EF4444;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            color: #6B7280;
            font-size: 0.75rem;
        }

        .security-note {
            background: #1F2937;
            border: 1px solid #374151;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.8rem;
            color: #9CA3AF;
        }

        .security-note h4 {
            color: #FBBF24;
            margin-bottom: 8px;
        }

        .security-note ul {
            margin: 0;
            padding-left: 20px;
        }

        .security-note li {
            margin-bottom: 4px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🚀 eDonation System Test</h1>
            <p>Production Environment</p>
            <span class="badge"><?= $_SERVER['HTTP_HOST'] ?? 'Server' ?></span>
        </div>

        <div class="warning">⚠️ ลบไฟล์นี้หลังจากทดสอบเสร็จสิ้น</div>

        <div class="summary">
            <div class="card success">
                <div class="number"><?= $passed ?></div>
                <div class="label">Passed</div>
            </div>
            <div class="card danger">
                <div class="number"><?= $failed ?></div>
                <div class="label">Failed</div>
            </div>
            <div class="card critical">
                <div class="number"><?= $critical ?></div>
                <div class="label">Critical</div>
            </div>
            <div class="card info">
                <div class="number"><?= $percentage ?>%</div>
                <div class="label">Success</div>
            </div>
        </div>

        <?php if ($critical > 0): ?>
            <div class="status-banner danger">🚨 พบปัญหาวิกฤต <?= $critical ?> รายการ - ต้องแก้ไขก่อน Deploy</div>
        <?php elseif ($percentage === 100): ?>
            <div class="status-banner success">✓ พร้อมสำหรับ Production - ทุกการทดสอบผ่าน</div>
        <?php elseif ($percentage >= 80): ?>
            <div class="status-banner warning">⚠ เกือบพร้อมแล้ว - ควรแก้ไขปัญหาที่เหลือ</div>
        <?php else: ?>
            <div class="status-banner danger">✕ ยังไม่พร้อมสำหรับ Production</div>
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
                    <div class="test-item <?= $test['critical'] ? 'critical' : '' ?>">
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

        <div class="security-note">
            <h4>🔐 Security Notes</h4>
            <ul>
                <li>ลบไฟล์นี้หลังทดสอบเสร็จ</li>
                <li>เปลี่ยน access key ก่อนใช้งาน</li>
                <li>ตรวจสอบ logs หลัง deploy</li>
            </ul>
        </div>

        <div class="footer">
            Tested at <?= date('d/m/Y H:i:s') ?> • PHP <?= phpversion() ?> • <?= php_uname('n') ?>
        </div>
    </div>
</body>

</html>