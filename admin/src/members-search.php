<?php include 'partials/main.php'; ?>
<?php requireAuth(); ?>

<!doctype html>
<html lang="th">
<head>
    <?php
    $title = "ค้นหาประวัติผู้บริจาค";
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
            $pageTitle = "ค้นหาประวัติผู้บริจาค";
            $subTitle = "ผู้บริจาค";
            include 'partials/page-title.php'; ?>

            <!-- Search Card -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <iconify-icon icon="iconamoon:search-duotone" class="me-2"></iconify-icon>
                        ค้นหาข้อมูลผู้บริจาค
                    </h4>
                </div>
                <div class="card-body">
                    <form id="searchForm">
                        <div class="row justify-content-center">
                            <div class="col-md-6">
                                <label class="form-label">เลขบัตรประชาชน</label>
                                <div class="input-group input-group-lg">
                                    <input type="text" 
                                           id="idCardInput" 
                                           class="form-control" 
                                           placeholder="กรอกเลขบัตรประชาชน 13 หลัก"
                                           maxlength="13"
                                           pattern="[0-9]{13}">
                                    <button type="submit" class="btn btn-primary">
                                        <iconify-icon icon="iconamoon:search-duotone" class="me-1"></iconify-icon>
                                        ค้นหา
                                    </button>
                                </div>
                                <div class="form-text">กรอกเลขบัตรประชาชน 13 หลัก เพื่อตรวจสอบประวัติการบริจาค</div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Result Section (Hidden by default) -->
            <div id="resultSection" style="display: none;">
                <!-- Member Info -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="avatar-xl bg-soft-primary rounded-circle mx-auto mb-3">
                                    <iconify-icon icon="iconamoon:profile-circle-duotone" class="avatar-title text-primary" style="font-size: 48px;"></iconify-icon>
                                </div>
                                <h4 id="memberName" class="mb-1">-</h4>
                                <p id="memberId" class="text-muted mb-3">-</p>
                                
                                <div class="row text-center">
                                    <div class="col-4 border-end">
                                        <h4 id="totalDonations" class="text-primary mb-1">-</h4>
                                        <small class="text-muted">ครั้ง</small>
                                    </div>
                                    <div class="col-4 border-end">
                                        <h4 id="totalAmount" class="text-success mb-1">-</h4>
                                        <small class="text-muted">บาท</small>
                                    </div>
                                    <div class="col-4">
                                        <h4 id="memberSince" class="text-info mb-1">-</h4>
                                        <small class="text-muted">ปี</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">สรุปการบริจาคตามโครงการ</h5>
                            </div>
                            <div class="card-body">
                                <div id="projectSummary">
                                    <div class="text-center py-4 text-muted">ยังไม่มีข้อมูล</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Donation History -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <iconify-icon icon="iconamoon:clock-duotone" class="me-2"></iconify-icon>
                            ประวัติการบริจาค
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Ref</th>
                                        <th>โครงการ</th>
                                        <th class="text-end">จำนวนเงิน</th>
                                        <th>วันที่</th>
                                        <th>สถานะ</th>
                                        <th>ใบเสร็จ</th>
                                    </tr>
                                </thead>
                                <tbody id="donationHistory">
                                    <tr><td colspan="7" class="text-center py-4 text-muted">ยังไม่มีข้อมูล</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Year Summary -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <iconify-icon icon="iconamoon:3d-duotone" class="me-2"></iconify-icon>
                            สรุปรายปี
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="yearSummary" class="row">
                            <div class="col-12 text-center py-4 text-muted">ยังไม่มีข้อมูล</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Result -->
            <div id="noResultSection" style="display: none;">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <iconify-icon icon="iconamoon:search-duotone" class="text-muted" style="font-size: 64px;"></iconify-icon>
                        <h4 class="mt-3">ไม่พบข้อมูล</h4>
                        <p class="text-muted">ไม่พบประวัติการบริจาคสำหรับเลขบัตรประชาชนนี้</p>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'partials/footer.php'; ?>
    </div>
</div>

<?php include 'partials/vendor-scripts.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Input formatting - only numbers
    document.getElementById('idCardInput').addEventListener('input', function(e) {
        this.value = this.value.replace(/\D/g, '').substring(0, 13);
    });
    
    // Search form
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        searchMember();
    });
});

async function searchMember() {
    const idCard = document.getElementById('idCardInput').value;
    
    if (idCard.length !== 13) {
        showWarning('กรุณากรอกเลขบัตรประชาชน 13 หลัก');
        return;
    }
    
    document.getElementById('resultSection').style.display = 'none';
    document.getElementById('noResultSection').style.display = 'none';
    
    try {
        const response = await apiGet('/members/' + idCard + '/summary');
        const data = response.data;
        
        if (!data || !data.member) {
            document.getElementById('noResultSection').style.display = 'block';
            return;
        }
        
        displayResult(data);
        
    } catch (error) {
        if (error.message.includes('404') || error.message.includes('ไม่พบ')) {
            document.getElementById('noResultSection').style.display = 'block';
        } else {
            showError(error.message);
        }
    }
}

function displayResult(data) {
    const member = data.member;
    const summary = data.summary;
    const donations = data.donations || [];
    const byYear = data.byYear || {};
    const byProject = data.byProject || [];
    
    // Show result section
    document.getElementById('resultSection').style.display = 'block';
    
    // Member info
    document.getElementById('memberName').textContent = member.name || 'ไม่ระบุชื่อ';
    document.getElementById('memberId').textContent = maskIdCard(member.id_card);
    document.getElementById('totalDonations').textContent = summary.totalDonations || 0;
    document.getElementById('totalAmount').textContent = formatNumber(summary.totalAmount || 0);
    
    // Calculate years since first donation
    if (summary.firstDonation) {
        const firstYear = new Date(summary.firstDonation).getFullYear();
        const yearsActive = new Date().getFullYear() - firstYear + 1;
        document.getElementById('memberSince').textContent = yearsActive;
    } else {
        document.getElementById('memberSince').textContent = '-';
    }
    
    // Project summary
    if (byProject && byProject.length > 0) {
        document.getElementById('projectSummary').innerHTML = byProject.map(p => `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <span class="fw-medium">${escapeHtml(p.project_name || p.project_number)}</span>
                    <br><small class="text-muted">${p.count} ครั้ง</small>
                </div>
                <span class="badge bg-primary fs-14">${formatCurrency(p.amount)}</span>
            </div>
        `).join('');
    } else {
        document.getElementById('projectSummary').innerHTML = '<div class="text-center py-4 text-muted">ไม่มีข้อมูล</div>';
    }
    
    // Donation history
    if (donations && donations.length > 0) {
        document.getElementById('donationHistory').innerHTML = donations.map((d, i) => `
            <tr>
                <td>${i + 1}</td>
                <td><span class="badge bg-light text-dark font-monospace">${d.billPaymentRef1 || '-'}</span></td>
                <td>${escapeHtml(d.project_name || d.project_number || '-')}</td>
                <td class="text-end fw-medium text-primary">${formatCurrency(d.amount)}</td>
                <td>${formatThaiDateShort(d.transaction_date || d.created_at)}</td>
                <td>${getStatusBadge(d.status)}</td>
                <td>
                    ${d.receipt_number ? 
                        `<a href="../api/v1/receipts/${d.billPaymentRef1}/pdf" target="_blank" class="btn btn-sm btn-soft-success">
                            <iconify-icon icon="iconamoon:invoice-duotone"></iconify-icon>
                        </a>` : 
                        '<span class="text-muted">-</span>'}
                </td>
            </tr>
        `).join('');
    } else {
        document.getElementById('donationHistory').innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">ไม่มีประวัติการบริจาค</td></tr>';
    }
    
    // Year summary
    const years = Object.keys(byYear).sort((a, b) => b - a);
    if (years.length > 0) {
        document.getElementById('yearSummary').innerHTML = years.map(year => `
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="border rounded p-3 text-center">
                    <h6 class="text-muted mb-2">ปี ${parseInt(year) + 543}</h6>
                    <h4 class="text-primary mb-1">${formatNumber(byYear[year].amount || 0)}</h4>
                    <small class="text-muted">${byYear[year].count || 0} ครั้ง</small>
                </div>
            </div>
        `).join('');
    } else {
        document.getElementById('yearSummary').innerHTML = '<div class="col-12 text-center py-4 text-muted">ไม่มีข้อมูล</div>';
    }
}

function getStatusBadge(status) {
    const badges = {
        'CONFIRMED': '<span class="badge badge-soft-success">ยืนยัน</span>',
        'PENDING': '<span class="badge badge-soft-warning">รอยืนยัน</span>'
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
</script>

</body>
</html>
