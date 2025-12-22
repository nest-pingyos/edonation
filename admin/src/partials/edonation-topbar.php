<?php
$currentUser = getCurrentUser();
$userName = $currentUser['name'] ?? $currentUser['email'] ?? 'Admin';
$userInitial = strtoupper(substr($userName, 0, 1));
?>

<header class="topbar">
    <div class="container-xxl">
        <div class="d-flex align-items-center justify-content-between w-100">
            <!-- Left Side -->
            <div class="d-flex align-items-center gap-3">
                <!-- Mobile Menu Toggle -->
                <button type="button" class="btn btn-link p-0 d-xl-none menu-toggle" onclick="toggleMenu()">
                    <iconify-icon icon="iconamoon:menu-burger-horizontal" class="fs-24"></iconify-icon>
                </button>
            </div>

            <!-- Right Side -->
            <div class="d-flex align-items-center gap-2">
                <!-- Theme Toggle -->
                <button type="button" class="btn btn-link p-2" onclick="toggleTheme()" title="เปลี่ยนธีม">
                    <iconify-icon icon="iconamoon:mode-dark-duotone" class="fs-22"></iconify-icon>
                </button>

                <!-- Notification -->
                <div class="dropdown">
                    <button type="button" class="btn btn-link p-2 position-relative" data-bs-toggle="dropdown">
                        <iconify-icon icon="iconamoon:notification-duotone" class="fs-22"></iconify-icon>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
                            3
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                        <h6 class="dropdown-header">การแจ้งเตือน</h6>
                        <a class="dropdown-item py-2" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-soft-success rounded-circle me-2">
                                        <span class="avatar-title text-success">
                                            <iconify-icon icon="iconamoon:heart-duotone"></iconify-icon>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0 small">มีการบริจาคใหม่</p>
                                    <small class="text-muted">5 นาทีที่แล้ว</small>
                                </div>
                            </div>
                        </a>
                        <a class="dropdown-item py-2" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-soft-info rounded-circle me-2">
                                        <span class="avatar-title text-info">
                                            <iconify-icon icon="iconamoon:invoice-duotone"></iconify-icon>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0 small">ออกใบเสร็จสำเร็จ 5 รายการ</p>
                                    <small class="text-muted">1 ชั่วโมงที่แล้ว</small>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center small text-primary" href="#">ดูทั้งหมด</a>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="dropdown">
                    <button type="button" class="btn btn-link p-1 d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <div class="avatar-sm bg-primary rounded-circle">
                            <span class="avatar-title text-white"><?php echo $userInitial; ?></span>
                        </div>
                        <span class="d-none d-md-inline text-dark"><?php echo htmlspecialchars($userName); ?></span>
                        <iconify-icon icon="iconamoon:arrow-down-2" class="text-muted"></iconify-icon>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">สวัสดี, <?php echo htmlspecialchars($userName); ?></h6>
                        <a class="dropdown-item" href="profile.php">
                            <iconify-icon icon="iconamoon:profile-circle-duotone" class="me-2"></iconify-icon>
                            โปรไฟล์
                        </a>
                        <a class="dropdown-item" href="settings-general.php">
                            <iconify-icon icon="iconamoon:settings-duotone" class="me-2"></iconify-icon>
                            ตั้งค่า
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="logout.php">
                            <iconify-icon icon="iconamoon:sign-out-duotone" class="me-2"></iconify-icon>
                            ออกจากระบบ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
