<?php
include 'partials/main.php';

// Check permissions
if (!hasRole('super_admin')) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php
    $title = "จัดการผู้ดูแลระบบ";
    include 'partials/title-meta.php';
    ?>
    <?php include 'partials/head-css.php'; ?>
</head>

<body>
    <div class="wrapper">
        <?php include 'partials/edonation-nav.php'; ?>

        <div class="page-content">
            <?php include 'partials/edonation-topbar.php'; ?>

            <div class="container-xxl">

                <?php
                $pageTitle = "ตั้งค่า";
                $subTitle = "ผู้ดูแลระบบ";
                include 'partials/page-title.php'; ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">รายชื่อผู้ดูแลระบบ</h4>
                        <button type="button" class="btn btn-primary" onclick="openModal('create')">
                            <iconify-icon icon="solar:user-plus-bold-duotone"
                                class="align-middle me-1 fs-18"></iconify-icon>
                            เพิ่มผู้ดูแลระบบ
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-nowrap align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>อีเมล CMU Account</th>
                                        <th>สิทธิ์การใช้งาน</th>
                                        <th>สถานะ</th>
                                        <th>วันที่สร้าง</th>
                                        <th>เข้าสู่ระบบล่าสุด</th>
                                        <th style="width: 100px;" class="text-center">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">เพิ่มผู้ดูแลระบบ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="userForm" onsubmit="handleFormSubmit(event)">
                        <input type="hidden" id="formAction" value="create">
                        <input type="hidden" id="userId" value="">

                        <div class="mb-3">
                            <label for="email" class="form-label">อีเมล CMU Account <span
                                    class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required
                                placeholder="name.lastname@cmu.ac.th">
                            <small class="text-muted">ใช้สำหรับ Login ผ่าน CMU OAuth</small>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required
                                placeholder="ชื่อนามสกุลผู้ดูแลระบบ">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">สิทธิ์การใช้งาน</label>
                                    <select class="form-select" id="role" name="role">
                                        <option value="news_admin">News/Shipping (เจ้าหน้าที่ข่าว/จัดส่ง)</option>
                                        <option value="hr_admin">HR Admin (เจ้าหน้าที่บุคคล)</option>
                                        <option value="finance_admin">Finance Admin (เจ้าหน้าที่การเงิน)</option>
                                        <option value="super_admin">Super Admin (ผู้ดูแลระบบสูงสุด)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">สถานะ</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" id="btnSave">
                                <span class="spinner-border spinner-border-sm me-1 d-none" role="status"
                                    aria-hidden="true"></span>
                                บันทึกข้อมูล
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'partials/vendor-scripts.php'; ?>
    <script src="assets/js/api-helper.js"></script>

    <script>
        const userModal = new bootstrap.Modal(document.getElementById('userModal'));

        document.addEventListener('DOMContentLoaded', () => {
            loadUsers();
        });

        async function loadUsers() {
            const tbody = document.getElementById('usersTableBody');

            try {
                const response = await apiGet('/admin-users');
                const users = response.data || [];

                if (users.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted">ไม่พบข้อมูลผู้ดูแลระบบ</td></tr>`;
                    return;
                }

                tbody.innerHTML = users.map((user, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td class="fw-medium">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-light rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center text-primary fs-20 me-2">
                                    <iconify-icon icon="solar:user-bold-duotone"></iconify-icon>
                                </div>
                                ${escapeHtml(user.name)}
                            </div>
                        </td>
                        <td>${escapeHtml(user.email)}</td>
                        <td>${getRoleBadge(user.role)}</td>
                        <td>${getStatusBadge(user.status)}</td>
                        <td>${formatThaiDateShort(user.created_at)}</td>
                        <td>${user.last_login ? formatThaiDateTimeShort(user.last_login) : '<span class="text-muted">-</span>'}</td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light dropdown-toggle font-monospace" type="button" data-bs-toggle="dropdown">
                                    ...
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" onclick='editUser(${JSON.stringify(user)})'>
                                            <i class="mdi mdi-square-edit-outline me-1"></i> แก้ไขข้อมูล
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteUser(${user.id}, '${escapeHtml(user.email)}')">
                                            <i class="mdi mdi-delete me-1"></i> ลบผู้ใช้
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                `).join('');

            } catch (error) {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">เกิดข้อผิดพลาด: ${error.message}</td></tr>`;
            }
        }

        function getRoleBadge(role) {
            const map = {
                'super_admin': { class: 'badge-soft-danger', text: 'Super Admin' },
                'finance_admin': { class: 'badge-soft-success', text: 'Finance Admin' },
                'hr_admin': { class: 'badge-soft-primary', text: 'HR Admin' },
                'news_admin': { class: 'badge-soft-secondary', text: 'News/Shipping' }
            };
            const config = map[role] || { class: 'badge-soft-light', text: role };
            return `<span class="badge ${config.class} fs-12">${config.text}</span>`;
        }

        function getStatusBadge(status) {
            return status === 'active'
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-danger">Inactive</span>';
        }

        function openModal(mode) {
            document.getElementById('formAction').value = mode;
            const btn = document.getElementById('btnSave');
            const emailInput = document.getElementById('email');

            if (mode === 'create') {
                document.getElementById('modalTitle').textContent = 'เพิ่มผู้ดูแลระบบ';
                document.getElementById('userForm').reset();
                document.getElementById('userId').value = '';
                document.getElementById('role').value = 'news_admin';
                document.getElementById('status').value = 'active';
                emailInput.readOnly = false;
                btn.textContent = 'บันทึกข้อมูล';
            }
            userModal.show();
        }

        function editUser(user) {
            document.getElementById('formAction').value = 'update';
            document.getElementById('modalTitle').textContent = 'แก้ไขข้อมูลผู้ดูแลระบบ';
            document.getElementById('userId').value = user.id;
            document.getElementById('email').value = user.email;
            document.getElementById('email').readOnly = true; // Email cannot be changed
            document.getElementById('name').value = user.name;
            document.getElementById('role').value = user.role;
            document.getElementById('status').value = user.status;
            document.getElementById('btnSave').textContent = 'บันทึกการแก้ไข';
            userModal.show();
        }

        async function handleFormSubmit(e) {
            e.preventDefault();

            const action = document.getElementById('formAction').value;
            const id = document.getElementById('userId').value;
            const btn = document.getElementById('btnSave');

            // Loading state
            btn.disabled = true;
            const originalText = btn.textContent;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> กำลังบันทึก...';

            const data = {
                email: document.getElementById('email').value,
                name: document.getElementById('name').value,
                role: document.getElementById('role').value,
                status: document.getElementById('status').value
            };

            try {
                if (action === 'create') {
                    await apiPost('/admin-users', data);
                    showSuccess('เพิ่มผู้ดูแลระบบเรียบร้อยแล้ว');
                } else {
                    await apiPut(`/admin-users/${id}`, data); // Note: Make sure API supports PUT
                    showSuccess('บันทึกข้อมูลเรียบร้อยแล้ว');
                }

                userModal.hide();
                loadUsers();

            } catch (error) {
                showError(error.message);
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        }

        async function deleteUser(id, email) {
            const result = await Swal.fire({
                title: 'ยืนยันการลบ?',
                text: `คุณต้องการลบผู้ดูแลระบบ ${email} ใช่หรือไม่?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ลบข้อมูล',
                cancelButtonText: 'ยกเลิก'
            });

            if (result.isConfirmed) {
                try {
                    await apiDelete(`/admin-users/${id}`);
                    showSuccess('ลบผู้ดูแลระบบเรียบร้อยแล้ว');
                    loadUsers();
                } catch (error) {
                    showError(error.message);
                }
            }
        }
    </script>

</body>

</html>