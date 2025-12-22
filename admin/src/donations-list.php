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
        <?php include 'partials/topbar.php'; ?>
        
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
                                    <iconify-icon icon="iconamoon:heart-duotone" class="avatar-title text-primary fs-32"></iconify-icon>
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
                                    <iconify-icon icon="iconamoon:check-circle-1-duotone" class="avatar-title text-success fs-32"></iconify-icon>
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
                                    <iconify-icon icon="iconamoon:clock-duotone" class="avatar-title text-warning fs-32"></iconify-icon>
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
                                    <iconify-icon icon="iconamoon:trend-up-duotone" class="avatar-title text-white fs-32"></iconify-icon>
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
                            <input type="text" id="searchInput" class="form-control" placeholder="ค้นหาชื่อ, เลขบัตร...">
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
                                    <th>Ref</th>
                                    <th>ผู้บริจาค</th>
                                    <th>โครงการ</th>
                                    <th class="text-end">จำนวนเงิน</th>
                                    <th>วันที่</th>
                                    <th>สถานะ</th>
                                    <th class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="donationsTable">
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
                        <div id="pagination-info" class="text-muted"></div>
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

<?php include 'partials/vendor-scripts.php'; ?>

<script>
let donations = [];
let projects = [];
let currentPage = 1;
const perPage = 20;

document.addEventListener('DOMContentLoaded', function() {
    loadProjects();
    loadDonations();
    
    // Filter handlers
    document.getElementById('searchInput').addEventListener('input', debounce(loadDonations, 500));
    document.getElementById('statusFilter').addEventListener('change', loadDonations);
    document.getElementById('projectFilter').addEventListener('change', loadDonations);
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
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';
    
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
        
        // Update stats
        const stats = response.stats || {};
        document.getElementById('total-count').textContent = formatNumber(stats.total || donations.length);
        document.getElementById('confirmed-count').textContent = formatNumber(stats.confirmed || donations.filter(d => d.status === 'CONFIRMED').length);
        document.getElementById('pending-count').textContent = formatNumber(stats.pending || donations.filter(d => d.status === 'PENDING').length);
        document.getElementById('total-amount').textContent = formatNumber(stats.totalAmount || donations.reduce((sum, d) => sum + parseFloat(d.amount || 0), 0));
        
        renderTable(donations);
        
    } catch (error) {
        showError(error.message);
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger">${error.message}</td></tr>`;
    }
}

function renderTable(data) {
    const tbody = document.getElementById('donationsTable');
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">ไม่พบรายการบริจาค</td></tr>';
        return;
    }
    
    tbody.innerHTML = data.map(item => `
        <tr>
            <td>
                <span class="badge bg-light text-dark font-monospace">${item.billPaymentRef1 || item.ref || '-'}</span>
            </td>
            <td>
                <div class="fw-medium">${escapeHtml(item.donor_name || item.name || 'ไม่ระบุชื่อ')}</div>
                ${item.id_card ? `<small class="text-muted">${maskIdCard(item.id_card)}</small>` : ''}
            </td>
            <td>${escapeHtml(item.project_name || item.project_number || '-')}</td>
            <td class="text-end fw-semibold text-primary">${formatCurrency(item.amount || 0)}</td>
            <td>${formatThaiDateShort(item.transaction_date || item.created_at)}</td>
            <td>${getStatusBadge(item.status)}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-soft-primary" onclick="viewDetail('${item.billPaymentRef1 || item.id}')" title="ดูรายละเอียด">
                    <iconify-icon icon="iconamoon:eye-duotone"></iconify-icon>
                </button>
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

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}
</script>

</body>
</html>
