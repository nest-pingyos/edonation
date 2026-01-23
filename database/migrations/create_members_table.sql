-- =====================================================
-- Migration: Create edonation_members table
-- Date: 2026-01-21
-- Description: ตารางสมาชิก (ผู้บริจาค) แยกออกจากตารางใบเสร็จ
-- =====================================================

-- สร้างตาราง edonation_members
CREATE TABLE IF NOT EXISTS `edonation_members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_members` VARCHAR(20) UNIQUE NOT NULL COMMENT 'รหัสสมาชิก 10 หลัก',
    `id_card` VARCHAR(13) COMMENT 'เลขบัตรประชาชน/เลขผู้เสียภาษี',
    `type` ENUM('individual', 'juristic') DEFAULT 'individual' COMMENT 'บุคคลธรรมดา/นิติบุคคล',
    `title` VARCHAR(50) COMMENT 'คำนำหน้า',
    `first_name` VARCHAR(100) NOT NULL COMMENT 'ชื่อ/ชื่อองค์กร',
    `last_name` VARCHAR(100) COMMENT 'นามสกุล',
    `full_name` VARCHAR(255) GENERATED ALWAYS AS (
        CONCAT(COALESCE(title, ''), ' ', first_name, COALESCE(CONCAT(' ', last_name), ''))
    ) STORED COMMENT 'ชื่อเต็ม (Auto-generated)',
    `phone` VARCHAR(20),
    `email` VARCHAR(100),
    `occupation` VARCHAR(100),
    `address_line` TEXT COMMENT 'ที่อยู่บรรทัดที่ 1',
    `province` VARCHAR(100),
    `district` VARCHAR(100) COMMENT 'อำเภอ',
    `subdistrict` VARCHAR(100) COMMENT 'ตำบล',
    `zip_code` VARCHAR(10),
    `full_address` TEXT COMMENT 'ที่อยู่เต็ม',
    `shipping_address` TEXT COMMENT 'ที่อยู่จัดส่ง (ถ้าต่างจากที่อยู่หลัก)',
    `total_donated` DECIMAL(15,2) DEFAULT 0 COMMENT 'ยอดบริจาครวม',
    `donation_count` INT DEFAULT 0 COMMENT 'จำนวนครั้งที่บริจาค',
    `benefactor_level` VARCHAR(50) COMMENT 'ระดับผู้มีอุปการคุณ',
    `first_donation_date` DATE COMMENT 'วันที่บริจาคครั้งแรก',
    `last_donation_date` DATE COMMENT 'วันที่บริจาคล่าสุด',
    `notes` TEXT COMMENT 'หมายเหตุ',
    `is_active` TINYINT(1) DEFAULT 1 COMMENT 'สถานะใช้งาน',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for fast lookup
    INDEX idx_id_card (id_card),
    INDEX idx_name (first_name, last_name),
    INDEX idx_full_name (full_name(100)),
    INDEX idx_phone (phone),
    INDEX idx_email (email),
    INDEX idx_benefactor (benefactor_level),
    INDEX idx_last_donation (last_donation_date),
    INDEX idx_total_donated (total_donated)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='ตารางสมาชิก (ผู้บริจาค)';

-- =====================================================
-- เพิ่ม Foreign Key ใน edonation_donat_user (optional)
-- =====================================================
-- ALTER TABLE `edonation_donat_user` 
-- ADD COLUMN `member_id` INT NULL AFTER `id`,
-- ADD CONSTRAINT `fk_donat_user_member` 
--     FOREIGN KEY (`member_id`) REFERENCES `edonation_members`(`id`) 
--     ON DELETE SET NULL ON UPDATE CASCADE;

-- =====================================================
-- Trigger: อัปเดตสถิติสมาชิกเมื่อมีใบเสร็จใหม่
-- =====================================================
DELIMITER //

DROP TRIGGER IF EXISTS `trg_receipt_after_insert`//

CREATE TRIGGER `trg_receipt_after_insert` 
AFTER INSERT ON `edonation_receipts`
FOR EACH ROW
BEGIN
    -- อัปเดตสถิติสมาชิก
    IF NEW.id_members IS NOT NULL AND NEW.id_members != '' THEN
        UPDATE `edonation_members` 
        SET 
            `total_donated` = `total_donated` + NEW.amount,
            `donation_count` = `donation_count` + 1,
            `last_donation_date` = CURDATE(),
            `benefactor_level` = CASE 
                WHEN `total_donated` + NEW.amount >= 1000000 THEN 'มหากุศลาธิยาอา'
                WHEN `total_donated` + NEW.amount >= 500000 THEN 'กุศลาธิกาอา'
                WHEN `total_donated` + NEW.amount >= 100000 THEN 'อุดมกุศลา'
                WHEN `total_donated` + NEW.amount >= 50000 THEN 'มหากุศลา'
                WHEN `total_donated` + NEW.amount >= 10000 THEN 'กุศลา'
                ELSE 'ผู้บริจาค'
            END
        WHERE `id_members` = NEW.id_members;
    END IF;
END//

DELIMITER ;

-- =====================================================
-- View: สำหรับดูข้อมูลสมาชิกพร้อมสถิติ (Optional)
-- =====================================================
CREATE OR REPLACE VIEW `v_member_summary` AS
SELECT 
    m.id,
    m.id_members,
    m.id_card,
    m.type,
    m.full_name,
    m.phone,
    m.email,
    m.full_address,
    m.total_donated,
    m.donation_count,
    m.benefactor_level,
    m.first_donation_date,
    m.last_donation_date,
    CASE 
        WHEN m.donation_count > 5 THEN 'regular'
        WHEN m.donation_count > 1 THEN 'repeat'
        ELSE 'new'
    END as donor_type,
    m.created_at
FROM edonation_members m
WHERE m.is_active = 1;
