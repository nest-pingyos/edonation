-- Create edonation_admin_users table
-- สำหรับจัดการสิทธิ์ผู้ใช้งานระบบ Admin (CMU OAuth เท่านั้น)
-- ไม่เก็บ password เพราะ auth ผ่าน Azure Microsoft

CREATE TABLE IF NOT EXISTS `edonation_admin_users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `email` varchar(255) NOT NULL COMMENT 'CMU Account email (@cmu.ac.th)',
    `name` varchar(255) NOT NULL COMMENT 'ชื่อ-นามสกุล',
    `role` enum('super_admin','admin','editor','viewer') NOT NULL DEFAULT 'admin' COMMENT 'บทบาท',
    `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT 'สถานะ',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_login` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    KEY `idx_status` (`status`),
    KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- เพิ่ม Admin Users ที่มีสิทธิ์เข้าระบบ
-- ใส่ CMU Account email (@cmu.ac.th) ที่ต้องการให้เข้าถึงได้
-- =============================================

-- ตัวอย่าง: เพิ่ม admin users
-- INSERT INTO `edonation_admin_users` (`email`, `name`, `role`) VALUES
-- ('admin@cmu.ac.th', 'ผู้ดูแลระบบ', 'super_admin'),
-- ('staff@cmu.ac.th', 'เจ้าหน้าที่', 'admin'),
-- ('editor@cmu.ac.th', 'ผู้แก้ไข', 'editor');

-- =============================================
-- Role Hierarchy (สิทธิ์สูง -> ต่ำ)
-- =============================================
-- super_admin : สิทธิ์เต็ม (จัดการทุกอย่าง + จัดการ users)
-- admin       : จัดการข้อมูลทั่วไป (ใบเสร็จ, โครงการ, ข่าว)
-- editor      : แก้ไขข้อมูล (ข่าว, โครงการ)
-- viewer      : ดูข้อมูลอย่างเดียว
