<?php
session_start();

// Try to load database connection (optional for public pages)
$pdo = null;
try {
    require_once __DIR__ . '/connect.php';
} catch (Exception $e) {
    // Database connection failed - continue without it for public pages
    error_log("Database connection error: " . $e->getMessage());
}

// 🕒 กำหนดค่า Timeout เป็น 10 นาที (600 วินาที)
$timeout_duration = 600;

// ✅ ตรวจสอบว่ามีการกำหนดเวลาเริ่มต้นเซสชันหรือไม่
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    // 🔴 หมดเวลา -> ทำการล้างเซสชันและรีเฟรชหน้า
    session_unset();
    session_destroy();
    header("Location: ../home"); // เปลี่ยนเส้นทางไปที่หน้าล็อกอิน
    exit();
}

// ✅ อัปเดตเวลาล่าสุดที่มีการใช้งาน
$_SESSION['LAST_ACTIVITY'] = time();

// 🔹 ป้องกันการแคชข้อมูล (เคลียร์แคช)
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 🔹 ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
$isLoggedIn = isset($_SESSION['user_login']);

$user = null; // กำหนดค่าเริ่มต้นของ $user

if ($isLoggedIn && $pdo) {
    $user_id = $_SESSION['user_login'];

    try {
        // ดึงข้อมูลผู้ใช้จากฐานข้อมูล
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // ถ้าไม่พบผู้ใช้ ให้ logout
        if (!$user) {
            $isLoggedIn = false;
            unset($_SESSION['user_login']);
        }
    } catch (PDOException $e) {
        // Database error - treat as not logged in
        error_log("User lookup error: " . $e->getMessage());
        $isLoggedIn = false;
    }
}

