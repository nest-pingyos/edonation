<?php include 'partials/main.php'; ?>
<?php requireAuth(); ?>
<!doctype html>
<html lang="th">

<head>
    <?php
    $title = "ข้อมูลส่วนตัว";
    include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php'; ?>
</head>

<body>
    <div class="wrapper">
        <?php include 'partials/edonation-nav.php'; ?>

        <div class="page-content">
            <?php include 'partials/edonation-topbar.php'; ?>

            <div class="container-xxl">
                <!-- start page title -->
                <?php
                $pageTitle = "ตั้งค่า";
                $subTitle = "ข้อมูลส่วนตัว";
                include 'partials/page-title.php'; ?>

                <div class="row">
                    <div class="col-lg-4">
                        <div class="card overflow-hidden">
                            <div class="bg-soft-primary" style="height: 100px;"></div>
                            <div class="card-body pt-0">
                                <div class="row">
                                    <div class="col-sm-12 text-center">
                                        <div class="avatar-md profile-user-wid mb-4 mx-auto" style="margin-top: -35px;">
                                            <span
                                                class="avatar-title rounded-circle bg-light text-primary fs-24 fw-bold"
                                                id="profile-initial">
                                                -
                                            </span>
                                        </div>
                                        <h5 class="font-size-18 text-truncate" id="profile-name">Loading...</h5>
                                        <p class="text-muted mb-0 text-truncate" id="profile-role">-</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body border-top">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="mb-4">
                                            <p class="text-muted mb-1">อีเมล</p>
                                            <h5 class="font-size-14" id="view-email">-</h5>
                                        </div>
                                        <div class="mb-4">
                                            <p class="text-muted mb-1">สถานะ</p>
                                            <div id="view-status">-</div>
                                        </div>
                                        <div class="mb-0">
                                            <p class="text-muted mb-1">เข้าสู่ระบบล่าสุด</p>
                                            <h5 class="font-size-14" id="view-last-login">-</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">แก้ไขข้อมูลส่วนตัว</h4>

                                <form id="profileForm">
                                    <div class="row mb-4">
                                        <label for="name" class="col-sm-3 col-form-label">ชื่อ-นามสกุล</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="name" name="name" disabled>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="email" class="col-sm-3 col-form-label">อีเมล</label>
                                        <div class="col-sm-9">
                                            <input type="email" class="form-control" id="email" disabled>
                                            <small class="text-muted">อีเมลไม่สามารถแก้ไขได้</small>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label class="col-sm-3 col-form-label">ระดับสิทธิ์</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="role-display" disabled>
                                        </div>
                                    </div>

                                    <div class="row mb-0">
                                        <label class="col-sm-3 col-form-label">การแจ้งเตือน Line</label>
                                        <div class="col-sm-9 d-flex align-items-center">
                                            <div class="form-check form-switch form-switch-md mb-0" dir="ltr">
                                                <input type="checkbox" class="form-check-input" id="lineNotification">
                                                <label class="form-check-label ms-2" for="lineNotification"
                                                    id="lineStatusLabel">ปิดอยู่</label>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Optional: Security Card (Future expansion) -->
                        <div class="card" id="security-card" style="display: none;">
                            <div class="card-body">
                                <h4 class="card-title mb-4">ความปลอดภัย</h4>
                                <button class="btn btn-outline-danger btn-sm">เปลี่ยนรหัสผ่าน</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'partials/footer.php'; ?>
    </div>
    </div>

    <?php include 'partials/vendor-scripts.php'; ?>
    <script src="assets/js/api-helper.js"></script>

    <script>
        let currentUser = null;

        async function loadProfile() {
            try {
                // Try to get from auth/me (JWT)
                const res = await apiGet('/auth/me');
                if (res.success && res.data) {
                    const userId = res.data.id;

                    // Fetch full details from admin-users API
                    const userRes = await apiGet(`/admin-users/${userId}`);
                    if (userRes.success && userRes.data) {
                        currentUser = userRes.data;
                        renderProfile(currentUser);
                    }

                    // Load notification status
                    loadNotificationStatus();
                }
            } catch (error) {
                console.error('Failed to load profile:', error);
                showError('ไม่สามารถโหลดข้อมูลโปรไฟล์ได้');
            }
        }

        function renderProfile(user) {
            // Sidebar/Header card
            document.getElementById('profile-name').textContent = user.name;
            document.getElementById('profile-role').textContent = getRoleLabel(user.role);
            document.getElementById('profile-initial').textContent = user.name.charAt(0).toUpperCase();

            // Detail info
            document.getElementById('view-email').textContent = user.email;
            document.getElementById('view-status').innerHTML = getStatusBadge(user.status);
            document.getElementById('view-last-login').textContent = formatThaiDateTimeShort(user.last_login);

            // Form inputs
            document.getElementById('name').value = user.name;
            document.getElementById('email').value = user.email;
            document.getElementById('role-display').value = getRoleLabel(user.role);
        }

        async function loadNotificationStatus() {
            try {
                const res = await apiGet('/notifications/self-status');
                if (res.success) {
                    const checkbox = document.getElementById('lineNotification');
                    const label = document.getElementById('lineStatusLabel');

                    checkbox.checked = res.data.is_active;
                    label.textContent = res.data.is_active ? 'เปิดอยู่' : 'ปิดอยู่';
                    label.className = res.data.is_active ? 'form-check-label ms-2 text-success fw-bold' : 'form-check-label ms-2 text-muted';
                }
            } catch (error) {
                console.error('Failed to load notification status:', error);
            }
        }

        document.getElementById('lineNotification').addEventListener('change', async function (e) {
            const is_active = e.target.checked;
            const label = document.getElementById('lineStatusLabel');

            label.textContent = is_active ? 'กำลังบันทึก...' : 'กำลังบันทึก...';

            try {
                const res = await apiPost('/notifications/toggle-status', { is_active: is_active });
                if (res.success) {
                    label.textContent = is_active ? 'เปิดอยู่' : 'ปิดอยู่';
                    label.className = is_active ? 'form-check-label ms-2 text-success fw-bold' : 'form-check-label ms-2 text-muted';
                    showSuccess(is_active ? 'เปิดการแจ้งเตือน Line เรียบร้อย' : 'ปิดการแจ้งเตือน Line เรียบร้อย');
                }
            } catch (error) {
                e.target.checked = !is_active; // revert
                label.textContent = !is_active ? 'เปิดอยู่' : 'ปิดอยู่';
                showError('ไม่สามารถบันทึกสถานะได้');
            }
        });

        function getRoleLabel(role) {
            const roles = {
                'super_admin': 'ผู้ดูแลระบบสูงสุด',
                'finance_admin': 'เจ้าหน้าที่การเงิน (Finance)',
                'hr_admin': 'เจ้าหน้าที่บุคคล (HR)',
                'news_admin': 'เจ้าหน้าที่ข่าว/จัดส่ง (News/Shipping)'
            };
            return roles[role] || role;
        }

        function getStatusBadge(status) {
            if (status === 'active') {
                return '<span class=\"badge bg-success font-size-11\">ใช้งานอยู่</span>';
            }
            return '<span class=\"badge bg-danger font-size-11\">ไม่ใช้งาน</span>';
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', loadProfile);
    </script>
</body>

</html>