<div class="main-nav">
    <!-- Sidebar Logo -->
    <div class="logo-box">
        <a href="dashboard.php" class="logo-dark">
            <img src="assets/images/logo-sm.png" class="logo-sm" alt="logo sm" onerror="this.style.display='none'">
            <span class="fs-16 fw-semibold text-primary ms-2">eDonation</span>
        </a>
        <a href="dashboard.php" class="logo-light">
            <img src="assets/images/logo-sm.png" class="logo-sm" alt="logo sm" onerror="this.style.display='none'">
            <span class="fs-16 fw-semibold text-white ms-2">eDonation Admin</span>
        </a>
    </div>

    <!-- Menu Toggle Button -->
    <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
        <iconify-icon icon="iconamoon:arrow-left-4-square-duotone" class="button-sm-hover-icon"></iconify-icon>
    </button>

    <div class="scrollbar" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">

            <!-- หน้าหลัก -->
            <li class="menu-title">หน้าหลัก</li>

            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:home-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <!-- จัดการการบริจาค -->
            <li class="menu-title">จัดการการบริจาค</li>

            <li class="nav-item">
                <a class="nav-link" href="donations-list.php">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:heart-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text">รายการบริจาค</span>
                </a>
            </li>

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
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="receipts-list.php">รายการใบเสร็จ</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="receipts-generate.php">ออกใบเสร็จ</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="receipts-print-address.php">พิมพ์ที่อยู่จัดส่ง</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- จัดการโครงการ -->
            <li class="menu-title">จัดการโครงการ</li>

            <li class="nav-item">
                <a class="nav-link" href="projects-list.php">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:folder-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text">โครงการ</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="benefits-list.php">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:gift-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text">สิทธิประโยชน์</span>
                </a>
            </li>

            <!-- ข่าวสารและเนื้อหา -->
            <li class="menu-title">ข่าวสารและเนื้อหา</li>

            <li class="nav-item">
                <a class="nav-link" href="news-list.php">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:news-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text">ข่าวสาร</span>
                </a>
            </li>

            <!-- ผู้บริจาค -->
            <li class="menu-title">ผู้บริจาค</li>

            <li class="nav-item">
                <a class="nav-link menu-arrow" href="#sidebarMembers" data-bs-toggle="collapse" role="button"
                    aria-expanded="false" aria-controls="sidebarMembers">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:profile-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text">สมาชิก</span>
                </a>
                <div class="collapse" id="sidebarMembers">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="members-list.php">รายชื่อสมาชิก</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="members-search.php">ค้นหาประวัติ</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- รายงาน -->
            <li class="menu-title">รายงาน</li>

            <li class="nav-item">
                <a class="nav-link menu-arrow" href="#sidebarReports" data-bs-toggle="collapse" role="button"
                    aria-expanded="false" aria-controls="sidebarReports">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:3d-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text">รายงาน</span>
                </a>
                <div class="collapse" id="sidebarReports">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="reports-daily.php">รายงานประจำวัน</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="reports-monthly.php">รายงานประจำเดือน</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="reports-yearly.php">รายงานประจำปี</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- ตั้งค่า -->
            <li class="menu-title">ตั้งค่าระบบ</li>

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
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="settings-general.php">ตั้งค่าทั่วไป</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="admin-users.php">ผู้ดูแลระบบ</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Logout -->
            <li class="menu-title">บัญชี</li>

            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:sign-out-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text">ออกจากระบบ</span>
                </a>
            </li>

        </ul>
    </div>
</div>