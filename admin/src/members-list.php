<?php include 'partials/main.php'; ?>
<?php requireAuth(); ?>

<!doctype html>
<html lang="th">

<head>
    <?php
    $title = "รายชื่อสมาชิก";
    include 'partials/title-meta.php'; ?>

    <?php include 'partials/head-css.php'; ?>
    <style>
        .member-row td { vertical-align: middle; }
        .member-row:hover { background: rgba(28,132,238,.04) !important; cursor: pointer; }
        .member-name-link { font-weight: 600; color: var(--bs-headings-color); text-decoration: none; }
        .member-name-link:hover { color: var(--bs-primary); }
        .spin { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include 'partials/edonation-nav.php'; ?>

        <div class="page-content">
            <?php include 'partials/edonation-topbar.php'; ?>

            <div class="container-xxl">
                <?php
                $pageTitle = "รายชื่อสมาชิก";
                $subTitle = "ผู้บริจาค";
                include 'partials/page-title.php'; ?>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-soft-primary rounded">
                                        <span class="avatar-title text-primary fs-28">
                                            <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
                                        </span>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="stat-total-members">-</h3>
                                        <p class="text-muted mb-0">สมาชิกทั้งหมด</p>
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
                                        <span class="avatar-title text-warning fs-28">
                                            <iconify-icon icon="solar:user-plus-bold-duotone"></iconify-icon>
                                        </span>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="stat-new-this-year">-</h3>
                                        <p class="text-muted mb-0">สมาชิกใหม่ปีนี้</p>
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
                                        <span class="avatar-title text-success fs-28">
                                            <iconify-icon icon="solar:hand-heart-bold-duotone"></iconify-icon>
                                        </span>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="stat-total-donations">-</h3>
                                        <p class="text-muted mb-0">ครั้งบริจาครวม</p>
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
                                        <span class="avatar-title text-white fs-28">
                                            <iconify-icon icon="solar:wallet-bold-duotone"></iconify-icon>
                                        </span>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0 text-white" id="stat-total-amount">-</h3>
                                        <p class="mb-0 opacity-75">ยอดบริจาครวม (บาท)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Card -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">รายชื่อสมาชิก</h4>
                        <div class="d-flex gap-2">
                            <a href="receipts-generate.php" class="btn btn-success">
                                <iconify-icon icon="solar:receipt-bold-duotone" class="align-middle me-1"></iconify-icon>
                                ออกใบเสร็จ
                            </a>
                            <button class="btn btn-outline-secondary" onclick="syncMembers()" id="btnSync" title="Sync ข้อมูลสมาชิก">
                                <iconify-icon icon="iconamoon:synchronize-duotone" id="syncIcon" class="align-middle me-1"></iconify-icon>
                                <span class="d-none d-sm-inline">Sync</span>
                            </button>
                            <button class="btn btn-outline-secondary" onclick="exportSelected()" id="btnExport" title="Export">
                                <iconify-icon icon="solar:file-download-bold-duotone" class="align-middle me-1"></iconify-icon>
                                <span class="d-none d-sm-inline">Export</span>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">

                        <!-- Hidden Export Form -->
                        <form id="exportForm" action="export_members.php" method="POST" target="_blank" style="display:none;">
                            <input type="hidden" name="ids" id="exportIds">
                        </form>

                        <!-- Filters -->
                        <div class="bg-light p-3 rounded mb-4">
                            <div class="row g-3">
                                <div class="col-lg-5 col-md-6">
                                    <label class="form-label fw-bold">ค้นหา</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0">
                                            <iconify-icon icon="solar:magnifer-linear" class="fs-18"></iconify-icon>
                                        </span>
                                        <input type="text" id="searchInput" class="form-control border-start-0 ps-0"
                                            placeholder="ค้นหาชื่อ หรือรหัสสมาชิก..."
                                            onkeyup="handleSearch(event)">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3">
                                    <label class="form-label fw-bold">ปีที่บริจาค</label>
                                    <select id="filterYear" class="form-select" onchange="loadMembers()">
                                        <option value="">ทุกปี</option>
                                        <?php
                                        $currentYear = date('Y');
                                        for ($i = 0; $i < 10; $i++) {
                                            $y = $currentYear - $i;
                                            echo "<option value='$y'>พ.ศ. " . ($y + 543) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-3">
                                    <label class="form-label fw-bold">แสดงรายการ</label>
                                    <select id="limitSelector" class="form-select" onchange="changeLimit()">
                                        <option value="25">25 รายการ</option>
                                        <option value="50">50 รายการ</option>
                                        <option value="100">100 รายการ</option>
                                        <option value="250">250 รายการ</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-12 d-flex align-items-end">
                                    <button class="btn btn-primary w-100 d-flex align-items-center justify-content-center" onclick="loadMembers(1)">
                                        <iconify-icon icon="solar:filter-bold-duotone" class="me-2 fs-18"></iconify-icon>
                                        ค้นหา
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Result info + Select All -->
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-muted small" id="result-count">กำลังโหลด...</span>
                            <label class="d-flex align-items-center gap-2 text-muted small mb-0">
                                <input class="form-check-input mt-0" type="checkbox" id="selectAll" onchange="toggleAll(this)">
                                เลือกทั้งหมด
                            </label>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-nowrap align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width:40px;" class="text-center"></th>
                                        <th style="width:50px;">#</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>รหัสสมาชิก</th>
                                        <th>เลขบัตรประชาชน</th>
                                    </tr>
                                </thead>
                                <tbody id="membersTable">
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="spinner-border text-primary"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted small" id="pagination-info"></div>
                            <nav>
                                <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
                            </nav>
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
        let members = [];
        let currentPage = 1;
        let totalPages = 1;
        let perPage = 25;

        document.addEventListener('DOMContentLoaded', function () {
            loadDashboardStats();
            loadMembers();
        });

        async function loadDashboardStats() {
            try {
                const res = await apiGet('/members/stats');
                const d = res.data || {};
                document.getElementById('stat-total-members').textContent   = formatNumber(d.total_members   ?? 0);
                document.getElementById('stat-new-this-year').textContent   = formatNumber(d.new_this_year   ?? 0);
                document.getElementById('stat-total-donations').textContent = formatNumber(d.total_donations ?? 0);
                document.getElementById('stat-total-amount').textContent    = formatCurrency(d.total_amount  ?? 0);
            } catch (e) {
                // silent — stats are non-critical
            }
        }

        function handleSearch(e) {
            if (e.key === 'Enter') {
                loadMembers(1);
            }
        }

        async function loadMembers(page = 1) {
            currentPage = page;
            const tbody = document.getElementById('membersTable');
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5"><div class="spinner-border text-primary me-2"></div><span class="text-muted">กำลังโหลดข้อมูล...</span></td></tr>';
            document.getElementById('selectAll').checked = false;

            try {
                const query = document.getElementById('searchInput').value.trim();
                const year  = document.getElementById('filterYear').value;

                let url = query
                    ? `/members/search?q=${encodeURIComponent(query)}&limit=${perPage}`
                    : `/members?page=${page}&limit=${perPage}`;

                if (year) url += `&year=${year}`;

                const response = await apiGet(url);
                members = response.data || [];
                const meta = response.meta || {};

                document.getElementById('result-count').textContent = (meta.total ?? members.length) + ' รายการ';

                renderTable(members);
                renderPagination(meta);
            } catch (error) {
                showError(error.message);
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-danger">${error.message}</td></tr>`;
            }
        }

        function renderTable(data) {
            const tbody = document.getElementById('membersTable');

            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted">ไม่พบข้อมูล</td></tr>';
                return;
            }

            const sorted = [...data].sort((a, b) => (a.first_name || '').localeCompare(b.first_name || '', 'th'));

            tbody.innerHTML = sorted.map((item, index) => {
                const fullName = item.name || 'ไม่ระบุชื่อ';
                const startIdx = (currentPage - 1) * perPage + index + 1;
                const detailUrl = `member-detail.php?id=${encodeURIComponent(item.id_members)}`;

                return `
                <tr class="member-row" onclick="window.location='${detailUrl}'">
                    <td class="text-center ps-3" onclick="event.stopPropagation()">
                        <input class="form-check-input member-checkbox" type="checkbox" value="${item.id_members}">
                    </td>
                    <td class="text-center text-muted small">${startIdx}</td>
                    <td>
                        <a href="${detailUrl}" class="member-name-link" onclick="event.stopPropagation()">
                            ${escapeHtml(truncateText(fullName, 40))}
                        </a>
                        <div class="small text-muted">${displayPhone(item.phone)}</div>
                    </td>
                    <td class="font-monospace small text-muted">${escapeHtml(item.id_members)}</td>
                    <td class="font-monospace small text-muted">${formatIdCard(item.id_card)}</td>
                </tr>
            `}).join('');

            document.getElementById('selectAll').checked = false;
        }

        function changeLimit() {
            perPage = parseInt(document.getElementById('limitSelector').value);
            loadMembers(1);
        }

        function renderPagination(meta) {
            const pagination = document.getElementById('pagination');
            const info = document.getElementById('pagination-info');

            if (!meta || meta.total_pages <= 1) {
                pagination.innerHTML = '';
                info.textContent = `แสดงทั้งหมด ${meta?.total || members.length} รายการ`;
                return;
            }

            const total = meta.total || 0;
            const totalPages = meta.total_pages || 1;
            const start = (currentPage - 1) * perPage + 1;
            const end = Math.min(currentPage * perPage, total);
            info.textContent = `แสดง ${total > 0 ? start : 0}-${end} จาก ${total} รายการ`;

            let html = '<ul class="pagination pagination-sm mb-0">';

            // First & Previous
            html += `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="loadMembers(1)" title="หน้าแรก">«</a>
                </li>
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="loadMembers(${currentPage - 1})" title="ก่อนหน้า">‹</a>
                </li>
            `;

            // Calculate range
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

            for (let i = startPage; i <= endPage; i++) {
                html += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="javascript:void(0)" onclick="loadMembers(${i})">${i}</a>
                    </li>
                `;
            }

            // Next & Last
            html += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="loadMembers(${currentPage + 1})" title="ถัดไป">›</a>
                </li>
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="loadMembers(${totalPages})" title="หน้าสุดท้าย">»</a>
                </li>
            `;

            html += '</ul>';
            pagination.innerHTML = html;
        }

        function toggleAll(source) {
            document.querySelectorAll('.member-checkbox').forEach(cb => cb.checked = source.checked);
        }

        function exportSelected() {
            const checkboxes = document.querySelectorAll('.member-checkbox:checked');
            if (checkboxes.length === 0) {
                showWarning('กรุณาเลือกรายชื่อสมาชิกที่ต้องการ Export อย่างน้อย 1 รายการ');
                return;
            }

            const ids = Array.from(checkboxes).map(cb => cb.value);
            document.getElementById('exportIds').value = ids.join(',');
            document.getElementById('exportForm').submit();
        }

        async function syncMembers() {
            const btn = document.getElementById('btnSync');
            const icon = document.getElementById('syncIcon');

            // Confirm before sync
            const syncConfirm = await confirmAction({
                title: 'Sync ข้อมูลสมาชิก?',
                text: 'ระบบจะอัปเดตข้อมูลสมาชิกทั้งหมดจากใบเสร็จ',
                icon: 'question',
                confirmText: 'Sync เลย',
                cancelText: 'ยกเลิก'
            });
            if (!syncConfirm.isConfirmed) return;

            // Disable button and show loading
            btn.disabled = true;
            icon.setAttribute('icon', 'iconamoon:loading');
            icon.classList.add('spin');

            try {
                const response = await apiPost('/members/sync');
                const data = response.data || {};

                showSuccess(`Sync สำเร็จ!\n` +
                    `• สมาชิกใหม่: ${data.new_members || 0} คน\n` +
                    `• อัปเดต: ${data.updated_members || 0} คน\n` +
                    `• สมาชิกทั้งหมด: ${data.total_members || 0} คน`);

                // Reload member list
                loadMembers();

            } catch (error) {
                showError(error.message || 'เกิดข้อผิดพลาดในการ Sync');
            } finally {
                // Re-enable button
                btn.disabled = false;
                icon.setAttribute('icon', 'iconamoon:synchronize-duotone');
                icon.classList.remove('spin');
            }
        }

        function getInitials(name) {
            if (!name) return '?';
            const parts = name.trim().split(' ');
            if (parts.length > 1) {
                // First char of first name + first char of last name
                return (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase();
            }
            return name.charAt(0).toUpperCase();
        }

        function maskIdCard(id) {
            if (!id || id.length < 10) return id;
            return id.substring(0, 3) + 'xxxx' + id.substring(id.length - 4);
        }

        function formatIdCard(id) { return maskIdCard(id); } // Alias

        function displayPhone(phone) {
            if (!phone) return '-';
            return phone.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
        }

        function truncateText(text, length) {
            if (!text) return '';
            if (text.length <= length) return text;
            return text.substring(0, length) + '...';
        }

    </script>
</body>

</html>