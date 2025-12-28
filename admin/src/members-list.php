<?php include 'partials/main.php'; ?>
<?php requireAuth(); ?>

<!doctype html>
<html lang="th">

<head>
    <?php
    $title = "รายชื่อสมาชิก";
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
                $pageTitle = "รายชื่อสมาชิก";
                $subTitle = "ผู้บริจาค";
                include 'partials/page-title.php'; ?>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
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
                    <div class="col-md-4">
                        <div class="card">
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
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
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
                        <span class="badge bg-primary" id="result-count">0 รายการ</span>
                    </div>
                    <div class="card-body">
                        <!-- Search -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <iconify-icon icon="iconamoon:search-duotone"></iconify-icon>
                                    </span>
                                    <input type="text" id="searchInput" class="form-control"
                                        placeholder="ค้นหาชื่อ, เลขบัตรประชาชน..." onkeyup="handleSearch(event)">
                                    <button class="btn btn-primary" onclick="searchMembers()">
                                        ค้นหา
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select id="searchType" class="form-select">
                                    <option value="all">ทุกประเภท</option>
                                    <option value="name">ชื่อ-นามสกุล</option>
                                    <option value="id_card">เลขบัตรประชาชน</option>
                                </select>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-nowrap align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>เลขบัตรประชาชน</th>
                                        <th>เบอร์โทร</th>
                                        <th class="text-center" style="width: 150px;">ประวัติการบริจาค</th>
                                    </tr>
                                </thead>
                                <tbody id="membersTable">
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="text-muted">
                                                <iconify-icon icon="iconamoon:search-duotone"
                                                    class="fs-48 d-block mb-2"></iconify-icon>
                                                พิมพ์ชื่อหรือเลขบัตรประชาชนเพื่อค้นหา
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

        document.addEventListener('DOMContentLoaded', function () {
            // Load initial stats and list
            loadStats();
            searchMembers();
        });

        async function loadStats() {
            try {
                const response = await apiGet('/reports/summary');
                const data = response.data || {};

                document.getElementById('total-donations').textContent = formatNumber(data.all_time?.count || 0);
                document.getElementById('total-amount').textContent = formatCurrency(data.all_time?.total || 0);
                document.getElementById('total-members').textContent = formatNumber(data.all_time?.members || 0);
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        function handleSearch(e) {
            if (e.key === 'Enter') {
                searchMembers();
            }
        }

        async function searchMembers() {
            const query = document.getElementById('searchInput').value.trim();
            const type = document.getElementById('searchType').value;

            // Allow empty query for listing latest items

            const tbody = document.getElementById('membersTable');
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';

            try {
                const response = await apiGet(`/members/search?q=${encodeURIComponent(query)}&type=${type}&limit=50`);
                members = response.data || [];

                document.getElementById('result-count').textContent = members.length + ' รายการ';

                // Note: Total members stat is handled by loadStats() from summary report

                renderTable(members);
            } catch (error) {
                // Backward compatibility if API still requires query
                if (query === '' && error.message.includes('ระบุคำค้นหา')) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted"><iconify-icon icon="iconamoon:file-search-duotone" class="fs-48 d-block mb-2"></iconify-icon>พิมพ์ชื่อหรือเลขบัตรประชาชนเพื่อค้นหา</td></tr>';
                    return;
                }
                showError(error.message);
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger">${error.message}</td></tr>`;
            }
        }

        function renderTable(data) {
            const tbody = document.getElementById('membersTable');

            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted"><iconify-icon icon="iconamoon:file-search-duotone" class="fs-48 d-block mb-2"></iconify-icon>ไม่พบข้อมูลที่ค้นหา</td></tr>';
                return;
            }

            tbody.innerHTML = data.map((item, idx) => {
                // Fix: Handle null values in first_name/last_name to avoid "null null"
                const fullName = item.name || [item.first_name, item.last_name].filter(val => val).join(' ') || 'ไม่ระบุ';

                return `
                <tr>
                    <td>${idx + 1}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-soft-primary rounded-circle me-2">
                                <span class="avatar-title text-primary fs-14">
                                    ${getInitials(fullName)}
                                </span>
                            </div>
                            <div>
                                <div class="fw-semibold">${escapeHtml(fullName)}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="font-monospace">${item.id_card_formatted || maskIdCard(item.id_card) || '-'}</span>
                    </td>
                    <td>${item.phone || '-'}</td>
                    <td class="text-center">
                        ${item.id_card ? `
                            <button class="btn btn-sm btn-soft-primary" onclick="viewMemberDetail('${item.id_card}')">
                                <iconify-icon icon="iconamoon:history-duotone" class="me-1"></iconify-icon>
                                ดูประวัติ
                            </button>
                        ` : ''}
                    </td>
                </tr>
            `;
            }).join('');
        }

        async function viewMemberDetail(idCard) {
            const modal = new bootstrap.Modal(document.getElementById('memberModal'));
            const body = document.getElementById('memberModalBody');
            body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
            modal.show();

            try {
                const response = await apiGet(`/members/${encodeURIComponent(idCard)}/summary`);
                const data = response.data || {};

                body.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">ข้อมูลส่วนตัว</h6>
                            <table class="table table-sm">
                                <tr><th>ชื่อ-นามสกุล:</th><td>${escapeHtml(data.name || '-')}</td></tr>
                                <tr><th>เลขบัตรประชาชน:</th><td class="font-monospace">${data.id_card_formatted || '-'}</td></tr>
                                <tr><th>เบอร์โทร:</th><td>${data.phone || '-'}</td></tr>
                                <tr><th>ที่อยู่:</th><td>${escapeHtml(data.address || '-')}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">สรุปการบริจาค</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="card bg-soft-primary border-0">
                                        <div class="card-body text-center py-3">
                                            <h3 class="text-primary mb-0">${formatNumber(data.total_donations || 0)}</h3>
                                            <small>รายการบริจาค</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-soft-success border-0">
                                        <div class="card-body text-center py-3">
                                            <h3 class="text-success mb-0">${formatCurrency(data.total_amount || 0)}</h3>
                                            <small>ยอดรวมทั้งหมด</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            ${data.benefactor_level ? `
                                <div class="mt-3 text-center">
                                    <span class="badge bg-warning text-dark fs-14 px-3 py-2">
                                        <iconify-icon icon="iconamoon:star-duotone" class="me-1"></iconify-icon>
                                        ${escapeHtml(data.benefactor_level.name)}
                                    </span>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    ${data.recent_donations && data.recent_donations.length > 0 ? `
                        <hr class="my-3">
                        <h6 class="text-muted mb-2">รายการบริจาคล่าสุด</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>วันที่</th>
                                        <th>โครงการ</th>
                                        <th class="text-end">จำนวนเงิน</th>
                                        <th>สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.recent_donations.slice(0, 5).map(d => `
                                        <tr>
                                            <td>${formatThaiDateShort(d.donation_date)}</td>
                                            <td>${escapeHtml(d.project_name || '-')}</td>
                                            <td class="text-end text-primary">${formatCurrency(d.amount)}</td>
                                            <td>${d.status === 'completed' ? '<span class="badge badge-soft-success">สำเร็จ</span>' : '<span class="badge badge-soft-warning">รอดำเนินการ</span>'}</td>
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
    </script>

</body>

</html>