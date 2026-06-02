<?php include 'partials/main.php'; ?>

<?php requireAuth(); ?>

<!doctype html>
<html lang="th">

<head>
    <?php
    $pageTitle = "พิมพ์ที่อยู่จัดส่งใบเสร็จ";
    include 'partials/title-meta.php';
    ?>
    <?php include 'partials/head-css.php'; ?>

    <!-- Print Styles -->
    <style>
        @media print {
            body>*:not(#printArea) {
                display: none !important;
            }

            body {
                background: white !important;
                height: auto !important;
                overflow: visible !important;
            }

            #printArea,
            #printArea * {
                display: block !important;
                visibility: visible !important;
            }

            #printArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background: white;
                z-index: 99999;
                padding: 0;
                margin: 0;
            }

            .address-label {
                border: 1px dotted #ccc;
                padding: 15px;
                margin-bottom: 25px;
                page-break-inside: avoid;
                font-size: 16pt;
                color: black;
                font-family: 'Sarabun', sans-serif;
                line-height: 1.6;
            }

            #print-grid {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
                gap: 30px;
                padding: 20px;
            }
        }

        .address-label-container {
            display: none;
        }
    </style>
</head>

<body>
    <!-- START Wrapper -->
    <div class="wrapper">
        <?php include 'partials/edonation-nav.php'; ?>

        <div class="page-content">
            <?php include 'partials/edonation-topbar.php'; ?>

            <div class="container-xxl">

                <?php
                $pageTitle = "ใบเสร็จรับเงิน";
                $subTitle  = "พิมพ์ที่อยู่จัดส่ง";
                include 'partials/page-title.php'; ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">รายการใบเสร็จรับเงิน</h4>
                        <div class="d-flex gap-2">
                            <a href="receipts-list.php" class="btn btn-outline-secondary">
                                <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1"></iconify-icon>
                                กลับรายการใบเสร็จ
                            </a>
                            <button class="btn btn-primary" onclick="printSelected()">
                                <iconify-icon icon="solar:printer-bold-duotone" class="align-middle me-1"></iconify-icon>
                                พิมพ์รายการที่เลือก
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="bg-light p-3 rounded mb-4">
                            <div class="row g-3">
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label fw-bold">ค้นหา</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0">
                                            <iconify-icon icon="solar:magnifer-linear" class="fs-18"></iconify-icon>
                                        </span>
                                        <input type="text" class="form-control border-start-0 ps-0" id="searchInput"
                                            placeholder="เลขที่ใบเสร็จ, ชื่อผู้บริจาค...">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label fw-bold">โครงการ</label>
                                    <input type="text" class="form-control" id="projectFilter"
                                        placeholder="รหัสโครงการ">
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label fw-bold">สถานะการส่ง</label>
                                    <select class="form-select" id="shippingStatusFilter">
                                        <option value="">ทั้งหมด</option>
                                        <option value="pending">รอการจัดส่ง</option>
                                        <option value="shipped">จัดส่งแล้ว</option>
                                        <option value="delivered">ถึงผู้รับแล้ว</option>
                                        <option value="returned">พัสดุตีกลับ</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-6 d-flex align-items-end">
                                    <button class="btn btn-primary w-100 d-flex align-items-center justify-content-center" onclick="loadData()">
                                        <iconify-icon icon="solar:filter-bold-duotone" class="me-2 fs-18"></iconify-icon>
                                        กรองข้อมูล
                                    </button>
                                </div>
                            </div>
                        </div>

                                <!-- Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover table-nowrap align-middle">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="width: 50px;" class="text-center">
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" id="selectAll"
                                                            onclick="toggleAll(this)">
                                                        <label class="form-check-label" for="selectAll"></label>
                                                    </div>
                                                </th>
                                                <th style="width: 120px;">วันที่</th>
                                                <th style="width: 140px;">เลขที่ใบเสร็จ</th>
                                                <th style="width: 250px;">ผู้บริจาค</th>
                                                <th>ที่อยู่จัดส่ง</th>
                                                <th style="width: 150px;">การจัดส่ง</th>
                                                <th style="width: 80px;" class="text-center">จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tableBody">
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted">
                                                    <div class="spinner-border text-primary mb-2" role="status"></div>
                                                    <div>กำลังโหลดข้อมูล...</div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="text-muted small" id="pageInfo">แสดง 0 รายการ</div>
                                    <nav id="pagination"></nav>
                                </div>

                    </div>
                </div>

            </div> <!-- container-xxl -->

            <?php include 'partials/footer.php'; ?>
        </div>
        <!-- End Page-content -->
    </div>
    <!-- end wrapper -->

    <!-- Hidden Print Area -->
    <div id="printArea" class="address-label-container"></div>

    <!-- Shipping Update Modal -->
    <div class="modal fade" id="shippingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">อัปเดตข้อมูลการจัดส่ง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="shippingForm" onsubmit="saveShipping(event)">
                    <div class="modal-body">
                        <input type="hidden" id="ship_receipt_id">
                        <div class="mb-3">
                            <label class="form-label">เลขที่ใบเสร็จ</label>
                            <input type="text" class="form-control bg-light" id="ship_receipt_no" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ผู้รับ</label>
                            <input type="text" class="form-control bg-light" id="ship_payer_name" readonly>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">วิธีจัดส่ง</label>
                                <select class="form-select" id="ship_method" required>
                                    <option value="ThaiPost">ไปรษณีย์ไทย (ลงทะเบียน)</option>
                                    <option value="EMS">ไปรษณีย์ไทย (EMS)</option>
                                    <option value="Flash">Flash Express</option>
                                    <option value="Kerry">Kerry Express</option>
                                    <option value="J&T">J&T Express</option>
                                    <option value="Other">อื่นๆ</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">สถานะ</label>
                                <select class="form-select" id="ship_status" required>
                                    <option value="shipped">จัดส่งแล้ว</option>
                                    <option value="delivered">ถึงผู้รับแล้ว</option>
                                    <option value="returned">พัสดุตีกลับ</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">เลขพัสดุ (Tracking Number)</label>
                            <input type="text" class="form-control" id="ship_tracking" placeholder="เช่น EF123456789TH">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">หมายเหตุ</label>
                            <textarea class="form-control" id="ship_notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveShipping">
                            บันทึกข้อมูล
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'partials/vendor-scripts.php'; ?>
    <script src="assets/js/api-helper.js"></script>

    <script>
        let currentPage = 1;
        const perPage = 50;

        document.addEventListener('DOMContentLoaded', () => {
            loadData();

            // Search with debounce
            document.getElementById('searchInput').addEventListener('input', debounce(loadData, 500));
            document.getElementById('shippingStatusFilter').addEventListener('change', loadData);
        });

        async function loadData(page = 1) {
            currentPage = Number(page);
            const search = document.getElementById('searchInput').value;
            const project = document.getElementById('projectFilter').value;
            const shippingStatus = document.getElementById('shippingStatusFilter').value;

            // Show Loading
            showTableLoading('tableBody', 7);

            const params = {
                page: currentPage,
                limit: perPage,
                search: search,
                project: project,
                shipping_status: shippingStatus
            };

            try {
                const result = await apiGet('/receipts', params);
                const data = result.data || [];
                const meta = result.meta || result.pagination || {};

                renderTable(data);

                // Render Pagination using api-helper
                // Note: renderPagination in helper expects string function name for onclick
                renderPaginationHelper('pagination', meta.page, meta.total_pages, 'loadData');

                // Update Info Text
                const info = document.getElementById('pageInfo');
                if (info && meta.total > 0) {
                    info.textContent = `แสดง ${data.length} รายการ จากทั้งหมด ${meta.total} รายการ (หน้า ${meta.page}/${meta.total_pages})`;
                } else {
                    info.textContent = 'ไม่พบรายการ';
                }

            } catch (error) {
                console.error(error);
                showTableError('tableBody', 7, error.message);
            }
        }

        // Custom Pagination Wrapper to work with api-helper that expects string func name
        window.loadData = loadData; // Expose to global scope for onclick string

        function renderPaginationHelper(containerId, page, totalPages, funcName) {
            // Re-implement simplified version if api-helper's one is strict, 
            // OR use the one from api-helper if it exists.
            if (typeof renderPagination === 'function') {
                renderPagination(containerId, page, totalPages, funcName);
            }
        }

        function renderTable(data) {
            const tbody = document.getElementById('tableBody');
            if (!tbody) return;
            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                showTableEmpty('tableBody', 7);
                return;
            }

            data.forEach(item => {
                const hasAddress = item.full_address && item.full_address.length > 5;
                const addressHtml = hasAddress ?
                    `<div class="text-wrap small">${escapeHtml(item.full_address)}</div>` :
                    `<span class="badge badge-soft-warning"><iconify-icon icon="solar:danger-circle-bold-duotone" class="align-middle me-1"></iconify-icon> ไม่มีที่อยู่</span>`;

                // Shipping info
                let shippingHtml = '';
                if (item.tracking_number) {
                    let statusBadge = 'bg-soft-primary text-primary';
                    if (item.shipping_status === 'delivered') statusBadge = 'bg-soft-success text-success';
                    if (item.shipping_status === 'returned') statusBadge = 'bg-soft-danger text-danger';

                    shippingHtml = `
                        <div class="small fw-bold text-dark">${escapeHtml(item.shipping_method || 'ThaiPost')}</div>
                        <div class="font-monospace small text-primary mb-1">${escapeHtml(item.tracking_number)}</div>
                        <span class="badge ${statusBadge}">${item.shipping_status}</span>
                    `;
                } else {
                    shippingHtml = `<span class="text-muted small">ยังไม่ได้จัดส่ง</span>`;
                }

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-center">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input row-checkbox" 
                                value="${item.id}" 
                                data-name="${escapeHtml(item.payer_name)}"
                                data-address="${escapeHtml(item.full_address || '')}"
                                ${!hasAddress ? 'disabled' : ''}>
                                <label class="form-check-label"></label>
                        </div>
                    </td>
                    <td><small class="text-muted">${formatThaiDateShort(item.issued_at)}</small></td>
                    <td><span class="fw-medium text-primary">${escapeHtml(item.receipt_no)}</span></td>
                    <td><div class="fw-medium">${escapeHtml(item.payer_name)}</div></td>
                    <td>${addressHtml}</td>
                    <td>${shippingHtml}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-icon btn-soft-primary" onclick='openShippingModal(${JSON.stringify(item)})'>
                            <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            const selAll = document.getElementById('selectAll');
            if (selAll) selAll.checked = false;
        }

        const shippingModal = new bootstrap.Modal(document.getElementById('shippingModal'));

        function openShippingModal(item) {
            document.getElementById('ship_receipt_id').value = item.id;
            document.getElementById('ship_receipt_no').value = item.receipt_no;
            document.getElementById('ship_payer_name').value = item.payer_name;
            document.getElementById('ship_method').value = item.shipping_method || 'ThaiPost';
            document.getElementById('ship_status').value = item.shipping_status || 'shipped';
            document.getElementById('ship_tracking').value = item.tracking_number || '';
            document.getElementById('ship_notes').value = item.notes || '';

            shippingModal.show();
        }

        async function saveShipping(event) {
            event.preventDefault();

            const btn = document.getElementById('btnSaveShipping');
            const receiptId = document.getElementById('ship_receipt_id').value;
            const data = {
                shipping_method: document.getElementById('ship_method').value,
                status: document.getElementById('ship_status').value,
                tracking_number: document.getElementById('ship_tracking').value,
                notes: document.getElementById('ship_notes').value
            };

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> กำลังบันทึก...';

            try {
                await apiPost(`/receipts/${receiptId}/shipping`, data);
                showSuccess('บันทึกข้อมูลการจัดส่งสำเร็จ');
                shippingModal.hide();
                loadData(currentPage);
            } catch (error) {
                showError(error.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'บันทึกข้อมูล';
            }
        }

        function toggleAll(source) {
            document.querySelectorAll('.row-checkbox:not(:disabled)').forEach(cb => cb.checked = source.checked);
        }

        function printSelected() {
            const selected = [];
            document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
                selected.push({
                    name: cb.dataset.name,
                    address: cb.dataset.address
                });
            });

            if (selected.length === 0) {
                showWarning('กรุณาเลือกรายการที่มีที่อยู่เพื่อพิมพ์ Label');
                return;
            }

            // Generate Print Content
            const container = document.getElementById('printArea');
            container.innerHTML = '';

            let html = '<div id="print-grid">';

            selected.forEach(item => {
                html += `
                    <div class="address-label">
                        <div style="font-weight: bold; font-size: 1.4rem; margin-bottom: 12px;">กรุณาส่ง: ${item.name}</div>
                        <div style="font-size: 1.2rem; line-height: 1.6;">${item.address}</div>
                    </div>
                `;
            });

            html += '</div>';
            container.innerHTML = html;

            // Wait for DOM
            setTimeout(() => {
                window.print();
            }, 500);
        }
    </script>
</body>

</html>