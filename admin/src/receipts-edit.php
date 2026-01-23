<?php include 'partials/main.php'; ?>
<?php requireAuth(); ?>
<?php require_once __DIR__ . '/../../config/autoprovince.php'; ?>

<!doctype html>
<html lang="th">

<head>
    <?php
    $title = "แก้ไขใบเสร็จรับเงิน";
    include 'partials/title-meta.php'; ?>

    <?php include 'partials/head-css.php'; ?>
    <?php autoprovinceCss(); ?>

    <style>
        .form-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .receipt-info-card {
            background: linear-gradient(135deg, #1c84ee 0%, #0d6efd 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
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
                $pageTitle = "แก้ไขใบเสร็จรับเงิน";
                $subTitle = "ใบเสร็จ";
                include 'partials/page-title.php'; ?>

                <!-- Loading State -->
                <div id="loadingState" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                    <p class="mt-3">กำลังโหลดข้อมูลใบเสร็จ...</p>
                </div>

                <!-- Error State -->
                <div id="errorState" class="alert alert-danger" style="display: none;">
                    <p class="mb-0" id="errorMessage">ไม่พบใบเสร็จ</p>
                    <a href="receipts-list.php" class="btn btn-outline-danger mt-3">กลับไปรายการใบเสร็จ</a>
                </div>

                <form id="editForm" onsubmit="handleSubmit(event)" style="display: none;">
                    <input type="hidden" id="receiptId" name="receipt_id">
                    <input type="hidden" id="donation_id" name="donation_id">

                    <div class="row">
                        <div class="col-12">
                            <!-- Receipt Info Card -->
                            <div class="receipt-info-card bg-primary text-white mb-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 id="displayReceiptNo" class="text-white mb-1">-</h4>
                                        <small>เลขที่ใบเสร็จ</small>
                                    </div>
                                    <div class="text-end">
                                        <h4 id="displayAmount" class="text-white mb-1">-</h4>
                                        <small>จำนวนเงินเดิม</small>
                                    </div>
                                </div>
                            </div>

                            <!-- ข้อมูลผู้บริจาค -->
                            <div class="card mb-4">
                                <div class="card-header bg-soft-primary">
                                    <h5 class="card-title mb-0 text-primary">ข้อมูลผู้บริจาค</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">


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
                                            <label class="form-label" id="firstNameLabel">ชื่อ <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="first_name" name="first_name"
                                                required>
                                        </div>

                                        <div class="col-md-6" id="lastNameGroup">
                                            <label class="form-label">นามสกุล <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="last_name" name="last_name"
                                                required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label" id="idCardLabel">เลขบัตรประชาชน /
                                                เลขผู้เสียภาษี</label>
                                            <input type="text" class="form-control" id="id_card" name="id_card"
                                                maxlength="13">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">เบอร์โทรศัพท์</label>
                                            <input type="tel" class="form-control" id="phone" name="phone"
                                                maxlength="10">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">อาชีพ</label>
                                            <input type="text" class="form-control" id="occupation" name="occupation">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">อีเมล</label>
                                            <input type="email" class="form-control" id="email" name="email">
                                        </div>
                                    </div>

                                    <!-- ที่อยู่ -->
                                    <hr class="my-3">
                                    <h6 class="text-muted mb-3">ที่อยู่สำหรับใบเสร็จ</h6>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">ที่อยู่ (บ้านเลขที่ ซอย ถนน)</label>
                                            <input type="text" class="form-control" id="address_line"
                                                name="address_line" onchange="updateFullAddress()">
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
                                        <input type="hidden" id="receipt_address" name="receipt_address">
                                    </div>
                                </div>
                            </div>

                            <!-- ข้อมูลการบริจาค -->
                            <div class="card mb-4">
                                <div class="card-header bg-soft-primary">
                                    <h5 class="card-title mb-0 text-primary">ข้อมูลการบริจาค</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label">โครงการ <span class="text-danger">*</span></label>
                                            <select class="form-select" id="project_number" name="project_number"
                                                required>
                                                <option value="">-- เลือกโครงการ --</option>
                                            </select>
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
                                            <input type="date" class="form-control" id="receiptDate" name="receiptDate"
                                                required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">ช่องทางการชำระเงิน</label>
                                            <select class="form-select" id="payment_method" name="payment_method">
                                                <option value="Cash">เงินสด/Cash</option>
                                                <option value="Prompt Pay">โอน/Prompt Pay</option>
                                                <option value="Cheque">เช็ค/Cheque</option>
                                                <option value="Other">อื่นๆ</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex gap-2 mb-5">
                                <button type="submit" class="btn btn-primary px-5 py-2" id="submitBtn">
                                    <span class="spinner-border spinner-border-sm d-none me-2"
                                        id="submitSpinner"></span>
                                    บันทึกการแก้ไข
                                </button>
                                <a href="receipts-list.php" class="btn btn-outline-secondary px-5 py-2">ยกเลิก</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <?php include 'partials/vendor-scripts.php'; ?>
    <script src="assets/js/api-helper.js"></script>
    <?php autoprovinceJs(); ?>

    <script>
        let receiptData = null;
        let projects = [];

        // Toggle Donor Type logic from generate.php
        // Toggle between บุคคลธรรมดา and นิติบุคคล modes based on Title
        function toggleDonorType() {
            const title = document.getElementById('title').value;
            const juristicTitles = ['บริษัท', 'ห้างหุ้นส่วน', 'มูลนิธิ', 'สมาคม'];
            const isJuristic = juristicTitles.includes(title);

            const lastNameGroup = document.getElementById('lastNameGroup');
            const lastNameInput = document.getElementById('last_name');
            const firstNameLabel = document.getElementById('firstNameLabel');
            const idCardLabel = document.getElementById('idCardLabel');
            const idCardInput = document.getElementById('id_card');

            if (isJuristic) {
                // นิติบุคคล mode
                firstNameLabel.innerHTML = 'ชื่อองค์กร <span class="text-danger">*</span>';
                if (lastNameGroup) lastNameGroup.style.display = 'none';
                lastNameInput.removeAttribute('required');
                idCardLabel.textContent = 'เลขประจำตัวผู้เสียภาษี';
                idCardInput.placeholder = 'เลขประจำตัวผู้เสียภาษี 13 หลัก';
            } else {
                // บุคคลธรรมดา mode
                firstNameLabel.innerHTML = 'ชื่อ <span class="text-danger">*</span>';
                if (lastNameGroup) lastNameGroup.style.display = 'block';
                lastNameInput.setAttribute('required', 'required');
                idCardLabel.textContent = 'เลขบัตรประชาชน / เลขผู้เสียภาษี';
                idCardInput.placeholder = 'เลขบัตรประชาชน 13 หลัก';
            }
        }

        document.addEventListener('DOMContentLoaded', async function () {
            // Init AutoProvince
            if (typeof AutoProvince !== 'undefined') {
                AutoProvince.init({
                    onAddressComplete: updateFullAddress,
                    onProvinceChange: updateFullAddress,
                    onDistrictChange: updateFullAddress,
                    onSubdistrictChange: updateFullAddress
                });
            }

            const urlParams = new URLSearchParams(window.location.search);
            const receiptId = urlParams.get('id');

            if (!receiptId) {
                showError('ไม่พบ ID ใบเสร็จ');
                return;
            }

            try {
                await loadProjects();
                await loadReceipt(receiptId);
            } catch (error) {
                showError(error.message);
            }
        });

        function updateFullAddress() {
            const parts = [];
            const addressLine = document.getElementById('address_line').value;
            const subdistrict = $('#subdistrict option:selected').text();
            const district = $('#district option:selected').text();
            const province = $('#province option:selected').text();
            const postcode = document.getElementById('postcode').value;

            if (addressLine) parts.push(addressLine);
            if (subdistrict && !subdistrict.includes('--')) parts.push('ต.' + subdistrict);
            if (district && !district.includes('--')) parts.push('อ.' + district);
            if (province && !province.includes('--')) parts.push('จ.' + province);
            if (postcode) parts.push(postcode);

            document.getElementById('receipt_address').value = parts.join(' ');
        }

        async function loadProjects() {
            const response = await apiGet('/projects');
            projects = response.data || [];
            const select = document.getElementById('project_number');
            projects.forEach(p => {
                const option = document.createElement('option');
                option.value = p.project_number;
                option.textContent = p.project_name;
                option.dataset.name = p.project_receipt_name || p.project_name;
                select.appendChild(option);
            });
        }

        async function loadReceipt(id) {
            const response = await apiGet('/receipts/' + id);
            receiptData = response.data;

            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('editForm').style.display = 'block';

            document.getElementById('displayReceiptNo').textContent = receiptData.receipt_no || '-';
            document.getElementById('displayAmount').textContent = formatCurrency(receiptData.amount);

            document.getElementById('receiptId').value = receiptData.id;
            document.getElementById('donation_id').value = receiptData.donation_id || '';
            document.getElementById('first_name').value = receiptData.first_name || '';
            document.getElementById('last_name').value = receiptData.last_name || '';
            const rawIdCard = (receiptData.id_card || '').replace(/-/g, '');
            document.getElementById('id_card').value = rawIdCard;
            document.getElementById('phone').value = receiptData.phone || '';
            document.getElementById('occupation').value = receiptData.occupation || '';
            document.getElementById('email').value = receiptData.email || '';
            document.getElementById('address_line').value = receiptData.address_line || '';
            document.getElementById('project_number').value = receiptData.project_number || '';
            document.getElementById('amount').value = receiptData.amount || '';
            handleAmountInput(document.getElementById('amount'));
            document.getElementById('receiptDate').value = (receiptData.receiptDate || '').split(' ')[0];

            if (receiptData.pay_by) {
                document.getElementById('payment_method').value = receiptData.pay_by;
            }

            document.getElementById('title').value = receiptData.title || '';
            toggleDonorType();

            // Handle Address Dropdowns by Matching Text
            setTimeout(async () => {
                if (receiptData.province) {
                    const $prov = $('#province');
                    const provOption = $prov.find('option').filter(function () { return $(this).text() === receiptData.province; });
                    if (provOption.length) {
                        $prov.val(provOption.val()).trigger('change');

                        // Wait for districts
                        setTimeout(() => {
                            if (receiptData.amphure) {
                                const $dist = $('#district');
                                const distOption = $dist.find('option').filter(function () { return $(this).text() === receiptData.amphure; });
                                if (distOption.length) {
                                    $dist.val(distOption.val()).trigger('change');

                                    // Wait for subdistricts
                                    setTimeout(() => {
                                        if (receiptData.district) {
                                            const $sub = $('#subdistrict');
                                            const subOption = $sub.find('option').filter(function () { return $(this).text() === receiptData.district; });
                                            if (subOption.length) {
                                                $sub.val(subOption.val()).trigger('change');
                                                if (receiptData.zip_code) {
                                                    document.getElementById('postcode').value = receiptData.zip_code;
                                                }
                                            }
                                        }
                                    }, 600);
                                }
                            }
                        }, 600);
                    }
                }
            }, 800);
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

        async function handleSubmit(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.getElementById('submitSpinner');
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');

            try {
                const projectSelect = document.getElementById('project_number');
                const projectName = projectSelect.options[projectSelect.selectedIndex]?.dataset?.name || '';

                updateFullAddress(); // Final sync

                const formData = {
                    title: document.getElementById('title').value,
                    first_name: document.getElementById('first_name').value,
                    last_name: document.getElementById('last_name').value,
                    id_card: document.getElementById('id_card').value,
                    phone: document.getElementById('phone').value,
                    occupation: document.getElementById('occupation').value,
                    email: document.getElementById('email').value,
                    receipt_address: document.getElementById('receipt_address').value,
                    address_line: document.getElementById('address_line').value,
                    province: $('#province option:selected').text(),
                    amphure: $('#district option:selected').text(),
                    district: $('#subdistrict option:selected').text(),
                    zip_code: document.getElementById('postcode').value,
                    project_number: projectSelect.value,
                    project_name: projectName,
                    amount: parseFloat(document.getElementById('amount').value.replace(/,/g, '')),
                    receiptDate: document.getElementById('receiptDate').value,
                    payby: document.getElementById('payment_method').value,
                    payer_name: buildPayerName()
                };

                await apiPut('/receipts/' + document.getElementById('receiptId').value, formData);
                showSuccess('แก้ไขใบเสร็จสำเร็จ');
                setTimeout(() => window.location.href = 'receipts-list.php', 1500);
            } catch (error) {
                showError(error.message);
            } finally {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        function buildPayerName() {
            const title = document.getElementById('title').value;
            const firstName = document.getElementById('first_name').value;
            const lastName = document.getElementById('last_name').value;
            return [title, firstName, lastName].filter(Boolean).join(' ');
        }

        function showError(message) {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('editForm').style.display = 'none';
            document.getElementById('errorState').style.display = 'block';
            document.getElementById('errorMessage').textContent = message;
        }

        function formatCurrency(num) {
            return new Intl.NumberFormat('th-TH', { style: 'currency', currency: 'THB' }).format(num || 0);
        }
    </script>
</body>

</html>