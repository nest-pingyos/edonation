<?php include 'partials/main.php'; ?>
<?php requireAuth(); ?>

<!doctype html>
<html lang="th">

<head>
    <?php
    $title = "จัดการโครงการ";
    include 'partials/title-meta.php'; ?>

    <?php include 'partials/head-css.php'; ?>
</head>

<body>
    <!-- START Wrapper -->
    <div class="wrapper">
        <?php include 'partials/edonation-nav.php'; ?>

        <div class="page-content">
            <?php include 'partials/edonation-topbar.php'; ?>

            <div class="container-xxl">
                <?php
                $pageTitle = "จัดการโครงการ";
                $subTitle = "โครงการ";
                include 'partials/page-title.php'; ?>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-soft-primary rounded">
                                        <iconify-icon icon="iconamoon:folder-duotone"
                                            class="avatar-title text-primary fs-32"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="total-projects">-</h3>
                                        <p class="text-muted mb-0">โครงการทั้งหมด</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-soft-success rounded">
                                        <iconify-icon icon="iconamoon:check-circle-1-duotone"
                                            class="avatar-title text-success fs-32"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="active-projects">-</h3>
                                        <p class="text-muted mb-0">กำลังดำเนินการ</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Card -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">รายการโครงการ</h4>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#projectModal"
                            onclick="openCreateModal()">
                            <iconify-icon icon="iconamoon:sign-plus-duotone" class="me-1"></iconify-icon>
                            เพิ่มโครงการ
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Search -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <input type="text" id="searchInput" class="form-control" placeholder="ค้นหาโครงการ...">
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-nowrap">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th style="width: 100px;">รหัส</th>
                                        <th>ชื่อโครงการ</th>
                                        <th style="width: 120px;">สถานะ</th>
                                        <th style="width: 150px;" class="text-center">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="projectsTable">
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">กำลังโหลด...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav id="pagination" class="mt-3"></nav>
                    </div>
                </div>
            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>
    <!-- END Wrapper -->

    <!-- Project Modal -->
    <div class="modal fade" id="projectModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">เพิ่มโครงการใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="projectForm">
                    <div class="modal-body">
                        <input type="hidden" id="projectId" name="id">

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">รหัสโครงการ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="project_number" name="project_number"
                                    required>
                                <div class="form-text">เช่น PRJ001, EDU2024</div>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">ชื่อโครงการ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="project_name" name="project_name" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">รายละเอียด</label>
                            <textarea class="form-control" id="project_tex" name="project_tex" rows="4"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">สถานะ</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active">กำลังดำเนินการ</option>
                                    <option value="inactive">ปิดรับบริจาค</option>
                                    <option value="completed">เสร็จสิ้น</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="spinner-border spinner-border-sm me-1 d-none" id="submitSpinner"></span>
                            บันทึก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'partials/vendor-scripts.php'; ?>
    <script src="assets/js/api-helper.js"></script>

    <script>
        // Global state
        let projects = [];
        let currentPage = 1;
        let editMode = false;

        // Load projects on page load
        document.addEventListener('DOMContentLoaded', function () {
            loadProjects();

            // Search handler
            document.getElementById('searchInput').addEventListener('input', debounce(function (e) {
                filterProjects(e.target.value);
            }, 300));

            // Form submit handler
            document.getElementById('projectForm').addEventListener('submit', handleSubmit);
        });

        // Load projects from API
        async function loadProjects() {
            try {
                const response = await apiGet('/projects');
                projects = response.data || [];

                // Update stats
                document.getElementById('total-projects').textContent = projects.length;
                document.getElementById('active-projects').textContent = projects.filter(p => p.status === 'active' || !p.status).length;

                renderTable(projects);
            } catch (error) {
                showError('ไม่สามารถโหลดข้อมูลได้: ' + error.message);
                document.getElementById('projectsTable').innerHTML = `
            <tr><td colspan="5" class="text-center py-4 text-danger">เกิดข้อผิดพลาด: ${error.message}</td></tr>
        `;
            }
        }

        // Render table
        function renderTable(data) {
            const tbody = document.getElementById('projectsTable');

            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">ไม่พบข้อมูลโครงการ</td></tr>';
                return;
            }

            tbody.innerHTML = data.map((item, index) => `
        <tr>
            <td>${index + 1}</td>
            <td><span class="badge bg-light text-dark">${escapeHtml(item.project_number || '-')}</span></td>
            <td>
                <div class="d-flex align-items-center">
                    <img src="${item.image_url || 'assets/images/placeholder.jpg'}" 
                         class="rounded me-2" width="40" height="40" 
                         style="object-fit: cover;"
                         onerror="this.src='assets/images/placeholder.jpg'">
                    <div>
                        <span class="fw-medium">${escapeHtml(item.project_name)}</span>
                        ${item.project_tex ? `<br><small class="text-muted">${escapeHtml(item.project_tex.substring(0, 50))}${item.project_tex.length > 50 ? '...' : ''}</small>` : ''}
                    </div>
                </div>
            </td>
            <td>${getStatusBadge(item.status)}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-soft-primary me-1" onclick="openEditModal(${item.id})" title="แก้ไข">
                    <iconify-icon icon="iconamoon:edit-duotone"></iconify-icon>
                </button>
                <button class="btn btn-sm btn-soft-danger" onclick="deleteProject(${item.id}, '${escapeHtml(item.project_name)}')" title="ลบ">
                    <iconify-icon icon="iconamoon:trash-duotone"></iconify-icon>
                </button>
            </td>
        </tr>
    `).join('');
        }

        // Filter projects
        function filterProjects(query) {
            if (!query) {
                renderTable(projects);
                return;
            }

            const filtered = projects.filter(p =>
                p.project_name?.toLowerCase().includes(query.toLowerCase()) ||
                p.project_number?.toLowerCase().includes(query.toLowerCase())
            );
            renderTable(filtered);
        }

        // Open create modal
        function openCreateModal() {
            editMode = false;
            document.getElementById('modalTitle').textContent = 'เพิ่มโครงการใหม่';
            document.getElementById('projectForm').reset();
            document.getElementById('projectId').value = '';
        }

        // Open edit modal
        function openEditModal(id) {
            const project = projects.find(p => p.id == id);
            if (!project) return;

            editMode = true;
            document.getElementById('modalTitle').textContent = 'แก้ไขโครงการ';
            document.getElementById('projectId').value = project.id;
            document.getElementById('project_number').value = project.project_number || '';
            document.getElementById('project_name').value = project.project_name || '';
            document.getElementById('project_tex').value = project.project_tex || '';
            document.getElementById('status').value = project.status || 'active';

            new bootstrap.Modal(document.getElementById('projectModal')).show();
        }

        // Handle form submit
        async function handleSubmit(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.getElementById('submitSpinner');

            submitBtn.disabled = true;
            spinner.classList.remove('d-none');

            try {
                const formData = {
                    project_number: document.getElementById('project_number').value,
                    project_name: document.getElementById('project_name').value,
                    project_tex: document.getElementById('project_tex').value,
                    status: document.getElementById('status').value
                };

                if (editMode) {
                    const id = document.getElementById('projectId').value;
                    await apiPut('/projects/' + id, formData);
                    showSuccess('อัปเดตโครงการสำเร็จ');
                } else {
                    await apiPost('/projects', formData);
                    showSuccess('เพิ่มโครงการสำเร็จ');
                }

                bootstrap.Modal.getInstance(document.getElementById('projectModal')).hide();
                loadProjects();

            } catch (error) {
                showError(error.message);
            } finally {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        // Delete project
        async function deleteProject(id, name) {
            const result = await confirmDelete(name);
            if (!result.isConfirmed) return;

            try {
                await apiDelete('/projects/' + id);
                showSuccess('ลบโครงการสำเร็จ');
                loadProjects();
            } catch (error) {
                showError(error.message);
            }
        }

        // Utility functions
        function getStatusBadge(status) {
            const badges = {
                'active': '<span class="badge badge-soft-success">กำลังดำเนินการ</span>',
                'inactive': '<span class="badge badge-soft-warning">ปิดรับบริจาค</span>',
                'completed': '<span class="badge badge-soft-secondary">เสร็จสิ้น</span>'
            };
            return badges[status] || badges['active'];
        }

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
    </script>

</body>

</html>