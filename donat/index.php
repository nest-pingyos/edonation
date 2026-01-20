<!DOCTYPE html>
<html lang="th">

<?php
$pageTitle = "ร่วมบริจาค";
$pageDesc = "รายละเอียดโครงการบริจาคและช่องทางการชำระเงิน คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่";
include_once('../config/head.php');

$basePath = defined('BASE_PATH') ? BASE_PATH : '/appdev/edonation';
?>

<!-- Select2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<!-- Donation Page Styles -->
<link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/donation.css" />

<body>
    <div class="wrapper">
        <?php include_once('../config/header.php'); ?>

        <section class="donation-page">
            <div class="donation-container">

                <!-- Loading State -->
                <div class="donation-card" id="loadingState">
                    <div class="state-container">
                        <div class="spinner"></div>
                        <p class="state-desc">กำลังโหลดข้อมูลโครงการ...</p>
                    </div>
                </div>

                <!-- Error State -->
                <div class="donation-card" id="errorState" style="display: none;">
                    <div class="state-container">
                        <div style="font-size: 2rem; color: #dc3545; margin-bottom: 14px;">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <h2 class="state-title" id="errorMessage">ไม่พบโครงการ</h2>
                        <p class="state-desc">กรุณาเลือกโครงการจากหน้าหลัก</p>
                        <a href="../home/" class="back-btn">
                            <i class="fas fa-arrow-left"></i> กลับหน้าหลัก
                        </a>
                    </div>
                </div>

                <!-- Main Card -->
                <div class="donation-card" id="mainCard" style="display: none;">
                    <!-- Project Header (Clean Design) -->
                    <div class="project-header-clean">
                        <h1 class="project-title-clean" id="projectTitle">-</h1>
                    </div>

                    <!-- Stepper -->
                    <div style="padding: 0 28px;">
                        <div class="stepper-wrapper">
                            <div class="stepper-item active" id="stepper1">
                                <div class="step-counter">1</div>
                                <div class="step-name">ระบุข้อมูล</div>
                            </div>
                            <div class="stepper-item" id="stepper2">
                                <div class="step-counter">2</div>
                                <div class="step-name">ตรวจสอบ</div>
                            </div>
                            <div class="stepper-item" id="stepper3">
                                <div class="step-counter">3</div>
                                <div class="step-name">ชำระเงิน</div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Body -->
                    <form class="form-body" id="donationForm" novalidate>
                        <input type="hidden" id="project_number" name="project_number">
                        <input type="hidden" id="project_name_hidden" name="project_name">

                        <!-- Step 1: Input -->
                        <div id="step1-content">
                            <!-- Amount -->
                            <div class="form-section">
                                <div class="section-label">จำนวนเงินบริจาค</div>
                                <div class="amount-grid">
                                    <button type="button" class="amount-btn" data-amount="100">฿100</button>
                                    <button type="button" class="amount-btn" data-amount="500">฿500</button>
                                    <button type="button" class="amount-btn" data-amount="1000">฿1,000</button>
                                    <button type="button" class="amount-btn" data-amount="5000">฿5,000</button>
                                </div>
                                <input type="text" class="form-control" id="amount" name="amount"
                                    placeholder="หรือระบุจำนวนเอง (บาท)" inputmode="numeric" required>
                            </div>

                            <!-- Basic Info -->
                            <div class="form-section">
                                <div class="section-label">ข้อมูลผู้บริจาค</div>
                                <div class="form-row cols-2">
                                    <div>
                                        <label class="form-label">ประเภท <span class="required">*</span></label>
                                        <select class="form-select" id="type" name="type" required>
                                            <option value="บุคคลทั่วไป" selected>บุคคลทั่วไป</option>
                                            <option value="ศิษย์เก่าคณะพยาบาล มช.">ศิษย์เก่า มช.</option>
                                            <option value="บุคลากร อาจารย์คณะพยาบาล มช.">บุคลากร/อาจารย์</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">เบอร์โทรศัพท์ <span class="required">*</span></label>
                                        <input type="tel" class="form-control" id="phone" name="phone"
                                            placeholder="0812345678" pattern="[0-9]{10}" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Receipt Option -->
                            <div class="form-section">
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" id="needReceipt" name="needReceipt">
                                    <span class="checkbox-label">ต้องการใบเสร็จรับเงิน / ใบอนุโมทนาบัตร</span>
                                </label>

                                <div id="receiptSection" style="display: none;">
                                    <div class="receipt-section">
                                        <div class="receipt-title">ข้อมูลสำหรับใบเสร็จ</div>



                                        <div class="form-row">
                                            <div>
                                                <label class="form-label">ที่อยู่ <span
                                                        class="required">*</span></label>
                                                <input type="text" class="form-control" id="receiptAddressLine"
                                                    placeholder="บ้านเลขที่, หมู่, ซอย, ถนน">
                                            </div>
                                        </div>

                                        <div class="form-row cols-4" style="margin-top: 12px;">
                                            <div>
                                                <label class="form-label">จังหวัด <span
                                                        class="required">*</span></label>
                                                <select class="form-select" id="receiptProvince"></select>
                                            </div>
                                            <div>
                                                <label class="form-label">อำเภอ/เขต</label>
                                                <select class="form-select" id="receiptDistrict" disabled></select>
                                            </div>
                                            <div>
                                                <label class="form-label">ตำบล/แขวง</label>
                                                <select class="form-select" id="receiptSubdistrict" disabled></select>
                                            </div>
                                            <div>
                                                <label class="form-label">รหัสไปรษณีย์</label>
                                                <input type="text" class="form-control" id="receiptPostcode" readonly>
                                            </div>
                                        </div>
                                        <input type="hidden" id="receiptAddress" name="receiptAddress">

                                        <div class="receipt-title"
                                            style="margin-top: 24px; border-bottom: none; padding-bottom: 0;">
                                            <label class="checkbox-wrapper"
                                                style="background: none; border: none; padding: 0; gap: 8px;">
                                                <input type="checkbox" id="useSameAddressCb">
                                                <span class="checkbox-label"
                                                    style="font-weight: 500; color: var(--secondary);">ใช้ที่อยู่เดียวกับใบเสร็จ</span>
                                            </label>
                                        </div>

                                        <div id="shippingFields">
                                            <div class="form-row">
                                                <div>
                                                    <label class="form-label">ที่อยู่</label>
                                                    <input type="text" class="form-control" id="shippingAddressLine"
                                                        placeholder="บ้านเลขที่, หมู่, ซอย, ถนน">
                                                </div>
                                            </div>

                                            <div class="form-row cols-4" style="margin-top: 12px;">
                                                <div>
                                                    <label class="form-label">จังหวัด</label>
                                                    <select class="form-select" id="shippingProvince"></select>
                                                </div>
                                                <div>
                                                    <label class="form-label">อำเภอ/เขต</label>
                                                    <select class="form-select" id="shippingDistrict" disabled></select>
                                                </div>
                                                <div>
                                                    <label class="form-label">ตำบล/แขวง</label>
                                                    <select class="form-select" id="shippingSubdistrict"
                                                        disabled></select>
                                                </div>
                                                <div>
                                                    <label class="form-label">รหัสไปรษณีย์</label>
                                                    <input type="text" class="form-control" id="shippingPostcode"
                                                        readonly>
                                                </div>
                                            </div>
                                            <input type="hidden" id="shippingAddress" name="shippingAddress">
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="submit-btn" id="nextBtn">
                                    <span>ตรวจสอบข้อมูล</span>
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Review -->
                        <div id="step2-content" style="display: none;">
                            <div class="donation-summary" id="donationSummary">
                                <!-- Populated by JS -->
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <button type="button" class="submit-btn" id="backBtn"
                                    style="background: #fff; color: var(--text-secondary); border: 1px solid var(--border-color);">
                                    <i class="fas fa-arrow-left"></i>
                                    <span>แก้ไขข้อมูล</span>
                                </button>
                                <button type="submit" class="submit-btn" id="submitBtn">
                                    <span>ยืนยันการบริจาค</span>
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Payment (QR) -->
                        <div id="step3-content" style="display: none; text-align: center;">
                            <!-- Thai QR Payment Logo -->
                            <div style="margin-bottom: 12px;">
                                <img src="../assets/images/Thai_QR_Payment_Logo-01.jpg" alt="Thai QR Payment"
                                    style="max-width: 280px; width: 100%; border-radius: 8px;">
                            </div>

                            <!-- PromptPay Logo -->
                            <div style="margin-bottom: 20px;">
                                <img src="../assets/images/PromptPay2.png" alt="PromptPay"
                                    style="height: 50px; border: 1px solid #e7ebef; border-radius: 6px; padding: 8px 16px; background: #fff;">
                            </div>

                            <!-- QR Code -->
                            <div style="margin-bottom: 20px;">
                                <img id="qrImage" src="" alt="QR Code"
                                    style="width: 280px; height: 280px; display: block; margin: 0 auto;">
                            </div>

                            <!-- Timer -->
                            <div style="margin-bottom: 16px;">
                                <div id="paymentTimer"
                                    style="font-size: 1.8rem; font-weight: 600; color: var(--secondary);">15:00
                                </div>
                            </div>

                            <!-- Reference Number -->
                            <div id="refDisplay"
                                style="margin-bottom: 20px; padding: 12px 20px; background: #f8f9fa; border-radius: 8px; display: inline-block;">
                                <div style="font-size: 0.85rem; color: #6c757d; margin-bottom: 4px;">เลขอ้างอิง (Ref.1)
                                </div>
                                <div id="refNumber"
                                    style="font-size: 1.1rem; font-weight: 600; color: var(--primary); font-family: monospace; letter-spacing: 1px;">
                                    -</div>
                            </div>

                            <!-- Waiting Button -->
                            <div style="margin-bottom: 20px;">
                                <button type="button" class="submit-btn" id="waitingBtn" disabled
                                    style="max-width: 240px; margin: 0 auto; background: var(--primary); opacity: 0.9;">
                                    <div class="spinner"
                                        style="width: 18px; height: 18px; margin: 0; border-width: 2px;"></div>
                                    <span>รอการชำระเงิน...</span>
                                </button>
                            </div>

                            <!-- Test Button (Dev Only) -->
                            <div class="text-center mt-3 mb-3">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    onclick="openTestPaymentModal()">
                                    <i class="fas fa-bug"></i> Test Payment (JSON)
                                </button>
                            </div>

                            <input type="hidden" id="qrAmount" value="0">
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <?php include_once('../config/footer.php'); ?>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // API Configuration
        // API Configuration
        const API_BASE = '<?php echo $basePath; ?>/api/v1';
        const AUTOPROVINCE_API = '<?php echo $basePath; ?>/shared/autoprovince/api.php';

        // Get Project ID
        const pathParts = window.location.pathname.split('/');
        const projectNum = pathParts[pathParts.length - 1] !== 'index.php' && pathParts[pathParts.length - 1] !== ''
            ? pathParts[pathParts.length - 1]
            : (new URLSearchParams(window.location.search).get('id') || '001');

        document.addEventListener('DOMContentLoaded', function () {
            loadProject(projectNum);
            setupAmountButtons();
            setupStepper();
            setupReceiptSection();
            initAutoProvince();

            // Phone number validation
            const ph = document.getElementById('phone');
            if (ph) {
                ph.addEventListener('input', function (e) {
                    let v = e.target.value.replace(/\D/g, '');
                    if (v.length > 10) v = v.slice(0, 10);
                    e.target.value = v;
                });
            }
        });

        function setupStepper() {
            const nextBtn = document.getElementById('nextBtn');
            const backBtn = document.getElementById('backBtn');

            nextBtn.addEventListener('click', () => {
                if (validateStep1()) {
                    showReviewStep();
                }
            });

            backBtn.addEventListener('click', () => {
                const step1 = document.getElementById('step1-content');
                const step2 = document.getElementById('step2-content');
                const stepper1 = document.getElementById('stepper1');
                const stepper2 = document.getElementById('stepper2');

                step2.style.display = 'none';
                step1.style.display = 'block';

                stepper2.classList.remove('active');
                stepper1.classList.remove('completed');
                stepper1.classList.add('active');
            });
        }

        function validateStep1() {
            const amt = document.getElementById('amount');
            const ph = document.getElementById('phone');
            let ok = true;

            [amt, ph].forEach(el => el.style.borderColor = '');

            // Check Amount - get raw number value
            const amtVal = getAmountValue();
            if (!amtVal || amtVal < 1) {
                amt.style.borderColor = '#dc3545';
                ok = false;
            }
            // Check Phone
            if (!ph.value || !/^[0-9]{10}$/.test(ph.value)) {
                ph.style.borderColor = '#dc3545';
                ok = false;
            }

            // Check Receipt Fields if checked
            const needReceipt = document.getElementById('needReceipt').checked;
            if (needReceipt) {
                // Name and ID Card removed
                const reqFields = ['receiptProvince', 'receiptAddressLine'];
                reqFields.forEach(id => {
                    const el = document.getElementById(id);
                    if (id === 'receiptProvince') {
                        if (!$(el).val()) { ok = false; }
                    } else {
                        if (!el.value.trim()) { el.style.borderColor = '#dc3545'; ok = false; }
                        else el.style.borderColor = '';
                    }
                });
            }

            if (!ok) {
                Swal.fire({ icon: 'warning', title: 'กรุณากรอกข้อมูลให้ครบถ้วน', timer: 1500, showConfirmButton: false });
            }
            return ok;
        }

        function showReviewStep() {
            const step1 = document.getElementById('step1-content');
            const step2 = document.getElementById('step2-content');
            const stepper1 = document.getElementById('stepper1');
            const stepper2 = document.getElementById('stepper2');

            // Collect Data - get raw number value
            const amount = getAmountValue();
            const phone = document.getElementById('phone').value;
            const needReceipt = document.getElementById('needReceipt').checked;

            let html = `
                <div class="summary-row">
                    <span class="summary-label">จำนวนเงิน</span>
                    <span class="summary-value" style="color: var(--primary); font-size: 1.1rem;">฿${fmt(amount)}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">โครงการ</span>
                    <span class="summary-value">${document.getElementById('projectTitle').textContent}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">เบอร์โทรศัพท์</span>
                    <span class="summary-value">${phone}</span>
                </div>
            `;

            if (needReceipt) {
                // const name = document.getElementById('firstName').value + ' ' + document.getElementById('lastName').value;
                const addr = document.getElementById('receiptAddress').value || '-';

                html += `
                    <div class="summary-row" style="margin-top: 16px; border-top: 1px dashed var(--border-color); padding-top: 12px;">
                        <span class="summary-label">ขอใบเสร็จรับเงิน</span>
                        <span class="summary-value text-success"><i class="fas fa-check-circle"></i> ต้องการ</span>
                    </div>
                    <!-- Name removed -->
                    <div class="summary-row" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                        <span class="summary-label">ที่อยู่ใบเสร็จ</span>
                        <span class="summary-value" style="text-align: left; font-weight: 500;">${addr}</span>
                    </div>
                `;
            } else {
                html += `
                    <div class="summary-row">
                        <span class="summary-label">ขอใบเสร็จรับเงิน</span>
                        <span class="summary-value text-muted">ไม่ต้องการ</span>
                    </div>
                `;
            }

            document.getElementById('donationSummary').innerHTML = html;

            step1.style.display = 'none';
            step2.style.display = 'block';

            stepper1.classList.remove('active');
            stepper1.classList.add('completed');
            stepper2.classList.add('active');

            document.querySelector('.stepper-wrapper').scrollIntoView({ behavior: 'smooth' });
        }

        function initAutoProvince() {
            ['receipt', 'shipping'].forEach(prefix => {
                const sel = {
                    province: `#${prefix}Province`,
                    district: `#${prefix}District`,
                    subdistrict: `#${prefix}Subdistrict`,
                    postcode: `#${prefix}Postcode`,
                    addressLine: `#${prefix}AddressLine`,
                    fullAddress: `#${prefix}Address`
                };
                initAddressDropdowns(sel);
            });
        }

        function initAddressDropdowns(sel) {
            const $p = $(sel.province), $d = $(sel.district), $s = $(sel.subdistrict), $pc = $(sel.postcode);

            [$p, $d, $s].forEach($el => $el.select2({ theme: 'bootstrap-5', width: '100%', placeholder: 'เลือก...', allowClear: true }));

            $.get(AUTOPROVINCE_API + '?action=get_provinces', res => {
                if (res.status === 'success') {
                    $p.empty().append('<option></option>');
                    res.data.forEach(i => $p.append(new Option(i.name, i.id)));
                }
            });

            $p.on('change', function () {
                $d.empty().append('<option></option>').prop('disabled', true).trigger('change.select2');
                $s.empty().append('<option></option>').prop('disabled', true).trigger('change.select2');
                $pc.val('');
                updateAddr(sel);
                if ($(this).val()) {
                    $.post(AUTOPROVINCE_API + '?action=get_districts', { province_id: $(this).val() }, res => {
                        if (res.status === 'success') {
                            $d.prop('disabled', false);
                            res.data.forEach(i => $d.append(new Option(i.name, i.id)));
                        }
                    });
                }
            });

            $d.on('change', function () {
                $s.empty().append('<option></option>').prop('disabled', true).trigger('change.select2');
                $pc.val('');
                updateAddr(sel);
                if ($(this).val()) {
                    $.post(AUTOPROVINCE_API + '?action=get_subdistricts', { district_id: $(this).val() }, res => {
                        if (res.status === 'success') {
                            $s.prop('disabled', false);
                            res.data.forEach(i => {
                                const o = new Option(i.name, i.id);
                                $(o).data('postcode', i.postcode);
                                $s.append(o);
                            });
                        }
                    });
                }
                $s.on('change', function () {
                    const sel2 = $(this).select2('data')[0];
                    if (sel2?.element) {
                        const pc = $(sel2.element).data('postcode');
                        if (pc && pc !== '0') $pc.val(pc);
                    }
                    updateAddr(sel);
                });

            });



            $(sel.addressLine).on('change blur', () => updateAddr(sel));
        }

        function updateAddr(sel) {
            const parts = [$(sel.addressLine).val()];
            const sub = $(sel.subdistrict).find(':selected').text();
            const dist = $(sel.district).find(':selected').text();
            const prov = $(sel.province).find(':selected').text();
            const pc = $(sel.postcode).val();
            if (sub && sub !== 'เลือก...') parts.push('ต.' + sub);
            if (dist && dist !== 'เลือก...') parts.push('อ.' + dist);
            if (prov && prov !== 'เลือก...') parts.push('จ.' + prov);
            if (pc) parts.push(pc);
            $(sel.fullAddress).val(parts.filter(p => p && p.trim() !== '').join(' '));
        }

        function setupReceiptSection() {
            const cb = document.getElementById('needReceipt');
            const sec = document.getElementById('receiptSection');
            const sameAddrCb = document.getElementById('useSameAddressCb');
            const shippingFields = document.getElementById('shippingFields');

            cb.addEventListener('change', () => {
                sec.style.display = cb.checked ? 'block' : 'none';
            });

            sameAddrCb.addEventListener('change', () => {
                if (sameAddrCb.checked) {
                    shippingFields.style.display = 'none';
                    syncAddressDirectly();
                } else {
                    shippingFields.style.display = 'block';
                }
            });

            // Sync logic when fields change (if checked)
            ['receiptAddressLine', 'receiptProvince', 'receiptDistrict', 'receiptSubdistrict', 'receiptPostcode', 'receiptAddress'].forEach(id => {
                if (id === 'receiptAddressLine') {
                    document.getElementById(id).addEventListener('input', () => { if (sameAddrCb.checked) syncAddressDirectly(); });
                } else {
                    $(`#${id}`).on('change', () => { if (sameAddrCb.checked) syncAddressDirectly(); });
                }
            });




        }

        function syncAddressDirectly() {
            $('#shippingAddressLine').val($('#receiptAddressLine').val());

            const p = $('#receiptProvince').val();
            if (p && p != $('#shippingProvince').val()) {
                $('#shippingProvince').val(p).trigger('change');
                setTimeout(() => {
                    const d = $('#receiptDistrict').val();
                    if (d) {
                        $('#shippingDistrict').val(d).trigger('change');
                        setTimeout(() => {
                            const s = $('#receiptSubdistrict').val();
                            if (s) {
                                $('#shippingSubdistrict').val(s).trigger('change');
                                $('#shippingPostcode').val($('#receiptPostcode').val());
                            }
                        }, 400);
                    }
                }, 400);
            } else {
                if (!p) {
                    $('#shippingProvince').val(null).trigger('change');
                }
            }
        }

        async function loadProject(num) {
            try {
                const res = await fetch(`${API_BASE}/projects/${num}`);
                const r = await res.json();
                if (r.success && r.data) displayProject(r.data);
                else showError('ไม่พบโครงการนี้');
            } catch (e) { showError('ไม่สามารถโหลดข้อมูลได้'); }
        }

        function displayProject(p) {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('mainCard').style.display = 'block';

            // Only update project title (other elements have been removed)
            document.getElementById('projectTitle').textContent = p.project_name;

            // Hidden fields for form submission
            document.getElementById('project_number').value = p.project_number;
            document.getElementById('project_name_hidden').value = p.project_name;
        }

        function showError(msg) {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('errorState').style.display = 'block';
            document.getElementById('errorMessage').textContent = msg;
        }

        // ==========================================
        // TEST SIMULATION FUNCTION
        // ==========================================
        async function openTestPaymentModal() {
            const data = window.currentData || {};
            if (!data.billPaymentRef1) {
                Swal.fire('Warning', 'No transaction data found. Please complete step 2 first.', 'warning');
                return;
            }

            const { value: formValues } = await Swal.fire({
                title: 'Test Payment Simulation',
                html: `
                    <div style="text-align: left; padding: 0 20px;">
                        <div class="mb-2">
                            <label class="small text-muted">Bill Payment Ref1</label>
                            <input id="swal-ref1" class="swal2-input" style="margin: 0; width: 100%;" value="${data.billPaymentRef1 || ''}">
                        </div>
                        <div class="mb-2">
                            <label class="small text-muted">Bill Payment Ref2</label>
                            <input id="swal-ref2" class="swal2-input" style="margin: 0; width: 100%;" value="${data.billPaymentRef2 || ''}">
                        </div>
                        <div class="mb-2">
                            <label class="small text-muted">Amount</label>
                            <input id="swal-amount" class="swal2-input" style="margin: 0; width: 100%;" value="${data.amount || ''}">
                        </div>
                    </div>
                `,
                focusConfirm: false,
                confirmButtonText: 'Send JSON',
                showCancelButton: true,
                preConfirm: () => {
                    return {
                        ref1: document.getElementById('swal-ref1').value,
                        ref2: document.getElementById('swal-ref2').value,
                        amount: document.getElementById('swal-amount').value
                    }
                }
            });

            if (formValues) {
                simulatePayment(formValues.ref1, formValues.ref2, formValues.amount);
            }
        }

        async function simulatePayment(ref1, ref2, amount) {
            const payload = {
                "payeeProxyId": "099400258783792",
                "payeeProxyType": "BILLERID",
                "payeeAccountNumber": "5663044095",
                "payeeName": "FACULTY OF NURSING CMU",
                "payerAccountNumber": "5662488652",
                "payerAccountName": "พัชรพล ปิงยศ",
                "payerName": "พัชรพล ปิงยศ",
                "sendingBankCode": "014",
                "receivingBankCode": "014",
                "amount": parseFloat(amount).toFixed(2),
                "transactionId": "TEST_" + new Date().getTime(),
                "transactionDateandTime": new Date().toISOString(),
                "billPaymentRef1": ref1,
                "billPaymentRef2": ref2,
                "currencyCode": "764",
                "channelCode": "PMH",
                "transactionType": "Domestic Transfers"
            };

            Swal.fire({
                title: 'Sending...',
                didOpen: () => Swal.showLoading()
            });

            try {
                // Determine absolute URL for receive.php
                // Assuming /edonation/recieve.php based on project structure
                const response = await fetch('/edonation/recieve.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const resultText = await response.text();

                let displayMsg = resultText;
                try {
                    const json = JSON.parse(resultText);
                    displayMsg = JSON.stringify(json, null, 2);
                } catch (e) { }

                Swal.fire({
                    icon: response.ok ? 'success' : 'error',
                    title: 'Server Response',
                    html: `<pre style="text-align:left; max-height: 200px; overflow:auto;">${displayMsg}</pre>`
                });

            } catch (error) {
                console.error(error);
                Swal.fire('Error', error.message, 'error');
            }
        }

        function fmt(n) { return new Intl.NumberFormat('th-TH').format(n); }

        // Get raw amount value (remove commas)
        function getAmountValue() {
            const val = document.getElementById('amount').value;
            return parseFloat(val.replace(/,/g, '')) || 0;
        }

        // Format amount input with commas
        function formatAmountInput(input) {
            let val = input.value.replace(/[^0-9]/g, '');
            if (val) {
                val = parseInt(val, 10).toLocaleString('th-TH');
            }
            input.value = val;
        }

        function setupAmountButtons() {
            const amountInput = document.getElementById('amount');

            document.querySelectorAll('.amount-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    // Format the amount with comma
                    amountInput.value = parseInt(this.dataset.amount).toLocaleString('th-TH');
                });
            });

            amountInput.addEventListener('input', function () {
                formatAmountInput(this);
                if (this.value) document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
            });
        }

        document.getElementById('donationForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const amt = document.getElementById('amount');
            const ph = document.getElementById('phone');

            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span>กำลังดำเนินการ...</span>';

            const nr = document.getElementById('needReceipt').checked;
            const data = {
                project_number: document.getElementById('project_number').value,
                project_name: document.getElementById('project_name_hidden').value,
                type: document.getElementById('type').value,
                phone: ph.value,
                amount: getAmountValue(), // Use raw number without commas
                needReceipt: nr
            };

            if (nr) {
                data.receiptAddress = document.getElementById('receiptAddress').value;
                data.shippingAddress = document.getElementById('shippingAddress').value;

                // Send split address fields
                data.addressLine = document.getElementById('receiptAddressLine').value;
                // Get text from Select2
                const pData = $('#receiptProvince').select2('data')[0];
                const aData = $('#receiptDistrict').select2('data')[0];
                const dData = $('#receiptSubdistrict').select2('data')[0];

                data.province = pData ? pData.text : '';
                data.amphure = aData ? aData.text : '';
                data.district = dData ? dData.text : '';
                data.zipCode = document.getElementById('receiptPostcode').value;
            }

            try {
                const res = await fetch(`${API_BASE}/donations`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const r = await res.json();

                if (r.success) {
                    showPaymentStep(r.data);
                } else throw new Error(r.error?.message || 'เกิดข้อผิดพลาด');
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: err.message });
                btn.disabled = false;
                btn.innerHTML = '<span>ยืนยันการบริจาค</span><i class="fas fa-check"></i>';
            }
        });

        function showPaymentStep(data) {
            window.currentData = data; // Save for testing
            const step2 = document.getElementById('step2-content');
            const step3 = document.getElementById('step3-content');
            const stepper2 = document.getElementById('stepper2');
            const stepper3 = document.getElementById('stepper3');

            // Update UI
            step2.style.display = 'none';
            step3.style.display = 'block';

            stepper2.classList.remove('active');
            stepper2.classList.add('completed');
            stepper3.classList.add('active');

            // Set QR info
            document.getElementById('qrAmount').textContent = fmt(data.amount);

            // Load QR from API
            const timestamp = new Date().getTime();
            document.getElementById('qrImage').src = `qrcode_api.php?id=${data.id}&ref=${data.billPaymentRef1}&ref2=${data.billPaymentRef2 || ''}&amount=${data.amount}&t=${timestamp}`;

            // Show Info Popup
            Swal.fire({
                icon: 'info',
                title: 'ข้อแนะนำการชำระเงิน',
                html: '<div style="text-align: left; font-size: 0.95rem; line-height: 1.6;">' +
                    '<p style="margin-bottom: 15px;">เพื่อประโยชน์ในการลดหย่อนภาษี กรุณาใช้ <strong>"บัญชีอิเล็กทรอนิกส์"</strong> หรือ <strong>"แอปธนาคาร"</strong> ที่เป็นชื่อของตัวท่านเองในการสแกนจ่าย</p>' +
                    '<hr style="margin: 15px 0; border-top: 1px solid #eee;">' +
                    '<p style="margin-bottom: 5px; color: #666; font-size: 0.9rem;">กรณีที่ท่านไม่สะดวกในการดำเนินการบริจาคด้วยตนเอง กรุณาติดต่อ:</p>' +
                    '<p style="margin-bottom: 0px; color: #213360; font-weight: 500;"><i class="fas fa-phone-alt me-2"></i> 053-949075 (คุณชนิดา ต้นพิพัฒน์)</p>' +
                    '</div>',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#fb974e',
                customClass: {
                    content: 'text-start'
                }
            });

            // Display Ref Number
            if (data.billPaymentRef1) {
                document.getElementById('refNumber').textContent = data.billPaymentRef1;
            }

            // Start Timer (15 mins)
            let timeLeft = 15 * 60;
            const timerEl = document.getElementById('paymentTimer');
            const timerInt = setInterval(() => {
                const m = Math.floor(timeLeft / 60);
                const s = timeLeft % 60;
                timerEl.textContent = `${m}:${s < 10 ? '0' : ''}${s}`;
                if (timeLeft <= 0) clearInterval(timerInt);
                timeLeft--;
            }, 1000);

            // Start Status Check
            const statusInt = setInterval(async () => {
                try {
                    const res = await fetch(`${API_BASE}/donations/${data.id}/status`);
                    const result = await res.json();
                    if (result.success && result.data.status === 'completed') {
                        clearInterval(statusInt);
                        clearInterval(timerInt);
                        Swal.fire({
                            icon: 'success',
                            title: 'ชำระเงินสำเร็จ!',
                            text: 'ขอบคุณที่ร่วมบริจาค',
                            showCancelButton: true,
                            confirmButtonText: '<i class="fas fa-receipt"></i> แสดงใบเสร็จรับเงิน',
                            cancelButtonText: '<i class="fas fa-home"></i> หน้าหลัก',
                            confirmButtonColor: '#fb974e',
                            cancelButtonColor: '#213360',
                            reverseButtons: true
                        }).then((res) => {
                            if (res.isConfirmed) {
                                window.location.href = '../receipts/';
                            } else {
                                window.location.href = '../home/';
                            }
                        });
                    }
                } catch (e) { }
            }, 3000);

            document.querySelector('.stepper-wrapper').scrollIntoView({ behavior: 'smooth' });
        }


    </script>
</body>

</html>