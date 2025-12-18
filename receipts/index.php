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
                    <div class="col-lg-8 offset-lg-2 mb-4">
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
                                               autocomplete="off"
                                               style="font-size: 16px; text-align: center;">
                                        <small class="text-muted">
                                            กรอก<strong>เลขบัตรประชาชน 13 หลัก</strong> หรือ <strong>เลขที่ใบเสร็จ</strong>
                                        </small>
                                    </div>
                                    
                                    <!-- Search Button -->
                                    <div class="col-12">
                                        <button type="submit" class="btn btn__secondary btn__rounded btn__block btn__xhight mt-10" id="searchBtn">
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
                    <div class="col-lg-8 offset-lg-2">
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

    <script src="../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <style>
        /* Contact Panel - เหมือนหน้า donat */
        .contact-panel { 
            background: #fff; 
            border-radius: 10px; 
            box-shadow: 0 5px 30px rgba(0,0,0,0.08); 
            padding: 30px; 
        }

        .contact-panel__title {
            color: #1b3a5f;
            font-weight: 600;
        }

        .form-label {
            font-weight: 600;
            color: #1b3a5f;
            margin-bottom: 8px;
            font-size: 14px;
        }

        /* Results Header */
        .results-header {
            padding: 15px 20px;
            background: #fff;
            border-radius: 10px 10px 0 0;
            box-shadow: 0 5px 30px rgba(0,0,0,0.08);
        }

        .results-header h5 {
            color: #1b3a5f;
            font-weight: 600;
        }

        .badge {
            font-size: 12px;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .bg-primary {
            background: #00a651 !important;
        }

        /* Result Item Card - เหมือน contact-panel */
        .result-item {
            background: #fff;
            border-radius: 10px;
            padding: 24px;
            margin-bottom: 16px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.08);
        }

        .result-item__header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .result-item__receipt-no {
            font-size: 16px;
            color: #00a651;
            font-weight: 600;
        }

        .result-item__amount {
            text-align: right;
        }

        .result-item__amount-value {
            font-size: 20px;
            font-weight: 700;
            color: #00a651;
        }

        .result-item__amount-label {
            font-size: 12px;
            color: #888;
        }

        .result-item__details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .result-item__detail {
            font-size: 14px;
            color: #666;
        }

        .result-item__detail-label {
            font-weight: 600;
            color: #1b3a5f;
            margin-right: 8px;
        }

        .result-item__footer {
            display: flex;
            justify-content: flex-end;
            padding-top: 16px;
            border-top: 1px solid #f1f1f1;
        }

        .btn__open-receipt {
            background: #1b3a5f;
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn__open-receipt:hover {
            background: #2c5282;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .result-item__header {
                flex-direction: column;
            }

            .result-item__amount {
                text-align: left;
                margin-top: 12px;
            }
        }
    </style>

    <script>
    // API Base URL
    const API_BASE = '/appdev/edonation/api/v1';

    document.addEventListener('DOMContentLoaded', function() {
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
        searchForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const keyword = document.getElementById('keyword').value.trim();
            
            // ตรวจสอบรูปแบบ
            const validation = validateSearchKeyword(keyword);
            
            if (!validation.valid) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                    html: `
                        <p>กรุณากรอกอย่างใดอย่างหนึ่ง:</p>
                        <ul style="text-align: left; margin-top: 10px;">
                            <li><strong>เลขบัตรประชาชน</strong> ครบ 13 หลัก</li>
                            <li><strong>เลขที่ใบเสร็จ</strong></li>
                        </ul>
                    `,
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
                input.addEventListener('input', function(e) {
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
    </script>
</body>
</html>
