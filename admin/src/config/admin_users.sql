-- =====================================================
-- eDonation Admin Users Table
-- สร้างตาราง admin users สำหรับระบบ admin
-- =====================================================

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Default Admin User
-- Password: admin@123 (change this immediately!)
-- =====================================================

INSERT INTO `edonation_admin_users` (`email`, `password_hash`, `name`, `role`, `status`) VALUES
('admin@edonation.cmu.ac.th', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'super_admin', 'active');

-- Note: The password hash above is for 'password' - please change immediately!
-- To generate new password hash, use: password_hash('your_password', PASSWORD_DEFAULT);
