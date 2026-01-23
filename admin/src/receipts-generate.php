<?php include 'partials/main.php'; ?>
<?php requireAuth(); ?>
<?php require_once __DIR__ . '/../../config/autoprovince.php'; ?>

<!doctype html>
<html lang="th">

<head>
    <?php
    $title = "ออกใบเสร็จรับเงิน";
    include 'partials/title-meta.php'; ?>

    <?php include 'partials/head-css.php'; ?>
    <?php autoprovinceCss(); ?>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <style>
        .receipt-preview {
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            padding: 24px;
            position: sticky;
            top: 90px;
        }

        .receipt-preview-inner {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .receipt-header {
            border-bottom: 2px solid #1c84ee;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dotted #dee2e6;
        }

        .receipt-row:last-child {
            border-bottom: none;
        }

        .receipt-label {
            color: #6c757d;
            font-size: 13px;
        }

        .receipt-value {
            font-weight: 500;
            text-align: right;
        }

        .amount-display {
            font-size: 28px;
            font-weight: 700;
            color: #1c84ee;
        }

        .form-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .form-section-title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .lookup-results {
            max-height: 200px;
            overflow-y: auto;
        }

        .donation-item {
            cursor: pointer;
            transition: all 0.2s;
        }

        .donation-item:hover {
            background: #e7f3ff !important;
        }

        .donation-item.selected {
            background: #1c84ee !important;
            color: white;
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
                $pageTitle = "ออกใบเสร็จรับเงิน";
                $subTitle = "ใบเสร็จ";
                include 'partials/page-title.php'; ?>

                <form id="receiptForm" onsubmit="handleSubmit(event)">
                    <div class="row">
                        <!-- Form Column -->
                        <div class="col-12">

                            <!-- ค้นหาผู้บริจาค -->
                            <div class="card mb-4">
                                <div class="card-header bg-soft-primary">
                                    <h5 class="card-title mb-0 text-primary">
                                        ค้นหาผู้บริจาค
                                        <small class="text-muted fw-normal ms-2">(กรอกข้อมูลเพื่อดึงประวัติ)</small>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <!-- Search Type Selection -->
                                    <div class="mb-3 d-none">
                                        <label class="form-label d-block mb-2 text-muted small">ค้นหาจาก</label>
                                        <input type="hidden" id="searchType" value="all">
                                    </div>

                                    <!-- Search Input -->
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="searchQuery"
                                            placeholder="พิมพ์ชื่อ, นามสกุล หรือเลขบัตรประชาชน..."
                                            onkeypress="if(event.key==='Enter'){event.preventDefault(); searchDonor();}">
                                        <button class="btn btn-primary" type="button" onclick="searchDonor()">
                                            ค้นหา
                                        </button>
                                    </div>

                                    <!-- Search Results -->
                                    <div id="searchResults" class="mt-3" style="display: none;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted small">ผลการค้นหา</span>
                                            <span class="badge bg-primary rounded-pill" id="resultCount">0 รายการ</span>
                                        </div>
                                        <div class="table-responsive border rounded"
                                            style="max-height: 220px; overflow-y: auto;">
                                            <table class="table table-hover table-sm align-middle mb-0">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th class="ps-3">ชื่อผู้บริจาค</th>
                                                        <th>เลขบัตรประชาชน</th>
                                                        <th class="text-center" width="80"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="searchResultsBody">
                                                    <!-- Dynamic results -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- No Results Message -->
                                    <div id="noResults" class="text-center py-4" style="display: none;">
                                        <p class="text-muted mb-0 small">ไม่พบข้อมูล กรุณากรอกข้อมูลด้านล่าง</p>
                                    </div>
                                </div>
                            </div>

                            <!-- ข้อมูลผู้บริจาค -->
                            <div class="card mb-4">
                                <div class="card-header bg-soft-primary">
                                    <h5 class="card-title mb-0 text-primary">
                                        ข้อมูลผู้บริจาค
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" id="donation_id" name="donation_id">

                                    <div class="row g-3">



                                        <div class="col-md-6" id="affiliationGroup">
                                            <label class="form-label">ประเภท <span class="text-danger">*</span></label>
                                            <select class="form-select" id="affiliation" name="type">
                                                <option value="บุคคลทั่วไป">บุคคลทั่วไป</option>
                                                <option value="ศิษย์เก่าคณะพยาบาล มช.">ศิษย์เก่า มช.</option>
                                                <option value="บุคลากร อาจารย์คณะพยาบาล มช.">บุคลากร/อาจารย์</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6" id="affiliationSpacer" style="display:none;"></div>

                                        <div class="col-md-6" id="titleGroup">
                                            <label class="form-label">คำนำหน้า <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" id="title" name="title" required
                                                onchange="toggleDonorType()">
                                                <option value="">-- เลือก --</option>
                                                <optgroup label="บุคคลธรรมดา">
                                                    <option value="นาย">นาย</option>
                                                    <option value="นาง">นาง</option>
                                                    <option value="นางสาว">นางสาว</option>
                                                    <option value="ด.ช.">ด.ช.</option>
                                                    <option value="ด.ญ.">ด.ญ.</option>
                                                    <option value="อื่นๆ">อื่นๆ</option>
                                                </optgroup>
                                                <optgroup label="นิติบุคคล">
                                                    <option value="บริษัท">บริษัท</option>
                                                    <option value="ห้างหุ้นส่วน">ห้างหุ้นส่วน</option>
                                                    <option value="มูลนิธิ">มูลนิธิ</option>
                                                    <option value="สมาคม">สมาคม</option>
                                                </optgroup>
                                            </select>
                                        </div>


                                        <div class="col-md-6">
                                            <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="first_name" name="first_name"
                                                required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">นามสกุล <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="last_name" name="last_name"
                                                required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">
                                                เลขบัตรประชาชน / เลขผู้เสียภาษี
                                            </label>
                                            <input type="text" class="form-control" id="id_card" name="id_card"
                                                maxlength="13">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">เบอร์โทรศัพท์</label>
                                            <input type="tel" class="form-control" id="phone" name="phone"
                                                maxlength="10">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">อาชีพ</label>
                                            <input type="text" class="form-control" id="occupation" name="occupation">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">อีเมล</label>
                                            <input type="email" class="form-control" id="email" name="email">
                                        </div>
                                    </div>

                                    <!-- ที่อยู่ with AutoProvince -->
                                    <hr class="my-3">
                                    <h6 class="text-muted mb-3">
                                        ที่อยู่สำหรับใบเสร็จ
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">ที่อยู่ (บ้านเลขที่ ซอย ถนน)</label>
                                            <input type="text" class="form-control" id="address_line"
                                                name="address_line">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">จังหวัด</label>
                                            <select class="form-select" id="province" name="province">
                                                <option value="">-- เลือกจังหวัด --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">อำเภอ/เขต</label>
                                            <select class="form-select" id="district" name="district" disabled>
                                                <option value="">-- เลือกอำเภอ/เขต --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">ตำบล/แขวง</label>
                                            <select class="form-select" id="subdistrict" name="subdistrict" disabled>
                                                <option value="">-- เลือกตำบล/แขวง --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">รหัสไปรษณีย์</label>
                                            <input type="text" class="form-control" id="postcode" name="postcode"
                                                readonly>
                                        </div>
                                        <!-- Hidden Full Address for compatibility -->
                                        <input type="hidden" id="address" name="address">
                                    </div>

                                    <!-- ที่อยู่จัดส่งใบเสร็จ -->
                                    <hr class="my-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="text-muted mb-0">ที่อยู่จัดส่งใบเสร็จ <small>(ถ้ามี)</small></h6>
                                        <button type="button" class="btn btn-sm btn-outline-info"
                                            onclick="copyReceiptAddress()">
                                            <iconify-icon icon="solar:copy-bold-duotone"
                                                class="align-middle me-1"></iconify-icon>
                                            ใช้ที่อยู่เดียวกับใบเสร็จ
                                        </button>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">ที่อยู่ (บ้านเลขที่ ซอย ถนน)</label>
                                            <input type="text" class="form-control" id="ship_address_line"
                                                name="ship_address_line">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">จังหวัด</label>
                                            <select class="form-select" id="ship_province" name="ship_province">
                                                <option value="">-- เลือกจังหวัด --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">อำเภอ/เขต</label>
                                            <select class="form-select" id="ship_district" name="ship_district"
                                                disabled>
                                                <option value="">-- เลือกอำเภอ/เขต --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">ตำบล/แขวง</label>
                                            <select class="form-select" id="ship_subdistrict" name="ship_subdistrict"
                                                disabled>
                                                <option value="">-- เลือกตำบล/แขวง --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">รหัสไปรษณีย์</label>
                                            <input type="text" class="form-control" id="ship_postcode"
                                                name="ship_postcode" readonly>
                                        </div>
                                        <input type="hidden" id="shipping_address" name="shipping_address">
                                    </div>
                                </div>
                            </div>

                            <!-- ข้อมูลการบริจาค -->
                            <div class="card mb-4">
                                <div class="card-header bg-soft-primary">
                                    <h5 class="card-title mb-0 text-primary">
                                        ข้อมูลการบริจาค
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label">โครงการ <span class="text-danger">*</span></label>
                                            <select class="form-select" id="project_number" name="project_number"
                                                required onchange="handleProjectChange()">
                                                <option value="">-- เลือกโครงการ --</option>
                                            </select>
                                            <!-- Custom Project Name Input -->
                                            <input type="text" class="form-control mt-2" id="custom_project_name"
                                                name="custom_project_name" style="display: none;"
                                                placeholder="ระบุชื่อโครงการ">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">จำนวนเงิน (บาท) <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="amount" name="amount"
                                                oninput="handleAmountInput(this)" placeholder="0.00" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">วันที่บริจาค <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="donation_date"
                                                name="donation_date" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">ช่องทางการชำระเงิน</label>
                                            <select class="form-select" id="payment_method" name="payment_method">
                                                <option value="Cash">เงินสด/Cash</option>
                                                <option value="Prompt Pay">โอน/Prompt Pay</option>
                                                <option value="Cheque">เช็ค/Cheque</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">หมายเหตุ (ถ้ามี)</label>
                                            <input type="text" class="form-control" id="note" name="note"
                                                placeholder="เช่น เลขที่เช็ค, ข้อมูลเพิ่มเติม">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ตัวเลือกเพิ่มเติม -->
                            <div class="card mb-4">
                                <div class="card-header bg-soft-primary">
                                    <h5 class="card-title mb-0 text-primary">
                                        ตัวเลือกเพิ่มเติม
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="send_email">
                                        <label class="form-check-label" for="send_email">ส่งใบเสร็จทางอีเมล</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="print_receipt" checked>
                                        <label class="form-check-label"
                                            for="print_receipt">เปิดหน้าต่างพิมพ์ใบเสร็จหลังบันทึก</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="notify_line" checked>
                                        <label class="form-check-label" for="notify_line">แจ้งเตือนเจ้าหน้าที่
                                            (Line)</label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-3" id="submitBtn">
                                <div class="spinner-border spinner-border-sm d-none me-2" id="submitSpinner"
                                    role="status"></div>
                                ยืนยันข้อมูลและออกใบเสร็จ
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-5">
                    <div class="avatar-xl bg-soft-success rounded-circle mx-auto mb-4">
                        <span class="avatar-title text-success" style="font-size: 48px;">✓</span>
                    </div>
                    <h4 class="mb-2">ออกใบเสร็จสำเร็จ!</h4>
                    <p class="text-muted mb-4">
                        เลขที่ใบเสร็จ: <span class="fw-bold text-primary" id="result_receipt_no">-</span>
                    </p>
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="#" id="downloadPdfBtn" class="btn btn-primary" target="_blank">
                            ดาวน์โหลดใบเสร็จ
                        </a>
                        <button type="button" class="btn btn-success" onclick="createAnother()">
                            ออกใบเสร็จใหม่
                        </button>
                    </div>
                    <div class="mt-3">
                        <a href="receipts-list.php" class="text-muted">
                            กลับไปรายการใบเสร็จ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'partials/vendor-scripts.php'; ?>
    <script src="assets/js/api-helper.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>



    <!-- AutoProvince JS -->
    <?php autoprovinceJs(); ?>

    <script>
        let projects = [];
        let selectedDonation = null;

        const JURISTIC_TITLES = ['บริษัท', 'ห้างหุ้นส่วน', 'มูลนิธิ', 'สมาคม'];

        // Toggle between บุคคลธรรมดา and นิติบุคคล modes based on Title
        function toggleDonorType() {
            const title = document.getElementById('title').value;
            const juristicTitles = ['บริษัท', 'ห้างหุ้นส่วน', 'มูลนิธิ', 'สมาคม'];
            const isJuristic = juristicTitles.includes(title);

            const lastNameInput = document.getElementById('last_name');
            const lastNameGroup = lastNameInput.closest('.col-md-6');
            const firstNameLabel = document.querySelector('label[for="first_name"]') || document.getElementById('firstNameLabel');
            const idCardInput = document.getElementById('id_card');
            const idCardLabel = idCardInput.closest('.col-md-6').querySelector('label');

            if (isJuristic) {
                // นิติบุคคล mode
                document.getElementById('first_name').placeholder = 'ชื่อองค์กร/บริษัท';
                if (firstNameLabel) {
                    firstNameLabel.innerHTML = 'ชื่อองค์กร <span class="text-danger">*</span>';
                }

                // Hide last name for juristic
                if (lastNameGroup) {
                    lastNameGroup.style.display = 'none';
                }
                // Remove required for last_name
                lastNameInput.removeAttribute('required');

                // Change ID card to Tax ID
                if (idCardLabel) {
                    idCardLabel.textContent = 'เลขประจำตัวผู้เสียภาษี';
                }
                idCardInput.placeholder = 'เลขประจำตัวผู้เสียภาษี 13 หลัก';
                idCardInput.maxLength = 13;
            } else {
                // บุคคลธรรมดา mode
                document.getElementById('first_name').placeholder = 'ชื่อจริง';
                if (firstNameLabel) {
                    firstNameLabel.innerHTML = 'ชื่อ <span class="text-danger">*</span>';
                }

                // Show last name for individual
                if (lastNameGroup) {
                    lastNameGroup.style.display = '';
                }
                // Add back required for last_name
                lastNameInput.setAttribute('required', 'required');

                // Change back to ID card
                if (idCardLabel) {
                    idCardLabel.textContent = 'เลขบัตรประชาชน / เลขผู้เสียภาษี';
                }
                idCardInput.placeholder = 'เลขบัตรประชาชน 13 หลัก';
                idCardInput.maxLength = 13;
            }
        }

        let receiptAutoAddr, shippingAutoAddr;

        document.addEventListener('DOMContentLoaded', function () {
            // Init AutoProvince for Receipt
            receiptAutoAddr = new AutoProvince({
                provinceSelector: '#province',
                districtSelector: '#district',
                subdistrictSelector: '#subdistrict',
                postcodeSelector: '#postcode',
                onAddressComplete: function (addr) {
                    updateFullAddress();
                }
            });

            // Init AutoProvince for Shipping
            shippingAutoAddr = new AutoProvince({
                provinceSelector: '#ship_province',
                districtSelector: '#ship_district',
                subdistrictSelector: '#ship_subdistrict',
                postcodeSelector: '#ship_postcode',
                onAddressComplete: function (addr) {
                    updateShippingFullAddress();
                }
            });

            loadProjects();
            setDefaultDate();
        });

        // ===== AUTOPROVINCE FUNCTIONS =====

        function updateFullAddress() {
            if (receiptAutoAddr) {
                $('#address').val(receiptAutoAddr.formatAddress());
            }
        }

        function updateShippingFullAddress() {
            if (shippingAutoAddr) {
                $('#shipping_address').val(shippingAutoAddr.formatAddress());
            }
        }

        async function copyReceiptAddress() {
            if (!receiptAutoAddr || !shippingAutoAddr) return;

            const receiptData = receiptAutoAddr.getAddress();
            const addressLine = $('#address_line').val();

            if (!addressLine && !receiptData.province.id) {
                showError('กรุณากรอกที่อยู่ใบเสร็จก่อนสำเนา');
                return;
            }

            // Copy Address Line
            $('#ship_address_line').val(addressLine);

            // Copy Province -> District -> Subdistrict (Cascading)
            if (receiptData.province.id) {
                $('#ship_province').val(receiptData.province.id).trigger('change');

                // Wait for districts
                setTimeout(() => {
                    if (receiptData.district.id) {
                        $('#ship_district').val(receiptData.district.id).trigger('change');

                        // Wait for subdistricts
                        setTimeout(() => {
                            if (receiptData.subdistrict.id) {
                                $('#ship_subdistrict').val(receiptData.subdistrict.id).trigger('change');
                            }
                        }, 500);
                    }
                }, 500);
            }

            showSuccess('คัดลอกที่อยู่เรียบร้อย');
        }

        // Helper to set address from search result
        function setAddressFromText(addressText) {
            // If we have structured address, set address_line only
            // The dropdowns should be selected manually or via lookup
            $('#address_line').val(addressText);
            $('#address').val(addressText);
        }

        // Helper to prevent XSS
        function escapeHtml(text) {
            if (!text) return text;
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        async function loadProjects() {
            try {
                const response = await apiGet('/projects');
                projects = response.data || [];

                const select = document.getElementById('project_number');
                projects.forEach(p => {
                    const option = document.createElement('option');
                    option.value = p.project_number;
                    option.textContent = p.project_name;
                    option.dataset.name = p.project_name;
                    option.dataset.receiptName = p.project_receipt_name || p.project_name;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Failed to load projects:', error);
            }
        }

        function setDefaultDate() {
            document.getElementById('donation_date').value = new Date().toISOString().split('T')[0];
        }

        function setupFormListeners() {
            // Update preview on any input change
            const fields = ['title', 'first_name', 'last_name', 'id_card', 'project_number', 'amount', 'donation_date'];
            fields.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', updatePreview);
                    el.addEventListener('change', updatePreview);
                }
            });
        }

        function updatePreview() {
            // Functionality removed as per request
        }

        function formatIdCard(id) {
            if (!id || id.length !== 13) return id;
            return `${id[0]}-${id.substring(1, 5)}-${id.substring(5, 10)}-${id.substring(10, 12)}-${id[12]}`;
        }

        function handleAmountInput(input) {
            let value = input.value.replace(/[^0-9.]/g, '');
            let parts = value.split('.');
            let integerPart = parts[0];
            let decimalPart = parts.length > 1 ? '.' + parts[1].substring(0, 2) : '';

            if (integerPart !== '') {
                integerPart = Number(integerPart).toLocaleString('en-US');
            }
            input.value = integerPart + decimalPart;
        }

        // ===== NEW SEARCH FUNCTIONS =====

        function setSearchType(type) {
            document.getElementById('searchType').value = type;

            // Check the correct radio button
            const radioMap = {
                'all': 'searchTypeAll',
                'name': 'searchTypeName',
                'id_card': 'searchTypeIdCard'
            };
            const radioId = radioMap[type];
            if (radioId) {
                document.getElementById(radioId).checked = true;
            }

            // Update placeholder
            const placeholders = {
                'all': 'พิมพ์ชื่อ, นามสกุล หรือเลขบัตรประชาชน...',
                'name': 'พิมพ์ชื่อ หรือ นามสกุล...',
                'id_card': 'พิมพ์เลขบัตรประชาชน 13 หลัก...'
            };
            document.getElementById('searchQuery').placeholder = placeholders[type] || placeholders['all'];
        }

        async function searchDonor() {
            const query = document.getElementById('searchQuery').value.trim();
            const type = document.getElementById('searchType').value;

            if (!query) {
                showWarning('กรุณากรอกข้อมูลที่ต้องการค้นหา');
                return;
            }

            // Reset display
            document.getElementById('searchResults').style.display = 'none';
            document.getElementById('noResults').style.display = 'none';

            try {
                // Call new search API
                const response = await apiGet(`/members/search?q=${encodeURIComponent(query)}&type=${type}`);
                const results = response.data || [];

                if (results.length === 0) {
                    document.getElementById('noResults').style.display = 'block';
                    return;
                }

                // Display results in table
                displaySearchResultsTable(results);

            } catch (error) {
                if (error.message.includes('404') || error.message.includes('ไม่พบ')) {
                    document.getElementById('noResults').style.display = 'block';
                } else {
                    showError(error.message);
                }
            }
        }

        function displaySearchResultsTable(results) {
            const container = document.getElementById('searchResults');
            const tbody = document.getElementById('searchResultsBody');
            const countBadge = document.getElementById('resultCount');

            container.style.display = 'block';
            countBadge.textContent = results.length + ' รายการ';

            tbody.innerHTML = results.map((item, index) => `
                <tr class="search-result-row" style="cursor: pointer;" onclick="selectSearchResult(${index})">
                    <td>
                        <div class="fw-medium">${escapeHtml(item.name || 'ไม่ระบุชื่อ')}</div>
                        <small class="text-muted">รหัส: ${item.id_members || '-'}</small>
                    </td>
                    <td>
                        <span class="font-monospace small">${escapeHtml(item.id_card_formatted || item.id_card || '-')}</span>
                    </td>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary" onclick="event.stopPropagation(); selectSearchResult(${index})">
                            เลือก
                        </button>
                    </td>
                </tr>
            `).join('');

            // Store results for selection
            window.searchResults = results;
        }

        function selectSearchResult(index) {
            const item = window.searchResults[index];
            if (!item) return;

            // Highlight selected row
            document.querySelectorAll('.search-result-row').forEach((row, i) => {
                row.classList.toggle('table-primary', i === index);
            });

            // Fill form with selected data
            fillFormFromSearchResult(item);

            showSuccess('เลือกข้อมูลจาก: ' + (item.name || 'ไม่ระบุชื่อ'));
        }

        function fillFormFromSearchResult(item) {
            // Fill Title & Name first so toggleDonorType() can use it
            if (item.title) document.getElementById('title').value = item.title;
            if (item.first_name) document.getElementById('first_name').value = item.first_name;
            if (item.last_name) document.getElementById('last_name').value = item.last_name;

            // Trigger UI toggle based on title
            toggleDonorType();

            // Fill other fields
            if (item.id_card) document.getElementById('id_card').value = item.id_card;
            if (item.phone) document.getElementById('phone').value = item.phone;
            if (item.email) document.getElementById('email').value = item.email;
            if (item.occupation) document.getElementById('occupation').value = item.occupation;

            // Handle address components
            if (item.address_line) document.getElementById('address_line').value = item.address_line;
            if (item.address) document.getElementById('address').value = item.address;

            // Trigger AutoProvince if data available
            if (item.province && receiptAutoAddr) {
                const $prov = $('#province');
                const provOption = $prov.find('option').filter(function () { return $(this).text() === item.province; });
                if (provOption.length) {
                    $prov.val(provOption.val()).trigger('change');

                    // Delay for district and subdistrict
                    setTimeout(() => {
                        if (item.district || item.amphure) {
                            const targetDist = item.amphure || item.district;
                            const $dist = $('#district');
                            const distOption = $dist.find('option').filter(function () { return $(this).text() === targetDist; });
                            if (distOption.length) {
                                $dist.val(distOption.val()).trigger('change');

                                setTimeout(() => {
                                    if (item.subdistrict || item.district) {
                                        const targetSub = item.subdistrict || item.district;
                                        const $sub = $('#subdistrict');
                                        const subOption = $sub.find('option').filter(function () { return $(this).text() === targetSub; });
                                        if (subOption.length) {
                                            $sub.val(subOption.val()).trigger('change');
                                            if (item.zip_code) document.getElementById('postcode').value = item.zip_code;
                                        }
                                    }
                                }, 600);
                            }
                        }
                    }, 600);
                }
            }
        }

        function selectDonation(donation) {
            // Store selected donation
            window.selectedDonation = donation;

            // Fill donation ID
            document.getElementById('donation_id').value = donation.id || donation.donation_id || '';

            // Parse and fill name
            const name = donation.payer_name || donation.name || '';
            const nameParts = name.split(' ');
            const titles = ['นาย', 'นาง', 'นางสาว', 'ด.ช.', 'ด.ญ.', 'บริษัท', 'ห้างหุ้นส่วน', 'มูลนิธิ', 'สมาคม'];

            if (nameParts.length > 0) {
                if (titles.includes(nameParts[0])) {
                    document.getElementById('title').value = nameParts[0];
                    document.getElementById('first_name').value = nameParts.slice(1, -1).join(' ') || nameParts[1] || '';
                    document.getElementById('last_name').value = nameParts.length > 2 ? nameParts[nameParts.length - 1] : '';
                } else {
                    document.getElementById('first_name').value = nameParts[0] || '';
                    document.getElementById('last_name').value = nameParts.slice(1).join(' ') || '';
                }
            }

            // Fill other fields
            document.getElementById('id_card').value = donation.id_card || '';
            document.getElementById('phone').value = donation.phone || '';
            document.getElementById('occupation').value = donation.occupation || '';
            document.getElementById('email').value = donation.email || '';

            // Fill address
            const addrValue = donation.receipt_address || donation.shipping_address || donation.address || '';
            document.getElementById('address').value = addrValue;
            document.getElementById('address_line').value = addrValue;

            // Fill amount
            if (donation.amount) {
                document.getElementById('amount').value = donation.amount;
            }

            // Fill project if available
            if (donation.project_number) {
                document.getElementById('project_number').value = donation.project_number;
            }

            // Fill donation date
            if (donation.donation_date || donation.created_at) {
                const dateStr = donation.donation_date || donation.created_at;
                document.getElementById('donation_date').value = dateStr.split('T')[0];
            }

            // Highlight selected row
            document.querySelectorAll('.donation-item').forEach(el => el.classList.remove('selected'));
            event.target.closest('.donation-item')?.classList.add('selected');

            showSuccess('เลือกรายการบริจาค: ' + name);
        }

        // ...

        async function handleSubmit(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.getElementById('submitSpinner');

            // Get form values
            const title = document.getElementById('title').value;
            const juristicTitles = ['บริษัท', 'ห้างหุ้นส่วน', 'มูลนิธิ', 'สมาคม'];
            const donorType = juristicTitles.includes(title) ? 'juristic' : 'person';
            const firstName = document.getElementById('first_name').value.trim();
            const lastName = document.getElementById('last_name').value.trim();
            const idCard = document.getElementById('id_card').value.trim();
            const address = document.getElementById('address').value.trim();
            const addressLine = document.getElementById('address_line').value.trim();
            const projectSelect = document.getElementById('project_number');
            const projectNumber = projectSelect.value;
            const projectName = projectSelect.options[projectSelect.selectedIndex]?.dataset?.receiptName || projectSelect.options[projectSelect.selectedIndex]?.dataset?.name || projectSelect.options[projectSelect.selectedIndex]?.text || '';
            const amount = document.getElementById('amount').value.replace(/,/g, '');
            const donationDate = document.getElementById('donation_date').value;

            // Validation
            if (!firstName) {
                showError('กรุณากรอกชื่อ');
                return;
            }
            if (!amount || parseFloat(amount) <= 0) {
                showError('กรุณากรอกจำนวนเงินที่ถูกต้อง');
                return;
            }
            if (!donationDate) {
                showError('กรุณาเลือกวันที่บริจาค');
                return;
            }

            submitBtn.disabled = true;
            spinner.classList.remove('d-none');

            try {
                const formData = {
                    donation_id: document.getElementById('donation_id').value || null,
                    donor_type: donorType,
                    type: document.getElementById('affiliation').value,
                    title: document.getElementById('title').value,
                    first_name: firstName,
                    last_name: lastName,
                    id_card: idCard,
                    phone: document.getElementById('phone').value,
                    occupation: document.getElementById('occupation').value, // Add occupation
                    email: document.getElementById('email').value,
                    address: address, // Or use specific parts
                    address_line: addressLine,
                    shipping_address: document.getElementById('shipping_address').value.trim(),
                    province: $('#province option:selected').text(),
                    amphure: $('#district option:selected').text(),
                    district: $('#subdistrict option:selected').text(),
                    zip_code: document.getElementById('postcode').value,
                    // Shipping components
                    ship_address_line: document.getElementById('ship_address_line').value.trim(),
                    ship_province: $('#ship_province option:selected').text(),
                    ship_amphure: $('#ship_district option:selected').text(),
                    ship_district: $('#ship_subdistrict option:selected').text(),
                    ship_zip_code: document.getElementById('ship_postcode').value,
                    project_number: projectNumber,
                    project_name: projectName,
                    amount: parseFloat(amount),
                    donation_date: donationDate,
                    payment_method: document.getElementById('payment_method').value,
                    note: document.getElementById('note').value,
                    send_email: document.getElementById('send_email').checked,
                    print_receipt: document.getElementById('print_receipt').checked,
                    notify_line: document.getElementById('notify_line').checked
                };
                // ...


                // Call POST /donations/admin
                const response = await apiPost('/donations/admin', formData);

                // Auto handle by backend


                // Show success
                document.getElementById('result_receipt_no').textContent = response.data.receipt_no;
                // เพิ่ม admin=1 เพื่อข้ามการยืนยันตัวตน
                let pdfUrl = response.data.pdf_url;
                if (pdfUrl && pdfUrl.indexOf('?') > -1) {
                    pdfUrl += '&admin=1';
                } else if (pdfUrl) {
                    pdfUrl += '?admin=1';
                }
                document.getElementById('downloadPdfBtn').href = pdfUrl;

                new bootstrap.Modal(document.getElementById('successModal')).show();

            } catch (error) {
                showError(error.message);
            } finally {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        function resetForm() {
            document.getElementById('receiptForm').reset();
            document.getElementById('donation_id').value = '';
            document.getElementById('searchResults').style.display = 'none';
            selectedDonation = null;
            setDefaultDate();
            updatePreview();
            handleAmountInput(document.getElementById('amount'));

            // Remove selection
            document.querySelectorAll('.donation-item').forEach(el => el.classList.remove('selected'));
        }

        function createAnother() {
            bootstrap.Modal.getInstance(document.getElementById('successModal')).hide();
            resetForm();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>

</body>

</html>