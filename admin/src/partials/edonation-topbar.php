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
                <!-- User Menu -->

                <!-- User Menu -->
                <div class="dropdown">
                    <button type="button"
                        class="btn btn-link p-0 text-decoration-none d-flex align-items-center gap-2 border-0"
                        data-bs-toggle="dropdown" style="outline: none; box-shadow: none;">
                        <div
                            class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center">
                            <span class="avatar-title text-white fs-14 fw-bold"><?php echo $userInitial; ?></span>
                        </div>
                        <div class="d-none d-md-block text-start" style="line-height: normal;">
                            <span class="text-dark fw-medium d-block"><?php echo htmlspecialchars($userName); ?></span>
                        </div>
                        <iconify-icon icon="iconamoon:arrow-down-2" class="text-muted fs-18"></iconify-icon>
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