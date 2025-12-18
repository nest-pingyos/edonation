<!DOCTYPE html>
<html lang="th">

<?php 
$pageTitle = "ร่วมบริจาค";
$pageDesc = "รายละเอียดโครงการบริจาคและช่องทางการชำระเงิน คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่";
include_once('../config/head.php'); 
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
                                <img id="projectImage" src="" alt="Project Image" onerror="this.src='../assets/images/projects/pro-1.jpg'">
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
                                            <input type="checkbox" class="form-check-input" id="needReceipt" name="needReceipt">
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
                                                    <input type="text" class="form-control" id="firstName" name="firstName" placeholder="ชื่อ">
                                                </div>
                                                <div class="col-sm-6 mb-3">
                                                    <label>นามสกุล <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="lastName" name="lastName" placeholder="นามสกุล">
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label>เลขประจำตัวประชาชน <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="idCard" name="idCard" 
                                                           placeholder="x-xxxx-xxxxx-xx-x" maxlength="17">
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label>ที่อยู่สำหรับระบุบนใบเสร็จ <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" id="receiptAddress" name="receiptAddress" 
                                                              rows="3" placeholder="บ้านเลขที่ ซอย ถนน แขวง/ตำบล เขต/อำเภอ จังหวัด รหัสไปรษณีย์"></textarea>
                                                </div>
                                            </div>

                                            <h6 class="mb-2 mt-4">
                                                ที่อยู่สำหรับจัดส่งใบอนุโมทนาบัตร
                                                <a href="javascript:void(0)" class="copy-address-link" id="useSameAddressBtn">ใช้ที่อยู่เดียวกัน</a>
                                            </h6>
                                            
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <label>ที่อยู่จัดส่ง <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" id="shippingAddress" name="shippingAddress" 
                                                              rows="3" placeholder="บ้านเลขที่ ซอย ถนน แขวง/ตำบล เขต/อำเภอ จังหวัด รหัสไปรษณีย์"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit -->
                                    <div class="col-12 mt-3">
                                        <button type="submit" class="btn btn__primary btn__rounded btn__block" id="submitBtn">
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

    <script src="../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>


    <script>
    const API_BASE = '/appdev/edonation/api/v1';
    
    // Get project_number from URL path or query parameter
    function getProjectNumber() {
        // Try URL path first: /donat/P003
        const pathMatch = window.location.pathname.match(/\/donat\/([A-Za-z0-9_-]+)\/?$/);
        if (pathMatch) return pathMatch[1];
        
        // Fallback to query parameter: /donat/?project_number=P003
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('project_number');
    }
    
    const projectNumber = getProjectNumber();

    document.addEventListener('DOMContentLoaded', function() {
        if (!projectNumber) {
            showError('ไม่พบรหัสโครงการ');
            return;
        }
        loadProject(projectNumber);
        setupAmountButtons();
        setupReceiptSection();
        
        // Clear validation error on type change
        document.getElementById('type').addEventListener('change', function() {
            this.classList.remove('is-invalid');
        });
    });

    function setupReceiptSection() {
        const checkbox = document.getElementById('needReceipt');
        const section = document.getElementById('receiptSection');
        const useSameBtn = document.getElementById('useSameAddressBtn');
        
        // Toggle receipt section
        checkbox.addEventListener('change', function() {
            section.style.display = this.checked ? 'block' : 'none';
            
            // Toggle required on receipt fields
            const fields = ['firstName', 'lastName', 'idCard', 'receiptAddress', 'shippingAddress'];
            fields.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.required = this.checked;
            });
        });
        
        // Copy address button
        useSameBtn.addEventListener('click', function() {
            const receiptAddr = document.getElementById('receiptAddress').value;
            document.getElementById('shippingAddress').value = receiptAddr;
        });
        
        // Format ID card input
        document.getElementById('idCard').addEventListener('input', function(e) {
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
            btn.addEventListener('click', function() {
                document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('amount').value = this.dataset.amount;
            });
        });
        
        // Clear selection when typing custom amount
        document.getElementById('amount').addEventListener('input', function() {
            if (this.value) {
                document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
            }
        });
    }

    document.getElementById('donationForm').addEventListener('submit', async function(e) {
        // Custom validation for select
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