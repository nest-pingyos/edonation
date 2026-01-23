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
        /* Custom UI Tweaks */
        .card-flush {
            border: none;
            box-shadow: 0 0.1rem 1rem rgba(0, 0, 0, 0.05);
        }

        .avatar-initial {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #f3f6f9;
            color: #3f4254;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .table-custom th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #6c757d;
            background-color: #f8f9fa;
        }

        .btn-icon-soft {
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
            border: none;
        }

        .btn-icon-soft:hover {
            background-color: var(--bs-primary);
            color: white;
        }

        .form-control-flush {
            border: none;
            background: transparent;
        }

        .search-box {
            background-color: #f1f3f5;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
        }

        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="bg-light">
    <div class="wrapper">
        <?php include 'partials/edonation-nav.php'; ?>

        <div class="page-content">
            <?php include 'partials/edonation-topbar.php'; ?>

            <div class="container-xxl">
                <?php
                $pageTitle = "รายชื่อสมาชิก";
                $subTitle = "ผู้บริจาค";
                include 'partials/page-title.php'; ?>

                <!-- Stats Overview -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-flush h-100">
                            <div class="card-body d-flex align-items-center">
                                <div
                                    class="avatar-lg rounded bg-primary-subtle d-flex align-items-center justify-content-center flex-shrink-0">
                                    <iconify-icon icon="iconamoon:profile-duotone"
                                        class="fs-1 text-primary"></iconify-icon>
                                </div>
                                <div class="ms-3">
                                    <span class="d-block text-muted mb-1 text-truncate">สมาชิกทั้งหมด</span>
                                    <h4 class="mb-0 fw-bold" id="total-members">-</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-flush h-100">
                            <div class="card-body d-flex align-items-center">
                                <div
                                    class="avatar-lg rounded bg-warning-subtle d-flex align-items-center justify-content-center flex-shrink-0">
                                    <iconify-icon icon="iconamoon:star-duotone"
                                        class="fs-1 text-warning"></iconify-icon>
                                </div>
                                <div class="ms-3">
                                    <span class="d-block text-muted mb-1 text-truncate">ผู้บริจาคซ้ำ</span>
                                    <h4 class="mb-0 fw-bold" id="repeat-donors">-</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-flush h-100">
                            <div class="card-body d-flex align-items-center">
                                <div
                                    class="avatar-lg rounded bg-success-subtle d-flex align-items-center justify-content-center flex-shrink-0">
                                    <iconify-icon icon="iconamoon:heart-duotone"
                                        class="fs-1 text-success"></iconify-icon>
                                </div>
                                <div class="ms-3">
                                    <span class="d-block text-muted mb-1 text-truncate">รายการบริจาค</span>
                                    <h4 class="mb-0 fw-bold" id="total-donations">-</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-flush h-100 bg-primary text-white">
                            <div class="card-body d-flex align-items-center">
                                <div
                                    class="avatar-lg rounded bg-white bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0">
                                    <iconify-icon icon="iconamoon:trend-up-duotone"
                                        class="fs-1 text-white"></iconify-icon>
                                </div>
                                <div class="ms-3">
                                    <span class="d-block text-white-50 mb-1 text-truncate">ยอดบริจาครวม</span>
                                    <h4 class="mb-0 fw-bold text-white" id="total-amount">-</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="card card-flush">
                    <div class="card-body">
                        <!-- Toolbar -->
                        <div
                            class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-4">
                            <div class="col-md-2">
                                <div class="input-group">
                                    <span class="input-group-text bg-light">แสดง</span>
                                    <select id="limitSelector" class="form-select border-0 bg-light"
                                        onchange="changeLimit()">
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="250">250</option>
                                        <option value="500">500</option>
                                    </select>
                                    <span class="input-group-text bg-light">แถว</span>
                                </div>
                            </div>
                            <div class="search-box d-flex align-items-center flex-grow-1" style="max-width: 400px;">
                                <iconify-icon icon="iconamoon:search-duotone"
                                    class="text-muted fs-5 me-2"></iconify-icon>
                                <input type="text" id="searchInput" class="form-control form-control-flush p-0"
                                    placeholder="ค้นหาชื่อ, รหัสสมาชิก หรือเบอร์โทร..." onkeyup="handleSearch(event)">
                            </div>

                            <div class="d-flex gap-2">
                                <select id="filterType" class="form-select border-0 bg-light"
                                    style="width: auto; min-width: 150px;" onchange="loadMembers()">
                                    <option value="">ทั้งหมด</option>
                                    <option value="loyal">ผู้บริจาคประจำ</option>
                                    <option value="repeat">ผู้บริจาคซ้ำ</option>
                                    <option value="new">ผู้บริจาคใหม่</option>
                                </select>

                                <button class="btn btn-light" onclick="loadMembers()" title="รีเฟรช">
                                    <iconify-icon icon="iconamoon:restart-duotone" class="fs-5"></iconify-icon>
                                </button>

                                <button class="btn btn-primary d-flex align-items-center gap-2" onclick="syncMembers()"
                                    id="btnSync" title="Sync ข้อมูลสมาชิกจากใบเสร็จ">
                                    <iconify-icon icon="iconamoon:synchronize-duotone" class="fs-5"
                                        id="syncIcon"></iconify-icon>
                                    <span class="d-none d-sm-inline">Sync</span>
                                </button>

                                <button class="btn btn-success d-flex align-items-center gap-2"
                                    onclick="exportSelected()" id="btnExport">
                                    <iconify-icon icon="iconamoon:file-document-duotone" class="fs-5"></iconify-icon>
                                    <span class="d-none d-sm-inline">Export</span>
                                </button>
                            </div>
                        </div>

                        <!-- Selected Count Badge -->
                        <div class="mb-2">
                            <span class="badge bg-primary-subtle text-primary" id="result-count">กำลังโหลด...</span>
                        </div>
                        <!-- Hidden Export Form -->
                        <form id="exportForm" action="export_members.php" method="POST" target="_blank"
                            style="display:none;">
                            <input type="hidden" name="ids" id="exportIds">
                        </form>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;" class="text-center">
                                            <input class="form-check-input" type="checkbox" id="selectAll"
                                                onchange="toggleAll(this)">
                                        </th>
                                        <th style="width: 60px;" class="text-center">#</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>รหัสสมาชิก</th>
                                        <th>เลขบัตรประชาชน</th>
                                        <th class="text-center" style="width: 100px;">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="membersTable">
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="spinner-border text-primary"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted" id="pagination-info"></div>
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

    <!-- View Member Modal -->
    <div class="modal fade" id="memberModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ข้อมูลผู้บริจาค</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="memberModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-primary" id="btn-edit-modal">แก้ไขข้อมูล</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Member Modal -->
    <div class="modal fade" id="editMemberModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editMemberForm" onsubmit="updateMember(event)">
                    <div class="modal-header">
                        <h5 class="modal-title">แก้ไขข้อมูลสมาชิก</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_id_members" name="id_members">

                        <div class="row g-3 mb-3">
                            <div class="col-md-2">
                                <label class="form-label">คำนำหน้า</label>
                                <input type="text" class="form-control" name="title" id="edit_title" list="titles">
                                <datalist id="titles">
                                    <option value="นาย">
                                    <option value="นาง">
                                    <option value="นางสาว">
                                    <option value="ด.ช.">
                                    <option value="ด.ญ.">
                                    <option value="บริษัท">
                                </datalist>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="first_name" id="edit_first_name" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">นามสกุล</label>
                                <input type="text" class="form-control" name="last_name" id="edit_last_name">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">เลขบัตรประชาชน</label>
                                <input type="text" class="form-control" name="id_card" id="edit_id_card" maxlength="13">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">เบอร์โทรศัพท์</label>
                                <input type="text" class="form-control" name="phone" id="edit_phone">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">อาชีพ</label>
                                <input type="text" class="form-control" name="occupation" id="edit_occupation">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ที่อยู่</label>
                            <input type="text" class="form-control mb-2" name="address_line" id="edit_address_line"
                                placeholder="บ้านเลขที่, ถนน">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="district" id="edit_district"
                                        placeholder="ตำบล">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="amphure" id="edit_amphure"
                                        placeholder="อำเภอ">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="province" id="edit_province"
                                        placeholder="จังหวัด">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="zip_code" id="edit_zip_code"
                                        placeholder="รหัสไปรษณีย์">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
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
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5"><div class="spinner-border text-primary me-2"></div><span class="text-muted">กำลังโหลดข้อมูล...</span></td></tr>';
            document.getElementById('selectAll').checked = false;

            try {
                // Determine API endpoint based on search
                const query = document.getElementById('searchInput').value.trim();
                let url = `/members?page=${page}&limit=${perPage}`;

                if (query) {
                    // If searching, we skip normal pagination for now as API search might have different behavior
                    // but we try to keep it consistent if possible
                    url = `/members/search?q=${encodeURIComponent(query)}&limit=${perPage}`;
                } else {
                    // Add filter if not searching
                    const filter = document.getElementById('filterType').value;
                    if (filter) url += `&type=${filter}`;
                }

                const response = await apiGet(url);
                members = response.data || [];
                const meta = response.meta || {};

                // Update Stats (Client-side calc if search, or meta from server)
                updateStats(members, meta);

                renderTable(members);
                renderPagination(meta);
            } catch (error) {
                showError(error.message);
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-danger">${error.message}</td></tr>`;
            }
        }

        async function searchMembers() {
            loadMembers(1);
        }

        function updateStats(data, meta) {
            document.getElementById('total-members').textContent = formatNumber(meta.total || data.length);
            document.getElementById('result-count').textContent = (meta.total || data.length) + ' รายการ';

            // Simple client side stats for demo purposes or consistent with previous logic
            const repeatCount = data.filter(m => m.is_repeat_donor).length;
            document.getElementById('repeat-donors').textContent = formatNumber(repeatCount);

            let totalDonations = 0;
            let totalAmount = 0;
            data.forEach(m => {
                totalDonations += m.receipt_count || 0;
                totalAmount += m.total_amount || 0;
            });
            document.getElementById('total-donations').textContent = formatNumber(totalDonations);
            document.getElementById('total-amount').textContent = formatCurrency(totalAmount);
        }

        function renderTable(data) {
            const tbody = document.getElementById('membersTable');
            const filterType = document.getElementById('filterType').value;

            // Frontend filtering if needed
            let filteredData = data;
            if (filterType && !document.getElementById('searchInput').value) {
                filteredData = data.filter(m => m.donor_type === filterType);
            }

            if (!filteredData || filteredData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted">ไม่พบข้อมูล</td></tr>';
                return;
            }

            tbody.innerHTML = filteredData.map((item, index) => {
                const fullName = item.name || 'ไม่ระบุชื่อ';
                const startIdx = (currentPage - 1) * perPage + index + 1;

                return `
                <tr>
                    <td class="text-center">
                        <input class="form-check-input member-checkbox" type="checkbox" value="${item.id_members}">
                    </td>
                    <td class="text-center">${startIdx}</td>
                    <td>
                        <a href="javascript:void(0)" onclick="viewMemberDetail('${item.id_members}')" class="text-decoration-none fw-bold text-dark">
                            ${escapeHtml(truncateText(fullName, 40))}
                        </a>
                        <div class="small text-muted d-md-none">${displayPhone(item.phone)}</div>
                    </td>
                    <td><span class="badge bg-light text-dark border">${item.id_members}</span></td>
                    <td>${formatIdCard(item.id_card)}</td>
                    <td class="text-center">
                         <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                จัดการ
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0)" onclick="viewMemberDetail('${item.id_members}')">
                                        <i class="fs-6 me-2 bi bi-eye"></i> ดูรายละเอียด
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0)" onclick="editMember('${item.id_members}')">
                                        <i class="fs-6 me-2 bi bi-pencil"></i> แก้ไขข้อมูล
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
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

        async function viewMemberDetail(idMembers) {
            const modal = new bootstrap.Modal(document.getElementById('memberModal'));
            const body = document.getElementById('memberModalBody');

            modal.show();
            // body has loading spinner initally

            document.getElementById('btn-edit-modal').onclick = () => {
                modal.hide();
                editMember(idMembers);
            };

            try {
                const response = await apiGet(`/members/${encodeURIComponent(idMembers)}`);
                const data = response.data || {};

                body.innerHTML = `
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <h4 class="fw-bold text-primary mb-3">${data.name || '-'}</h4>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="fw-bold text-muted small">รหัสสมาชิก</div>
                                    <div class="font-monospace">${data.id_members}</div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="fw-bold text-muted small">เลขบัตรประชาชน / ผู้เสียภาษี</div>
                                    <div class="font-monospace">${data.id_card_formatted || '-'}</div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="fw-bold text-muted small">เบอร์โทรศัพท์</div>
                                    <div>${displayPhone(data.phone)}</div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="fw-bold text-muted small">อาชีพ</div>
                                    <div>${data.occupation || '-'}</div>
                                </div>
                                <div class="col-12">
                                    <div class="fw-bold text-muted small">ที่อยู่</div>
                                    <div>${data.address?.full || '-'}</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <hr class="text-muted opacity-25">
                            <h5 class="fw-bold mb-3">สรุปข้อมูลการบริจาค</h5>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-sm-6">
                                    <span class="text-muted">จำนวนครั้งที่บริจาค:</span>
                                    <span class="fw-bold ms-2">${data.statistics?.receipt_count || 0} ครั้ง</span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted">ยอดบริจาครวม:</span>
                                    <span class="fw-bold ms-2 text-success">${formatCurrency(data.statistics?.total_amount || 0)}</span>
                                </div>
                            </div>

                            ${data.top_projects && data.top_projects.length > 0 ? `
                            <div class="fw-bold text-muted small mb-2">โครงการที่สนับสนุน</div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ชื่อโครงการ</th>
                                            <th class="text-end" style="width: 150px;">ยอดเงิน</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.top_projects.map(p => `
                                            <tr>
                                                <td>${p.project_name || p.project_number}</td>
                                                <td class="text-end fw-medium">${formatCurrency(p.total)}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>` : ''}
                        </div>
                    </div>
                `;
            } catch (error) {
                body.innerHTML = `<div class="alert alert-danger mx-3">${error.message}</div>`;
            }
        }

        async function editMember(idMembers) {
            const modal = new bootstrap.Modal(document.getElementById('editMemberModal'));
            // Reset Form
            const form = document.getElementById('editMemberForm');
            form.reset();

            try {
                const response = await apiGet(`/members/${encodeURIComponent(idMembers)}`);
                const data = response.data || {};

                document.getElementById('edit_id_members').value = data.id_members;
                document.getElementById('edit_title').value = data.title || '';
                document.getElementById('edit_first_name').value = data.first_name || '';
                document.getElementById('edit_last_name').value = data.last_name || '';
                document.getElementById('edit_id_card').value = data.id_card || '';
                document.getElementById('edit_phone').value = data.phone || '';
                document.getElementById('edit_occupation').value = data.occupation || '';

                if (data.address) {
                    document.getElementById('edit_address_line').value = data.address.address_line || '';
                    document.getElementById('edit_district').value = data.address.district || '';
                    document.getElementById('edit_amphure').value = data.address.amphure || '';
                    document.getElementById('edit_province').value = data.address.province || '';
                    document.getElementById('edit_zip_code').value = data.address.zip_code || '';
                }

                modal.show();
            } catch (e) {
                showError(e.message);
            }
        }

        async function updateMember(e) {
            e.preventDefault();
            const idMembers = document.getElementById('edit_id_members').value;
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());

            try {
                await apiPost(`/members/${encodeURIComponent(idMembers)}/update`, data);
                showSuccess('อัพเดทข้อมูลเรียบร้อยแล้ว');
                bootstrap.Modal.getInstance(document.getElementById('editMemberModal')).hide();
                loadMembers(currentPage);
            } catch (error) {
                showError(error.message);
            }
        }

        function toggleAll(source) {
            document.querySelectorAll('.member-checkbox').forEach(cb => cb.checked = source.checked);
        }

        function exportSelected() {
            const checkboxes = document.querySelectorAll('.member-checkbox:checked');
            const ids = Array.from(checkboxes).map(cb => cb.value);
            if (ids.length === 0) {
                showWarning('กรุณาเลือกรายการที่ต้องการ Export อย่างน้อย 1 รายการ');
                return;
            }
            document.getElementById('exportIds').value = ids.join(',');
            document.getElementById('exportForm').submit();
        }

        async function syncMembers() {
            const btn = document.getElementById('btnSync');
            const icon = document.getElementById('syncIcon');

            // Confirm before sync
            if (!confirm('ต้องการ Sync ข้อมูลสมาชิกจากใบเสร็จหรือไม่?\n(จะอัปเดตข้อมูลสมาชิกทั้งหมด)')) {
                return;
            }

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