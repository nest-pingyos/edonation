<!DOCTYPE html>
<html lang="th">

<?php
$pageTitle = "ร่วมบริจาค";
$pageDesc = "รายละเอียดโครงการบริจาคและช่องทางการชำระเงิน คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่";
include_once('../config/head.php');

// Get base path for autoprovince
$basePath = defined('BASE_PATH') ? BASE_PATH : '/appdev/edonation';
?>

<body>
    <div class="wrapper">
        <?php include_once('../config/header.php'); ?>

        <section class="team-layout1 pb-80 pt-80">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-6 offset-lg-3">
                        <div class="heading text-center mb-60">
                            <h3 class="heading__title">ร่วมบริจาค</h3>
                        </div>
                    </div>
                </div>

                <div class="row" id="mainContent">
                    <!-- Loading State -->
                    <div class="col-12 text-center" id="loadingState">
                        <div class="loading"><span></span><span></span><span></span><span></span></div>
                        <p class="mt-3">กำลังโหลดข้อมูลโครงการ...</p>
                    </div>
                </div>

                <!-- Project Content (hidden until loaded) -->
                <div class="row" id="projectContent" style="display: none;">
                    <!-- Left: Project Details Card -->
                    <div class="col-lg-6 mb-4">
                        <div class="project-card">
                            <div class="project-card__img">
                                <img id="projectImage" src="" alt="Project Image"
                                    onerror="this.src='../assets/images/projects/pro-1.jpg'">
                            </div>
                            <div class="project-card__header">
                                <span class="project-card__badge" id="projectBadge">เปิดรับบริจาค</span>
                            </div>
                            <div class="project-card__body">
                                <h4 class="project-card__title" id="projectTitle">-</h4>
                                <p class="project-card__desc" id="projectDescription">-</p>

                                <!-- Progress Section -->
                                <div class="project-card__progress">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="progress-current" id="currentAmount">฿0</span>
                                        <span class="progress-target">เป้า: <span id="targetAmount">฿0</span></span>
                                    </div>
                                    <div class="progress-bar-bg">
                                        <div class="progress-bar-fill" id="progressBar" style="width: 0%;"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <small class="text-muted"><span id="donorCount">0</span> ผู้บริจาค</small>
                                        <small class="text-muted" id="progressPercent">0%</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Donation Form Card -->
                    <div class="col-lg-6 mb-4">
                        <div class="contact-panel">
                            <form class="contact-panel__form" id="donationForm" novalidate>
                                <input type="hidden" id="project_number" name="project_number">
                                <input type="hidden" id="project_name_hidden" name="project_name">

                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <h5 class="contact-panel__title mb-20">ข้อมูลการบริจาค</h5>
                                    </div>

                                    <!-- Amount Quick Select -->
                                    <div class="col-12 mb-4">
                                        <label class="mb-2">จำนวนเงิน (บาท) <span class="text-danger">*</span></label>
                                        <div class="amount-options mb-3">
                                            <button type="button" class="amount-btn" data-amount="100">100</button>
                                            <button type="button" class="amount-btn" data-amount="500">500</button>
                                            <button type="button" class="amount-btn" data-amount="1000">1,000</button>
                                            <button type="button" class="amount-btn" data-amount="5000">5,000</button>
                                        </div>
                                        <input type="number" class="form-control" id="amount" name="amount"
                                            placeholder="หรือระบุจำนวนเอง" min="1" required>
                                        <div class="invalid-feedback">กรุณาระบุจำนวนเงิน</div>
                                    </div>

                                    <!-- Type Selection -->
                                    <div class="col-sm-6 mb-3">
                                        <label>ประเภท <span class="text-danger">*</span></label>
                                        <select class="form-control" id="type" name="type" required>
                                            <option value="ศิษย์เก่าคณะพยาบาล มช." selected>ศิษย์เก่า มช.</option>
                                            <option value="บุคลากร อาจารย์คณะพยาบาล มช.">บุคลากร/อาจารย์</option>
                                            <option value="บุคคลทั่วไป">บุคคลทั่วไป</option>
                                        </select>
                                        <div class="invalid-feedback" id="typeError">กรุณาเลือกประเภท</div>
                                    </div>

                                    <!-- Phone -->
                                    <div class="col-sm-6 mb-3">
                                        <label>โทรศัพท์ <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="phone" name="phone"
                                            placeholder="0812345678" pattern="[0-9]{10}" required>
                                        <div class="invalid-feedback">กรุณาระบุเบอร์โทร 10 หลัก</div>
                                    </div>

                                    <!-- Receipt Checkbox -->
                                    <div class="col-12 mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="needReceipt"
                                                name="needReceipt">
                                            <label class="form-check-label" for="needReceipt">
                                                <strong>ต้องการใบเสร็จรับเงิน / ใบอนุโมทนาบัตร</strong>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Receipt Info (Hidden by default) -->
                                    <div class="col-12" id="receiptSection" style="display: none;">
                                        <div class="receipt-form-box">
                                            <h6 class="mb-3">ข้อมูลสำหรับใบเสร็จรับเงิน</h6>

                                            <div class="row">
                                                <div class="col-sm-6 mb-3">
                                                    <label>ชื่อ <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="firstName"
                                                        name="firstName" placeholder="ชื่อ">
                                                </div>
                                                <div class="col-sm-6 mb-3">
                                                    <label>นามสกุล <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="lastName"
                                                        name="lastName" placeholder="นามสกุล">
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label>เลขประจำตัวประชาชน <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="idCard" name="idCard"
                                                        placeholder="x-xxxx-xxxxx-xx-x" maxlength="17">
                                                </div>
                                            </div>

                                            <!-- Receipt Address with AutoProvince -->
                                            <h6 class="mb-3 mt-3">ที่อยู่สำหรับระบุบนใบเสร็จ</h6>
                                            <div class="row address-section" id="receiptAddressSection">
                                                <div class="col-12 mb-3">
                                                    <label>ที่อยู่ (บ้านเลขที่ ซอย ถนน)</label>
                                                    <input type="text" class="form-control" id="receiptAddressLine"
                                                        name="receiptAddressLine" placeholder="บ้านเลขที่ ซอย ถนน">
                                                </div>
                                                <div class="col-sm-6 mb-3">
                                                    <label>จังหวัด <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="receiptProvince"
                                                        name="receiptProvince"></select>
                                                </div>
                                                <div class="col-sm-6 mb-3">
                                                    <label>อำเภอ/เขต <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="receiptDistrict"
                                                        name="receiptDistrict" disabled></select>
                                                </div>
                                                <div class="col-sm-6 mb-3">
                                                    <label>ตำบล/แขวง <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="receiptSubdistrict"
                                                        name="receiptSubdistrict" disabled></select>
                                                </div>
                                                <div class="col-sm-6 mb-3">
                                                    <label>รหัสไปรษณีย์</label>
                                                    <input type="text" class="form-control" id="receiptPostcode"
                                                        name="receiptPostcode" readonly>
                                                </div>
                                                <!-- Hidden full address for API -->
                                                <input type="hidden" id="receiptAddress" name="receiptAddress">
                                            </div>

                                            <!-- Shipping Address -->
                                            <h6 class="mb-2 mt-4">
                                                ที่อยู่สำหรับจัดส่งใบอนุโมทนาบัตร
                                                <a href="javascript:void(0)" class="copy-address-link"
                                                    id="useSameAddressBtn">ใช้ที่อยู่เดียวกัน</a>
                                            </h6>

                                            <div class="row address-section" id="shippingAddressSection">
                                                <div class="col-12 mb-3">
                                                    <label>ที่อยู่ (บ้านเลขที่ ซอย ถนน)</label>
                                                    <input type="text" class="form-control" id="shippingAddressLine"
                                                        name="shippingAddressLine" placeholder="บ้านเลขที่ ซอย ถนน">
                                                </div>
                                                <div class="col-sm-6 mb-3">
                                                    <label>จังหวัด <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="shippingProvince"
                                                        name="shippingProvince"></select>
                                                </div>
                                                <div class="col-sm-6 mb-3">
                                                    <label>อำเภอ/เขต <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="shippingDistrict"
                                                        name="shippingDistrict" disabled></select>
                                                </div>
                                                <div class="col-sm-6 mb-3">
                                                    <label>ตำบล/แขวง <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="shippingSubdistrict"
                                                        name="shippingSubdistrict" disabled></select>
                                                </div>
                                                <div class="col-sm-6 mb-3">
                                                    <label>รหัสไปรษณีย์</label>
                                                    <input type="text" class="form-control" id="shippingPostcode"
                                                        name="shippingPostcode" readonly>
                                                </div>
                                                <!-- Hidden full address for API -->
                                                <input type="hidden" id="shippingAddress" name="shippingAddress">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit -->
                                    <div class="col-12 mt-3">
                                        <button type="submit" class="btn btn__primary btn__rounded btn__block"
                                            id="submitBtn">
                                            <span>ยืนยันการบริจาค</span>
                                            <i class="icon-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Error State -->
            <div class="row" id="errorState" style="display: none;">
                <div class="col-12 text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h4 id="errorMessage">ไม่พบโครงการ</h4>
                    <p class="text-muted">กรุณาเลือกโครงการจากหน้าหลัก</p>
                    <a href="../home/" class="btn btn__primary btn__rounded mt-3">
                        <span>กลับหน้าหลัก</span>
                        <i class="icon-arrow-right"></i>
                    </a>
                </div>
            </div>
    </div>
    </section>

    <?php include_once('../config/footer.php'); ?>
    </div>

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/main.js"></script>

    <!-- Select2 for AutoProvince -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- AutoProvince -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>/shared/autoprovince/assets/autoprovince.css">
    <script src="<?php echo $basePath; ?>/shared/autoprovince/assets/autoprovince.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        // API Base Path
        const API_BASE = document.querySelector('meta[name="api-base"]')?.content || '/edonation/api/v1';
        const AUTOPROVINCE_API = '<?php echo $basePath; ?>/shared/autoprovince/api.php';

        // Get project_number from URL path or query parameter
        function getProjectNumber() {
            const pathMatch = window.location.pathname.match(/\/donat\/([A-Za-z0-9_-]+)\/?$/);
            if (pathMatch) return pathMatch[1];

            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('project_number');
        }

        const projectNumber = getProjectNumber();

        document.addEventListener('DOMContentLoaded', function () {
            if (!projectNumber) {
                showError('ไม่พบรหัสโครงการ');
                return;
            }
            loadProject(projectNumber);
            setupAmountButtons();
            setupReceiptSection();
            initAutoProvince();

            // Clear validation error on type change
            document.getElementById('type').addEventListener('change', function () {
                this.classList.remove('is-invalid');
            });
        });

        // Initialize AutoProvince for both address forms
        function initAutoProvince() {
            // Receipt Address
            initAddressDropdowns('receipt', {
                province: '#receiptProvince',
                district: '#receiptDistrict',
                subdistrict: '#receiptSubdistrict',
                postcode: '#receiptPostcode',
                addressLine: '#receiptAddressLine',
                fullAddress: '#receiptAddress'
            });

            // Shipping Address
            initAddressDropdowns('shipping', {
                province: '#shippingProvince',
                district: '#shippingDistrict',
                subdistrict: '#shippingSubdistrict',
                postcode: '#shippingPostcode',
                addressLine: '#shippingAddressLine',
                fullAddress: '#shippingAddress'
            });
        }

        function initAddressDropdowns(prefix, selectors) {
            const $province = $(selectors.province);
            const $district = $(selectors.district);
            const $subdistrict = $(selectors.subdistrict);
            const $postcode = $(selectors.postcode);

            // Initialize Select2
            $province.select2({ theme: 'bootstrap-5', width: '100%', placeholder: 'เลือกจังหวัด', allowClear: true });
            $district.select2({ theme: 'bootstrap-5', width: '100%', placeholder: 'เลือกอำเภอ', allowClear: true });
            $subdistrict.select2({ theme: 'bootstrap-5', width: '100%', placeholder: 'เลือกตำบล', allowClear: true });

            // Load provinces
            $.get(AUTOPROVINCE_API + '?action=get_provinces', function (response) {
                if (response.status === 'success') {
                    $province.empty().append('<option value=""></option>');
                    response.data.forEach(item => {
                        $province.append(new Option(item.name, item.id, false, false));
                    });
                    $province.trigger('change.select2');
                }
            });

            // Province change -> load districts
            $province.on('change', function () {
                const provinceId = $(this).val();
                $district.empty().append('<option value=""></option>').prop('disabled', true).trigger('change.select2');
                $subdistrict.empty().append('<option value=""></option>').prop('disabled', true).trigger('change.select2');
                $postcode.val('');
                updateFullAddress(selectors);

                if (provinceId) {
                    $.post(AUTOPROVINCE_API + '?action=get_districts', { province_id: provinceId }, function (response) {
                        if (response.status === 'success') {
                            $district.prop('disabled', false);
                            response.data.forEach(item => {
                                $district.append(new Option(item.name, item.id, false, false));
                            });
                            $district.trigger('change.select2');
                        }
                    });
                }
            });

            // District change -> load subdistricts
            $district.on('change', function () {
                const districtId = $(this).val();
                $subdistrict.empty().append('<option value=""></option>').prop('disabled', true).trigger('change.select2');
                $postcode.val('');
                updateFullAddress(selectors);

                if (districtId) {
                    $.post(AUTOPROVINCE_API + '?action=get_subdistricts', { district_id: districtId }, function (response) {
                        if (response.status === 'success') {
                            $subdistrict.prop('disabled', false);
                            response.data.forEach(item => {
                                const option = new Option(item.name, item.id, false, false);
                                $(option).data('postcode', item.postcode);
                                $subdistrict.append(option);
                            });
                            $subdistrict.trigger('change.select2');
                        }
                    });
                }
            });

            // Subdistrict change -> set postcode
            $subdistrict.on('change', function () {
                const selected = $(this).select2('data')[0];
                if (selected && selected.element) {
                    const postcode = $(selected.element).data('postcode');
                    if (postcode && postcode !== '0') {
                        $postcode.val(postcode);
                    }
                }
                updateFullAddress(selectors);
            });

            // Update on address line change
            $(selectors.addressLine).on('change blur', function () {
                updateFullAddress(selectors);
            });
        }

        function updateFullAddress(selectors) {
            const addressLine = $(selectors.addressLine).val() || '';
            const subdistrict = $(selectors.subdistrict).find(':selected').text() || '';
            const district = $(selectors.district).find(':selected').text() || '';
            const province = $(selectors.province).find(':selected').text() || '';
            const postcode = $(selectors.postcode).val() || '';

            let parts = [addressLine];
            if (subdistrict && subdistrict !== 'เลือกตำบล') parts.push('ต.' + subdistrict);
            if (district && district !== 'เลือกอำเภอ') parts.push('อ.' + district);
            if (province && province !== 'เลือกจังหวัด') parts.push('จ.' + province);
            if (postcode) parts.push(postcode);

            $(selectors.fullAddress).val(parts.filter(p => p).join(' '));
        }

        function setupReceiptSection() {
            const checkbox = document.getElementById('needReceipt');
            const section = document.getElementById('receiptSection');
            const useSameBtn = document.getElementById('useSameAddressBtn');

            // Toggle receipt section
            checkbox.addEventListener('change', function () {
                section.style.display = this.checked ? 'block' : 'none';

                // Toggle required on receipt fields
                const fields = ['firstName', 'lastName', 'idCard'];
                fields.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.required = this.checked;
                });
            });

            // Copy address button
            useSameBtn.addEventListener('click', function () {
                // Copy all address fields
                $('#shippingAddressLine').val($('#receiptAddressLine').val());

                // Copy select2 values
                const provinceId = $('#receiptProvince').val();
                const districtId = $('#receiptDistrict').val();
                const subdistrictId = $('#receiptSubdistrict').val();
                const postcode = $('#receiptPostcode').val();

                if (provinceId) {
                    $('#shippingProvince').val(provinceId).trigger('change');

                    // Wait for districts to load
                    setTimeout(() => {
                        if (districtId) {
                            $('#shippingDistrict').val(districtId).trigger('change');

                            // Wait for subdistricts to load
                            setTimeout(() => {
                                if (subdistrictId) {
                                    $('#shippingSubdistrict').val(subdistrictId).trigger('change');
                                    $('#shippingPostcode').val(postcode);
                                }
                            }, 500);
                        }
                    }, 500);
                }

                // Copy full address immediately
                $('#shippingAddress').val($('#receiptAddress').val());
            });

            // Format ID card input
            document.getElementById('idCard').addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 13) value = value.slice(0, 13);

                let formatted = '';
                if (value.length > 0) formatted += value.slice(0, 1);
                if (value.length > 1) formatted += '-' + value.slice(1, 5);
                if (value.length > 5) formatted += '-' + value.slice(5, 10);
                if (value.length > 10) formatted += '-' + value.slice(10, 12);
                if (value.length > 12) formatted += '-' + value.slice(12, 13);

                e.target.value = formatted;
            });
        }

        async function loadProject(projectNum) {
            try {
                const response = await fetch(`${API_BASE}/projects/${projectNum}`);
                const result = await response.json();

                if (result.success && result.data) {
                    displayProject(result.data);
                } else {
                    showError('ไม่พบโครงการนี้');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('ไม่สามารถโหลดข้อมูลได้');
            }
        }

        function displayProject(p) {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('projectContent').style.display = 'flex';

            document.getElementById('projectTitle').textContent = p.project_name;
            document.getElementById('projectDescription').textContent = p.description || p.short_description || 'ร่วมบริจาคสนับสนุนโครงการนี้';
            document.getElementById('projectBadge').textContent = p.status === 'active' ? 'เปิดรับบริจาค' : 'โครงการ';

            if (p.image_url) {
                document.getElementById('projectImage').src = p.image_url;
            }

            const current = parseFloat(p.current_amount) || 0;
            const target = parseFloat(p.target_amount) || 100000;
            const percent = Math.min(100, (current / target) * 100);

            document.getElementById('currentAmount').textContent = '฿' + formatNumber(current);
            document.getElementById('targetAmount').textContent = '฿' + formatNumber(target);
            document.getElementById('donorCount').textContent = formatNumber(p.donor_count || 0);
            document.getElementById('progressBar').style.width = percent + '%';
            document.getElementById('progressPercent').textContent = Math.round(percent) + '%';

            document.getElementById('project_number').value = p.project_number;
            document.getElementById('project_name_hidden').value = p.project_name;
        }

        function showError(msg) {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('errorState').style.display = 'flex';
            document.getElementById('errorMessage').textContent = msg;
        }

        function formatNumber(n) {
            return new Intl.NumberFormat('th-TH').format(n);
        }

        function setupAmountButtons() {
            document.querySelectorAll('.amount-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    document.getElementById('amount').value = this.dataset.amount;
                });
            });

            // Clear selection when typing custom amount
            document.getElementById('amount').addEventListener('input', function () {
                if (this.value) {
                    document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
                }
            });
        }

        document.getElementById('donationForm').addEventListener('submit', async function (e) {
            // Custom validation
            const typeSelect = document.getElementById('type');
            const amountInput = document.getElementById('amount');
            const phoneInput = document.getElementById('phone');
            let isValid = true;

            // Validate type
            if (!typeSelect.value) {
                typeSelect.classList.add('is-invalid');
                isValid = false;
            } else {
                typeSelect.classList.remove('is-invalid');
            }

            // Validate amount
            if (!amountInput.value || parseFloat(amountInput.value) < 1) {
                amountInput.classList.add('is-invalid');
                isValid = false;
            } else {
                amountInput.classList.remove('is-invalid');
            }

            // Validate phone
            if (!phoneInput.value || !/^[0-9]{10}$/.test(phoneInput.value)) {
                phoneInput.classList.add('is-invalid');
                isValid = false;
            } else {
                phoneInput.classList.remove('is-invalid');
            }

            if (!isValid) {
                e.preventDefault();
                return;
            }

            e.preventDefault();

            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span>กำลังดำเนินการ...</span>';

            const needReceipt = document.getElementById('needReceipt').checked;

            const data = {
                project_number: document.getElementById('project_number').value,
                project_name: document.getElementById('project_name_hidden').value,
                type: typeSelect.value,
                phone: phoneInput.value,
                amount: parseFloat(amountInput.value),
                needReceipt: needReceipt
            };

            // Add receipt fields if needed
            if (needReceipt) {
                data.firstName = document.getElementById('firstName').value;
                data.lastName = document.getElementById('lastName').value;
                data.idCard = document.getElementById('idCard').value;
                data.receiptAddress = document.getElementById('receiptAddress').value;
                data.shippingAddress = document.getElementById('shippingAddress').value;
            }

            try {
                const res = await fetch(`${API_BASE}/donations`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await res.json();

                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: 'กำลังไปหน้าชำระเงิน...',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = `qrgenerator.php?id=${result.data.id}&ref=${result.data.billPaymentRef1}&amount=${data.amount}`;
                    });
                } else {
                    throw new Error(result.error?.message || 'เกิดข้อผิดพลาด');
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: err.message });
                btn.disabled = false;
                btn.innerHTML = '<span>ยืนยันการบริจาค</span><i class="icon-arrow-right"></i>';
            }
        });
    </script>
</body>

</html>