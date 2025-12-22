<?php
/**
 * Admin Setup Script
 * 
 * สคริปต์สำหรับตั้งค่าระบบ admin เบื้องต้น
 * รัน: php setup.php
 * 
 * ⚠️ ลบไฟล์นี้หลังจากตั้งค่าเสร็จแล้ว!
 */

require_once __DIR__ . '/services/database.php';

echo "=========================================\n";
echo "   eDonation Admin Setup\n";
echo "=========================================\n\n";

try {
    $pdo = DatabaseService::getInstance();
    echo "✓ เชื่อมต่อฐานข้อมูลสำเร็จ\n";

    // Check if admin_users table exists
    $result = $pdo->query("SHOW TABLES LIKE 'edonation_admin_users'");
    $tableExists = $result->fetch() !== false;

    if (!$tableExists) {
        echo "\n→ กำลังสร้างตาราง edonation_admin_users...\n";
        
        $sql = "
            CREATE TABLE IF NOT EXISTS `edonation_admin_users` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `email` varchar(255) NOT NULL,
                `password_hash` varchar(255) NOT NULL,
                `name` varchar(100) NOT NULL,
                `role` enum('super_admin','admin','editor','viewer') NOT NULL DEFAULT 'admin',
                `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
                `avatar` varchar(255) DEFAULT NULL,
                `phone` varchar(20) DEFAULT NULL,
                `department` varchar(100) DEFAULT NULL,
                `last_login` datetime DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `email` (`email`),
                KEY `idx_status` (`status`),
                KEY `idx_role` (`role`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        $pdo->exec($sql);
        echo "✓ สร้างตารางสำเร็จ\n";
    } else {
        echo "✓ ตาราง edonation_admin_users มีอยู่แล้ว\n";
    }

    // Check if admin user exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM edonation_admin_users WHERE role = 'super_admin'");
    $adminCount = $stmt->fetchColumn();

    if ($adminCount == 0) {
        echo "\n→ กำลังสร้าง Admin User...\n";
        
        // Create default admin
        $email = 'admin@edonation.cmu.ac.th';
        $password = 'admin@123';
        $name = 'Administrator';
        
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO edonation_admin_users (email, password_hash, name, role, status) 
            VALUES (:email, :hash, :name, 'super_admin', 'active')
        ");
        $stmt->execute([
            ':email' => $email,
            ':hash' => $hash,
            ':name' => $name
        ]);
        
        echo "✓ สร้าง Admin User สำเร็จ\n";
        echo "\n=========================================\n";
        echo "   ข้อมูล Admin เริ่มต้น\n";
        echo "=========================================\n";
        echo "Email:    $email\n";
        echo "Password: $password\n";
        echo "=========================================\n";
        echo "\n⚠️  กรุณาเปลี่ยนรหัสผ่านหลังเข้าสู่ระบบ!\n";
    } else {
        echo "✓ มี Admin User อยู่แล้ว\n";
    }

    echo "\n=========================================\n";
    echo "   การตั้งค่าเสร็จสมบูรณ์!\n";
    echo "=========================================\n";
    echo "\nเข้าใช้งาน: /admin/src/auth-signin.php\n\n";
    
    echo "⚠️  สำคัญ: กรุณาลบไฟล์ setup.php หลังจากตั้งค่าเสร็จ!\n\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
