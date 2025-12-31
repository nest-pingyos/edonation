<?php include 'partials/main.php'; ?>
<?php requireAuth(); ?>

<!doctype html>
<html lang="th">

<head>
    <?php
    $title = "รายงานประจำเดือน";
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
                $pageTitle = "รายงานประจำเดือน";
                $subTitle = "รายงาน";
                include 'partials/page-title.php'; ?>

                <!-- Month/Year Filter -->
                <div class="row mb-4">
                    <div class="col-auto">
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white">
                                <iconify-icon icon="iconamoon:calendar-2-duotone"></iconify-icon>
                            </span>
                            <select id="reportMonth" class="form-select" style="max-width: 120px;">
                                <option value="1">มกราคม</option>
                                <option value="2">กุมภาพันธ์</option>
                                <option value="3">มีนาคม</option>
                                <option value="4">เมษายน</option>
                                <option value="5">พฤษภาคม</option>
                                <option value="6">มิถุนายน</option>
                                <option value="7">กรกฎาคม</option>
                                <option value="8">สิงหาคม</option>
                                <option value="9">กันยายน</option>
                                <option value="10">ตุลาคม</option>
                                <option value="11">พฤศจิกายน</option>
                                <option value="12">ธันวาคม</option>
                            </select>
                            <select id="reportYear" class="form-select" style="max-width: 100px;"></select>
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
                                        <iconify-icon icon="iconamoon:calendar-2-duotone"
                                            class="text-success fs-24"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="stat-best-day">-</h3>
                                        <p class="text-muted mb-0">วันที่ยอดสูงสุด</p>
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
                                        <p class="text-muted mb-0">เฉลี่ยต่อวัน</p>
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
                                    ยอดบริจาครายวัน
                                </h4>
                            </div>
                            <div class="card-body">
                                <div id="dailyChart" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Table by Project -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
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
                                                <th colspan="2" class="text-end">รวมทั้งหมด</th>
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
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">สัดส่วนตามโครงการ</h4>
                            </div>
                            <div class="card-body">
                                <div id="projectPieChart" style="height: 300px;"></div>
                            </div>
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
        let dailyChart, projectPieChart;

        document.addEventListener('DOMContentLoaded', function () {
            initYearSelector();
            initCharts();
            loadReport();
        });

        function initYearSelector() {
            const select = document.getElementById('reportYear');
            const currentYear = new Date().getFullYear();
            const currentMonth = new Date().getMonth() + 1;

            for (let y = currentYear; y >= currentYear - 5; y--) {
                const option = document.createElement('option');
                option.value = y;
                option.textContent = y + 543; // Buddhist year
                select.appendChild(option);
            }

            document.getElementById('reportMonth').value = currentMonth;
        }

        function initCharts() {
            // Daily chart
            dailyChart = new ApexCharts(document.getElementById('dailyChart'), {
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: { show: false }
                },
                series: [{
                    name: 'ยอดบริจาค',
                    data: []
                }],
                xaxis: {
                    categories: []
                },
                colors: ['#1c84ee'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.1
                    }
                },
                stroke: { curve: 'smooth', width: 2 },
                dataLabels: { enabled: false },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return '฿' + formatNumber(val);
                        }
                    }
                }
            });
            dailyChart.render();

            // Pie chart
            projectPieChart = new ApexCharts(document.getElementById('projectPieChart'), {
                chart: {
                    type: 'donut',
                    height: 300
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
            const month = document.getElementById('reportMonth').value;
            const year = document.getElementById('reportYear').value;

            const tbody = document.getElementById('projectTable');
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';

            try {
                // ใช้ API ใหม่ที่ดึงจาก edonation_receipts เป็นตารางหลัก
                const response = await apiGet(`/reports/monthly?month=${month}&year=${year}`);
                const data = response.data || {};
                donations = data.receipts || [];
                const stats = data.stats || {};
                const dailyData = data.daily_data || [];
                const projectSummary = data.project_summary || [];

                // Update stats from API
                document.getElementById('stat-count').textContent = formatNumber(stats.count || 0);
                document.getElementById('stat-total').textContent = formatNumber(stats.total_amount || 0);
                document.getElementById('stat-best-day').textContent = stats.best_day ? `วันที่ ${stats.best_day}` : '-';
                document.getElementById('stat-avg').textContent = formatNumber(stats.average || 0);
                document.getElementById('foot-count').textContent = stats.count || 0;
                document.getElementById('foot-total').textContent = formatCurrency(stats.total_amount || 0);

                // Update daily chart
                const lastDay = dailyData.length;
                const days = Array.from({ length: lastDay }, (_, i) => (i + 1).toString());
                dailyChart.updateOptions({
                    xaxis: { categories: days }
                });
                dailyChart.updateSeries([{
                    name: 'ยอดบริจาค',
                    data: dailyData
                }]);

                // Update project summary table
                updateProjectTable(projectSummary, stats.total_amount || 0);

                // Update pie chart
                if (projectSummary.length > 0) {
                    projectPieChart.updateOptions({
                        labels: projectSummary.slice(0, 6).map(p => truncateText(p.project_name, 20))
                    });
                    projectPieChart.updateSeries(projectSummary.slice(0, 6).map(p => p.amount));
                }

            } catch (error) {
                showError(error.message);
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger">${error.message}</td></tr>`;
            }
        }

        function updateProjectTable(data, total) {
            const tbody = document.getElementById('projectTable');

            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted"><iconify-icon icon="iconamoon:file-search-duotone" class="fs-48 d-block mb-2"></iconify-icon>ไม่มีข้อมูลในเดือนที่เลือก</td></tr>';
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

            const month = document.getElementById('reportMonth').value;
            const year = document.getElementById('reportYear').value;
            const monthNames = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

            const headers = ['วันที่', 'Ref', 'ผู้บริจาค', 'โครงการ', 'จำนวนเงิน', 'สถานะ'];
            const rows = donations.map(d => [
                formatThaiDateShort(d.transaction_date || d.created_at),
                d.billPaymentRef1 || d.ref || '',
                d.donor_name || d.name || '',
                d.project_name || d.project_number || '',
                d.amount || 0,
                d.status === 'CONFIRMED' ? 'ยืนยันแล้ว' : 'รอยืนยัน'
            ]);

            const csvContent = '\uFEFF' + [headers, ...rows].map(row => row.join(',')).join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `รายงานประจำเดือน_${monthNames[month]}_${parseInt(year) + 543}.csv`;
            link.click();

            showSuccess('ส่งออกไฟล์ CSV สำเร็จ');
        }

        function exportCSVCmu() {
            if (donations.length === 0) {
                showWarning('ไม่มีข้อมูลให้ส่งออก');
                return;
            }

            const month = document.getElementById('reportMonth').value;
            const year = document.getElementById('reportYear').value;
            const monthNames = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

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
                d.issued_at ? d.issued_at.split(' ')[0] : (d.transaction_date || d.created_at || '').split(' ')[0],
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
            link.download = `รายงาน_CVS_CMU_${monthNames[month]}_${parseInt(year) + 543}.csv`;
            link.click();

            showSuccess('ส่งออกไฟล์ CVS-CMU สำเร็จ');
        }
    </script>

</body>

</html>