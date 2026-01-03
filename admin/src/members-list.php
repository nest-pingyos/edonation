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
        .donor-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .donor-new {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .donor-repeat {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .donor-regular {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .donor-loyal {
            background-color: #fce4ec;
            color: #c2185b;
        }

        .stats-card {
            transition: transform 0.2s;
        }

        .stats-card:hover {
            transform: translateY(-2px);
        }
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
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-soft-primary rounded">
                                        <iconify-icon icon="iconamoon:profile-duotone"
                                            class="avatar-title text-primary fs-32"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="total-members">-</h3>
                                        <p class="text-muted mb-0">สมาชิกทั้งหมด</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-soft-warning rounded">
                                        <iconify-icon icon="iconamoon:arrow-up-duotone"
                                            class="avatar-title text-warning fs-32"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="repeat-donors">-</h3>
                                        <p class="text-muted mb-0">ผู้บริจาคซ้ำ</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-soft-success rounded">
                                        <iconify-icon icon="iconamoon:heart-duotone"
                                            class="avatar-title text-success fs-32"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="total-donations">-</h3>
                                        <p class="text-muted mb-0">รายการบริจาค</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-white bg-opacity-25 rounded">
                                        <iconify-icon icon="iconamoon:trend-up-duotone"
                                            class="avatar-title text-white fs-32"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0 text-white" id="total-amount">-</h3>
                                        <p class="mb-0 opacity-75">ยอดบริจาครวม</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Card -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">รายชื่อผู้บริจาค</h4>
                        <div class="d-flex gap-2 align-items-center">
                            <select id="filterType" class="form-select form-select-sm" style="width: auto;"
                                onchange="loadMembers()">
                                <option value="">ทุกประเภท</option>
                                <option value="loyal">ประจำ (10+ ครั้ง)</option>
                                <option value="regular">สม่ำเสมอ (5-9 ครั้ง)</option>
                                <option value="repeat">ซ้ำ (2-4 ครั้ง)</option>
                                <option value="new">ใหม่ (1 ครั้ง)</option>
                            </select>
                            <span class="badge bg-primary" id="result-count">0 รายการ</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Search -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <iconify-icon icon="iconamoon:search-duotone"></iconify-icon>
                                    </span>
                                    <input type="text" id="searchInput" class="form-control"
                                        placeholder="ค้นหาชื่อ, เลขบัตร, รหัสสมาชิก..." onkeyup="handleSearch(event)">
                                    <button class="btn btn-primary" onclick="searchMembers()">
                                        ค้นหา
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <button class="btn btn-outline-secondary" onclick="loadMembers()">
                                    <iconify-icon icon="iconamoon:restart-duotone" class="me-1"></iconify-icon>
                                    รีเฟรช
                                </button>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-nowrap align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>รหัสสมาชิก</th>
                                        <th>เลขบัตรประชาชน</th>
                                        <th class="text-end">ยอดรวม</th>
                                        <th class="text-center" style="width: 120px;">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="membersTable">
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="spinner-border text-primary"></div>
                                            <p class="text-muted mt-2 mb-0">กำลังโหลดข้อมูล...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted" id="pagination-info">แสดง 0-0 จาก 0 รายการ</div>
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

    <!-- Member Detail Modal -->
    <div class="modal fade" id="memberModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <iconify-icon icon="iconamoon:profile-duotone" class="me-2"></iconify-icon>
                        ข้อมูลผู้บริจาค
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="memberModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'partials/vendor-scripts.php'; ?>
    <script src="assets/js/api-helper.js"></script>

    <script>
        let members = [];
        let currentPage = 1;
        let totalPages = 1;
        const limit = 20;

        document.addEventListener('DOMContentLoaded', function () {
            loadMembers();
        });

        function handleSearch(e) {
            if (e.key === 'Enter') {
                searchMembers();
            }
        }

        async function loadMembers(page = 1) {
            currentPage = page;
            const tbody = document.getElementById('membersTable');
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';

            try {
                const response = await apiGet(`/members?page=${page}&limit=${limit}`);
                members = response.data || [];
                const meta = response.meta || {};

                // Update stats
                document.getElementById('total-members').textContent = formatNumber(meta.total || members.length);
                document.getElementById('result-count').textContent = (meta.total || members.length) + ' รายการ';

                // Count repeat donors
                const repeatCount = members.filter(m => m.is_repeat_donor).length;
                document.getElementById('repeat-donors').textContent = formatNumber(repeatCount);


                // Calculate totals from visible data
                let totalDonations = 0;
                let totalAmount = 0;
                members.forEach(m => {
                    totalDonations += m.receipt_count || 0;
                    totalAmount += m.total_amount || 0;
                });
                document.getElementById('total-donations').textContent = formatNumber(totalDonations);
                document.getElementById('total-amount').textContent = formatCurrency(totalAmount);

                totalPages = meta.total_pages || 1;

                renderTable(members);
                renderPagination(meta);
            } catch (error) {
                showError(error.message);
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">${error.message}</td></tr>`;
            }
        }

        async function searchMembers() {
            const query = document.getElementById('searchInput').value.trim();

            if (!query) {
                loadMembers(1);
                return;
            }

            const tbody = document.getElementById('membersTable');
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';

            try {
                const response = await apiGet(`/members/search?q=${encodeURIComponent(query)}&limit=50`);
                members = response.data || [];

                document.getElementById('result-count').textContent = members.length + ' รายการ';
                renderTable(members);

                // Hide pagination for search results
                document.getElementById('pagination').innerHTML = '';
                document.getElementById('pagination-info').textContent = `พบ ${members.length} รายการ`;
            } catch (error) {
                showError(error.message);
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">${error.message}</td></tr>`;
            }
        }

        function renderTable(data) {
            const tbody = document.getElementById('membersTable');
            const filterType = document.getElementById('filterType').value;

            // Filter by donor type if selected
            let filteredData = data;
            if (filterType) {
                filteredData = data.filter(m => m.donor_type === filterType);
            }

            if (!filteredData || filteredData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted"><iconify-icon icon="iconamoon:file-search-duotone" class="fs-48 d-block mb-2"></iconify-icon>ไม่พบข้อมูล</td></tr>';
                return;
            }

            tbody.innerHTML = filteredData.map((item, idx) => {
                const fullName = item.name || 'ไม่ระบุ';
                const startIdx = (currentPage - 1) * limit + idx + 1;

                return `
                <tr>
                    <td>${startIdx}</td>
                    <td>
                        <div class="fw-semibold">${escapeHtml(truncateText(fullName, 30))}</div>
                        ${item.phone ? `<small class="text-muted">${item.phone}</small>` : ''}
                    </td>
                    <td>
                        <code class="bg-light px-2 py-1 rounded">${item.id_members || '-'}</code>
                    </td>
                    <td>
                        <span class="font-monospace">${item.id_card_formatted || maskIdCard(item.id_card) || '-'}</span>
                    </td>
                    <td class="text-end fw-semibold text-success">
                        ${formatCurrency(item.total_amount || 0)}
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-soft-primary" onclick="viewMemberDetail('${item.id_members}')" title="ดูรายละเอียด">
                            <iconify-icon icon="iconamoon:eye-duotone"></iconify-icon>
                        </button>
                    </td>
                </tr>
            `;
            }).join('');
        }

        function renderPagination(meta) {
            const pagination = document.getElementById('pagination');
            const info = document.getElementById('pagination-info');

            if (!meta || meta.total_pages <= 1) {
                pagination.innerHTML = '';
                info.textContent = `แสดง ${meta?.total || 0} รายการ`;
                return;
            }

            const start = (currentPage - 1) * limit + 1;
            const end = Math.min(currentPage * limit, meta.total);
            info.textContent = `แสดง ${start}-${end} จาก ${meta.total} รายการ`;

            let html = '';

            // Previous
            html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadMembers(${currentPage - 1}); return false;">«</a>
            </li>`;

            // Page numbers
            for (let i = 1; i <= Math.min(5, meta.total_pages); i++) {
                html += `<li class="page-item ${currentPage === i ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadMembers(${i}); return false;">${i}</a>
                </li>`;
            }

            if (meta.total_pages > 5) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                html += `<li class="page-item ${currentPage === meta.total_pages ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadMembers(${meta.total_pages}); return false;">${meta.total_pages}</a>
                </li>`;
            }

            // Next
            html += `<li class="page-item ${currentPage === meta.total_pages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadMembers(${currentPage + 1}); return false;">»</a>
            </li>`;

            pagination.innerHTML = html;
        }

        function getDonorTypeBadge(type) {
            const badges = {
                'new': '<span class="badge donor-badge donor-new">ใหม่</span>',
                'repeat': '<span class="badge donor-badge donor-repeat">ซ้ำ</span>',
                'regular': '<span class="badge donor-badge donor-regular">สม่ำเสมอ</span>',
                'loyal': '<span class="badge donor-badge donor-loyal"><iconify-icon icon="iconamoon:star-duotone" class="me-1"></iconify-icon>ประจำ</span>'
            };
            return badges[type] || badges['new'];
        }

        async function viewMemberDetail(idMembers) {
            const modal = new bootstrap.Modal(document.getElementById('memberModal'));
            const body = document.getElementById('memberModalBody');
            body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
            modal.show();

            try {
                const response = await apiGet(`/members/${encodeURIComponent(idMembers)}`);
                const data = response.data || {};

                body.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">ข้อมูลส่วนตัว</h6>
                            <table class="table table-sm">
                                <tr><th width="120">รหัสสมาชิก:</th><td><code>${data.id_members || '-'}</code></td></tr>
                                <tr><th>ชื่อ-นามสกุล:</th><td>${escapeHtml(data.name || '-')}</td></tr>
                                <tr><th>เลขบัตร:</th><td class="font-monospace">${data.id_card_formatted || '-'}</td></tr>
                                <tr><th>เบอร์โทร:</th><td>${data.phone || '-'}</td></tr>
                                <tr><th>ที่อยู่:</th><td>${escapeHtml(data.address?.full || '-')}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">สถิติการบริจาค</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="card bg-soft-primary border-0">
                                        <div class="card-body text-center py-3">
                                            <h3 class="text-primary mb-0">${formatNumber(data.statistics?.receipt_count || 0)}</h3>
                                            <small>รายการบริจาค</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-soft-success border-0">
                                        <div class="card-body text-center py-3">
                                            <h3 class="text-success mb-0">${formatCurrency(data.statistics?.total_amount || 0)}</h3>
                                            <small>ยอดรวมทั้งหมด</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    ${data.top_projects && data.top_projects.length > 0 ? `
                        <hr class="my-3">
                        <h6 class="text-muted mb-2">โครงการที่บริจาค</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>โครงการ</th>
                                        <th class="text-center">จำนวนครั้ง</th>
                                        <th class="text-end">ยอดรวม</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.top_projects.map(p => `
                                        <tr>
                                            <td>${escapeHtml(p.project_name || '-')}</td>
                                            <td class="text-center">${p.count} ครั้ง</td>
                                            <td class="text-end text-primary">${formatCurrency(p.total)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    ` : ''}
                `;
            } catch (error) {
                body.innerHTML = `<div class="text-center py-4 text-danger">${error.message}</div>`;
            }
        }

        function getInitials(name) {
            if (!name) return '?';
            return name.charAt(0).toUpperCase();
        }

        function maskIdCard(idCard) {
            if (!idCard || idCard.length < 13) return idCard;
            return idCard.substring(0, 1) + '-' + idCard.substring(1, 5) + '-XXXXX-' + idCard.substring(10, 12) + '-X';
        }

        function truncateText(text, maxLength) {
            if (!text || text.length <= maxLength) return text;
            return text.substring(0, maxLength) + '...';
        }
    </script>

</body>

</html>