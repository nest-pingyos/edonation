<?php include 'partials/main.php'; ?>
<?php requireAuth(); ?>

<!doctype html>
<html lang="th">

<head>
    <?php
    $title = "Dashboard";
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
                $pageTitle = "Dashboard";
                $subTitle = "ภาพรวม";
                include 'partials/page-title.php'; ?>

                <!-- Year Filter & Stats Row -->
                <!-- Filters Row -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">เลือกปี</label>
                        <select class="form-select" id="dashboardYear" onchange="loadDashboardData()">
                            <?php
                            $curYear = intval(date('Y'));
                            for ($y = $curYear; $y >= 2023; $y--) {
                                $thaiYear = $y + 543;
                                $selected = ($y == $curYear) ? 'selected' : '';
                                echo "<option value='$y' $selected>ปี $thaiYear</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">เลือกเดือน</label>
                        <select class="form-select" id="dashboardMonth" onchange="loadDashboardData()">
                            <option value="">ทุกเดือน</option>
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
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold">เลือกโครงการ</label>
                        <select class="form-select" id="dashboardProject" onchange="loadDashboardData()">
                            <option value="">ทุกโครงการ</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100" onclick="loadDashboardData()">
                            <iconify-icon icon="solar:refresh-bold" class="me-1"></iconify-icon> อัปเดต
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-white bg-opacity-25 rounded">
                                        <iconify-icon icon="iconamoon:trend-up-duotone"
                                            class="avatar-title text-white fs-32"></iconify-icon>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3 class="mb-0 text-white" id="stat-amount-year">-</h3>
                                        <p class="mb-0 opacity-75">ยอดบริจาคตามตัวกรอง (บาท)</p>
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
                                        <iconify-icon icon="iconamoon:profile-circle-duotone"
                                            class="avatar-title text-success fs-32"></iconify-icon>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3 class="mb-0" id="stat-donors">-</h3>
                                        <p class="text-muted mb-0">ผู้บริจาคทั้งหมด (คน)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-soft-info rounded">
                                        <iconify-icon icon="iconamoon:lightning-2-duotone"
                                            class="avatar-title text-info fs-32"></iconify-icon>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3 class="mb-0" id="stat-total-all">-</h3>
                                        <p class="text-muted mb-0">ยอดบริจาครวมทุกปี (บาท)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row">
                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">ยอดบริจาครายเดือน</h4>
                                <span id="monthlyChartYearBadge" class="badge bg-soft-primary text-primary px-2"></span>
                            </div>
                            <div class="card-body">
                                <div id="monthlyChart" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">ยอดบริจาคตามโครงการ</h4>
                            </div>
                            <div class="card-body">
                                <div id="projectChart" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Donations & Quick Actions & Payments -->
                <div class="row">
                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">การบริจาคล่าสุด</h4>
                                <a href="donations-list.php" class="btn btn-sm btn-soft-primary">ดูทั้งหมด</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>ผู้บริจาค</th>
                                                <th>โครงการ</th>
                                                <th class="text-end">จำนวน</th>
                                                <th>เวลา</th>
                                                <th>สถานะ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="recentDonations">
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <div class="spinner-border spinner-border-sm text-primary"></div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <!-- Payment Method Distribution -->
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">ช่องทางการชำระเงิน</h4>
                            </div>
                            <div class="card-body">
                                <div id="paymentChart" style="height: 250px;"></div>
                            </div>
                        </div>

                        <!-- Quick Actions Menu -->
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">เมนูด่วน</h4>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <a href="receipts-generate.php"
                                            class="d-block p-3 border rounded text-center text-decoration-none bg-light-hover">
                                            <iconify-icon icon="iconamoon:invoice-duotone"
                                                class="fs-32 text-primary"></iconify-icon>
                                            <div class="mt-2 text-dark small fw-medium">ออกใบเสร็จ</div>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="projects-list.php"
                                            class="d-block p-3 border rounded text-center text-decoration-none bg-light-hover">
                                            <iconify-icon icon="iconamoon:folder-duotone"
                                                class="fs-32 text-success"></iconify-icon>
                                            <div class="mt-2 text-dark small fw-medium">โครงการ</div>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="members-search.php"
                                            class="d-block p-3 border rounded text-center text-decoration-none bg-light-hover">
                                            <iconify-icon icon="iconamoon:search-duotone"
                                                class="fs-32 text-warning"></iconify-icon>
                                            <div class="mt-2 text-dark small fw-medium">ค้นหาสมาชิก</div>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="reports-daily.php"
                                            class="d-block p-3 border rounded text-center text-decoration-none bg-light-hover">
                                            <iconify-icon icon="iconamoon:3d-duotone"
                                                class="fs-32 text-info"></iconify-icon>
                                            <div class="mt-2 text-dark small fw-medium">รายงาน</div>
                                        </a>
                                    </div>
                                </div>
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
        let monthlyChart, projectChart, paymentChart;

        // Get fiscal year type from settings (default to calendar)
        const fiscalYearType = 'calendar';

        // Month names based on calendar year
        const monthNames = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

        document.addEventListener('DOMContentLoaded', function () {
            initCharts();
            loadProjects();
            loadDashboardData();
        });

        async function loadProjects() {
            try {
                const res = await apiGet('/projects?limit=500&status=active');
                const select = document.getElementById('dashboardProject');
                if (res.data) {
                    res.data.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.project_number;
                        opt.textContent = `[${p.project_number}] ${p.project_name}`;
                        select.appendChild(opt);
                    });
                }
            } catch (e) { console.error('Load projects failed', e); }
        }

        async function loadDashboardData() {
            try {
                const year = document.getElementById('dashboardYear').value;
                const month = document.getElementById('dashboardMonth').value;
                const project = document.getElementById('dashboardProject').value;

                document.getElementById('monthlyChartYearBadge').textContent = 'ปี ' + (parseInt(year) + 543);

                // 1. Fetch Summary & Analytics Data with Year Filter and Fiscal Type
                const summaryRes = await apiGet(`/reports/summary`, {
                    year: year,
                    month: month,
                    project: project,
                    fiscal_type: fiscalYearType
                });
                const summary = summaryRes.data || {};

                // Update Stats (Original UI IDs)
                if (document.getElementById('stat-donations')) {
                    document.getElementById('stat-donations').textContent = formatNumber(summary.today?.count || 0);
                }
                document.getElementById('stat-amount-year').textContent = formatNumber(summary.selected_year?.total || 0);
                document.getElementById('stat-donors').textContent = formatNumber(summary.all_time?.members || 0);
                document.getElementById('stat-total-all').textContent = '฿' + formatNumber(summary.all_time?.total || 0);

                // Update Charts with real data
                if (summary.charts) {
                    // Update Monthly Area Chart
                    monthlyChart.updateSeries([{
                        name: 'ยอดบริจาค',
                        data: summary.charts.monthly || new Array(12).fill(0)
                    }]);

                    // Update Projects Donut Chart
                    const projects = summary.charts.projects || [];
                    if (projects.length > 0) {
                        projectChart.updateOptions({
                            labels: projects.map(p => p.project_name.substring(0, 20) + (p.project_name.length > 20 ? '...' : ''))
                        });
                        projectChart.updateSeries(projects.map(p => parseFloat(p.total)));
                    }

                    // Update Payments Chart
                    const payments = summary.charts.payments || [];
                    if (payments.length > 0) {
                        paymentChart.updateSeries(payments.map(p => parseFloat(p.total)));
                        paymentChart.updateOptions({
                            labels: payments.map(p => p.payby || 'ไม่ระบุ')
                        });
                    }
                }

                // 2. Fetch Recent Donations (Pull from actual Receipts as requested)
                const receiptParams = { limit: 5 };
                if (project) receiptParams.project = project;
                if (month) {
                    const fromDate = `${year}-${String(month).padStart(2, '0')}-01`;
                    const toDate = new Date(year, month, 0).toISOString().split('T')[0];
                    receiptParams.from = fromDate;
                    receiptParams.to = toDate;
                }
                const donationsRes = await apiGet('/receipts', receiptParams);
                renderRecentDonations(donationsRes.data || []);

            } catch (error) {
                console.error('Dashboard error:', error);
                showError('ไม่สามารถดึงข้อมูลแดชบอร์ดได้');
            }
        }

        function initCharts() {
            // Monthly Area Chart
            monthlyChart = new ApexCharts(document.querySelector("#monthlyChart"), {
                chart: { type: 'area', height: 300, toolbar: { show: false } },
                series: [{ name: 'ยอดบริจาค', data: new Array(12).fill(0) }],
                xaxis: { categories: monthNames },
                colors: ['#1c84ee'],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
                stroke: { curve: 'smooth', width: 2 },
                dataLabels: { enabled: false },
                tooltip: { y: { formatter: val => '฿' + formatNumber(val) } }
            });
            monthlyChart.render();

            // Project Donut Chart
            projectChart = new ApexCharts(document.querySelector("#projectChart"), {
                chart: { type: 'donut', height: 300 },
                series: [],
                labels: [],
                colors: ['#1c84ee', '#22c55e', '#f9b931', '#4ecac2', '#ef5f5f'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: true, formatter: val => val.toFixed(0) + '%' }
            });
            projectChart.render();

            // Payment Polar Area Chart
            paymentChart = new ApexCharts(document.querySelector("#paymentChart"), {
                chart: { type: 'polarArea', height: 250 },
                series: [],
                labels: [],
                colors: ['#1c84ee', '#f9b931', '#22c55e', '#4ecac2'],
                legend: { position: 'bottom' },
                stroke: { colors: ['#fff'] },
                fill: { opacity: 0.8 },
                yaxis: { show: false }
            });
            paymentChart.render();
        }

        function renderRecentDonations(donations) {
            const tbody = document.getElementById('recentDonations');
            if (!donations || donations.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">ไม่มีข้อมูลล่าสุด</td></tr>';
                return;
            }

            tbody.innerHTML = donations.map(d => `
                <tr>
                    <td>
                        <span>${escapeHtml(d.payer_name || 'ไม่ระบุชื่อ')}</span>
                    </td>
                    <td><div class="text-truncate" style="max-width: 200px;">${escapeHtml(d.project_name || '-')}</div></td>
                    <td class="text-end fw-semibold text-primary">฿${formatNumber(d.amount)}</td>
                    <td>${formatTimeAgo(d.issued_at)}</td>
                    <td>${getReceiptStatusBadge(d.status == 'CANCELLED' ? 'cancelled' : 'issued')}</td>
                </tr>
            `).join('');
        }

        function formatTimeAgo(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);
            if (diff < 60) return 'เมื่อสักครู่';
            if (diff < 3600) return Math.floor(diff / 60) + ' น.ที่แล้ว';
            if (diff < 86400) return Math.floor(diff / 3600) + ' ชม.ที่แล้ว';
            return formatThaiDateShort(dateStr);
        }

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    </script>

    <style>
        .bg-light-hover:hover {
            background-color: #f8f9fa !important;
        }
    </style>

</body>

</html>