<?php include 'partials/main.php'; ?>
<?php requireAuth(); ?>

<!doctype html>
<html lang="th">

<head>
    <?php
    $title = "รายละเอียดใบเสร็จ";
    include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php'; ?>
    <style>
        .info-label { font-size: 12px; color: #8486a7; font-weight: 500; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 2px; }
        .info-value { font-size: 14px; color: var(--bs-headings-color); font-weight: 500; }
        .amount-display { font-size: 2.5rem; font-weight: 700; line-height: 1.1; }
        .status-bar { height: 4px; border-radius: 2px; }
        .section-title { font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #8486a7; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid var(--bs-border-color); }
        .shipping-badge { font-size: 12px; padding: 4px 10px; border-radius: 20px; }
        @media print {
            .no-print { display: none !important; }
            .card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include 'partials/edonation-nav.php'; ?>

        <div class="page-content">
            <?php include 'partials/edonation-topbar.php'; ?>

            <div class="container-xxl">

                <!-- Loading State -->
                <div id="loadingState" class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;"></div>
                    <div class="text-muted">กำลังโหลดข้อมูล...</div>
                </div>

                <!-- Error State -->
                <div id="errorState" style="display:none;">
                    <div class="text-center py-5">
                        <iconify-icon icon="solar:danger-circle-bold-duotone" class="text-danger" style="font-size:4rem;"></iconify-icon>
                        <h5 class="mt-3 text-danger" id="errorMessage">เกิดข้อผิดพลาด</h5>
                        <a href="receipts-list.php" class="btn btn-outline-secondary mt-3">
                            <iconify-icon icon="solar:arrow-left-bold-duotone" class="me-1"></iconify-icon>
                            กลับรายการใบเสร็จ
                        </a>
                    </div>
                </div>

                <!-- Content -->
                <div id="mainContent" style="display:none;">

                    <!-- Page Title -->
                    <div class="d-flex align-items-start justify-content-between mb-4 no-print flex-wrap gap-3">
                        <div>
                            <a href="receipts-list.php" class="text-muted small d-inline-flex align-items-center gap-1 mb-2">
                                <iconify-icon icon="solar:arrow-left-linear"></iconify-icon> กลับรายการใบเสร็จ
                            </a>
                            <h4 class="mb-0 d-flex align-items-center gap-2 flex-wrap">
                                <iconify-icon icon="solar:receipt-bold-duotone" class="text-primary fs-22"></iconify-icon>
                                ใบเสร็จรับเงิน
                                <span class="badge bg-light text-dark font-monospace fw-semibold fs-14" id="hdr-receipt-no"></span>
                                <span id="hdr-status-badge"></span>
                            </h4>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                                <iconify-icon icon="solar:printer-bold-duotone" class="me-1"></iconify-icon>
                                พิมพ์
                            </button>
                            <button class="btn btn-outline-primary btn-sm" id="btnEdit" onclick="editReceipt()">
                                <iconify-icon icon="solar:pen-bold-duotone" class="me-1"></iconify-icon>
                                แก้ไข
                            </button>
                            <button class="btn btn-primary btn-sm" id="btnPdf" onclick="downloadPdf()">
                                <iconify-icon icon="solar:file-download-bold-duotone" class="me-1"></iconify-icon>
                                PDF
                            </button>
                            <button class="btn btn-outline-danger btn-sm" id="btnCancel" onclick="cancelReceipt()">
                                <iconify-icon icon="solar:close-circle-bold-duotone" class="me-1"></iconify-icon>
                                ยกเลิก
                            </button>
                        </div>
                    </div>

                    <div class="row g-4">

                        <!-- LEFT COLUMN -->
                        <div class="col-lg-8">

                            <!-- Receipt Info Card -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <iconify-icon icon="solar:document-text-bold-duotone" class="me-1 text-primary"></iconify-icon>
                                        ข้อมูลใบเสร็จ
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-4">
                                        <div class="col-sm-6">
                                            <div class="info-label">เลขที่ใบเสร็จ</div>
                                            <div class="info-value font-monospace fs-15" id="d-receipt-no">-</div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="info-label">วันที่ออกใบเสร็จ</div>
                                            <div class="info-value" id="d-receipt-date">-</div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="info-label">โครงการ</div>
                                            <div class="info-value" id="d-project">-</div>
                                            <div class="text-muted small font-monospace" id="d-project-no"></div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="info-label">วิธีชำระเงิน</div>
                                            <div class="info-value" id="d-pay-by">-</div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="info-label">ปีงบประมาณ</div>
                                            <div class="info-value" id="d-fiscal-year">-</div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="info-label">สถานะ</div>
                                            <div id="d-status">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Donor Info Card -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <iconify-icon icon="solar:user-bold-duotone" class="me-1 text-primary"></iconify-icon>
                                        ข้อมูลผู้บริจาค
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-4">
                                        <div class="col-sm-6">
                                            <div class="info-label">ชื่อ-นามสกุล</div>
                                            <div class="info-value" id="d-name">-</div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="info-label">เลขบัตรประชาชน</div>
                                            <div class="info-value font-monospace" id="d-idcard">-</div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="info-label">อีเมล</div>
                                            <div class="info-value" id="d-email">-</div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="info-label">โทรศัพท์</div>
                                            <div class="info-value" id="d-phone">-</div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="info-label">อาชีพ</div>
                                            <div class="info-value" id="d-occupation">-</div>
                                        </div>
                                        <div class="col-sm-6" id="d-member-wrap">
                                            <div class="info-label">รหัสสมาชิก</div>
                                            <div class="info-value" id="d-member"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Address Card -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <iconify-icon icon="solar:map-point-bold-duotone" class="me-1 text-primary"></iconify-icon>
                                        ที่อยู่สำหรับออกใบเสร็จ
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="info-value lh-lg" id="d-address">-</div>
                                </div>
                            </div>

                            <!-- Shipping Card -->
                            <div class="card" id="shippingCard">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <iconify-icon icon="solar:delivery-bold-duotone" class="me-1 text-primary"></iconify-icon>
                                        ข้อมูลการจัดส่ง
                                    </h5>
                                    <button class="btn btn-sm btn-outline-primary no-print" onclick="toggleShippingForm()">
                                        <iconify-icon icon="solar:pen-bold-duotone" class="me-1"></iconify-icon>
                                        อัปเดตการจัดส่ง
                                    </button>
                                </div>
                                <div class="card-body">
                                    <!-- Shipping Info Display -->
                                    <div id="shippingInfo">
                                        <div class="text-muted text-center py-3" id="noShipping">
                                            <iconify-icon icon="solar:box-bold-duotone" class="text-muted" style="font-size:2rem;"></iconify-icon>
                                            <div class="mt-2">ยังไม่มีข้อมูลการจัดส่ง</div>
                                        </div>
                                        <div id="shippingData" style="display:none;">
                                            <div class="row g-3">
                                                <div class="col-sm-4">
                                                    <div class="info-label">วิธีจัดส่ง</div>
                                                    <div class="info-value" id="d-ship-method">-</div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="info-label">เลขพัสดุ</div>
                                                    <div class="info-value font-monospace" id="d-ship-tracking">-</div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="info-label">สถานะการส่ง</div>
                                                    <div id="d-ship-status">-</div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="info-label">วันที่จัดส่ง</div>
                                                    <div class="info-value" id="d-ship-date">-</div>
                                                </div>
                                                <div class="col-sm-8">
                                                    <div class="info-label">หมายเหตุ</div>
                                                    <div class="info-value" id="d-ship-notes">-</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Shipping Update Form -->
                                    <div id="shippingForm" style="display:none;" class="border-top pt-3 mt-3 no-print">
                                        <div class="row g-3">
                                            <div class="col-sm-6">
                                                <label class="form-label fw-bold">วิธีจัดส่ง</label>
                                                <select class="form-select" id="sf-method">
                                                    <option value="ThaiPost">ไปรษณีย์ไทย (ลงทะเบียน)</option>
                                                    <option value="EMS">ไปรษณีย์ไทย (EMS)</option>
                                                    <option value="Flash">Flash Express</option>
                                                    <option value="Kerry">Kerry Express</option>
                                                    <option value="J&T">J&T Express</option>
                                                    <option value="Other">อื่นๆ</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label fw-bold">สถานะ</label>
                                                <select class="form-select" id="sf-status">
                                                    <option value="shipped">จัดส่งแล้ว</option>
                                                    <option value="delivered">ถึงผู้รับแล้ว</option>
                                                    <option value="returned">พัสดุตีกลับ</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-bold">เลขพัสดุ (Tracking Number)</label>
                                                <input type="text" class="form-control" id="sf-tracking" placeholder="เช่น EF123456789TH">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-bold">หมายเหตุ</label>
                                                <textarea class="form-control" id="sf-notes" rows="2"></textarea>
                                            </div>
                                            <div class="col-12 d-flex gap-2">
                                                <button class="btn btn-primary" onclick="saveShipping()">
                                                    <iconify-icon icon="solar:floppy-disk-bold-duotone" class="me-1"></iconify-icon>
                                                    บันทึกข้อมูลการจัดส่ง
                                                </button>
                                                <button class="btn btn-outline-secondary" onclick="toggleShippingForm(false)">ยกเลิก</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div><!-- /LEFT COLUMN -->

                        <!-- RIGHT COLUMN -->
                        <div class="col-lg-4">

                            <!-- Amount Card -->
                            <div class="card bg-primary text-white mb-4">
                                <div class="card-body text-center py-4">
                                    <div class="avatar-md bg-white bg-opacity-25 rounded d-flex align-items-center justify-content-center mx-auto mb-3">
                                        <iconify-icon icon="solar:hand-heart-bold-duotone" class="text-white fs-28"></iconify-icon>
                                    </div>
                                    <div class="opacity-75 small mb-1">จำนวนเงินบริจาค</div>
                                    <div class="amount-display text-white" id="d-amount">-</div>
                                    <div class="opacity-75 small mt-1">บาท</div>
                                </div>
                            </div>

                            <!-- Member Link (shown only when member exists) -->
                            <div id="memberLinkWrap" class="mb-4 no-print" style="display:none;">
                                <a class="btn btn-outline-secondary w-100" id="btnMember" href="#">
                                    <iconify-icon icon="solar:user-bold-duotone" class="me-2"></iconify-icon>
                                    ดูประวัติสมาชิก
                                </a>
                            </div>

                            <!-- Bank Reference Card -->
                            <div class="card mb-4" id="bankRefCard" style="display:none;">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <iconify-icon icon="solar:card-bold-duotone" class="me-1 text-primary"></iconify-icon>
                                        ข้อมูลธนาคาร
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="info-label">เลขอ้างอิง (Ref 1)</div>
                                        <div class="info-value font-monospace small" id="d-bank-ref1">-</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="info-label">เลขประจำตัวผู้เสียภาษี (Ref 2)</div>
                                        <div class="info-value font-monospace" id="d-bank-ref2">-</div>
                                    </div>
                                    <div>
                                        <div class="info-label">ชื่อบัญชีผู้โอน</div>
                                        <div class="info-value" id="d-payer-account">-</div>
                                    </div>
                                </div>
                            </div>

                        </div><!-- /RIGHT COLUMN -->

                    </div><!-- /row -->
                </div><!-- /mainContent -->

            </div><!-- /container-xxl -->

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <?php include 'partials/vendor-scripts.php'; ?>
    <script src="assets/js/api-helper.js"></script>

    <script>
        const urlParams   = new URLSearchParams(window.location.search);
        const receiptId   = urlParams.get('id');
        let   receiptData = null;

        document.addEventListener('DOMContentLoaded', () => {
            if (!receiptId) {
                showPageError('ไม่พบ ID ใบเสร็จ');
                return;
            }
            loadReceipt();
        });

        function showPageError(msg) {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('errorState').style.display   = '';
            document.getElementById('errorMessage').textContent   = msg;
        }

        async function loadReceipt() {
            try {
                const res  = await apiGet('/receipts/' + receiptId);
                receiptData = res.data;
                render(receiptData);

                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('mainContent').style.display  = '';
            } catch (err) {
                showPageError(err.message);
            }
        }

        function esc(str) {
            if (!str) return '-';
            const d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        }

        function render(r) {
            const isCancelled = r.status === 'cancelled';

            // Header
            document.getElementById('hdr-receipt-no').textContent   = r.receipt_no;
            document.getElementById('hdr-status-badge').innerHTML   = statusBadge(r.status);
            document.title = 'ใบเสร็จ ' + r.receipt_no;

            // Receipt Info
            document.getElementById('d-receipt-no').textContent   = r.receipt_no;
            document.getElementById('d-receipt-date').textContent  = formatThaiDate(r.receipt_date);
            document.getElementById('d-project').textContent       = r.project_name  || '-';
            document.getElementById('d-project-no').textContent    = r.project_number ? '[' + r.project_number + ']' : '';
            document.getElementById('d-pay-by').textContent        = r.pay_by        || '-';
            document.getElementById('d-fiscal-year').textContent   = r.fiscal_year   || '-';
            document.getElementById('d-status').innerHTML          = statusBadge(r.status);

            // Amount
            document.getElementById('d-amount').textContent = formatNumber(r.amount);

            // Donor
            const fullName = [r.title, r.first_name, r.last_name].filter(Boolean).join(' ') || r.payer_name;
            document.getElementById('d-name').textContent      = fullName || '-';
            document.getElementById('d-idcard').textContent    = formatIdCard(r.id_card);
            document.getElementById('d-email').textContent     = r.email     || '-';
            document.getElementById('d-phone').textContent     = formatPhone(r.phone);
            document.getElementById('d-occupation').textContent= r.occupation || '-';

            // Member link
            if (r.id_members) {
                document.getElementById('d-member').textContent = r.id_members;
                document.getElementById('btnMember').href = 'member-detail.php?id=' + encodeURIComponent(r.id_members);
                document.getElementById('memberLinkWrap').style.display = '';
            } else {
                document.getElementById('d-member-wrap').style.display = 'none';
            }

            // Address
            const addrParts = [
                r.address_line,
                r.district  ? 'ต.' + r.district  : '',
                r.amphure   ? 'อ.' + r.amphure   : '',
                r.province  ? 'จ.' + r.province  : '',
                r.zip_code
            ].filter(Boolean);
            document.getElementById('d-address').textContent = addrParts.length ? addrParts.join(' ') : (r.receipt_address || '-');

            // Bank ref
            if (r.bank_ref || r.tax_id_ref || r.payer_account) {
                document.getElementById('bankRefCard').style.display = '';
                document.getElementById('d-bank-ref1').textContent     = r.bank_ref      || '-';
                document.getElementById('d-bank-ref2').textContent     = r.tax_id_ref    || '-';
                document.getElementById('d-payer-account').textContent = r.payer_account || '-';
            }

            // Shipping
            if (r.shipping) {
                document.getElementById('noShipping').style.display   = 'none';
                document.getElementById('shippingData').style.display = '';
                document.getElementById('d-ship-method').textContent  = r.shipping.method   || '-';
                document.getElementById('d-ship-tracking').textContent= r.shipping.tracking || '-';
                document.getElementById('d-ship-status').innerHTML    = shippingBadge(r.shipping.status);
                document.getElementById('d-ship-date').textContent    = r.shipping.shipped_at ? formatThaiDate(r.shipping.shipped_at) : '-';
                document.getElementById('d-ship-notes').textContent   = r.shipping.notes || '-';

                // Pre-fill shipping form
                document.getElementById('sf-method').value   = r.shipping.method   || 'ThaiPost';
                document.getElementById('sf-status').value   = r.shipping.status   || 'shipped';
                document.getElementById('sf-tracking').value = r.shipping.tracking || '';
                document.getElementById('sf-notes').value    = r.shipping.notes    || '';
            }

            // Cancel/Edit button visibility
            if (isCancelled) {
                document.getElementById('btnCancel').style.display = 'none';
                document.getElementById('btnEdit').style.display   = 'none';
            }
        }

        function statusBadge(status) {
            if (status === 'cancelled')
                return '<span class="badge badge-soft-danger">ยกเลิก</span>';
            if (status === 'pending')
                return '<span class="badge badge-soft-warning">รอออก</span>';
            return '<span class="badge badge-soft-success">ออกแล้ว</span>';
        }

        function shippingBadge(status) {
            const map = {
                'shipped'  : '<span class="badge badge-soft-primary">จัดส่งแล้ว</span>',
                'delivered': '<span class="badge badge-soft-success">ถึงผู้รับแล้ว</span>',
                'returned' : '<span class="badge badge-soft-danger">พัสดุตีกลับ</span>',
            };
            return map[status] || '<span class="badge badge-soft-secondary">' + (status || '-') + '</span>';
        }

        function editReceipt() {
            window.location.href = 'receipts-edit.php?id=' + receiptId;
        }

        async function downloadPdf() {
            const btn = document.getElementById('btnPdf');
            btn.disabled = true;
            try {
                const res = await apiGet('/receipts/' + receiptId + '/admin_pdf');
                if (res.success && res.data.pdf_url) {
                    window.open(res.data.pdf_url, '_blank');
                } else {
                    showError('ไม่สามารถสร้างลิงก์ PDF ได้');
                }
            } catch (err) {
                showError(err.message);
            } finally {
                btn.disabled = false;
            }
        }

        async function cancelReceipt() {
            if (!receiptData) return;
            const result = await confirmAction({
                title      : 'ยืนยันการยกเลิก?',
                text       : `ยืนยันการยกเลิกใบเสร็จ ${receiptData.receipt_no}`,
                icon       : 'warning',
                confirmText: 'ยกเลิกใบเสร็จ',
                cancelText : 'ไม่ยกเลิก'
            });
            if (!result.isConfirmed) return;

            try {
                await apiPost('/receipts/' + receiptId + '/cancel');
                showSuccess('ยกเลิกใบเสร็จสำเร็จ');
                setTimeout(() => loadReceipt(), 1200);
            } catch (err) {
                showError(err.message);
            }
        }

        function toggleShippingForm(show) {
            const form = document.getElementById('shippingForm');
            const isVisible = form.style.display !== 'none';
            form.style.display = (show === false || isVisible) ? 'none' : '';
        }

        async function saveShipping() {
            const data = {
                shipping_method : document.getElementById('sf-method').value,
                status          : document.getElementById('sf-status').value,
                tracking_number : document.getElementById('sf-tracking').value,
                notes           : document.getElementById('sf-notes').value,
            };
            try {
                await apiPost('/receipts/' + receiptId + '/shipping', data);
                showSuccess('บันทึกข้อมูลการจัดส่งสำเร็จ');
                toggleShippingForm(false);
                loadReceipt();
            } catch (err) {
                showError(err.message);
            }
        }
    </script>
</body>

</html>
