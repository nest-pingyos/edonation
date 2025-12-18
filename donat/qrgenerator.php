<!DOCTYPE html>
<html lang="th">

<?php include_once('../config/head.php'); ?>
<!-- Custom Styles to match Theme -->
<!-- Custom Styles to match Theme -->
<link rel="stylesheet" href="../assets/css/qr-generator.css">

<body>
    <div class="wrapper">
        <!-- Optional: Header can be hidden if we want a pure slip look, keeping it for nav -->
        <?php include_once('../config/header.php'); ?>

        <section class="donation-confirmation-section">
            <div class="container">

                <!-- Loading -->
                <div id="loadingState">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>

                <!-- Error -->
                <div id="errorState" style="display: none;">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle mr-2"></i> <span id="errorMessage">Unknow Error</span>
                    </div>
                </div>

                <!-- Main Card -->
                <div id="qrContent" class="single-card" style="display: none;">

                    <!-- 1. Header Logo (Removed) -->

                    <!-- 2. Payment Banners -->
                    <div class="payment-banner-area">
                        <!-- Thai QR Payment Banner -->
                        <div class="thai-qr-banner" style="background: none; height: auto;">
                            <!-- Removed max-width restriction to let CSS control it (100% fill) -->
                            <img src="../assets/images/Thai_QR_Payment_Logo-01.jpg" alt="Thai QR Payment"
                                style="width: 100%; height: auto; border-radius: 4px;">
                        </div>

                        <!-- PromptPay Logo -->
                        <img src="../assets/images/PromptPay2.png" alt="PromptPay" class="promptpay-logo">
                    </div>

                    <!-- 3. QR Code -->
                    <div class="qr-area">
                        <div class="qr-frame">
                            <img id="qrImage" src="" alt="Scan to Pay">
                        </div>
                        <!-- Time or Ref Display below QR -->
                        <div class="ref-time-text" id="currentTime">00:00</div>

                        <!-- Status Badge (Waiting/Paid) -->
                        <div id="statusBox">
                            <span class="badge badge-warning font-weight-normal px-3 py-2">
                                <span class="spinner-grow spinner-grow-sm mr-1" role="status" aria-hidden="true"
                                    style="vertical-align: middle;"></span>
                                รอการชำระเงิน...
                            </span>
                        </div>
                    </div>

                    <!-- 4. Footer Bar -->
                    <div class="slip-footer">
                        <div class="footer-item">
                            <span class="footer-label">หมายเลขอ้างอิง</span>
                            <span class="footer-value" id="refNumber">-</span>
                        </div>
                        <div class="footer-item" style="align-items: flex-end;">
                            <span class="footer-label">ยอดชำระ</span>
                            <span class="amount-highlight"><span id="amountDisplay">-</span> บาท</span>
                        </div>
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
        const urlParams = new URLSearchParams(window.location.search);
        const donationId = urlParams.get('id');

        let checkInterval = null;
        let timeInterval = null;

        document.addEventListener('DOMContentLoaded', function () {
            if (!donationId) {
                showError('ไม่พบรหัสอ้างอิงการบริจาค');
                return;
            }
            startClock();
            loadData();
        });

        function startClock() {
            const timeEl = document.getElementById('currentTime');
            const updateTime = () => {
                const now = new Date();
                timeEl.textContent = now.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
            };
            updateTime();
            timeInterval = setInterval(updateTime, 1000);
        }

        async function loadData() {
            try {
                const res = await fetch(`${API_BASE}/donations/${donationId}/qr`);
                const result = await res.json();

                if (result.success) {
                    renderData(result.data);
                    startStatusCheck();
                } else {
                    throw new Error('ไม่พบข้อมูลการบริจาค');
                }
            } catch (err) {
                showError('เกิดข้อผิดพลาดในการโหลดข้อมูล');
                console.error(err);
            }
        }

        function renderData(data) {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('qrContent').style.display = 'flex';

            // Footer Info
            document.getElementById('refNumber').textContent = data.billPaymentRef1;
            document.getElementById('amountDisplay').textContent = new Intl.NumberFormat('th-TH').format(data.amount);

            // QR Image (ส่ง ref1 และ ref2 ไปสร้าง QR)
            const timestamp = new Date().getTime();
            const ref2 = data.billPaymentRef2 || '';
            document.getElementById('qrImage').src = `qrcode_api.php?ref=${data.billPaymentRef1}&ref2=${ref2}&amount=${data.amount}&t=${timestamp}`;
        }

        function startStatusCheck() {
            checkInterval = setInterval(async () => {
                try {
                    const res = await fetch(`${API_BASE}/donations/${donationId}/status`, {
                        credentials: 'include'
                    });
                    const result = await res.json();

                    if (result.success && result.data.status === 'completed') {
                        clearInterval(checkInterval);
                        clearInterval(timeInterval); // Stop clock

                        const data = result.data;
                        const statusBox = document.getElementById('statusBox');

                        // เก็บ receipt_id ไว้ใช้
                        window.currentReceiptId = data.receipt_id;

                        let statusHtml = '<div class="d-flex flex-column align-items-center">';
                        statusHtml += '<span class="badge badge-success px-3 py-2 mb-2"><i class="fas fa-check-circle mr-1"></i> ชำระเงินเรียบร้อยแล้ว</span>';

                        // ปุ่มเปิดใบเสร็จ (ต้องยืนยัน Tax ID)
                        if (data.receipt_id) {
                            statusHtml += `<button onclick="openReceipt(${data.receipt_id})" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="fas fa-file-invoice mr-1"></i> ดูใบเสร็จรับเงิน
                                      </button>`;
                        }
                        statusHtml += '</div>';

                        statusBox.innerHTML = statusHtml;

                        Swal.fire({
                            icon: 'success',
                            title: 'ชำระเงินสำเร็จ!',
                            html: `ขอบคุณที่ร่วมบริจาค<br>ยอดเงิน ${new Intl.NumberFormat('th-TH').format(data.amount)} บาท`,
                            confirmButtonText: 'กลับหน้าหลัก',
                            confirmButtonColor: '#00a651',
                            showCancelButton: !!data.receipt_id,
                            cancelButtonText: '<i class="fas fa-file-invoice"></i> ดูใบเสร็จ',
                            cancelButtonColor: '#1a3a5c',
                            allowOutsideClick: false,
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '../home/';
                            } else if (result.dismiss === Swal.DismissReason.cancel) {
                                openReceipt(data.receipt_id);
                            }
                        });
                    }
                } catch (err) { }
            }, 5000);
        }

        // ฟังก์ชันเปิดใบเสร็จ (ต้องยืนยัน Tax ID ก่อน)
        async function openReceipt(receiptId) {
            const { value: taxId } = await Swal.fire({
                title: 'ยืนยันตัวตน',
                html: `
                <p class="mb-3">กรุณากรอกเลขประจำตัวผู้เสียภาษี 13 หลัก<br>เพื่อยืนยันตัวตนก่อนเปิดใบเสร็จ</p>
                <input type="text" id="taxIdInput" class="swal2-input" placeholder="x-xxxx-xxxxx-xx-x" maxlength="17" style="text-align: center;">
            `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                confirmButtonColor: '#1a3a5c',
                cancelButtonText: 'ยกเลิก',
                focusConfirm: false,
                preConfirm: () => {
                    const inputValue = document.getElementById('taxIdInput').value.replace(/\D/g, '');
                    if (inputValue.length !== 13) {
                        Swal.showValidationMessage('กรุณากรอกเลขประจำตัวผู้เสียภาษีให้ครบ 13 หลัก');
                        return false;
                    }
                    return inputValue;
                }
            });

            if (taxId) {
                try {
                    // Verify via API
                    const verifyResponse = await fetch(`${API_BASE}/receipts/${receiptId}/verify?tax_id=${encodeURIComponent(taxId)}`, {
                        credentials: 'include'
                    });
                    const verifyResult = await verifyResponse.json();

                    if (verifyResult.success && verifyResult.data?.verified) {
                        // ดึง access_token ที่ได้จาก verify
                        const accessToken = verifyResult.data.access_token;

                        // Get PDF URL via API พร้อม token
                        const pdfResponse = await fetch(`${API_BASE}/receipts/${receiptId}/pdf?access_token=${encodeURIComponent(accessToken)}`, {
                            credentials: 'include'
                        });
                        const pdfResult = await pdfResponse.json();

                        if (pdfResult.success && pdfResult.data?.pdf_url) {
                            window.open(pdfResult.data.pdf_url, '_blank');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: pdfResult.error?.message || 'ไม่สามารถดึงข้อมูลใบเสร็จได้',
                                confirmButtonColor: '#00a651'
                            });
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'ไม่สามารถยืนยันตัวตนได้',
                            text: verifyResult.error?.message || 'เลขประจำตัวผู้เสียภาษีไม่ถูกต้อง',
                            confirmButtonColor: '#00a651'
                        });
                    }
                } catch (error) {
                    console.error('Verification error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถเชื่อมต่อ API ได้',
                        confirmButtonColor: '#00a651'
                    });
                }
            }
        }

        function showError(msg) {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('errorState').style.display = 'block';
            document.getElementById('errorMessage').textContent = msg;
        }
    </script>
</body>

</html>