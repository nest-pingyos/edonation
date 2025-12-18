-- Create news table for eDonation
-- Run this SQL in phpMyAdmin or MySQL client

CREATE TABLE IF NOT EXISTS `news` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(500) NOT NULL COMMENT 'หัวข้อข่าว',
    `excerpt` VARCHAR(1000) NULL COMMENT 'เนื้อหาย่อ',
    `content` TEXT NULL COMMENT 'เนื้อหาเต็ม',
    `img_file` VARCHAR(255) NULL COMMENT 'ชื่อไฟล์รูปภาพ',
    `category` VARCHAR(50) DEFAULT 'general' COMMENT 'หมวดหมู่ข่าว',
    `author` VARCHAR(255) NULL COMMENT 'ผู้เขียน',
    `published_at` DATETIME NULL COMMENT 'วันที่เผยแพร่',
    `is_featured` TINYINT(1) DEFAULT 0 COMMENT 'ข่าวเด่น',
    `is_active` TINYINT(1) DEFAULT 1 COMMENT 'สถานะเผยแพร่',
    `view_count` INT DEFAULT 0 COMMENT 'จำนวนการเข้าชม',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category`),
    INDEX `idx_published_at` (`published_at`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_is_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางข่าวสาร';

-- Insert sample news data
INSERT INTO `news` (`title`, `excerpt`, `content`, `category`, `author`, `published_at`, `is_featured`, `is_active`) VALUES
('คณะพยาบาลศาสตร์ มช. เปิดรับบริจาคเพื่อพัฒนาการศึกษา', 'คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่ เปิดรับบริจาคเพื่อสนับสนุนการพัฒนาการเรียนการสอน', 'คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่ เปิดรับบริจาคเพื่อสนับสนุนการพัฒนาการเรียนการสอน และการวิจัยทางการพยาบาล เพื่อผลิตบัณฑิตพยาบาลที่มีคุณภาพ', 'general', 'Admin', NOW(), 1, 1),
('ขอขอบคุณผู้มีอุปการคุณทุกท่าน', 'คณะพยาบาลศาสตร์ขอขอบคุณผู้มีอุปการคุณทุกท่านที่ร่วมบริจาค', 'คณะพยาบาลศาสตร์ขอขอบคุณผู้มีอุปการคุณทุกท่านที่ร่วมบริจาคเพื่อสนับสนุนกิจกรรมต่างๆ ของคณะ', 'thank', 'Admin', NOW(), 0, 1),
('ประกาศผลการมอบทุนการศึกษา ประจำปี 2568', 'ประกาศรายชื่อนักศึกษาที่ได้รับทุนการศึกษาจากเงินบริจาค', 'ประกาศรายชื่อนักศึกษาที่ได้รับทุนการศึกษาจากเงินบริจาคของผู้มีอุปการคุณ ประจำปีการศึกษา 2568', 'announcement', 'Admin', NOW(), 0, 1);

SELECT 'News table created and sample data inserted successfully!' as result;
