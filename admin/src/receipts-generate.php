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

                <form id="receiptForm">
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

                                        <!-- Donor Type Selection -->
                                        <div class="col-12">
                                            <div class="mb-2">
                                                <label class="form-label d-block">ประเภทผู้บริจาค</label>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="donorType"
                                                        id="typePerson" value="person" checked
                                                        onchange="toggleDonorType()">
                                                    <label class="form-check-label" for="typePerson">บุคคลธรรมดา</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="donorType"
                                                        id="typeJuristic" value="juristic" onchange="toggleDonorType()">
                                                    <label class="form-check-label" for="typeJuristic">นิติบุคคล
                                                        (บริษัท/องค์กร)</label>
                                                </div>
                                            </div>
                                        </div>

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
                                            <select class="form-select" id="title" name="title" required>
                                                <option value="">-- เลือก --</option>
                                                <option value="นาย">นาย</option>
                                                <option value="นาง">นาง</option>
                                                <option value="นางสาว">นางสาว</option>
                                                <option value="ด.ช.">ด.ช.</option>
                                                <option value="ด.ญ.">ด.ญ.</option>
                                                <option value="อื่นๆ">อื่นๆ</option>
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
                                            <input type="text" class="form-control" id="occupation" name="occupation"
                                                placeholder="ระบุอาชีพของเจ้าของใบเสร็จ">
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
                                            <input type="number" class="form-control" id="amount" name="amount" min="1"
                                                required>
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

    <?php include 'partials/vendor-scripts.php'; ?>
    <script src="assets/js/api-helper.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- AutoProvince JS -->
    <?php autoprovinceJs(); ?>

    <script>
        let projects = [];
        let selectedDonation = null;

        const JURISTIC_TITLES = ['บริษัท', 'ห้างหุ้นส่วน', 'มูลนิธิ', 'สมาคม'];

        // ... toggleDonorType ... (keep)

        document.addEventListener('DOMContentLoaded', function () {
            // Init AutoProvince with JS Function Callbacks
            if (typeof AutoProvince !== 'undefined') {
                AutoProvince.init({
                    onAddressComplete: function(addr) {
                        updateFullAddress();
                    },
                    onProvinceChange: function(p) {
                         updateFullAddress();
                    },
                    onDistrictChange: function(d) {
                         updateFullAddress();
                    },
                    onSubdistrictChange: function(s) {
                         updateFullAddress();
                    }
                });
            } else {
                console.error("AutoProvince library not loaded!");
            }

            loadProjects();
            setDefaultDate();
            // ... (rest of init)

        // ===== AUTOPROVINCE FUNCTIONS =====
        // Removed manual implementation

        function updateFullAddress() {
             // ... existing logic ...
        }


        function updateFullAddress() {
            const parts = [];
            const addressLine = $('#address_line').val();
            const subdistrict = $('#subdistrict option:selected').text();
            const district = $('#district option:selected').text();
            const province = $('#province option:selected').text();
            const postcode = $('#postcode').val();

            if (addressLine) parts.push(addressLine);
            if (subdistrict && !subdistrict.includes('--')) parts.push('ต.' + subdistrict);
            if (district && !district.includes('--')) parts.push('อ.' + district);
            if (province && !province.includes('--')) parts.push('จ.' + province);
            if (postcode) parts.push(postcode);

            $('#address').val(parts.join(' '));
        }

        // Helper to set address from search result
        function setAddressFromText(addressText) {
            // If we have structured address, set address_line only
            // The dropdowns should be selected manually or via lookup
            $('#address_line').val(addressText);
            $('#address').val(addressText);
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
            // Parse name
            const name = item.name || '';
            const nameParts = name.split(' ');
            const titles = ['นาย', 'นาง', 'นางสาว', 'ด.ช.', 'ด.ญ.', 'บริษัท', 'ห้างหุ้นส่วน', 'มูลนิธิ', 'สมาคม'];

            if (nameParts.length > 0) {
                if (titles.includes(nameParts[0])) {
                    document.getElementById('title').value = nameParts[0];
                    if (item.first_name && item.last_name) {
                        document.getElementById('first_name').value = item.first_name || '';
                        document.getElementById('last_name').value = item.last_name || '';
                    } else {
                        document.getElementById('first_name').value = nameParts.slice(1, -1).join(' ') || nameParts[1] || '';
                        document.getElementById('last_name').value = nameParts.length > 2 ? nameParts[nameParts.length - 1] : '';
                    }
                } else {
                    if (item.first_name && item.last_name) {
                        document.getElementById('first_name').value = item.first_name || '';
                        document.getElementById('last_name').value = item.last_name || '';
                    } else {
                        document.getElementById('first_name').value = nameParts[0] || '';
                        document.getElementById('last_name').value = nameParts.slice(1).join(' ') || '';
                    }
                }
            }

            // Fill other fields
            if (item.id_card) {
                document.getElementById('id_card').value = item.id_card;
            }
            if (item.phone) {
                document.getElementById('phone').value = item.phone;
            }
            if (item.occupation) {
                document.getElementById('occupation').value = item.occupation;
            }

            // Handle address - support both string and object format from Members API
            if (item.address) {
                if (typeof item.address === 'object') {
                    // Members API format: { full: "...", address_line: "...", province: "...", ... }
                    document.getElementById('address').value = item.address.full || '';
                    document.getElementById('address_line').value = item.address.address_line || item.address.full || '';
                } else {
                    // Legacy string format
                    document.getElementById('address').value = item.address;
                    document.getElementById('address_line').value = item.address;
                }
            }
            // ... (fillFormFromSearchResult end)
        }

        function selectDonation(donation) {
            // ... (existing code)
            
            document.getElementById('phone').value = donation.phone || '';
            document.getElementById('occupation').value = donation.occupation || ''; // Add occupation

            const addrValue = donation.receipt_address || donation.shipping_address || donation.address || '';
            // ... (rest of selectDonation)
        }

        // ...

        async function handleSubmit(e) {
            // ...
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
                    province: $('#province option:selected').text(),
                    amphure: $('#district option:selected').text(),
                    district: $('#subdistrict option:selected').text(),
                    zip_code: document.getElementById('postcode').value,
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
            } catch (error) {
                // ...
            }
        }

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