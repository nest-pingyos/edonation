-- =====================================================
-- Migration: Migrate data to edonation_members
-- Date: 2026-01-21
-- Description: ย้ายข้อมูลสมาชิกจาก receipts + donat_user
-- =====================================================

-- =====================================================
-- Step 1: Insert unique members from receipts
-- =====================================================
INSERT INTO `edonation_members` (
    `id_members`,
    `id_card`,
    `type`,
    `title`,
    `first_name`,
    `last_name`,
    `phone`,
    `occupation`,
    `address_line`,
    `province`,
    `district`,
    `subdistrict`,
    `zip_code`,
    `full_address`,
    `shipping_address`,
    `total_donated`,
    `donation_count`,
    `first_donation_date`,
    `last_donation_date`,
    `benefactor_level`
)
SELECT 
    r.id_members,
    r.id_card,
    -- ตรวจสอบประเภทจาก title
    CASE 
        WHEN du.title IN ('บริษัท', 'ห้างหุ้นส่วน', 'มูลนิธิ', 'สมาคม') THEN 'juristic'
        ELSE 'individual'
    END as type,
    du.title,
    du.first_name,
    du.last_name,
    MAX(du.phone) as phone,
    MAX(du.occupation) as occupation,
    MAX(du.address_line) as address_line,
    MAX(du.province) as province,
    MAX(du.amphure) as district,
    MAX(du.district) as subdistrict,
    MAX(du.zip_code) as zip_code,
    MAX(COALESCE(du.receipt_address, du.shipping_address, 
        CONCAT_WS(' ', du.address_line, du.district, du.amphure, du.province, du.zip_code)
    )) as full_address,
    MAX(du.shipping_address) as shipping_address,
    SUM(r.amount) as total_donated,
    COUNT(DISTINCT r.id) as donation_count,
    MIN(DATE(r.issued_at)) as first_donation_date,
    MAX(DATE(r.issued_at)) as last_donation_date,
    CASE 
        WHEN SUM(r.amount) >= 1000000 THEN 'มหากุศลาธิยาอา'
        WHEN SUM(r.amount) >= 500000 THEN 'กุศลาธิกาอา'
        WHEN SUM(r.amount) >= 100000 THEN 'อุดมกุศลา'
        WHEN SUM(r.amount) >= 50000 THEN 'มหากุศลา'
        WHEN SUM(r.amount) >= 10000 THEN 'กุศลา'
        ELSE 'ผู้บริจาค'
    END as benefactor_level
FROM edonation_receipts r
LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
WHERE r.id_members IS NOT NULL 
  AND r.id_members != ''
GROUP BY r.id_members, r.id_card, du.title, du.first_name, du.last_name
ON DUPLICATE KEY UPDATE
    `id_card` = COALESCE(VALUES(`id_card`), `id_card`),
    `phone` = COALESCE(VALUES(`phone`), `phone`),
    `occupation` = COALESCE(VALUES(`occupation`), `occupation`),
    `address_line` = COALESCE(VALUES(`address_line`), `address_line`),
    `province` = COALESCE(VALUES(`province`), `province`),
    `district` = COALESCE(VALUES(`district`), `district`),
    `subdistrict` = COALESCE(VALUES(`subdistrict`), `subdistrict`),
    `zip_code` = COALESCE(VALUES(`zip_code`), `zip_code`),
    `full_address` = COALESCE(VALUES(`full_address`), `full_address`),
    `shipping_address` = COALESCE(VALUES(`shipping_address`), `shipping_address`),
    `total_donated` = VALUES(`total_donated`),
    `donation_count` = VALUES(`donation_count`),
    `first_donation_date` = VALUES(`first_donation_date`),
    `last_donation_date` = VALUES(`last_donation_date`),
    `benefactor_level` = VALUES(`benefactor_level`),
    `updated_at` = CURRENT_TIMESTAMP;

-- =====================================================
-- Step 2: แสดงสรุปการ Migrate
-- =====================================================
SELECT 
    COUNT(*) as total_members,
    SUM(total_donated) as sum_total_donated,
    SUM(donation_count) as sum_donation_count,
    COUNT(CASE WHEN type = 'individual' THEN 1 END) as individual_count,
    COUNT(CASE WHEN type = 'juristic' THEN 1 END) as juristic_count
FROM edonation_members;

-- =====================================================
-- Step 3: สรุปตามระดับผู้มีอุปการคุณ
-- =====================================================
SELECT 
    benefactor_level,
    COUNT(*) as member_count,
    SUM(total_donated) as total_donated
FROM edonation_members
GROUP BY benefactor_level
ORDER BY total_donated DESC;
