<!DOCTYPE html>
<html lang="th">

<?php
$pageTitle = "ร่วมบริจาค";
$pageDesc = "รายละเอียดโครงการบริจาคและช่องทางการชำระเงิน คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่";
include_once('../config/head.php');

$basePath = defined('BASE_PATH') ? BASE_PATH : '/appdev/edonation';
?>

<style>
    :root {
        /* System Primary Colors */
        --primary: #fb974e;
        --primary-hover: #e8863f;
        --secondary: #213360;
        --secondary-light: #2d4a7c;

        /* Whites & Grays */
        --white: #ffffff;
        --bg-light: #f8f9fa;
        --border-color: #e7ebef;
        --text-main: #0e204d;
        --text-secondary: #5a6a85;
        --text-muted: #9aa5b5;

        /* Status */
        --success: #28a745;

        /* Spacing */
        --radius: 12px;
        --radius-sm: 8px;
        --card-shadow: 0 2px 12px rgba(33, 51, 96, 0.08);
    }

    .donation-page {
        background: var(--bg-light);
        min-height: 100vh;
        padding: 40px 0 80px;
    }

    .donation-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Card */
    .donation-card {
        background: var(--white);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    /* Project Header - White with Image */
    .project-header {
        display: grid;
        grid-template-columns: 260px 1fr;
        background: var(--white);
        border-bottom: 1px solid var(--border-color);
    }

    @media (max-width: 768px) {
        .project-header {
            grid-template-columns: 1fr;
        }
    }

    .project-image {
        width: 100%;
        height: 100%;
        min-height: 180px;
        object-fit: cover;
        background: var(--bg-light);
    }

    .project-info {
        padding: 24px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .project-badge {
        display: inline-block;
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--success);
        background: rgba(40, 167, 69, 0.1);
        padding: 4px 10px;
        border-radius: 20px;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        width: fit-content;
    }

    .project-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--secondary);
        margin-bottom: 6px;
        line-height: 1.4;
    }

    .project-desc {
        font-size: 0.85rem;
        color: var(--text-secondary);
        line-height: 1.5;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Progress */
    .progress-mini {
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid var(--border-color);
    }

    .progress-stats {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 8px;
    }

    .progress-amount {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary);
    }

    .progress-target {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .progress-bar-wrapper {
        height: 6px;
        background: var(--border-color);
        border-radius: 3px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--primary), #ffc078);
        border-radius: 3px;
        transition: width 0.8s ease;
    }

    .progress-meta {
        display: flex;
        justify-content: space-between;
        margin-top: 6px;
        font-size: 0.7rem;
        color: var(--text-muted);
    }

    /* Form Body */
    .form-body {
        padding: 28px;
        background: var(--white);
    }

    .form-section {
        margin-bottom: 24px;
    }

    .form-section:last-child {
        margin-bottom: 0;
    }

    .section-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }

    /* Amount Selection */
    .amount-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
        margin-bottom: 10px;
    }

    .amount-btn {
        padding: 10px 6px;
        border: 1.5px solid var(--border-color);
        border-radius: var(--radius-sm);
        background: var(--white);
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-main);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .amount-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .amount-btn.active {
        border-color: var(--primary);
        background: var(--primary);
        color: var(--white);
    }

    /* Form Controls */
    .form-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--text-secondary);
        margin-bottom: 4px;
    }

    .form-label .required {
        color: #dc3545;
    }

    .form-control,
    .form-select {
        width: 100%;
        height: 38px;
        padding: 0 12px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        font-size: 0.875rem;
        color: var(--text-main);
        background: var(--white);
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        line-height: 38px;
    }

    .form-control:focus,
    .form-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(251, 151, 78, 0.15);
    }

    .form-control::placeholder {
        color: var(--text-muted);
    }

    .form-control:disabled,
    .form-select:disabled {
        background: var(--bg-light);
    }

    .form-row {
        display: grid;
        gap: 12px;
        margin-bottom: 12px;
    }

    .form-row.cols-2 {
        grid-template-columns: repeat(2, 1fr);
    }

    .form-row.cols-3 {
        grid-template-columns: repeat(3, 1fr);
    }

    .form-row.cols-4 {
        grid-template-columns: repeat(4, 1fr);
    }

    @media (max-width: 576px) {

        .form-row.cols-2,
        .form-row.cols-3,
        .form-row.cols-4 {
            grid-template-columns: 1fr;
        }

        .amount-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /* Checkbox */
    .checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        background: var(--bg-light);
        border-radius: var(--radius-sm);
        cursor: pointer;
        border: 1px solid var(--border-color);
    }

    .checkbox-wrapper:hover {
        border-color: var(--primary);
    }

    .checkbox-wrapper input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: var(--primary);
    }

    .checkbox-label {
        font-size: 0.85rem;
        color: var(--text-main);
        font-weight: 500;
    }

    /* Receipt Section */
    .receipt-section {
        margin-top: 16px;
        padding: 18px;
        background: var(--white);
        border-radius: var(--radius-sm);
        border: 1px solid var(--border-color);
    }

    .receipt-title {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--secondary);
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .copy-link {
        font-size: 0.75rem;
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
    }

    .copy-link:hover {
        text-decoration: underline;
        color: var(--primary-hover);
    }

    /* Submit Button - Primary Orange */
    .submit-btn {
        width: 100%;
        height: 46px;
        background: var(--primary);
        color: var(--white);
        border: none;
        border-radius: var(--radius-sm);
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 8px;
    }

    .submit-btn:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(251, 151, 78, 0.3);
    }

    .submit-btn:disabled {
        background: var(--text-muted);
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* States */
    .state-container {
        text-align: center;
        padding: 50px 28px;
        background: var(--white);
    }

    .spinner {
        width: 32px;
        height: 32px;
        border: 3px solid var(--border-color);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin: 0 auto 16px;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .state-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 6px;
    }

    .state-desc {
        color: var(--text-secondary);
        font-size: 0.85rem;
        margin-bottom: 18px;
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        background: var(--secondary);
        color: var(--white);
        border-radius: var(--radius-sm);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .back-btn:hover {
        background: var(--secondary-light);
        color: var(--white);
    }

    /* Select2 */
    .select2-container--bootstrap-5 .select2-selection {
        border: 1px solid var(--border-color) !important;
        border-radius: var(--radius-sm) !important;
        min-height: 38px !important;
        height: 38px !important;
        padding: 0 8px !important;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        font-size: 0.875rem !important;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }

    .select2-container--bootstrap-5.select2-container--focus .select2-selection,
    .select2-container--bootstrap-5.select2-container--open .select2-selection {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(251, 151, 78, 0.15) !important;
    }
</style>

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
                    <!-- Project Header -->
                    <div class="project-header">
                        <img id="projectImage" class="project-image" src="" alt="Project"
                            onerror="this.src='../assets/images/projects/pro-1.jpg'">
                        <div class="project-info">
                            <span class="project-badge" id="projectBadge">เปิดรับบริจาค</span>
                            <h1 class="project-title" id="projectTitle">-</h1>
                            <p class="project-desc" id="projectDescription">-</p>

                            <div class="progress-mini">
                                <div class="progress-stats">
                                    <span class="progress-amount" id="currentAmount">฿0</span>
                                    <span class="progress-target">เป้าหมาย <span id="targetAmount">฿0</span></span>
                                </div>
                                <div class="progress-bar-wrapper">
                                    <div class="progress-bar-fill" id="progressBar" style="width: 0%;"></div>
                                </div>
                                <div class="progress-meta">
                                    <span><span id="donorCount">0</span> ผู้สนับสนุน</span>
                                    <span id="progressPercent">0%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Body -->
                    <form class="form-body" id="donationForm" novalidate>
                        <input type="hidden" id="project_number" name="project_number">
                        <input type="hidden" id="project_name_hidden" name="project_name">

                        <!-- Amount -->
                        <div class="form-section">
                            <div class="section-label">จำนวนเงินบริจาค</div>
                            <div class="amount-grid">
                                <button type="button" class="amount-btn" data-amount="100">฿100</button>
                                <button type="button" class="amount-btn" data-amount="500">฿500</button>
                                <button type="button" class="amount-btn" data-amount="1000">฿1,000</button>
                                <button type="button" class="amount-btn" data-amount="5000">฿5,000</button>
                            </div>
                            <input type="number" class="form-control" id="amount" name="amount"
                                placeholder="หรือระบุจำนวนเอง (บาท)" min="1" required>
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

                                    <div class="form-row cols-3">
                                        <div>
                                            <label class="form-label">ชื่อ <span class="required">*</span></label>
                                            <input type="text" class="form-control" id="firstName" placeholder="ชื่อ">
                                        </div>
                                        <div>
                                            <label class="form-label">นามสกุล <span class="required">*</span></label>
                                            <input type="text" class="form-control" id="lastName" placeholder="นามสกุล">
                                        </div>
                                        <div>
                                            <label class="form-label">เลขบัตรประชาชน <span
                                                    class="required">*</span></label>
                                            <input type="text" class="form-control" id="idCard"
                                                placeholder="x-xxxx-xxxxx-xx-x" maxlength="17">
                                        </div>
                                    </div>

                                    <div class="receipt-title" style="margin-top: 16px;">ที่อยู่สำหรับใบเสร็จ</div>
                                    <div class="form-row">
                                        <div>
                                            <label class="form-label">ที่อยู่</label>
                                            <input type="text" class="form-control" id="receiptAddressLine"
                                                placeholder="บ้านเลขที่ ซอย ถนน">
                                        </div>
                                    </div>
                                    <div class="form-row cols-4">
                                        <div>
                                            <label class="form-label">จังหวัด</label>
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

                                    <div class="receipt-title" style="margin-top: 16px;">
                                        ที่อยู่จัดส่ง
                                        <a href="javascript:void(0)" class="copy-link"
                                            id="useSameAddressBtn">ใช้ที่อยู่เดียวกัน</a>
                                    </div>
                                    <div class="form-row">
                                        <div>
                                            <label class="form-label">ที่อยู่</label>
                                            <input type="text" class="form-control" id="shippingAddressLine"
                                                placeholder="บ้านเลขที่ ซอย ถนน">
                                        </div>
                                    </div>
                                    <div class="form-row cols-4">
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
                                            <select class="form-select" id="shippingSubdistrict" disabled></select>
                                        </div>
                                        <div>
                                            <label class="form-label">รหัสไปรษณีย์</label>
                                            <input type="text" class="form-control" id="shippingPostcode" readonly>
                                        </div>
                                    </div>
                                    <input type="hidden" id="shippingAddress" name="shippingAddress">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn" id="submitBtn">
                            <span>ดำเนินการบริจาค</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>
                </div>

            </div>
        </section>

        <?php include_once('../config/footer.php'); ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/main.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        const API_BASE = document.querySelector('meta[name="api-base"]')?.content || '/edonation/api/v1';
        const AUTOPROVINCE_API = '<?php echo $basePath; ?>/shared/autoprovince/api.php';

        function getProjectNumber() {
            const m = window.location.pathname.match(/\/donat\/([A-Za-z0-9_-]+)\/?$/);
            return m ? m[1] : new URLSearchParams(window.location.search).get('project_number');
        }

        const projectNumber = getProjectNumber();

        document.addEventListener('DOMContentLoaded', function () {
            if (!projectNumber) { showError('ไม่พบรหัสโครงการ'); return; }
            loadProject(projectNumber);
            setupAmountButtons();
            setupReceiptSection();
            initAutoProvince();
        });

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
            });

            $s.on('change', function () {
                const sel2 = $(this).select2('data')[0];
                if (sel2?.element) {
                    const pc = $(sel2.element).data('postcode');
                    if (pc && pc !== '0') $pc.val(pc);
                }
                updateAddr(sel);
            });

            $(sel.addressLine).on('change blur', () => updateAddr(sel));
        }

        function updateAddr(sel) {
            const parts = [$(sel.addressLine).val()];
            const sub = $(sel.subdistrict).find(':selected').text();
            const dist = $(sel.district).find(':selected').text();
            const prov = $(sel.province).find(':selected').text();
            const pc = $(sel.postcode).val();
            if (sub) parts.push('ต.' + sub);
            if (dist) parts.push('อ.' + dist);
            if (prov) parts.push('จ.' + prov);
            if (pc) parts.push(pc);
            $(sel.fullAddress).val(parts.filter(p => p).join(' '));
        }

        function setupReceiptSection() {
            const cb = document.getElementById('needReceipt');
            const sec = document.getElementById('receiptSection');
            const btn = document.getElementById('useSameAddressBtn');

            cb.addEventListener('change', () => {
                sec.style.display = cb.checked ? 'block' : 'none';
            });

            btn.addEventListener('click', () => {
                $('#shippingAddressLine').val($('#receiptAddressLine').val());
                const p = $('#receiptProvince').val();
                if (p) {
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
                }
            });

            document.getElementById('idCard').addEventListener('input', function (e) {
                let v = e.target.value.replace(/\D/g, '').slice(0, 13);
                let f = v.slice(0, 1);
                if (v.length > 1) f += '-' + v.slice(1, 5);
                if (v.length > 5) f += '-' + v.slice(5, 10);
                if (v.length > 10) f += '-' + v.slice(10, 12);
                if (v.length > 12) f += '-' + v.slice(12, 13);
                e.target.value = f;
            });
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

            document.getElementById('projectTitle').textContent = p.project_name;
            document.getElementById('projectDescription').textContent = p.description || p.short_description || '';
            document.getElementById('projectBadge').textContent = p.status === 'active' ? 'เปิดรับบริจาค' : 'โครงการ';

            if (p.image_url) document.getElementById('projectImage').src = p.image_url;

            const cur = parseFloat(p.current_amount) || 0;
            const tar = parseFloat(p.target_amount) || 100000;
            const pct = Math.min(100, (cur / tar) * 100);

            document.getElementById('currentAmount').textContent = '฿' + fmt(cur);
            document.getElementById('targetAmount').textContent = '฿' + fmt(tar);
            document.getElementById('donorCount').textContent = fmt(p.donor_count || 0);
            document.getElementById('progressBar').style.width = pct + '%';
            document.getElementById('progressPercent').textContent = Math.round(pct) + '%';

            document.getElementById('project_number').value = p.project_number;
            document.getElementById('project_name_hidden').value = p.project_name;
        }

        function showError(msg) {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('errorState').style.display = 'block';
            document.getElementById('errorMessage').textContent = msg;
        }

        function fmt(n) { return new Intl.NumberFormat('th-TH').format(n); }

        function setupAmountButtons() {
            document.querySelectorAll('.amount-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    document.getElementById('amount').value = this.dataset.amount;
                });
            });
            document.getElementById('amount').addEventListener('input', function () {
                if (this.value) document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
            });
        }

        document.getElementById('donationForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const amt = document.getElementById('amount');
            const ph = document.getElementById('phone');
            let ok = true;

            [amt, ph].forEach(el => el.style.borderColor = '');

            if (!amt.value || parseFloat(amt.value) < 1) { amt.style.borderColor = '#dc3545'; ok = false; }
            if (!ph.value || !/^[0-9]{10}$/.test(ph.value)) { ph.style.borderColor = '#dc3545'; ok = false; }
            if (!ok) return;

            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span>กำลังดำเนินการ...</span>';

            const nr = document.getElementById('needReceipt').checked;
            const data = {
                project_number: document.getElementById('project_number').value,
                project_name: document.getElementById('project_name_hidden').value,
                type: document.getElementById('type').value,
                phone: ph.value,
                amount: parseFloat(amt.value),
                needReceipt: nr
            };

            if (nr) {
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
                const r = await res.json();

                if (r.success) {
                    Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: 'กำลังไปหน้าชำระเงิน...', timer: 2000, showConfirmButton: false })
                        .then(() => window.location.href = `qrgenerator.php?id=${r.data.id}&ref=${r.data.billPaymentRef1}&amount=${data.amount}`);
                } else throw new Error(r.error?.message || 'เกิดข้อผิดพลาด');
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: err.message });
                btn.disabled = false;
                btn.innerHTML = '<span>ดำเนินการบริจาค</span><i class="fas fa-arrow-right"></i>';
            }
        });
    </script>
</body>

</html>