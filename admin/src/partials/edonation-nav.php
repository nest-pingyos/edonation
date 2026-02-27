<?php
// Get BASE_PATH from environment or config
$basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';
?>
<div class="main-nav">
    <!-- Sidebar Logo -->
    <div class="logo-box">
        <a href="dashboard.php" class="logo-dark">
            <img src="<?= $basePath ?>/assets/images/logo/logo.svg" class="h-auto" style="max-height: 40px;"
                alt="eDonation">
        </a>
        <a href="dashboard.php" class="logo-light">
            <img src="<?= $basePath ?>/assets/images/logo/logo.svg" class="h-auto" style="max-height: 40px;"
                alt="eDonation">
        </a>
    </div>

    <!-- Menu Toggle Button -->
    <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
        <iconify-icon icon="iconamoon:arrow-left-4-square-duotone" class="button-sm-hover-icon"></iconify-icon>
    </button>

    <div class="scrollbar" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">

            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:home-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <?php if (canAccess('receipts-list.php') || canAccess('receipts-generate.php')): ?>
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarReceipts" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarReceipts">
                        <span class="nav-icon">
                            <iconify-icon icon="iconamoon:invoice-duotone"></iconify-icon>
                        </span>
                        <span class="nav-text">ใบเสร็จรับเงิน</span>
                    </a>
                    <div class="collapse" id="sidebarReceipts">
                        <ul class="nav sub-navbar-nav">
                            <?php if (canAccess('receipts-list.php')): ?>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="receipts-list.php">รายการใบเสร็จ</a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess('receipts-generate.php')): ?>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="receipts-generate.php">ออกใบเสร็จ</a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess('receipts-print-address.php')): ?>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="receipts-print-address.php">พิมพ์ที่อยู่จัดส่ง</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
            <?php endif; ?>

            <?php if (canAccess('projects-list.php')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="projects-list.php">
                        <span class="nav-icon">
                            <iconify-icon icon="iconamoon:folder-duotone"></iconify-icon>
                        </span>
                        <span class="nav-text">โครงการ</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (canAccess('benefits-list.php')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="benefits-list.php">
                        <span class="nav-icon">
                            <iconify-icon icon="iconamoon:gift-duotone"></iconify-icon>
                        </span>
                        <span class="nav-text">สิทธิประโยชน์</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (canAccess('news-list.php')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="news-list.php">
                        <span class="nav-icon">
                            <iconify-icon icon="iconamoon:news-duotone"></iconify-icon>
                        </span>
                        <span class="nav-text">ข่าวสาร</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (canAccess('members-list.php') || canAccess('members-search.php')): ?>
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarMembers" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarMembers">
                        <span class="nav-icon">
                            <iconify-icon icon="iconamoon:profile-circle-duotone"></iconify-icon>
                        </span>
                        <span class="nav-text">สมาชิก</span>
                    </a>
                    <div class="collapse" id="sidebarMembers">
                        <ul class="nav sub-navbar-nav">
                            <?php if (canAccess('members-list.php')): ?>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="members-list.php">รายชื่อสมาชิก</a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess('members-search.php')): ?>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="members-search.php">ค้นหาประวัติ</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
            <?php endif; ?>

            <?php if (canAccess('reports-daily.php') || canAccess('reports-monthly.php') || canAccess('reports-yearly.php')): ?>
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarReports" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarReports">
                        <span class="nav-icon">
                            <iconify-icon icon="iconamoon:file-document-duotone"></iconify-icon>
                        </span>
                        <span class="nav-text">รายงาน</span>
                    </a>
                    <div class="collapse" id="sidebarReports">
                        <ul class="nav sub-navbar-nav">
                            <?php if (canAccess('reports-daily.php')): ?>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="reports-daily.php">รายงานประจำวัน</a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess('reports-monthly.php')): ?>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="reports-monthly.php">รายงานประจำเดือน</a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess('reports-yearly.php')): ?>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="reports-yearly.php">รายงานประจำปี</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
            <?php endif; ?>

            <?php if (canAccess('settings-general.php') || canAccess('admin-users-list.php')): ?>
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarSettings" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarSettings">
                        <span class="nav-icon">
                            <iconify-icon icon="iconamoon:settings-duotone"></iconify-icon>
                        </span>
                        <span class="nav-text">ตั้งค่า</span>
                    </a>
                    <div class="collapse" id="sidebarSettings">
                        <ul class="nav sub-navbar-nav">
                            <?php if (canAccess('settings-general.php')): ?>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="settings-general.php">ตั้งค่าทั่วไป</a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess('admin-users-list.php')): ?>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="admin-users-list.php">ผู้ดูแลระบบ</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link text-danger" href="<?= defined('ADMIN_PATH') ? ADMIN_PATH : 'auth' ?>/logout.php">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:exit-duotone" class="text-danger"></iconify-icon>
                    </span>
                    <span class="nav-text">ออกจากระบบ</span>
                </a>
            </li>

        </ul>
    </div>
</div>