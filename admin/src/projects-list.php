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
                        <!-- Filters -->
                        <div
                            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted">แสดง</span>
                                    <select id="limitSelector" class="form-select form-select-sm border-0 bg-light"
                                        style="width: auto;" onchange="changeLimit()">
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="250">250</option>
                                        <option value="500">500</option>
                                    </select>
                                    <span class="text-muted">แถว</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 bg-light rounded px-3 py-2"
                                style="min-width: 280px;">
                                <iconify-icon icon="iconamoon:search-duotone" class="text-muted fs-5"></iconify-icon>
                                <input type="text" id="searchInput"
                                    class="form-control form-control-sm border-0 bg-transparent p-0"
                                    placeholder="ค้นหาโครงการ...">
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
                                        <th>ชื่อในใบเสร็จ</th>
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
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div id="pagination-info" class="text-muted small"></div>
                            <nav id="pagination"></nav>
                        </div>
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
                            <label class="form-label">ชื่อที่แสดงในใบเสร็จ</label>
                            <input type="text" class="form-control" id="project_receipt_name"
                                name="project_receipt_name">
                            <div class="form-text">ถ้าไม่ระบุจะใช้ชื่อโครงการเดียวกัน</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">รายละเอียดโครงการ</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            <div class="form-text">แสดงในหน้าเว็บสาธารณะ</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">รูปภาพโครงการ</label>
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="file" class="form-control" id="project_image" name="project_image"
                                        accept="image/jpeg,image/png,image/gif,image/webp"
                                        onchange="previewImage(this)">
                                    <div class="form-text">รองรับ JPG, PNG, GIF, WebP (สูงสุด 5MB)</div>
                                    <input type="hidden" id="image_url" name="image_url">
                                </div>
                                <div class="col-md-4">
                                    <div id="imagePreviewContainer" class="border rounded p-2 text-center"
                                        style="min-height: 100px;">
                                        <img id="imagePreview" src="" alt="Preview" class="img-fluid rounded d-none"
                                            style="max-height: 150px;">
                                        <span id="noImageText" class="text-muted small">ยังไม่มีรูปภาพ</span>
                                    </div>
                                </div>
                            </div>
                            <div id="uploadProgress" class="progress mt-2 d-none" style="height: 5px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                    style="width: 0%"></div>
                            </div>
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
        let perPage = 25;
        let editMode = false;

        // Load projects on page load
        document.addEventListener('DOMContentLoaded', function () {
            loadProjects();

            // Search handler
            document.getElementById('searchInput').addEventListener('input', debounce(function (e) {
                currentPage = 1;
                loadProjects();
            }, 500));

            // Form submit handler
            document.getElementById('projectForm').addEventListener('submit', handleSubmit);
        });

        // Load projects from API
        async function loadProjects() {
            try {
                const search = document.getElementById('searchInput').value;
                const params = new URLSearchParams();
                params.append('page', currentPage);
                params.append('limit', perPage);
                if (search) params.append('search', search);

                const response = await apiGet('/projects?' + params.toString());
                projects = response.data || [];
                const stats = response.meta || {};

                // Update stats (only on first load or total change?)
                document.getElementById('total-projects').textContent = stats.total || projects.length;
                document.getElementById('active-projects').textContent = stats.total ? (stats.total - (stats.completed || 0)) : projects.filter(p => p.status === 'active' || !p.status).length;

                renderTable(projects);

                // Update pagination
                const total = stats.total || projects.length;
                const totalPages = stats.total_pages || Math.ceil(total / perPage);
                renderPagination(totalPages, currentPage);

                const from = total > 0 ? (currentPage - 1) * perPage + 1 : 0;
                const to = Math.min(currentPage * perPage, total);
                document.getElementById('pagination-info').textContent = `แสดง ${from}-${to} จาก ${total} รายการ`;

            } catch (error) {
                showError('ไม่สามารถโหลดข้อมูลได้: ' + error.message);
                document.getElementById('projectsTable').innerHTML = `
            <tr><td colspan="6" class="text-center py-4 text-danger">เกิดข้อผิดพลาด: ${error.message}</td></tr>
        `;
            }
        }

        // Render table
        function renderTable(data) {
            const tbody = document.getElementById('projectsTable');

            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">ไม่พบข้อมูลโครงการ</td></tr>';
                return;
            }

            tbody.innerHTML = data.map((item, index) => `
        <tr>
            <td>${(currentPage - 1) * perPage + index + 1}</td>
            <td><span class="badge bg-light text-dark">${escapeHtml(item.project_number || '-')}</span></td>
            <td><span class="fw-medium">${escapeHtml(item.project_name)}</span></td>
            <td><span class="text-muted">${escapeHtml(item.project_receipt_name || item.project_name || '-')}</span></td>
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

        function changeLimit() {
            perPage = parseInt(document.getElementById('limitSelector').value);
            currentPage = 1;
            loadProjects();
        }

        function goToPage(page) {
            if (page < 1) return;
            currentPage = page;
            loadProjects();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function renderPagination(totalPages, currentPage) {
            const pagination = document.getElementById('pagination');
            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }

            let html = '<ul class="pagination pagination-sm mb-0">';

            // First & Previous
            html += `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="goToPage(1)" title="หน้าแรก">«</a>
                </li>
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="goToPage(${currentPage - 1})" title="ก่อนหน้า">‹</a>
                </li>
            `;

            // Calculate range
            let start = Math.max(1, currentPage - 2);
            let end = Math.min(totalPages, start + 4);
            if (end - start < 4) start = Math.max(1, end - 4);

            for (let i = start; i <= end; i++) {
                html += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="javascript:void(0)" onclick="goToPage(${i})">${i}</a>
                    </li>
                `;
            }

            // Next & Last
            html += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="goToPage(${currentPage + 1})" title="ถัดไป">›</a>
                </li>
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="goToPage(${totalPages})" title="หน้าสุดท้าย">»</a>
                </li>
            `;

            html += '</ul>';
            pagination.innerHTML = html;
        }

        // Open create modal
        function openCreateModal() {
            editMode = false;
            document.getElementById('modalTitle').textContent = 'เพิ่มโครงการใหม่';
            document.getElementById('projectForm').reset();
            document.getElementById('projectId').value = '';
            document.getElementById('image_url').value = '';

            // Reset image preview
            document.getElementById('imagePreview').classList.add('d-none');
            document.getElementById('imagePreview').src = '';
            document.getElementById('noImageText').classList.remove('d-none');
            document.getElementById('project_image').value = '';
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
            document.getElementById('project_receipt_name').value = project.project_receipt_name || '';
            document.getElementById('description').value = project.description || '';
            document.getElementById('status').value = project.status || 'active';
            document.getElementById('image_url').value = project.image_url || '';
            document.getElementById('project_image').value = '';

            // Show existing image preview
            if (project.image_url) {
                document.getElementById('imagePreview').src = project.image_url;
                document.getElementById('imagePreview').classList.remove('d-none');
                document.getElementById('noImageText').classList.add('d-none');
            } else {
                document.getElementById('imagePreview').classList.add('d-none');
                document.getElementById('noImageText').classList.remove('d-none');
            }

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
                // Upload image first if selected
                const imageInput = document.getElementById('project_image');
                let imageUrl = document.getElementById('image_url').value;

                if (imageInput.files && imageInput.files[0]) {
                    const uploadResult = await uploadProjectImage(imageInput.files[0]);
                    if (uploadResult.success) {
                        imageUrl = uploadResult.data.url;
                        document.getElementById('image_url').value = imageUrl;
                    } else {
                        throw new Error(uploadResult.error?.message || 'อัปโหลดรูปภาพล้มเหลว');
                    }
                }

                const formData = {
                    project_number: document.getElementById('project_number').value,
                    project_name: document.getElementById('project_name').value,
                    project_receipt_name: document.getElementById('project_receipt_name').value,
                    description: document.getElementById('description').value,
                    image_url: imageUrl,
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

        // Preview selected image
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const noImageText = document.getElementById('noImageText');

            if (input.files && input.files[0]) {
                const file = input.files[0];

                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    showError('ไฟล์มีขนาดใหญ่เกินไป (สูงสุด 5MB)');
                    input.value = '';
                    return;
                }

                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    showError('ประเภทไฟล์ไม่ถูกต้อง (รองรับ: JPG, PNG, GIF, WebP)');
                    input.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                    noImageText.classList.add('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                // Reset preview if no file
                if (!document.getElementById('image_url').value) {
                    preview.classList.add('d-none');
                    preview.src = '';
                    noImageText.classList.remove('d-none');
                }
            }
        }

        // Upload project image to server
        async function uploadProjectImage(file) {
            const progressBar = document.getElementById('uploadProgress');
            const progressInner = progressBar.querySelector('.progress-bar');

            progressBar.classList.remove('d-none');
            progressInner.style.width = '0%';

            return new Promise((resolve, reject) => {
                const formData = new FormData();
                formData.append('image', file);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', API_BASE + '/projects/upload-image', true);

                // Progress handler
                xhr.upload.onprogress = function (e) {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        progressInner.style.width = percent + '%';
                    }
                };

                // Complete handler
                xhr.onload = function () {
                    progressBar.classList.add('d-none');
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (xhr.status === 200 && response.success) {
                            resolve(response);
                        } else {
                            resolve({ success: false, error: response.error || { message: 'Upload failed' } });
                        }
                    } catch (e) {
                        resolve({ success: false, error: { message: 'Invalid response' } });
                    }
                };

                // Error handler
                xhr.onerror = function () {
                    progressBar.classList.add('d-none');
                    resolve({ success: false, error: { message: 'Network error' } });
                };

                xhr.send(formData);
            });
        }
    </script>

</body>

</html>