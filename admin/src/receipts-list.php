<?php include 'partials/main.php'; ?>
<?php requireAuth(); ?>

<!doctype html>
<html lang="th">

<head>
    <?php
    $title = "ใบเสร็จรับเงิน";
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
                $pageTitle = "ใบเสร็จรับเงิน";
                $subTitle = "รายการใบเสร็จ";
                include 'partials/page-title.php'; ?>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-soft-primary rounded">
                                        <span class="avatar-title text-primary fs-32">#</span>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="total-receipts">-</h3>
                                        <p class="text-muted mb-0">ใบเสร็จทั้งหมด</p>
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
                                        <span class="avatar-title text-success fs-32">✓</span>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="issued-count">-</h3>
                                        <p class="text-muted mb-0">ออกแล้ว</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-soft-danger rounded">
                                        <span class="avatar-title text-danger fs-32">✕</span>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="cancelled-count">-</h3>
                                        <p class="text-muted mb-0">ยกเลิก</p>
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
                                        <span class="avatar-title text-white fs-32">฿</span>
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">รายการใบเสร็จรับเงิน</h4>
                        <div>
                            <a href="receipts-print-address.php" class="btn btn-outline-primary me-2">
                                <iconify-icon icon="solar:printer-bold-duotone"
                                    class="align-middle me-1"></iconify-icon>
                                พิมพ์ที่อยู่จัดส่ง
                            </a>
                            <button class="btn btn-outline-success" onclick="exportExcel()">
                                <iconify-icon icon="solar:file-download-bold-duotone"
                                    class="align-middle me-1"></iconify-icon>
                                ส่งออก Excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-3 g-2">
                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        ค้นหา
                                    </span>
                                    <input type="text" id="searchInput" class="form-control"
                                        placeholder="ค้นหาเลขใบเสร็จ, ชื่อ, เลขบัตร...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select id="statusFilter" class="form-select">
                                    <option value="">ทุกสถานะ</option>
                                    <option value="issued">ออกแล้ว</option>
                                    <option value="cancelled">ยกเลิก</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="projectFilter" class="form-select">
                                    <option value="">ทุกโครงการ</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select id="yearFilter" class="form-select">
                                    <option value="">ทุกปี</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary w-100" onclick="loadReceipts()">
                                    ค้นหา
                                </button>
                            </div>
                        </div>
                        <div class="row mb-3 g-2">
                            <div class="col-md-3">
                                <input type="date" id="dateFrom" class="form-control" placeholder="จากวันที่">
                            </div>
                            <div class="col-md-3">
                                <input type="date" id="dateTo" class="form-control" placeholder="ถึงวันที่">
                            </div>

                            <!-- Table -->
                            <div class="table-responsive">
                                <table class="table table-hover table-nowrap align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th style="width: 130px;">เลขที่ใบเสร็จ</th>
                                            <th>ผู้บริจาค</th>
                                            <th>โครงการ</th>
                                            <th class="text-end" style="width: 120px;">จำนวนเงิน</th>
                                            <th style="width: 120px;">วันที่ออก</th>
                                            <th style="width: 150px;" class="text-center">จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody id="receiptsTable">
                                        <tr>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="spinner-border text-primary"></div>
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

        <!-- Detail Modal -->
        <div class="modal fade" id="detailModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">รายละเอียดใบเสร็จ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="detailContent">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">ปิด</button>
                        <button type="button" class="btn btn-outline-danger" id="cancelReceiptBtn"
                            onclick="cancelReceipt()">
                            ยกเลิกใบเสร็จ
                        </button>
                        <a href="#" id="downloadPdfBtn" class="btn btn-primary" target="_blank">
                            ดาวน์โหลด PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'partials/vendor-scripts.php'; ?>
        <script src="assets/js/api-helper.js"></script>

        <script>
            // Get BASE_PATH from current URL
            const BASE_PATH = (() => {
                const path = window.location.pathname;
                const match = path.match(/^(.*?\/edonation)/);
                return match ? match[1] : '/edonation';
            })();

            let receipts = [];
            let currentPage = 1;
            let currentReceipt = null;
            const perPage = 20;

            document.addEventListener('DOMContentLoaded', function () {
                // Populate year filter
                const yearSelect = document.getElementById('yearFilter');
                const currentYear = new Date().getFullYear() + 543; // BE Year
                for (let y = currentYear; y >= currentYear - 5; y--) {
                    const option = document.createElement('option');
                    option.value = y;
                    option.textContent = y;
                    yearSelect.appendChild(option);
                }

                loadProjects(); // Load projects first
                loadReceipts();

                // Filter handlers
                document.getElementById('searchInput').addEventListener('input', debounce(loadReceipts, 500));
                document.getElementById('statusFilter').addEventListener('change', loadReceipts);
                document.getElementById('yearFilter').addEventListener('change', loadReceipts);
                document.getElementById('projectFilter').addEventListener('change', loadReceipts); // Add listener
            });

            async function loadProjects() {
                try {
                    const response = await apiGet('/projects');
                    const projects = response.data || [];
                    const select = document.getElementById('projectFilter');

                    projects.forEach(p => {
                        const option = document.createElement('option');
                        option.value = p.project_number; // Use project_number as value
                        // If project has no number, fallback to ID? Usually project_number is key.
                        option.textContent = truncateText(p.project_name, 40);
                        select.appendChild(option);
                    });
                } catch (error) {
                    console.error('Failed to load projects:', error);
                }
            }

            function truncateText(text, length) {
                if (!text) return '';
                if (text.length <= length) return text;
                return text.substring(0, length) + '...';
            }

            async function loadReceipts() {
                showTableLoading('receiptsTable', 7);

                try {
                    const params = new URLSearchParams();
                    params.append('page', currentPage);
                    params.append('limit', perPage);

                    const search = document.getElementById('searchInput').value;
                    if (search) params.append('search', search);

                    const status = document.getElementById('statusFilter').value;
                    if (status) params.append('status', status);

                    const year = document.getElementById('yearFilter').value;
                    if (year) params.append('year', year);

                    const project = document.getElementById('projectFilter').value; // Add project param
                    if (project) params.append('project', project);

                    const dateFrom = document.getElementById('dateFrom').value;
                    if (dateFrom) params.append('from', dateFrom);

                    const dateTo = document.getElementById('dateTo').value;
                    if (dateTo) params.append('to', dateTo);

                    const response = await apiGet('/receipts?' + params.toString());
                    receipts = response.data || [];

                    // Update stats
                    const stats = response.meta || {};
                    document.getElementById('total-receipts').textContent = formatNumber(stats.total || receipts.length);
                    document.getElementById('issued-count').textContent = formatNumber(stats.issued || receipts.filter(r => r.status !== 'cancelled').length);
                    document.getElementById('cancelled-count').textContent = formatNumber(stats.cancelled || receipts.filter(r => r.status === 'cancelled').length);

                    // Calculate total amount
                    const totalAmount = receipts.reduce((sum, r) => sum + parseFloat(r.amount || 0), 0);
                    document.getElementById('total-amount').textContent = formatNumber(stats.total_amount || totalAmount);

                    renderTable(receipts);

                    // Update pagination info
                    const total = stats.total || receipts.length;
                    const from = (currentPage - 1) * perPage + 1;
                    const to = Math.min(currentPage * perPage, total);
                    document.getElementById('pagination-info').textContent = `แสดง ${from}-${to} จาก ${total} รายการ`;

                } catch (error) {
                    showTableError('receiptsTable', 7, error.message);
                    showError(error.message);
                }
            }

            function renderTable(data) {
                const tbody = document.getElementById('receiptsTable');

                if (!data || data.length === 0) {
                    showTableEmpty('receiptsTable', 7, 'ไม่พบใบเสร็จ');
                    return;
                }

                tbody.innerHTML = data.map((item, index) => `
        <tr>
            <td>${(currentPage - 1) * perPage + index + 1}</td>
            <td>
                <span class="badge bg-light text-dark font-monospace">${escapeHtml(item.receipt_no || item.id)}</span>
            </td>
            <td style="max-width: 200px;">
                <div class="fw-medium text-truncate" title="${escapeHtml(item.payer_name || 'ไม่ระบุชื่อ')}">
                    ${escapeHtml(item.payer_name || 'ไม่ระบุชื่อ')}
                </div>
            </td>
            <td style="max-width: 180px;">
                <div class="text-truncate" title="${escapeHtml(item.project_name || '-')}">
                    ${escapeHtml(item.project_name || '-')}
                </div>
            </td>
            <td class="text-end fw-semibold text-primary">${formatCurrency(item.amount || 0)}</td>
            <td>${formatThaiDateShort(item.issued_at || item.created_at)}</td>
            <td class="text-center">
                 <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle font-monospace" type="button" data-bs-toggle="dropdown">
                        ...
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="viewDetail('${item.id}')">
                                ดูรายละเอียด
                            </a>
                        </li>
                        ${item.status !== 'cancelled' ? `
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="editReceipt('${item.id}', '${item.donation_id}')">
                                แก้ไข
                            </a>
                        </li>
                        ` : ''}
                        <li>
                            <a class="dropdown-item" href="${BASE_PATH}/receipts/pdf_maker.php?id=${item.id}&admin=1" target="_blank">
                                ดาวน์โหลด PDF
                            </a>
                        </li>
                        ${item.status !== 'cancelled' ? `
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-warning" href="javascript:void(0)" onclick="cancelReceiptItem('${item.id}', '${item.receipt_no}')">
                                ยกเลิก
                            </a>
                        </li>
                        ` : ''}
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteReceipt('${item.donation_id}', '${item.receipt_no}')">
                                ลบ
                            </a>
                        </li>
                    </ul>
                </div>
            </td>
        </tr>
    `).join('');
            }

            function getReceiptStatusBadge(status) {
                const badges = {
                    'issued': '<span class="badge badge-soft-success">ออกแล้ว</span>',
                    'pending': '<span class="badge badge-soft-warning">รอออก</span>',
                    'cancelled': '<span class="badge badge-soft-danger">ยกเลิก</span>'
                };
                return badges[status] || badges['issued'];
            }

            async function viewDetail(id) {
                const modal = new bootstrap.Modal(document.getElementById('detailModal'));
                modal.show();

                const content = document.getElementById('detailContent');
                content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';

                try {
                    const response = await apiGet('/receipts/' + id);
                    const r = response.data;
                    currentReceipt = r;

                    content.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted mb-3">ข้อมูลใบเสร็จ</h6>
                    <table class="table table-sm">
                        <tr><td class="text-muted" width="130">เลขที่ใบเสร็จ</td><td class="fw-medium font-monospace">${escapeHtml(r.receipt_number)}</td></tr>
                        <tr><td class="text-muted">วันที่ออก</td><td>${formatThaiDate(r.receipt_date || r.created_at)}</td></tr>
                        <tr><td class="text-muted">จำนวนเงิน</td><td class="fw-semibold text-primary fs-18">${formatCurrency(r.amount)}</td></tr>
                        <tr><td class="text-muted">โครงการ</td><td>${escapeHtml(r.project_name || r.project_number)}</td></tr>
                        <tr><td class="text-muted">สถานะ</td><td>${getReceiptStatusBadge(r.status)}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted mb-3">ข้อมูลผู้บริจาค</h6>
                    <table class="table table-sm">
                        <tr><td class="text-muted" width="130">ชื่อ</td><td class="fw-medium">${escapeHtml(r.donor_name || r.name)}</td></tr>
                        <tr><td class="text-muted">เลขบัตรประชาชน</td><td>${formatIdCard(r.id_card)}</td></tr>
                        <tr><td class="text-muted">อีเมล</td><td>${r.email || '-'}</td></tr>
                        <tr><td class="text-muted">โทรศัพท์</td><td>${formatPhone(r.phone)}</td></tr>
                        <tr><td class="text-muted">ที่อยู่</td><td>${escapeHtml(r.address) || '-'}</td></tr>
                    </table>
                </div>
            </div>
            ${r.note ? `
            <div class="mt-3">
                <h6 class="text-muted">หมายเหตุ</h6>
                <p class="mb-0">${escapeHtml(r.note)}</p>
            </div>
            ` : ''}
        `;

                    // Update buttons in modal (keep them for detail view)
                    document.getElementById('downloadPdfBtn').href = BASE_PATH + '/receipts/pdf_maker.php?id=' + r.id + '&admin=1';
                    document.getElementById('cancelReceiptBtn').style.display = r.status === 'cancelled' ? 'none' : '';
                    // re-bind cancel button in modal to use cancelReceiptItem if needed, or keep cancelReceipt() which uses currentReceipt

                } catch (error) {
                    content.innerHTML = `<div class="alert alert-danger">${escapeHtml(error.message)}</div>`;
                }
            }

            // Wrapper for modal button
            function cancelReceipt() {
                if (currentReceipt) cancelReceiptItem(currentReceipt.id, currentReceipt.receipt_number);
            }

            async function cancelReceiptItem(id, receiptNo) {
                const result = await confirmAction('ยกเลิกใบเสร็จ?', `คุณต้องการยกเลิกใบเสร็จ ${receiptNo} ใช่หรือไม่?`, 'ยกเลิกใบเสร็จ');
                if (!result.isConfirmed) return;

                try {
                    await apiPost('/receipts/' + id + '/cancel');
                    showSuccess('ยกเลิกใบเสร็จสำเร็จ');
                    // If modal open, close it
                    const modalEl = document.getElementById('detailModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    loadReceipts();
                } catch (error) {
                    showError(error.message);
                }
            }

            async function deleteReceipt(donationId, receiptNo) {
                const result = await confirmAction('ลบข้อมูล?', `คุณต้องการลบใบเสร็จ ${receiptNo} และข้อมูลการบริจาคนี้ออกจากระบบหรือไม่?\n(ไม่สามารถกู้คืนได้)`, 'ลบข้อมูล', 'warning');
                if (!result.isConfirmed) return;

                try {
                    await apiDelete('/donations/' + donationId);
                    showSuccess('ลบข้อมูลสำเร็จ');
                    loadReceipts();
                } catch (error) {
                    showError(error.message);
                }
            }

            function editReceipt(id, donationId) {
                // Placeholder for Edit
                Swal.fire({
                    icon: 'info',
                    title: 'แก้ไขข้อมูล',
                    text: 'ฟีเจอร์แก้ไขข้อมูลใบเสร็จยังไม่เปิดใช้งาน',
                    confirmButtonText: 'ตกลง'
                });
            }

            async function resendReceipt(id) {
                const result = await confirmAction('ส่งใบเสร็จทางอีเมล?', 'ระบบจะส่งใบเสร็จไปยังอีเมลของผู้บริจาคอีกครั้ง', 'ส่งอีเมล');
                if (!result.isConfirmed) return;

                try {
                    await apiPost('/receipts/' + id + '/resend');
                    showSuccess('ส่งใบเสร็จทางอีเมลสำเร็จ');
                } catch (error) {
                    showError(error.message);
                }
            }

            function exportExcel() {
                // Build query string for export
                const params = new URLSearchParams();
                params.append('export', 'excel');

                const status = document.getElementById('statusFilter').value;
                if (status) params.append('status', status);

                const year = document.getElementById('yearFilter').value;
                if (year) params.append('year', year);

                const dateFrom = document.getElementById('dateFrom').value;
                if (dateFrom) params.append('from', dateFrom);

                const dateTo = document.getElementById('dateTo').value;
                if (dateTo) params.append('to', dateTo);

                window.open('../api/v1/receipts/export?' + params.toString(), '_blank');
            }

            function goToPage(page) {
                currentPage = page;
                loadReceipts();
            }
        </script>

</body>

</html>