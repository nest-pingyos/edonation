<?php include 'partials/main.php'; ?>
<?php requireAuth(); ?>

<!doctype html>
<html lang="th">

<head>
    <?php
    $title = "รายการบริจาค";
    include 'partials/title-meta.php'; ?>

    <?php include 'partials/head-css.php'; ?>
</head>

<body>
    <div class="wrapper">
        <?php include 'partials/edonation-nav.php'; ?>

        <div class="page-content">
            <?php include 'partials/edonation-topbar.php'; ?>

            <div class="container-xxl">
                <?php
                $pageTitle = "รายการบริจาค";
                $subTitle = "การบริจาค";
                include 'partials/page-title.php'; ?>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-soft-primary rounded">
                                        <iconify-icon icon="iconamoon:heart-duotone"
                                            class="avatar-title text-primary fs-32"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="total-count">-</h3>
                                        <p class="text-muted mb-0">รายการทั้งหมด</p>
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
                                        <h3 class="mb-0" id="confirmed-count">-</h3>
                                        <p class="text-muted mb-0">ยืนยันแล้ว</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-soft-warning rounded">
                                        <iconify-icon icon="iconamoon:clock-duotone"
                                            class="avatar-title text-warning fs-32"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="pending-count">-</h3>
                                        <p class="text-muted mb-0">รอยืนยัน</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-white bg-opacity-25 rounded">
                                        <iconify-icon icon="iconamoon:trend-up-duotone"
                                            class="avatar-title text-white fs-32"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0 text-white" id="total-amount">-</h3>
                                        <p class="mb-0 opacity-75">ยอดรวม (บาท)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Card -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">รายการบริจาค</h4>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <input type="text" id="searchInput" class="form-control"
                                    placeholder="ค้นหาชื่อ, เลขบัตร...">
                            </div>
                            <div class="col-md-2">
                                <select id="statusFilter" class="form-select">
                                    <option value="">ทุกสถานะ</option>
                                    <option value="CONFIRMED">ยืนยันแล้ว</option>
                                    <option value="PENDING">รอยืนยัน</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select id="projectFilter" class="form-select">
                                    <option value="">ทุกโครงการ</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" id="dateFrom" class="form-control" placeholder="จากวันที่">
                            </div>
                            <div class="col-md-2">
                                <input type="date" id="dateTo" class="form-control" placeholder="ถึงวันที่">
                            </div>
                            <div class="col-md-1">
                                <button class="btn btn-outline-primary w-100" onclick="loadDonations()">
                                    <iconify-icon icon="iconamoon:search-duotone"></iconify-icon>
                                </button>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-nowrap">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 50px;">ลำดับ</th>
                                        <th>ผู้บริจาค</th>
                                        <th>โครงการ</th>
                                        <th class="text-end">จำนวนเงิน</th>
                                        <th>วันที่</th>
                                        <th class="text-center">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="donationsTable">
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="spinner-border text-primary"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div id="pagination-info" class="text-muted"></div>
                            <nav id="pagination"></nav>
                        </div>
                    </div>
                </div>
            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">รายละเอียดการบริจาค</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ปิด</button>
                    <a href="#" id="viewReceiptBtn" class="btn btn-primary" target="_blank">
                        <iconify-icon icon="iconamoon:invoice-duotone" class="me-1"></iconify-icon>
                        ดูใบเสร็จ
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แก้ไขข้อมูลการบริจาค</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm">
                    <div class="modal-body">
                        <input type="hidden" id="editId" name="id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">เบอร์โทรศัพท์</label>
                                <input type="text" class="form-control" id="edit_phone" name="phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">สถานะ</label>
                                <select class="form-select" id="edit_status" name="status_donat">
                                    <option value="pending">รอยืนยัน</option>
                                    <option value="completed">ยืนยันแล้ว</option>
                                    <option value="cancelled">ยกเลิก</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ที่อยู่ใบเสร็จ</label>
                            <textarea class="form-control" id="edit_receipt_address" name="receipt_address"
                                rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary" id="editSubmitBtn">
                            <span class="spinner-border spinner-border-sm me-1 d-none" id="editSubmitSpinner"></span>
                            บันทึกการแก้ไข
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'partials/vendor-scripts.php'; ?>
    <script src="assets/js/api-helper.js"></script>

    <script>
        let donations = [];
        let projects = [];
        let currentPage = 1;
        const perPage = 20;

        document.addEventListener('DOMContentLoaded', function () {
            loadProjects();
            loadDonations();

            // Filter handlers
            document.getElementById('searchInput').addEventListener('input', debounce(loadDonations, 500));
            document.getElementById('statusFilter').addEventListener('change', loadDonations);
            document.getElementById('projectFilter').addEventListener('change', loadDonations);

            // Form submit handler
            document.getElementById('editForm').addEventListener('submit', handleEditSubmit);
        });

        async function loadProjects() {
            try {
                const response = await apiGet('/projects');
                projects = response.data || [];

                const select = document.getElementById('projectFilter');
                projects.forEach(p => {
                    const option = document.createElement('option');
                    option.value = p.project_number;
                    option.textContent = p.project_name;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Failed to load projects:', error);
            }
        }

        async function loadDonations() {
            const tbody = document.getElementById('donationsTable');
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';

            try {
                // Build query params
                const params = new URLSearchParams();
                params.append('page', currentPage);
                params.append('limit', perPage);

                const search = document.getElementById('searchInput').value;
                if (search) params.append('search', search);

                const status = document.getElementById('statusFilter').value;
                if (status) params.append('status', status);

                const project = document.getElementById('projectFilter').value;
                if (project) params.append('project', project);

                const dateFrom = document.getElementById('dateFrom').value;
                if (dateFrom) params.append('from', dateFrom);

                const dateTo = document.getElementById('dateTo').value;
                if (dateTo) params.append('to', dateTo);

                const response = await apiGet('/donations?' + params.toString());
                donations = response.data || [];
                const meta = response.meta || {};

                // Update stats
                document.getElementById('total-count').textContent = formatNumber(meta.total || 0);
                document.getElementById('confirmed-count').textContent = formatNumber(meta.confirmed || 0);
                document.getElementById('pending-count').textContent = formatNumber(meta.pending || 0);
                document.getElementById('total-amount').textContent = formatNumber(meta.totalAmount || 0);

                // Update pagination info
                const start = meta.total === 0 ? 0 : (currentPage - 1) * perPage + 1;
                const end = Math.min(currentPage * perPage, meta.total);
                document.getElementById('pagination-info').textContent = `แสดง ${start} ถึง ${end} จากทั้งหมด ${meta.total} รายการ`;

                renderTable(donations);
                renderPagination('pagination', currentPage, meta.totalPages, 'changePage');

            } catch (error) {
                showError(error.message);
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">${error.message}</td></tr>`;
            }
        }

        function changePage(page) {
            currentPage = page;
            loadDonations();
        }

        function renderTable(data) {
            const tbody = document.getElementById('donationsTable');

            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">ไม่พบรายการบริจาค</td></tr>';
                return;
            }

            tbody.innerHTML = data.map((item, index) => `
        <tr>
            <td>
                ${(currentPage - 1) * perPage + index + 1}
            </td>
            <td style="max-width: 200px;">
                <div class="fw-medium text-truncate" title="${escapeHtml(item.donor_name || item.name || 'ไม่ระบุชื่อ')}">
                    ${escapeHtml(item.donor_name || item.name || 'ไม่ระบุชื่อ')}
                </div>
            </td>
            <td>${escapeHtml(item.project_name || item.project_number || '-')}</td>
            <td class="text-end fw-semibold text-primary">${formatCurrency(item.amount || 0)}</td>
            <td>${formatThaiDateShort(item.transaction_date || item.created_at)}</td>
            <td class="text-center">
                <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-sm btn-soft-primary" onclick="viewDetail('${item.billPaymentRef1 || item.id}')" title="ดูรายละเอียด">
                        <iconify-icon icon="iconamoon:eye-duotone"></iconify-icon>
                    </button>
                    <button class="btn btn-sm btn-soft-info" onclick="editDonation('${item.id}')" title="แก้ไข">
                        <iconify-icon icon="iconamoon:edit-duotone"></iconify-icon>
                    </button>
                    <button class="btn btn-sm btn-soft-danger" onclick="deleteDonation('${item.id}')" title="ลบ">
                        <iconify-icon icon="iconamoon:trash-duotone"></iconify-icon>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
        }

        async function viewDetail(ref) {
            const modal = new bootstrap.Modal(document.getElementById('detailModal'));
            modal.show();

            const content = document.getElementById('detailContent');
            content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';

            try {
                const response = await apiGet('/donations/' + ref);
                const d = response.data;

                content.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted mb-3">ข้อมูลผู้บริจาค</h6>
                    <table class="table table-sm">
                        <tr><td class="text-muted" width="120">ชื่อ</td><td class="fw-medium">${escapeHtml(d.donor_name || d.name)}</td></tr>
                        <tr><td class="text-muted">เลขบัตรประชาชน</td><td>${maskIdCard(d.id_card)}</td></tr>
                        <tr><td class="text-muted">อีเมล</td><td>${d.email || '-'}</td></tr>
                        <tr><td class="text-muted">โทรศัพท์</td><td>${d.phone || '-'}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted mb-3">ข้อมูลการบริจาค</h6>
                    <table class="table table-sm">
                        <tr><td class="text-muted" width="120">Ref</td><td class="font-monospace">${d.billPaymentRef1 || d.ref}</td></tr>
                        <tr><td class="text-muted">จำนวนเงิน</td><td class="fw-semibold text-primary fs-18">${formatCurrency(d.amount)}</td></tr>
                        <tr><td class="text-muted">โครงการ</td><td>${escapeHtml(d.project_name || d.project_number)}</td></tr>
                        <tr><td class="text-muted">วันที่</td><td>${formatThaiDate(d.transaction_date || d.created_at)}</td></tr>
                        <tr><td class="text-muted">สถานะ</td><td>${getStatusBadge(d.status)}</td></tr>
                    </table>
                </div>
            </div>
        `;

                // Update receipt button
                document.getElementById('viewReceiptBtn').href = '../api/v1/receipts/' + (d.billPaymentRef1 || d.id) + '/pdf';

            } catch (error) {
                content.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
            }
        }

        function getStatusBadge(status) {
            const badges = {
                'CONFIRMED': '<span class="badge badge-soft-success">ยืนยันแล้ว</span>',
                'PENDING': '<span class="badge badge-soft-warning">รอยืนยัน</span>',
                'CANCELLED': '<span class="badge badge-soft-danger">ยกเลิก</span>'
            };
            return badges[status] || badges['PENDING'];
        }

        function maskIdCard(id) {
            if (!id || id.length < 13) return id || '-';
            return id.substring(0, 3) + '-****-*****-' + id.substring(10);
        }

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        async function deleteDonation(id) {
            const result = await confirmDelete('รายการบริจาคนี้');
            if (!result.isConfirmed) return;

            try {
                await apiDelete('/donations/' + id);
                showSuccess('ลบรายการสำเร็จ');
                loadDonations();
            } catch (error) {
                showError(error.message);
            }
        }

        async function editDonation(id) {
            try {
                const response = await apiGet('/donations/' + id);
                const d = response.data;

                document.getElementById('editId').value = d.id;
                document.getElementById('edit_first_name').value = d.first_name || '';
                document.getElementById('edit_last_name').value = d.last_name || '';
                document.getElementById('edit_phone').value = d.phone || '';
                document.getElementById('edit_status').value = d.status_donat || 'pending';
                document.getElementById('edit_receipt_address').value = d.receipt_address || '';

                new bootstrap.Modal(document.getElementById('editModal')).show();
            } catch (error) {
                showError('ไม่สามารถโหลดข้อมูลได้: ' + error.message);
            }
        }

        async function handleEditSubmit(e) {
            e.preventDefault();
            const id = document.getElementById('editId').value;
            const submitBtn = document.getElementById('editSubmitBtn');
            const spinner = document.getElementById('editSubmitSpinner');

            submitBtn.disabled = true;
            spinner.classList.remove('d-none');

            try {
                const formData = {
                    first_name: document.getElementById('edit_first_name').value,
                    last_name: document.getElementById('edit_last_name').value,
                    phone: document.getElementById('edit_phone').value,
                    status_donat: document.getElementById('edit_status').value,
                    receipt_address: document.getElementById('edit_receipt_address').value
                };

                await apiPut('/donations/' + id, formData);
                showSuccess('อัปเดตข้อมูลสำเร็จ');
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                loadDonations();
            } catch (error) {
                showError(error.message);
            } finally {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
            }
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