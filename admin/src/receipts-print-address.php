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

            <div class="container-fluid">

                <!-- Page Title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-flex align-items-center justify-content-between">
                            <h4 class="mb-0 font-size-18">พิมพ์ที่อยู่จัดส่งใบเสร็จ</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="receipts-list.php">ใบเสร็จ</a></li>
                                    <li class="breadcrumb-item active">พิมพ์ที่อยู่</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">รายการใบเสร็จรับเงิน</h4>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-soft-secondary btn-sm" onclick="window.history.back()">
                                        <iconify-icon icon="solar:arrow-left-bold-duotone"
                                            class="fs-16 align-middle me-1"></iconify-icon>
                                        ย้อนกลับ
                                    </button>
                                    <button class="btn btn-primary btn-sm" onclick="printSelected()">
                                        <iconify-icon icon="solar:printer-bold-duotone"
                                            class="fs-16 align-middle me-1"></iconify-icon>
                                        พิมพ์รายการที่เลือก
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Filters -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label text-muted">ค้นหา</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <iconify-icon icon="solar:magnifer-linear" class="fs-18"></iconify-icon>
                                            </span>
                                            <input type="text" class="form-control border-start-0 ps-0" id="searchInput"
                                                placeholder="เลขที่ใบเสร็จ, ชื่อผู้บริจาค...">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label text-muted">โครงการ</label>
                                        <input type="text" class="form-control" id="projectFilter"
                                            placeholder="รหัสโครงการ">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label text-muted">วันที่ออกใบเสร็จ</label>
                                        <input type="date" class="form-control" id="dateFilter">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button class="btn btn-primary w-100" onclick="loadData()">
                                            <iconify-icon icon="solar:filter-bold-duotone"
                                                class="fs-16 align-middle me-1"></iconify-icon>
                                            กรองข้อมูล
                                        </button>
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
                    </div>
                </div>

            </div> <!-- container-fluid -->

            <?php include 'partials/footer.php'; ?>
        </div>
        <!-- End Page-content -->
    </div>
    <!-- end wrapper -->

    <!-- Hidden Print Area -->
    <div id="printArea" class="address-label-container"></div>

    <?php include 'partials/vendor-scripts.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/api-helper.js"></script>

    <script>
        let currentPage = 1;
        const perPage = 50;

        document.addEventListener('DOMContentLoaded', () => {
            loadData();

            // Search with debounce
            document.getElementById('searchInput').addEventListener('input', debounce(loadData, 500));
            document.getElementById('projectFilter').addEventListener('change', loadData);
            document.getElementById('dateFilter').addEventListener('change', loadData);
        });

        async function loadData(page = 1) {
            currentPage = Number(page);
            const search = document.getElementById('searchInput').value;
            const project = document.getElementById('projectFilter').value;
            const date = document.getElementById('dateFilter').value;

            // Show Loading
            showTableLoading('tableBody', 5);

            const params = {
                page: currentPage,
                limit: perPage,
                search: search,
                project: project,
                from: date || ''
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
                showTableError('tableBody', 5, error.message);
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
                showTableEmpty('tableBody', 5);
                return;
            }

            data.forEach(item => {
                const hasAddress = item.full_address && item.full_address.length > 5;
                const addressHtml = hasAddress ?
                    `<div class="text-wrap">${escapeHtml(item.full_address)}</div>` :
                    `<span class="badge badge-soft-warning"><iconify-icon icon="solar:danger-circle-bold-duotone" class="align-middle me-1"></iconify-icon> ไม่มีที่อยู่</span>`;

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
                    <td><small class="text-muted">${formatThaiDateShort(item.receipt_date)}</small></td>
                    <td><span class="fw-medium text-primary">${escapeHtml(item.receipt_no)}</span></td>
                    <td><div class="fw-medium">${escapeHtml(item.payer_name)}</div></td>
                    <td>${addressHtml}</td>
                `;
                tbody.appendChild(tr);
            });

            const selAll = document.getElementById('selectAll');
            if (selAll) selAll.checked = false;
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