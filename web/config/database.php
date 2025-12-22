<?php
/**
 * Database Configuration - eDonation Project
 * 
 * ไฟล์รวมการเชื่อมต่อฐานข้อมูล (Single Source of Truth)
 * รองรับทั้ง PDO และ MySQLi
 * 
 * Configuration จะถูกโหลดจาก .env ที่ root level
 * 
 * การใช้งาน:
 *   require_once __DIR__ . '/database.php';
 *   // หรือ
 *   require_once $_SERVER['DOCUMENT_ROOT'] . '/appdev/edonation/config/database.php';
 * 
 * ตัวแปรที่ใช้งานได้:
 *   $pdo  - PDO connection
 *   $con  - MySQLi connection (alias)
 *   $link - MySQLi connection (alias)
 */

// ===== Load Environment Configuration =====
// โหลด .env จาก root level ของโปรเจ็ค
$envFile = dirname(__DIR__, 2) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
        }
    }
}

// ===== Database Credentials (from .env or defaults) =====
if (!defined('DB_HOST')) define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', $_ENV['DB_NAME'] ?? 'edonation');
if (!defined('DB_USER')) define('DB_USER', $_ENV['DB_USER'] ?? 'root');
if (!defined('DB_PASS')) define('DB_PASS', $_ENV['DB_PASS'] ?? '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8');

// ===== Email Configuration (Optional) =====
define('GMAIL_ID', '');         // YOUR gmail email
define('GMAIL_PASSWORD', '');   // YOUR gmail App password
define('GMAIL_USERNAME', '');   // YOUR gmail Username

// ===== PDO Connection =====
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // Log error (ไม่แสดงรายละเอียดให้ผู้ใช้เห็น)
    error_log("Database Connection Error: " . $e->getMessage());
    
    // แสดง error แบบ user-friendly
    if (php_sapi_name() !== 'cli') {
        echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล',
                    text: 'กรุณาติดต่อผู้ดูแลระบบ',
                    confirmButtonColor: '#ffaa00'
                });
            </script>
        ";
    }
    exit();
}

// ===== MySQLi Connection =====
$mysqli = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (mysqli_connect_errno()) {
    error_log("MySQLi Connection Error: " . mysqli_connect_error());
    die("Database connection failed. Please contact administrator.");
}

mysqli_set_charset($mysqli, DB_CHARSET);

// ===== Backward Compatibility Aliases =====
// สำหรับโค้ดเก่าที่ใช้ตัวแปรต่างๆ
$con = $mysqli;   // สำหรับ connect_pdf.php
$link = $mysqli;  // สำหรับ office/partials/config.php

// Legacy variables (for old code compatibility)
$servername = DB_HOST;
$hostname = DB_HOST;
$username = DB_USER;
$password = DB_PASS;
$dbname = DB_NAME;
$database = DB_NAME;

// Gmail legacy variables
$gmailid = GMAIL_ID;
$gmailpassword = GMAIL_PASSWORD;
$gmailusername = GMAIL_USERNAME;
