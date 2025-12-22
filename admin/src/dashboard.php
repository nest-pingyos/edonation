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

                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-soft-primary rounded">
                                        <iconify-icon icon="iconamoon:heart-duotone"
                                            class="avatar-title text-primary fs-32"></iconify-icon>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3 class="mb-0" id="stat-donations">-</h3>
                                        <p class="text-muted mb-0">การบริจาควันนี้</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-white bg-opacity-25 rounded">
                                        <iconify-icon icon="iconamoon:trend-up-duotone"
                                            class="avatar-title text-white fs-32"></iconify-icon>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3 class="mb-0 text-white" id="stat-amount">-</h3>
                                        <p class="mb-0 opacity-75">ยอดบริจาควันนี้ (บาท)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-soft-success rounded">
                                        <iconify-icon icon="iconamoon:folder-duotone"
                                            class="avatar-title text-success fs-32"></iconify-icon>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3 class="mb-0" id="stat-projects">-</h3>
                                        <p class="text-muted mb-0">โครงการทั้งหมด</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-soft-info rounded">
                                        <iconify-icon icon="iconamoon:profile-circle-duotone"
                                            class="avatar-title text-info fs-32"></iconify-icon>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3 class="mb-0" id="stat-donors">-</h3>
                                        <p class="text-muted mb-0">ผู้บริจาคทั้งหมด</p>
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
                                <select class="form-select form-select-sm w-auto" id="chartYear">
                                    <option value="2024">2567</option>
                                    <option value="2023">2566</option>
                                </select>
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

                <!-- Recent Donations & Quick Actions -->
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
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">เมนูด่วน</h4>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <a href="donations-list.php"
                                            class="d-block p-3 border rounded text-center text-decoration-none">
                                            <iconify-icon icon="iconamoon:heart-duotone"
                                                class="fs-32 text-primary"></iconify-icon>
                                            <div class="mt-2 text-dark">การบริจาค</div>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="projects-list.php"
                                            class="d-block p-3 border rounded text-center text-decoration-none">
                                            <iconify-icon icon="iconamoon:folder-duotone"
                                                class="fs-32 text-success"></iconify-icon>
                                            <div class="mt-2 text-dark">โครงการ</div>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="news-list.php"
                                            class="d-block p-3 border rounded text-center text-decoration-none">
                                            <iconify-icon icon="iconamoon:news-duotone"
                                                class="fs-32 text-info"></iconify-icon>
                                            <div class="mt-2 text-dark">ข่าวสาร</div>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="members-search.php"
                                            class="d-block p-3 border rounded text-center text-decoration-none">
                                            <iconify-icon icon="iconamoon:search-duotone"
                                                class="fs-32 text-warning"></iconify-icon>
                                            <div class="mt-2 text-dark">ค้นหาสมาชิก</div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">โครงการยอดนิยม</h4>
                            </div>
                            <div class="card-body" id="topProjects">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm text-primary"></div>
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
        let monthlyChart, projectChart;

        document.addEventListener('DOMContentLoaded', function () {
            loadDashboardData();
            initCharts();
        });

        async function loadDashboardData() {
            try {
                // Load donations
                const donationsRes = await apiGet('/donations?limit=10');
                const donations = donationsRes.data || [];

                // Load projects
                const projectsRes = await apiGet('/projects');
                const projects = projectsRes.data || [];

                // Update stats
                document.getElementById('stat-donations').textContent = donations.length;
                document.getElementById('stat-amount').textContent = formatNumber(donations.reduce((sum, d) => sum + parseFloat(d.amount || 0), 0));
                document.getElementById('stat-projects').textContent = projects.length;
                document.getElementById('stat-donors').textContent = '-';

                // Render recent donations
                renderRecentDonations(donations.slice(0, 5));

                // Render top projects
                renderTopProjects(projects.slice(0, 5));

            } catch (error) {
                console.error('Dashboard error:', error);
            }
        }

        function initCharts() {
            // Monthly Chart
            if (typeof ApexCharts !== 'undefined') {
                monthlyChart = new ApexCharts(document.getElementById('monthlyChart'), {
                    chart: { type: 'area', height: 300, toolbar: { show: false } },
                    series: [{
                        name: 'ยอดบริจาค',
                        data: [52000, 48000, 61000, 45000, 53000, 72000, 68000, 59000, 81000, 76000, 89000, 95000]
                    }],
                    xaxis: {
                        categories: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.']
                    },
                    colors: ['#1c84ee'],
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
                    stroke: { curve: 'smooth', width: 2 },
                    dataLabels: { enabled: false },
                    tooltip: {
                        y: { formatter: function (val) { return '฿' + formatNumber(val); } }
                    }
                });
                monthlyChart.render();

                // Project Chart
                projectChart = new ApexCharts(document.getElementById('projectChart'), {
                    chart: { type: 'donut', height: 300 },
                    series: [42, 28, 18, 12],
                    labels: ['ทุนการศึกษา', 'อาคารเรียน', 'อุปกรณ์การแพทย์', 'อื่นๆ'],
                    colors: ['#1c84ee', '#22c55e', '#f9b931', '#4ecac2'],
                    legend: { position: 'bottom' },
                    dataLabels: { enabled: true, formatter: function (val) { return val.toFixed(0) + '%'; } }
                });
                projectChart.render();
            }
        }

        function renderRecentDonations(donations) {
            const tbody = document.getElementById('recentDonations');

            if (!donations || donations.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">ไม่มีข้อมูล</td></tr>';
                return;
            }

            tbody.innerHTML = donations.map(d => `
        <tr>
            <td>
                <div class="d-flex align-items-center">
                    <div class="avatar-sm bg-soft-primary rounded-circle me-2">
                        <span class="avatar-title text-primary">${(d.donor_name || d.name || 'N')[0].toUpperCase()}</span>
                    </div>
                    <span>${escapeHtml(d.donor_name || d.name || 'ไม่ระบุชื่อ')}</span>
                </div>
            </td>
            <td>${escapeHtml(d.project_name || d.project_number || '-')}</td>
            <td class="text-end fw-semibold text-primary">${formatCurrency(d.amount)}</td>
            <td>${formatTimeAgo(d.transaction_date || d.created_at)}</td>
            <td>${getStatusBadge(d.status)}</td>
        </tr>
    `).join('');
        }

        function renderTopProjects(projects) {
            const container = document.getElementById('topProjects');

            if (!projects || projects.length === 0) {
                container.innerHTML = '<div class="text-center py-3 text-muted">ไม่มีข้อมูล</div>';
                return;
            }

            container.innerHTML = projects.map((p, i) => `
        <div class="d-flex align-items-center mb-3">
            <span class="badge bg-soft-primary text-primary rounded-circle me-2" style="width: 28px; height: 28px; line-height: 28px;">${i + 1}</span>
            <div class="flex-grow-1">
                <div class="fw-medium">${escapeHtml(p.project_name)}</div>
                <small class="text-muted">${p.project_number}</small>
            </div>
        </div>
    `).join('');
        }

        function getStatusBadge(status) {
            if (status === 'CONFIRMED') return '<span class="badge badge-soft-success">ยืนยัน</span>';
            return '<span class="badge badge-soft-warning">รอยืนยัน</span>';
        }

        function formatTimeAgo(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);

            if (diff < 60) return 'เมื่อสักครู่';
            if (diff < 3600) return Math.floor(diff / 60) + ' นาทีที่แล้ว';
            if (diff < 86400) return Math.floor(diff / 3600) + ' ชั่วโมงที่แล้ว';

            return formatThaiDateShort(dateStr);
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