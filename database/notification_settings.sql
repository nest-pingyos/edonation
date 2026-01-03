-- =============================================
-- Notification Settings Table
-- สำหรับจัดการการตั้งค่าการแจ้งเตือน
-- =============================================

CREATE TABLE IF NOT EXISTS `notification_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `is_enabled` TINYINT(1) DEFAULT 1,
    `description` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Notification Recipients Table
-- สำหรับเก็บรายชื่อผู้รับการแจ้งเตือน
-- =============================================

CREATE TABLE IF NOT EXISTS `notification_recipients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `notification_type` VARCHAR(50) NOT NULL,  -- 'payment_success', 'new_donation', etc.
    `recipient_email` VARCHAR(255) NOT NULL,
    `recipient_name` VARCHAR(255),
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_notification_type` (`notification_type`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Notification Logs Table
-- สำหรับเก็บ log การแจ้งเตือน
-- =============================================

CREATE TABLE IF NOT EXISTS `notification_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `notification_type` VARCHAR(50) NOT NULL,
    `recipient_email` VARCHAR(255) NOT NULL,
    `message` TEXT,
    `status` ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    `response` TEXT,
    `reference_id` INT,  -- ID ของข้อมูลที่เกี่ยวข้อง เช่น donation_id
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_notification_type` (`notification_type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Default Settings
-- =============================================

INSERT INTO `notification_settings` (`setting_key`, `setting_value`, `is_enabled`, `description`) VALUES
('line_notification_enabled', '1', 1, 'เปิด/ปิดการแจ้งเตือนผ่าน LINE'),
('line_api_url', 'https://mis.nurse.cmu.ac.th/LineConnext/API/SendLineOA', 1, 'URL สำหรับส่งแจ้งเตือน LINE'),
('line_api_key', 'FON_ConnectAPI01', 1, 'API Key สำหรับ LINE'),
('line_program_name', 'e-Donation', 1, 'ชื่อโปรแกรมที่แสดงใน LINE'),
('line_message_color', '#FB974E', 1, 'สีของข้อความใน LINE'),
('office_base_url', 'https://app.nurse.cmu.ac.th/edonation/office', 1, 'URL สำหรับลิงก์ไปยัง Office')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- =============================================
-- Default Recipients (ทีมงาน)
-- =============================================

INSERT INTO `notification_recipients` (`notification_type`, `recipient_email`, `recipient_name`, `is_active`) VALUES
('payment_success', 'phatcharapon.p@cmu.ac.th', 'Phatcharapon', 1)
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- ถ้าต้องการเพิ่มผู้รับเพิ่มเติม สามารถ INSERT ได้ที่นี่
-- INSERT INTO `notification_recipients` (`notification_type`, `recipient_email`, `recipient_name`, `is_active`) VALUES
-- ('payment_success', 'another@cmu.ac.th', 'Another Person', 1);
