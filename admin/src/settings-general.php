<?php include 'partials/main.php'; ?>
<?php requireAuth(); ?>

<!doctype html>
<html lang="th">

<head>
    <?php
    $title = "ตั้งค่าทั่วไป";
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
                $pageTitle = "ตั้งค่าทั่วไป";
                $subTitle = "ตั้งค่าระบบ";
                include 'partials/page-title.php'; ?>

                <div class="row">
                    <!-- Fiscal Year Settings -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <iconify-icon icon="iconamoon:calendar-2-duotone" class="me-2"></iconify-icon>
                                    ตั้งค่าปีสำหรับรายงาน
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-4">
                                    เลือกรูปแบบการคำนวณปีสำหรับรายงานและ Dashboard
                                </p>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">รูปแบบปีรายงาน</label>
                                    <div class="d-flex flex-column gap-3">
                                        <div class="form-check form-check-success">
                                            <input class="form-check-input" type="radio" name="fiscalYearType"
                                                id="fiscalCalendar" value="calendar" checked>
                                            <label class="form-check-label" for="fiscalCalendar">
                                                <strong>ปีปฏิทิน (Calendar Year)</strong>
                                                <small class="d-block text-muted">
                                                    1 มกราคม - 31 ธันวาคม<br>
                                                    เช่น ปี 2569 = 1 ม.ค. 2569 - 31 ธ.ค. 2569
                                                </small>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info mb-4">
                                    <div class="d-flex">
                                        <iconify-icon icon="iconamoon:information-circle-duotone"
                                            class="fs-20 me-2"></iconify-icon>
                                        <div>
                                            <strong>หมายเหตุ:</strong> การเปลี่ยนแปลงนี้จะมีผลกับ:
                                            <ul class="mb-0 mt-2">
                                                <li>Dashboard ภาพรวม</li>
                                                <li>รายงานประจำปี</li>
                                                <li>รายงานประจำเดือน</li>
                                                <li>การแสดงผลกราฟรายเดือน</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-primary" onclick="saveFiscalYearSettings()">
                                    <iconify-icon icon="iconamoon:check-duotone" class="me-1"></iconify-icon>
                                    บันทึกการตั้งค่า
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- API Information -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <iconify-icon icon="iconamoon:link-external-duotone" class="me-2"></iconify-icon>
                                    ข้อมูล API
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-4">
                                    รายละเอียดการใช้งาน API สำหรับนักพัฒนา
                                </p>

                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <td class="text-muted">API Version</td>
                                                <td><span class="badge bg-primary">2.0</span></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Base URL</td>
                                                <td><code>/edonation/api/v1</code></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">รูปแบบปี</td>
                                                <td><span class="badge bg-info">ค.ศ. (CE)</span></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">ตัวอย่างการใช้งาน</td>
                                                <td><code>?year=2026</code> (ปี 2569)</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="alert alert-warning mb-0 mt-3">
                                    <iconify-icon icon="iconamoon:attention-circle-duotone" class="me-2"></iconify-icon>
                                    <strong>สำคัญ:</strong> API ใช้ปี ค.ศ. (เช่น 2026) ไม่ใช่ พ.ศ. (2569)
                                </div>
                            </div>
                        </div>

                        <!-- Current Settings Display -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <iconify-icon icon="iconamoon:eye-duotone" class="me-2"></iconify-icon>
                                    การตั้งค่าปัจจุบัน
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h4 class="text-primary mb-1" id="currentFiscalYear">-</h4>
                                        <p class="text-muted mb-0">ปีรายงานปัจจุบัน</p>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-success mb-1" id="fiscalYearRange">-</h4>
                                        <p class="text-muted mb-0">ช่วงวันที่</p>
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

    <script>
        // Load settings on page load
        document.addEventListener('DOMContentLoaded', function () {
            loadSettings();
            updateCurrentYearDisplay();
        });

        function loadSettings() {
            const savedType = localStorage.getItem('fiscalYearType') || 'calendar';
            if (document.querySelector(`input[value="${savedType}"]`)) {
                document.querySelector(`input[value="${savedType}"]`).checked = true;
            }
            updateCurrentYearDisplay();
        }

        function updateCurrentYearDisplay() {
            const now = new Date();
            const currentYear = now.getFullYear();

            // Calendar Year: Jan-Dec
            const fiscalYear = currentYear;
            const startDate = `1 ม.ค. ${fiscalYear + 543}`;
            const endDate = `31 ธ.ค. ${fiscalYear + 543}`;

            document.getElementById('currentFiscalYear').textContent = fiscalYear + 543;
            document.getElementById('fiscalYearRange').textContent = `${startDate} - ${endDate}`;
        }

        function saveFiscalYearSettings() {
            const fiscalType = document.querySelector('input[name="fiscalYearType"]:checked').value;

            // Save to localStorage
            localStorage.setItem('fiscalYearType', fiscalType);

            // Show success message
            showSuccess('การตั้งค่าปีสำหรับรายงานถูกบันทึกแล้ว');

            updateCurrentYearDisplay();
        }

        // Update display when radio changes
        document.querySelectorAll('input[name="fiscalYearType"]').forEach(radio => {
            radio.addEventListener('change', updateCurrentYearDisplay);
        });
    </script>
</body>

</html>