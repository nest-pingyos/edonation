<?php
/**
 * Server Compatibility Checker
 * 
 * This script checks the server's PHP version, extensions, and configuration
 * to ensurecompatibility with modern web applications.
 * 
 * @author Antigravity AI
 * @version 1.0.0
 */

// Set header for UTF-8
header('Content-Type: text/html; charset=UTF-8');

// Configuration
$required_php = '7.4.0';
$recommended_php = '8.1.0';

// Check Extensions
$extensions = [
    'ftp' => 'Required for FTP operations',
    'pdo' => 'Required for database connection',
    'pdo_mysql' => 'Required for MySQL support',
    'mysqli' => 'Alternative MySQL support',
    'curl' => 'Required for API requests',
    'gd' => 'Required for image processing',
    'mbstring' => 'Required for multibyte string handling',
    'json' => 'Required for JSON processing',
    'xml' => 'Required for XML processing',
    'zip' => 'Required for ZIP archive handling',
    'openssl' => 'Required for secure connections',
    'bcmath' => 'Required for precise calculations',
    'fileinfo' => 'Required for file type detection',
    'intl' => 'Required for internationalization',
];

// Check Configs
$configs = [
    'memory_limit' => ['label' => 'Memory Limit', 'min' => '128M'],
    'upload_max_filesize' => ['label' => 'Max Upload Size', 'min' => '10M'],
    'post_max_size' => ['label' => 'Max Post Size', 'min' => '10M'],
    'max_execution_time' => ['label' => 'Max Execution Time', 'min' => 30],
    'display_errors' => ['label' => 'Display Errors', 'info' => 'Should be Off in production'],
];

function get_status_icon($status)
{
    if ($status === 'pass')
        return '<span class="status-icon pass">✓</span>';
    if ($status === 'warn')
        return '<span class="status-icon warn">⚠</span>';
    return '<span class="status-icon fail">✕</span>';
}

function parse_size($size)
{
    $unit = preg_replace('/[^bkmgtp]/i', '', $size);
    $size = preg_replace('/[^0-9\.]/', '', $size);
    if ($unit) {
        return round($size * pow(1024, stripos('bkmgtp', $unit[0])));
    } else {
        return round($size);
    }
}

// PHP Version Check
$current_php = phpversion();
$php_status = 'fail';
if (version_compare($current_php, $recommended_php, '>=')) {
    $php_status = 'pass';
} elseif (version_compare($current_php, $required_php, '>=')) {
    $php_status = 'warn';
}

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Compatibility Check</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg: #f9fafb;
            --card-bg: #ffffff;
            --text-main: #111827;
            --text-muted: #6b7280;
            --border: #e5e7eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            line-height: 1.6;
            padding: 40px 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 40px;
        }

        header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        header p {
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        .card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 2px solid var(--bg);
            padding-bottom: 15px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: var(--bg);
            border-radius: 10px;
            transition: transform 0.2s;
        }

        .info-item:hover {
            transform: translateY(-2px);
        }

        .info-label {
            font-weight: 500;
            font-size: 0.95rem;
        }

        .info-value {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .status-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            font-size: 14px;
            font-weight: bold;
            margin-left: 8px;
        }

        .status-icon.pass {
            background-color: #dcfce7;
            color: var(--success);
        }

        .status-icon.warn {
            background-color: #fef3c7;
            color: var(--warning);
        }

        .status-icon.fail {
            background-color: #fee2e2;
            color: var(--danger);
        }

        .tag {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .tag-pass {
            background-color: #dcfce7;
            color: #166534;
        }

        .tag-warn {
            background-color: #fef3c7;
            color: #92400e;
        }

        .tag-fail {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .php-hero {
            text-align: center;
            padding: 40px;
            background: linear-gradient(135deg, #f3f4f6, #ffffff);
            border-radius: 20px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
        }

        .php-version-huge {
            font-size: 4rem;
            font-weight: 900;
            line-height: 1;
            margin: 15px 0;
            color: var(--primary);
        }

        .footer {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 40px;
        }

        .details-list {
            list-style: none;
        }

        .details-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }

        .details-item:last-child {
            border-bottom: none;
        }

        .btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: background 0.2s;
        }

        .btn:hover {
            background: var(--primary-hover);
        }

        @media (max-width: 640px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <header>
            <h1>Server Health Check</h1>
            <p>ตรวจสอบความพร้อมของเซิร์ฟเวอร์และเวอร์ชัน PHP</p>
        </header>

        <div class="php-hero">
            <div class="tag tag-<?= $php_status ?>">PHP VERSION</div>
            <div class="php-version-huge">
                <?= $current_php ?>
            </div>
            <p>Required:
                <?= $required_php ?> | Recommended:
                <?= $recommended_php ?>
            </p>
            <?php if ($php_status == 'pass'): ?>
                <p style="color: var(--success); font-weight: 600; margin-top: 10px;">✓ เวอร์ชัน PHP
                    ของคุณเป็นปัจจุบันและรองรับการทำงานได้ดีเยี่ยม</p>
            <?php elseif ($php_status == 'warn'): ?>
                <p style="color: var(--warning); font-weight: 600; margin-top: 10px;">⚠ เวอร์ชัน PHP ของคุณรองรับขั้นต่ำ
                    แต่แนะนำให้อัปเกรดเป็น
                    <?= $recommended_php ?>
                </p>
            <?php else: ?>
                <p style="color: var(--danger); font-weight: 600; margin-top: 10px;">✕ เวอร์ชัน PHP เก่าเกินไป
                    อาจทำงานผิดพลาดได้</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-title">🧩 PHP Extensions</div>
            <div class="grid">
                <?php foreach ($extensions as $ext => $desc): ?>
                    <?php $loaded = extension_loaded($ext); ?>
                    <div class="info-item">
                        <div class="info-label" title="<?= $desc ?>">
                            <?= $ext ?>
                        </div>
                        <div class="info-value">
                            <?= $loaded ? 'Enabled' : 'Disabled' ?>
                            <?= get_status_icon($loaded ? 'pass' : 'fail') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-title">⚙️ Configuration Limits</div>
            <div class="grid">
                <?php foreach ($configs as $key => $data): ?>
                    <?php
                    $val = ini_get($key);
                    $status = 'pass';
                    if (isset($data['min'])) {
                        if (is_numeric($data['min'])) {
                            $status = ($val >= $data['min']) ? 'pass' : 'warn';
                        } else {
                            $status = (parse_size($val) >= parse_size($data['min'])) ? 'pass' : 'warn';
                        }
                    }
                    ?>
                    <div class="info-item">
                        <div class="info-label">
                            <?= $data['label'] ?>
                        </div>
                        <div class="info-value">
                            <?= $val ?: 'Off' ?>
                            <?= get_status_icon($status) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-title">🖥️ Server Information</div>
            <div class="details-list">
                <div class="details-item">
                    <span class="info-label">Server Software</span>
                    <span class="info-value">
                        <?= $_SERVER['SERVER_SOFTWARE'] ?>
                    </span>
                </div>
                <div class="details-item">
                    <span class="info-label">Operating System</span>
                    <span class="info-value">
                        <?= php_uname('s') . ' ' . php_uname('r') ?>
                    </span>
                </div>
                <div class="details-item">
                    <span class="info-label">Architecture</span>
                    <span class="info-value">
                        <?= php_uname('m') ?>
                    </span>
                </div>
                <div class="details-item">
                    <span class="info-label">Document Root</span>
                    <span class="info-value">
                        <?= $_SERVER['DOCUMENT_ROOT'] ?>
                    </span>
                </div>
                <div class="details-item">
                    <span class="info-label">Interface Type (SAPI)</span>
                    <span class="info-value">
                        <?= php_sapi_name() ?>
                    </span>
                </div>
            </div>
        </div>

        <div style="text-align: center;">
            <a href="?phpinfo=1" class="btn">แสดง PHP Information ทั้งหมด</a>
        </div>

        <?php if (isset($_GET['phpinfo'])): ?>
            <div class="card" style="margin-top: 40px; overflow-x: auto;">
                <div class="card-title">📄 Full PHP Info</div>
                <div style="background: #fff; color: #000; padding: 10px;">
                    <?php
                    ob_start();
                    phpinfo();
                    $pinfo = ob_get_contents();
                    ob_end_clean();
                    $pinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $pinfo);
                    echo $pinfo;
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="footer">
            Generated by eDonation Test Tool •
            <?= date('Y-m-d H:i:s') ?>
        </div>
    </div>

</body>

</html>