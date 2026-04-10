<?php include 'partials/main.php'; ?>
<?php requireAuth(); ?>

<!doctype html>
<html lang="th">

<head>
    <?php
    $title = "รายงานประจำวัน";
    include 'partials/title-meta.php'; ?>

    <?php include 'partials/head-css.php'; ?>
    <style>
        .stat-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
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
                $pageTitle = "รายงานประจำวัน";
                $subTitle = "รายงาน";
                include 'partials/page-title.php'; ?>

                <!-- Date Filter -->
                <div class="row mb-4">
                    <div class="col-auto">
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white">
                                <iconify-icon icon="iconamoon:calendar-2-duotone"></iconify-icon>
                            </span>
                            <input type="date" id="reportDate" class="form-control" style="max-width: 180px;">
                            <button class="btn btn-primary" onclick="loadReport()">
                                <iconify-icon icon="iconamoon:search-duotone" class="me-1"></iconify-icon>
                                ดูรายงาน
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Export Buttons -->
                <div class="row mb-4">
                    <div class="col-12 text-end">
                        <button class="btn btn-outline-success me-2" onclick="exportCSV()">
                            <iconify-icon icon="iconamoon:file-document-duotone" class="me-1"></iconify-icon>
                            ส่งออก CSV (ทั่วไป)
                        </button>
                        <button class="btn btn-primary" onclick="exportCSVCmu()">
                            <iconify-icon icon="iconamoon:file-excel-duotone" class="me-1"></iconify-icon>
                            รายงาน CVS-CMU
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-soft-primary">
                                        <iconify-icon icon="iconamoon:heart-duotone"
                                            class="text-primary fs-24"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="stat-count">-</h3>
                                        <p class="text-muted mb-0">รายการบริจาค</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-white bg-opacity-25">
                                        <iconify-icon icon="iconamoon:trend-up-duotone"
                                            class="text-white fs-24"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0 text-white" id="stat-total">-</h3>
                                        <p class="mb-0 opacity-75">ยอดรวม (บาท)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-soft-success">
                                        <iconify-icon icon="iconamoon:check-circle-1-duotone"
                                            class="text-success fs-24"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="stat-confirmed">-</h3>
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
                                    <div class="stat-icon bg-soft-info">
                                        <iconify-icon icon="iconamoon:arrow-up-5-circle-duotone"
                                            class="text-info fs-24"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="stat-avg">-</h3>
                                        <p class="text-muted mb-0">เฉลี่ยต่อรายการ</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">
                                    <iconify-icon icon="iconamoon:3d-duotone" class="me-1"></iconify-icon>
                                    ยอดบริจาคแยกตามชั่วโมง
                                </h4>
                            </div>
                            <div class="card-body">
                                <div id="hourlyChart" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Donations Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">รายการบริจาคประจำวัน</h4>
                        <span class="badge bg-primary" id="table-count">0 รายการ</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-nowrap">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>เวลา</th>
                                        <th>Ref</th>
                                        <th>ผู้บริจาค</th>
                                        <th>โครงการ</th>
                                        <th class="text-end">จำนวนเงิน</th>
                                        <th>สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody id="donationsTable">
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="spinner-border text-primary"></div>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="5" class="text-end">รวมทั้งหมด</th>
                                        <th class="text-end text-primary fs-16" id="foot-total">฿0</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <?php include 'partials/vendor-scripts.php'; ?>
    <script src="assets/js/api-helper.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.44.2/dist/apexcharts.min.js"></script>

    <script>
        let donations = [];
        let hourlyChart;

        document.addEventListener('DOMContentLoaded', function () {
            // Set default date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('reportDate').value = today;

            initChart();
            loadReport();
        });

        function initChart() {
            hourlyChart = new ApexCharts(document.getElementById('hourlyChart'), {
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: { show: false }
                },
                series: [{
                    name: 'ยอดบริจาค',
                    data: new Array(24).fill(0)
                }],
                xaxis: {
                    categories: Array.from({ length: 24 }, (_, i) => `${i.toString().padStart(2, '0')}:00`)
                },
                colors: ['#1c84ee'],
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '60%'
                    }
                },
                dataLabels: { enabled: false },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return '฿' + formatNumber(val);
                        }
                    }
                }
            });
            hourlyChart.render();
        }

        async function loadReport() {
            const date = document.getElementById('reportDate').value;
            if (!date) {
                showWarning('กรุณาเลือกวันที่');
                return;
            }

            const tbody = document.getElementById('donationsTable');
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';

            try {
                // ใช้ API ใหม่ที่ดึงจาก edonation_receipts เป็นตารางหลัก
                const response = await apiGet(`/reports/daily?date=${date}`);
                const data = response.data || {};
                donations = data.receipts || [];
                const stats = data.stats || {};
                const hourlyData = data.hourly_data || new Array(24).fill(0);

                // Update stats from API
                document.getElementById('stat-count').textContent = formatNumber(stats.count || 0);
                document.getElementById('stat-total').textContent = formatNumber(stats.total_amount || 0);
                document.getElementById('stat-confirmed').textContent = formatNumber(stats.confirmed_count || 0);
                document.getElementById('stat-avg').textContent = formatNumber(stats.average || 0);
                document.getElementById('table-count').textContent = (stats.count || 0) + ' รายการ';
                document.getElementById('foot-total').textContent = formatCurrency(stats.total_amount || 0);

                // Update chart with data from API
                hourlyChart.updateSeries([{
                    name: 'ยอดบริจาค',
                    data: hourlyData
                }]);

                // Render table
                renderTable(donations);

            } catch (error) {
                showError(error.message);
                tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger">${error.message}</td></tr>`;
            }
        }

        function renderTable(data) {
            const tbody = document.getElementById('donationsTable');

            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted"><iconify-icon icon="iconamoon:file-search-duotone" class="fs-48 d-block mb-2"></iconify-icon>ไม่มีรายการบริจาคในวันที่เลือก</td></tr>';
                return;
            }

            tbody.innerHTML = data.map((item, idx) => `
                <tr>
                    <td>${idx + 1}</td>
                    <td>${formatTime(item.issued_at)}</td>
                    <td><span class="badge bg-light text-dark font-monospace">${item.ref1 || '-'}</span></td>
                    <td>
                        <div class="fw-medium">${escapeHtml(item.donor_name || 'ไม่ระบุชื่อ')}</div>
                    </td>
                    <td>
                        <div>
                            <div class="small text-muted font-monospace">${escapeHtml(item.project_number || '-')}</div>
                            <div>${escapeHtml(item.project_name || '-')}</div>
                        </div>
                    </td>
                    <td class="text-end fw-semibold text-primary">${formatCurrency(item.amount || 0)}</td>
                    <td>${getStatusBadge(item.status)}</td>
                </tr>
            `).join('');
        }

        function getStatusBadge(status) {
            const badges = {
                'CONFIRMED': '<span class="badge badge-soft-success">ยืนยันแล้ว</span>',
                'PENDING': '<span class="badge badge-soft-warning">รอยืนยัน</span>',
                'CANCELLED': '<span class="badge badge-soft-danger">ยกเลิก</span>'
            };
            return badges[status] || badges['PENDING'];
        }

        function formatTime(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
        }

        function exportCSV() {
            if (donations.length === 0) {
                showWarning('ไม่มีข้อมูลให้ส่งออก');
                return;
            }

            const date = document.getElementById('reportDate').value;
            const headers = ['ลำดับ', 'เวลา', 'Ref', 'ผู้บริจาค', 'โครงการ', 'จำนวนเงิน', 'สถานะ'];
            const rows = donations.map((d, i) => [
                i + 1,
                formatTime(d.issued_at),
                d.ref1 || '',
                d.donor_name || '',
                d.project_name || '',
                d.amount || 0,
                d.status === 'CONFIRMED' ? 'ยืนยันแล้ว' : 'รอยืนยัน'
            ]);

            const csvContent = '\uFEFF' + [headers, ...rows].map(row => row.join(',')).join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `รายงานประจำวัน_${date}.csv`;
            link.click();

            showSuccess('ส่งออกไฟล์ CSV สำเร็จ');
        }

        function exportCSVCmu() {
            if (donations.length === 0) {
                showWarning('ไม่มีข้อมูลให้ส่งออก');
                return;
            }

            const date = document.getElementById('reportDate').value;
            // Columns as requested
            const headers = [
                'ลำดับ',
                'เลขที่ใบเสร็จ',
                'วันที่บริจาค',
                'จำนวนเงิน',
                'รายการทรัพย์สิน',
                'มูลค่าทรัพท์สิน',
                'เลขประจำตัวผู้เสียภาษีอากร',
                'คำนำหน้าตามบัตรประชาชน',
                'ชื่อ',
                'นามสกุล',
                'บ้านเลขที่',
                'หมู่บ้าน/อาคาร', // Blank
                'หมู่ที่',         // Blank
                'ซอย',           // Blank
                'ถนน',           // Blank
                'ตำบล',
                'อำเภอ',
                'จังหวัด',
                'รหัสไฟรษณีย์',
                'เบอร์โทรศัพท์',
                'ช่องทางการชำระเงิน',
                'อาชีพ',         // Blank
                'วันเกิด'        // Blank
            ];

            const rows = donations.map((d, i) => [
                i + 1,
                d.receipt_no || '',
                d.issued_at ? d.issued_at.split(' ')[0] : '', // Date Only YYYY-MM-DD
                d.amount || 0,
                d.project_name || '', // รายการทรัพย์สิน (เอาชื่อโครงการ)
                d.amount || 0,        // มูลค่า (เท่ากับยอดเงิน)
                d.tax_id || '',
                d.title || '',
                d.first_name || '',
                d.last_name || '',
                d.address_line || '', // บ้านเลขที่ (ใช้ที่อยู่รวมไปก่อน)
                '', // หมู่บ้าน
                '', // หมู่ที่
                '', // ซอย
                '', // ถนน
                d.district || '',
                d.amphure || '',
                d.province || '',
                d.zip_code || '',
                d.phone || '',
                d.pay_by || '',
                '', // อาชีพ
                ''  // วันเกิด
            ]);

            const csvContent = '\uFEFF' + [headers, ...rows].map(row => {
                // Escape quotes and wrap in quotes if contains comma
                return row.map(cell => {
                    const str = String(cell);
                    if (str.includes(',') || str.includes('"') || str.includes('\n')) {
                        return `"${str.replace(/"/g, '""')}"`;
                    }
                    return str;
                }).join(',');
            }).join('\n');

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `รายงาน_CVS_CMU_${date}.csv`;
            link.click();

            showSuccess('ส่งออกไฟล์ CVS-CMU สำเร็จ');
        }
    </script>

</body>

</html>