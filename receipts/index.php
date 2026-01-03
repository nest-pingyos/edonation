<!DOCTYPE html>
<html lang="th">

<?php include_once('../config/head.php'); ?>

<body>
    <div class="wrapper">
        <?php include_once('../config/header.php'); ?>

        <section class="team-layout1 pb-80 pt-80">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-6 offset-lg-3">
                        <div class="heading text-center mb-60">
                            <h3 class="heading__title">ค้นหาใบเสร็จรับเงิน</h3>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Search Form Card -->
                    <div class="col-lg-12 mb-4">
                        <div class="contact-panel">
                            <form id="searchForm">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <h5 class="contact-panel__title mb-20">กรอกข้อมูลเพื่อค้นหาใบเสร็จ</h5>
                                    </div>

                                    <!-- Keyword Input -->
                                    <div class="col-12 mb-3">
                                        <label class="form-label">ค้นหาใบเสร็จ</label>
                                        <input type="text" class="form-control" id="keyword" name="keyword"
                                            placeholder="กรอกเลขบัตรประชาชน 13 หลัก หรือ เลขที่ใบเสร็จ"
                                            autocomplete="off" style="font-size: 16px; text-align: center;">
                                        <small class="text-muted">
                                            กรอก<strong>เลขบัตรประชาชน 13 หลัก</strong> หรือ
                                            <strong>เลขที่ใบเสร็จ</strong>
                                        </small>
                                    </div>

                                    <!-- Search Button -->
                                    <div class="col-12">
                                        <button type="submit"
                                            class="btn btn__secondary btn__rounded btn__block btn__xhight mt-10"
                                            id="searchBtn">
                                            <span>ค้นหาใบเสร็จ</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Results Section -->
                <div class="row mt-30">
                    <div class="col-lg-12">
                        <!-- Loading State -->
                        <div id="loadingState" class="text-center py-5" style="display: none;">
                            <div class="loading"><span></span><span></span><span></span><span></span></div>
                            <p class="mt-3">กำลังค้นหา...</p>
                        </div>

                        <!-- Empty State -->
                        <div id="emptyState" class="text-center py-5">
                            <p class="text-muted">กรอกเลขบัตรประชาชนหรือเลขที่ใบเสร็จเพื่อค้นหา</p>
                        </div>

                        <!-- Results Container -->
                        <div id="resultsContainer" style="display: none;">
                            <div class="results-header mb-3">
                                <h5 class="m-0">
                                    ผลการค้นหา
                                    <span class="badge bg-primary" id="resultCount">0</span> รายการ
                                </h5>
                            </div>
                            <div id="resultsList"></div>
                        </div>

                        <!-- No Results State -->
                        <div id="noResultsState" class="text-center py-5" style="display: none;">
                            <h5>ไม่พบใบเสร็จ</h5>
                            <p class="text-muted">ลองค้นหาด้วยคำค้นอื่น</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php include_once('../config/footer.php'); ?>
        <button id="scrollTopBtn"><i class="fas fa-long-arrow-alt-up"></i></button>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <!-- Receipt Search Styles -->
    <link rel="stylesheet" href="../assets/css/receipt-search.css">

    <script>
        // Get API_BASE from meta tag (set by PHP head.php)
        const API_BASE = document.querySelector('meta[name="api-base"]')?.content || '/edonation/api/v1';

        document.addEventListener('DOMContentLoaded', function () {
            const searchForm = document.getElementById('searchForm');
            const loadingState = document.getElementById('loadingState');
            const emptyState = document.getElementById('emptyState');
            const resultsContainer = document.getElementById('resultsContainer');
            const noResultsState = document.getElementById('noResultsState');
            const resultsList = document.getElementById('resultsList');
            const resultCount = document.getElementById('resultCount');

            // Hide all states
            function hideAllStates() {
                loadingState.style.display = 'none';
                emptyState.style.display = 'none';
                resultsContainer.style.display = 'none';
                noResultsState.style.display = 'none';
            }

            // Format currency
            function formatCurrency(amount) {
                return new Intl.NumberFormat('th-TH', {
                    style: 'currency',
                    currency: 'THB',
                    minimumFractionDigits: 2
                }).format(amount);
            }

            // Format Thai date
            function formatThaiDate(dateStr) {
                if (!dateStr) return 'ไม่ระบุ';
                const date = new Date(dateStr);
                const options = {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    timeZone: 'Asia/Bangkok'
                };
                return date.toLocaleDateString('th-TH', options);
            }

            // ตรวจสอบรูปแบบการค้นหา
            function validateSearchKeyword(keyword) {
                // ลบ dash ออกเพื่อตรวจสอบ
                const cleanKeyword = keyword.replace(/\D/g, '');

                // ตรวจสอบว่าเป็นเลขบัตรประชาชน 13 หลัก
                if (cleanKeyword.length === 13) {
                    return { valid: true, type: 'id_card', value: cleanKeyword };
                }

                // ตรวจสอบว่าเป็นเลขที่ใบเสร็จ (รูปแบบเก่า: 2568/00001 หรือใหม่: 2568-E0001)
                if (/^\d{4}[\/-]E?\d{4,5}$/.test(keyword)) {
                    return { valid: true, type: 'receipt_no', value: keyword };
                }

                return { valid: false };
            }

            // Search form submit
            searchForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                const keyword = document.getElementById('keyword').value.trim();

                // ตรวจสอบรูปแบบ
                const validation = validateSearchKeyword(keyword);

                if (!validation.valid) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'กรุณากรอกข้อมูลให้ถูกต้อง',
                        text: 'กรอกเลขบัตรประชาชน 13 หลัก หรือ เลขที่ใบเสร็จ',
                        confirmButtonColor: '#00a651'
                    });
                    return;
                }

                // Show loading
                hideAllStates();
                loadingState.style.display = 'block';

                try {
                    // Call API endpoint
                    const response = await fetch(`${API_BASE}/receipts/search?keyword=${encodeURIComponent(keyword)}`);
                    const result = await response.json();

                    hideAllStates();

                    if (result.success && result.data && result.data.length > 0) {
                        displayResults(result.data);
                    } else if (result.success && (!result.data || result.data.length === 0)) {
                        noResultsState.style.display = 'block';
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: result.error?.message || 'ไม่สามารถค้นหาได้',
                            confirmButtonColor: '#00a651'
                        });
                        emptyState.style.display = 'block';
                    }
                } catch (error) {
                    console.error('Search error:', error);
                    hideAllStates();
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถเชื่อมต่อ API ได้ กรุณาลองใหม่',
                        confirmButtonColor: '#00a651'
                    });
                    emptyState.style.display = 'block';
                }
            });

            // Display results
            function displayResults(results) {
                resultsList.innerHTML = '';
                resultCount.textContent = results.length;

                results.forEach(item => {
                    const card = document.createElement('div');
                    card.className = 'result-item';
                    card.innerHTML = `
                    <div class="result-item__header">
                        <div>
                            <div class="result-item__receipt-no">
                                เลขที่ใบเสร็จ: ${escapeHtml(item.receipt_no || '-')}
                            </div>
                        </div>
                        <div class="result-item__amount">
                            <div class="result-item__amount-value">${formatCurrency(item.amount || 0)}</div>
                            <div class="result-item__amount-label">จำนวนเงินบริจาค</div>
                        </div>
                    </div>
                    <div class="result-item__details">
                        <div class="result-item__detail">
                            <span class="result-item__detail-label">โครงการ:</span>
                            <span>${escapeHtml(item.project_name || '-')}</span>
                        </div>
                        <div class="result-item__detail">
                            <span class="result-item__detail-label">วันที่:</span>
                            <span>${formatThaiDate(item.receipt_date)}</span>
                        </div>
                        <div class="result-item__detail">
                            <span class="result-item__detail-label">ช่องทาง:</span>
                            <span>${escapeHtml(item.pay_by || 'QR Payment')}</span>
                        </div>
                    </div>
                    <div class="result-item__footer">
                        <button type="button" class="btn__open-receipt" 
                                onclick="openReceipt('${item.id}')">
                            เปิดใบเสร็จ
                        </button>
                    </div>
                `;
                    resultsList.appendChild(card);
                });

                resultsContainer.style.display = 'block';
            }

            // Escape HTML
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        });

        // Open receipt with verification via API
        async function openReceipt(receiptId) {
            const { value: taxId } = await Swal.fire({
                title: 'ยืนยันตัวตน',
                html: `
                <p class="mb-3">กรุณากรอกหมายเลขประจำตัวผู้เสียภาษี<br>เพื่อเปิดดูใบเสร็จรับเงิน</p>
                <input type="text" id="taxIdInput" class="swal2-input" 
                       placeholder="x-xxxx-xxxxx-xx-x" maxlength="17"
                       style="font-size: 16px; text-align: center;">
            `,
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#00a651',
                cancelButtonColor: '#6c757d',
                focusConfirm: false,
                didOpen: () => {
                    const input = document.getElementById('taxIdInput');
                    input.focus();

                    // Auto-format ID card
                    input.addEventListener('input', function (e) {
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
                },
                preConfirm: () => {
                    const inputValue = document.getElementById('taxIdInput').value;
                    if (!inputValue) {
                        Swal.showValidationMessage('กรุณากรอกหมายเลขประจำตัวผู้เสียภาษี');
                        return false;
                    }
                    return inputValue;
                }
            });

            if (taxId) {
                try {
                    // Verify via API
                    const verifyResponse = await fetch(`${API_BASE}/receipts/${receiptId}/verify?tax_id=${encodeURIComponent(taxId)}`, {
                        credentials: 'include' // ส่ง session cookie
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
                            // พยายามเปิด Popup ก่อน
                            const pdfWindow = window.open(pdfResult.data.pdf_url, '_blank');

                            // ตรวจสอบว่าโดน Block หรือไม่
                            if (!pdfWindow || pdfWindow.closed || typeof pdfWindow.closed == 'undefined') {
                                // ถ้าโดน Block ให้แสดงปุ่มให้ user กดเปิดเอง
                                Swal.fire({
                                    icon: 'success',
                                    title: 'ยืนยันตัวตนสำเร็จ',
                                    text: 'Browser ปิดกั้นการเปิดหน้าต่างใหม่ กรุณากดปุ่มด้านล่างเพื่อเปิดใบเสร็จ',
                                    confirmButtonText: 'เปิดดูใบเสร็จ',
                                    confirmButtonColor: '#00a651',
                                    showCancelButton: true,
                                    cancelButtonText: 'ปิด',
                                    reverseButtons: true
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.open(pdfResult.data.pdf_url, '_blank');
                                    }
                                });
                            }
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
    </script>
</body>

</html>