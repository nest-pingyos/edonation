<div class="main-nav">
    <!-- Sidebar Logo -->
    <div class="logo-box">
        <a href="dashboard.php" class="logo-dark">
            <img src="/edonation/assets/images/logo/logo.svg" class="h-auto" style="max-height: 40px;" alt="eDonation">
        </a>
        <a href="dashboard.php" class="logo-light">
            <img src="/edonation/assets/images/logo/logo.svg" class="h-auto" style="max-height: 40px;" alt="eDonation">
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
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link menu-arrow" href="#sidebarReceipts" data-bs-toggle="collapse" role="button"
                    aria-expanded="false" aria-controls="sidebarReceipts">
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

            <li class="nav-item">
                <a class="nav-link" href="projects-list.php">
                    <span class="nav-text">โครงการ</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="benefits-list.php">
                    <span class="nav-text">สิทธิประโยชน์</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="news-list.php">
                    <span class="nav-text">ข่าวสาร</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link menu-arrow" href="#sidebarMembers" data-bs-toggle="collapse" role="button"
                    aria-expanded="false" aria-controls="sidebarMembers">
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

            <li class="nav-item">
                <a class="nav-link menu-arrow" href="#sidebarReports" data-bs-toggle="collapse" role="button"
                    aria-expanded="false" aria-controls="sidebarReports">
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

            <li class="nav-item">
                <a class="nav-link menu-arrow" href="#sidebarSettings" data-bs-toggle="collapse" role="button"
                    aria-expanded="false" aria-controls="sidebarSettings">
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

            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php">
                    <span class="nav-text">ออกจากระบบ</span>
                </a>
            </li>

        </ul>
    </div>
</div>