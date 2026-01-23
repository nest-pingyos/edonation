<?php include 'partials/main.php'; ?>
<?php requireAuth(); ?>

<!doctype html>
<html lang="th">

<head>
    <?php
    $title = "รายงานประจำปี";
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

        .month-card {
            transition: all 0.2s ease;
        }

        .month-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
                $pageTitle = "รายงานประจำปี";
                $subTitle = "รายงาน";
                include 'partials/page-title.php'; ?>

                <!-- Year Filter -->
                <div class="row mb-4">
                    <div class="col-auto">
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white">
                                <iconify-icon icon="iconamoon:calendar-2-duotone"></iconify-icon>
                            </span>
                            <select id="reportYear" class="form-select" style="max-width: 120px;"></select>
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
                                        <iconify-icon icon="iconamoon:star-duotone"
                                            class="text-success fs-24"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="stat-best-month">-</h3>
                                        <p class="text-muted mb-0">เดือนที่ยอดสูงสุด</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-soft-warning">
                                        <iconify-icon icon="iconamoon:compare-duotone"
                                            class="text-warning fs-24"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="stat-growth">-</h3>
                                        <p class="text-muted mb-0">เทียบกับปีก่อน</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">
                                    <iconify-icon icon="iconamoon:3d-duotone" class="me-1"></iconify-icon>
                                    ยอดบริจาครายเดือน
                                </h4>
                            </div>
                            <div class="card-body">
                                <div id="monthlyChart" style="height: 350px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">สัดส่วนตามโครงการ</h4>
                            </div>
                            <div class="card-body">
                                <div id="projectPieChart" style="height: 350px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Summary Table -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">สรุปยอดรายเดือน</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3" id="monthlyCards">
                            <!-- Generated by JS -->
                        </div>
                    </div>
                </div>

                <!-- Project Summary Table -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h4 class="card-title mb-0">สรุปยอดแยกตามโครงการ</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>โครงการ</th>
                                        <th class="text-center">จำนวนรายการ</th>
                                        <th class="text-end">ยอดรวม</th>
                                        <th class="text-end">สัดส่วน</th>
                                    </tr>
                                </thead>
                                <tbody id="projectTable">
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="spinner-border text-primary"></div>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="2" class="text-end">รวมทั้งปี</th>
                                        <th class="text-center" id="foot-count">0</th>
                                        <th class="text-end text-primary fs-16" id="foot-total">฿0</th>
                                        <th class="text-end">100%</th>
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
        let monthlyChart, projectPieChart;

        // Default to calendar year
        const fiscalYearType = 'calendar';

        // Month names for display (standard order)
        const monthNames = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
            'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        const monthNamesFull = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
            'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

        // Chart month names for calendar year
        const fiscalMonthNames = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

        document.addEventListener('DOMContentLoaded', function () {
            initYearSelector();
            initCharts();
            loadReport();
        });

        function initYearSelector() {
            const select = document.getElementById('reportYear');
            const now = new Date();
            const currentYear = now.getFullYear();

            const defaultYear = currentYear;

            for (let y = defaultYear; y >= 2023; y--) {
                const option = document.createElement('option');
                option.value = y;
                option.textContent = y + 543; // Buddhist year
                select.appendChild(option);
            }
        }

        function initCharts() {
            // Monthly bar chart
            monthlyChart = new ApexCharts(document.getElementById('monthlyChart'), {
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: { show: false }
                },
                series: [{
                    name: 'ปีนี้',
                    data: new Array(12).fill(0)
                }, {
                    name: 'ปีก่อน',
                    data: new Array(12).fill(0)
                }],
                xaxis: {
                    categories: fiscalMonthNames
                },
                colors: ['#1c84ee', '#e2e8f0'],
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '60%'
                    }
                },
                dataLabels: { enabled: false },
                legend: { position: 'top' },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return '฿' + formatNumber(val);
                        }
                    }
                }
            });
            monthlyChart.render();

            // Project pie chart
            projectPieChart = new ApexCharts(document.getElementById('projectPieChart'), {
                chart: {
                    type: 'pie',
                    height: 350
                },
                series: [],
                labels: [],
                colors: ['#1c84ee', '#22c55e', '#f9b931', '#4ecac2', '#ef5f5f', '#7c3aed'],
                legend: { position: 'bottom' },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val.toFixed(0) + '%';
                    }
                }
            });
            projectPieChart.render();
        }

        async function loadReport() {
            const year = document.getElementById('reportYear').value;

            try {
                // ใช้ API ใหม่ที่ดึงจาก edonation_receipts เป็นตารางหลัก
                const response = await apiGet(`/reports/yearly?year=${year}`);
                const data = response.data || {};
                donations = data.receipts || [];
                const stats = data.stats || {};
                const monthlyData = data.monthly_data || new Array(12).fill(0);
                const prevMonthlyData = data.prev_monthly_data || new Array(12).fill(0);
                const projectSummary = data.project_summary || [];

                // Update stats from API
                document.getElementById('stat-count').textContent = formatNumber(stats.count || 0);
                document.getElementById('stat-total').textContent = formatNumber(stats.total_amount || 0);
                document.getElementById('stat-best-month').textContent = stats.best_month ? monthNamesFull[stats.best_month - 1] : '-';

                const growthEl = document.getElementById('stat-growth');
                const growth = stats.growth_percent || 0;
                if (growth > 0) {
                    growthEl.innerHTML = `<span class="text-success">+${growth}%</span>`;
                } else if (growth < 0) {
                    growthEl.innerHTML = `<span class="text-danger">${growth}%</span>`;
                } else {
                    growthEl.textContent = '-';
                }

                document.getElementById('foot-count').textContent = stats.count || 0;
                document.getElementById('foot-total').textContent = formatCurrency(stats.total_amount || 0);

                // Update monthly chart
                monthlyChart.updateSeries([
                    { name: `ปี ${parseInt(year) + 543}`, data: monthlyData },
                    { name: `ปี ${parseInt(year) + 542}`, data: prevMonthlyData }
                ]);

                // Monthly cards
                updateMonthlyCards(monthlyData);

                // Project summary table
                updateProjectTable(projectSummary, stats.total_amount || 0);

                // Update pie chart
                if (projectSummary.length > 0) {
                    projectPieChart.updateOptions({
                        labels: projectSummary.slice(0, 6).map(p => truncateText(p.project_name, 15))
                    });
                    projectPieChart.updateSeries(projectSummary.slice(0, 6).map(p => p.amount));
                }

            } catch (error) {
                showError(error.message);
            }
        }

        function updateMonthlyCards(data) {
            const container = document.getElementById('monthlyCards');

            container.innerHTML = data.map((amount, idx) => {
                const bgClass = amount > 0 ? 'bg-soft-primary' : 'bg-light';
                const textClass = amount > 0 ? 'text-primary' : 'text-muted';
                return `
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card month-card ${bgClass} border-0 mb-0">
                            <div class="card-body text-center py-3">
                                <div class="fw-semibold ${textClass}">${monthNames[idx]}</div>
                                <div class="fs-16 fw-bold ${textClass}">${amount > 0 ? formatNumber(amount) : '-'}</div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function updateProjectTable(data, total) {
            const tbody = document.getElementById('projectTable');

            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted"><iconify-icon icon="iconamoon:file-search-duotone" class="fs-48 d-block mb-2"></iconify-icon>ไม่มีข้อมูลในปีที่เลือก</td></tr>';
                projectPieChart.updateSeries([]);
                return;
            }

            tbody.innerHTML = data.map((item, idx) => `
                <tr>
                    <td>${idx + 1}</td>
                    <td>${escapeHtml(item.project_name)}</td>
                    <td class="text-center">${item.count}</td>
                    <td class="text-end fw-semibold text-primary">${formatCurrency(item.amount)}</td>
                    <td class="text-end">
                        <div class="progress" style="height: 6px; width: 80px; display: inline-block;">
                            <div class="progress-bar bg-primary" style="width: ${item.percent}%"></div>
                        </div>
                        <span class="ms-1">${item.percent}%</span>
                    </td>
                </tr>
            `).join('');
        }

        function truncateText(text, max) {
            return text.length > max ? text.substring(0, max) + '...' : text;
        }

        function exportCSV() {
            if (donations.length === 0) {
                showWarning('ไม่มีข้อมูลให้ส่งออก');
                return;
            }

            const year = document.getElementById('reportYear').value;

            const headers = ['เดือน', 'วันที่', 'Ref', 'ผู้บริจาค', 'โครงการ', 'จำนวนเงิน', 'สถานะ'];
            const rows = donations.map(d => {
                const date = new Date(d.issued_at);
                return [
                    monthNamesFull[date.getMonth()],
                    formatThaiDateShort(d.issued_at),
                    d.ref1 || '',
                    d.donor_name || '',
                    d.project_name || '',
                    d.amount || 0,
                    d.status === 'CONFIRMED' ? 'ยืนยันแล้ว' : 'รอยืนยัน'
                ];
            });

            const csvContent = '\uFEFF' + [headers, ...rows].map(row => row.join(',')).join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `รายงานประจำปี_${parseInt(year) + 543}.csv`;
            link.click();

            showSuccess('ส่งออกไฟล์ CSV สำเร็จ');
        }

        function exportCSVCmu() {
            if (donations.length === 0) {
                showWarning('ไม่มีข้อมูลให้ส่งออก');
                return;
            }

            const year = document.getElementById('reportYear').value;

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
                'อาชีพ',         // Blank
                'วันเกิด'        // Blank
            ];

            const rows = donations.map((d, i) => [
                i + 1,
                d.receipt_no || '',
                d.issued_at ? d.issued_at.split(' ')[0] : '',
                d.amount || 0,
                d.project_name || '',
                d.amount || 0,
                d.tax_id || '',
                d.title || '',
                d.first_name || '',
                d.last_name || '',
                d.address_line || '',
                '',
                '',
                '',
                '',
                d.district || '',
                d.amphure || '',
                d.province || '',
                d.zip_code || '',
                d.phone || '',
                '',
                ''
            ]);

            const csvContent = '\uFEFF' + [headers, ...rows].map(row => {
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
            link.download = `รายงานประจำปี_CVS_CMU_${parseInt(year) + 543}.csv`;
            link.click();

            showSuccess('ส่งออกไฟล์ CVS-CMU สำเร็จ');
        }
    </script>

</body>

</html>