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
                                            <div class="avatar-md profile-user-wid mb-4 mx-auto"
                                                style="margin-top: -35px;">
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
                                                <input type="text" class="form-control" id="name" name="name" required>
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

                                        <div class="row justify-content-end">
                                            <div class="col-sm-9">
                                                <div>
                                                    <button type="submit" class="btn btn-primary w-md"
                                                        id="saveBtn">บันทึกการเปลี่ยนแปลง</button>
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

        function getRoleLabel(role) {
            const roles = {
                'super_admin': 'ผู้ดูแลระบบสูงสุด',
                'admin': 'ผู้ดูแลระบบ',
                'editor': 'เจ้าหน้าที่แก้ไข',
                'viewer': 'เจ้าหน้าที่ดูอย่างเดียว'
            };
            return roles[role] || role;
        }

        function getStatusBadge(status) {
            if (status === 'active') {
                return '<span class=\"badge bg-success font-size-11\">ใช้งานอยู่</span>';
            }
            return '<span class=\"badge bg-danger font-size-11\">ไม่ใช้งาน</span>';
        }

        document.getElementById('profileForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            if (!currentUser) return;

            const name = document.getElementById('name').value.trim();
            if (!name) {
                showWarning('กรุณากรอกชื่อ-นามสกุล');
                return;
            }

            const saveBtn = document.getElementById('saveBtn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class=\"spinner-border spinner-border-sm me-1\" role=\"status\" aria-hidden=\"true\"></span> กำลังบันทึก...';

            try {
                const res = await apiPut(`/admin-users/${currentUser.id}`, { name: name });
                if (res.success) {
                    showSuccess('อัปเดตโปรไฟล์สำเร็จ');
                    // Refresh current user data
                    currentUser.name = name;
                    renderProfile(currentUser);
                }
            } catch (error) {
                showError(error.message || 'ไม่สามารถอัปเดตโปรไฟล์ได้');
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = 'บันทึกการเปลี่ยนแปลง';
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', loadProfile);
    </script>
</body>

</html>