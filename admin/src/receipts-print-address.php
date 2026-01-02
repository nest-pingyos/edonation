<?php include 'partials/main.php'; ?>
<?php requireAuth(); ?>

<!doctype html>
<html lang="th">

<head>
    <?php
    $title = "พิมพ์ที่อยู่จัดส่ง";
    include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php'; ?>
</head>

<body>
    <div class="wrapper">
        <?php include 'partials/edonation-nav.php'; ?>

        <div class="page-content">
            <?php include 'partials/edonation-topbar.php'; ?>

            <div class="container-xxl">
                <?php
                $pageTitle = "ใบเสร็จรับเงิน";
                $subTitle = "พิมพ์ที่อยู่จัดส่ง";
                include 'partials/page-title.php'; ?>

                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <form id="printAddressForm" action="print-preview-address.php" method="GET" target="_blank">

                            <!-- Card 1: ข้อมูลและเงื่อนไข -->
                            <div class="card mb-3">
                                <div class="card-header bg-soft-primary py-3 d-flex align-items-center">
                                    <h5 class="card-title mb-0 text-primary">
                                        <iconify-icon icon="line-md:clipboard-list-twotone"
                                            class="me-2 fs-20"></iconify-icon>
                                        กำหนดเงื่อนไขการพิมพ์
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <!-- เลือกรูปแบบรายงาน -->
                                    <div class="mb-4 pb-3 border-bottom border-dashed">
                                        <label class="form-label fw-semibold">รูปแบบรายงาน</label>
                                        <div class="d-flex gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="report_mode"
                                                    id="modeAddress" value="address" checked>
                                                <label class="form-check-label text-dark fw-medium" for="modeAddress">
                                                    ที่อยู่สำหรับจ่าหน้า (Labels)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="report_mode"
                                                    id="modeSummary" value="summary">
                                                <label class="form-check-label text-dark fw-medium" for="modeSummary">
                                                    ใบสรุปรายชื่อการส่ง (Summary List)
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 1. เลือกแหล่งข้อมูล -->
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">เลือกข้อมูล (ตาราง)</label>
                                        <div class="d-flex flex-wrap gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="tables[]"
                                                    value="edonation_receipts" id="tableReceipts" checked>
                                                <label class="form-check-label" for="tableReceipts">รายการใบเสร็จทั้งหมด
                                                    (edonation_receipts)</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">ยอดบริจาคขั้นต่ำ (บาท)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">฿</span>
                                                <input type="number" class="form-control" name="min_amount"
                                                    placeholder="เช่น 1000" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">เงื่อนไขที่อยู่</label>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="has_address"
                                                    id="hasAddress" value="1" checked>
                                                <label class="form-check-label"
                                                    for="hasAddress">เฉพาะผู้ที่มีข้อมูลที่อยู่</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="labelsOptions">
                                <!-- Card 2: ขนาดและการวางแนว -->
                                <div class="card mb-3">
                                    <div class="card-header bg-soft-info py-2">
                                        <h6 class="card-title mb-0 text-info">ขนาดกระดาษและการวางแนว</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-4">
                                            <label
                                                class="form-label fw-semibold text-muted small">เลือกขนาดกระดาษ</label>
                                            <div class="d-flex flex-wrap gap-3">
                                                <div class="form-check card-radio">
                                                    <input class="form-check-input" type="radio" name="paper_size"
                                                        id="sizeA4" value="A4" checked>
                                                    <label class="form-check-label" for="sizeA4">
                                                        <span class="fs-14 fw-bold d-block">A4</span>
                                                        <span class="text-muted small">List View</span>
                                                    </label>
                                                </div>
                                                <div class="form-check card-radio">
                                                    <input class="form-check-input" type="radio" name="paper_size"
                                                        id="sizeA5" value="A5">
                                                    <label class="form-check-label" for="sizeA5">
                                                        <span class="fs-14 fw-bold d-block">A5</span>
                                                        <span class="text-muted small">Card View</span>
                                                    </label>
                                                </div>
                                                <div class="form-check card-radio">
                                                    <input class="form-check-input" type="radio" name="paper_size"
                                                        id="sizeCustom" value="custom">
                                                    <label class="form-check-label" for="sizeCustom">
                                                        <span class="fs-14 fw-bold d-block">กำหนดเอง</span>
                                                        <span class="text-muted small">Custom CM</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div id="customSizeInputs" class="mt-3 row g-2 mb-4" style="display: none;">
                                                <div class="col-6">
                                                    <label class="form-label small">กว้าง (cm)</label>
                                                    <input type="number" class="form-control" name="custom_width"
                                                        id="customWidth" value="10" step="0.1">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small">สูง (cm)</label>
                                                    <input type="number" class="form-control" name="custom_height"
                                                        id="customHeight" value="15" step="0.1">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label
                                                    class="form-label fw-semibold text-muted small">วางแนวกระดาษ</label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="orientation"
                                                            id="orientPortrait" value="portrait" checked>
                                                        <label class="form-check-label"
                                                            for="orientPortrait">แนวตั้ง</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="orientation"
                                                            id="orientLandscape" value="landscape">
                                                        <label class="form-check-label"
                                                            for="orientLandscape">แนวนอน</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card 3: ปรับตำแหน่ง -->
                                <div class="card mb-3">
                                    <div class="card-header bg-soft-warning py-2">
                                        <h6 class="card-title mb-0 text-warning">ปรับตำแหน่ง (Position Offset)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label class="form-label small">ขยับซ้าย-ขวา (cm)</label>
                                                <input type="number" class="form-control" name="offset_x" id="offsetX"
                                                    value="0" step="0.1">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small">ขยับขึ้น-ลง (cm)</label>
                                                <input type="number" class="form-control" name="offset_y" id="offsetY"
                                                    value="0" step="0.1">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- End labelsOptions -->

                            <!-- Visual Preview Box (Integrated) -->
                            <div class="card mb-4 bg-light border-dashed">
                                <div class="card-body text-center p-4">
                                    <h6 class="text-muted mb-3">ตัวอย่างรูปแบบการจัดวาง</h6>
                                    <div class="d-flex justify-content-center align-items-center mb-3"
                                        style="min-height: 320px;">
                                        <div id="preview-paper"
                                            class="bg-white shadow d-flex align-items-center justify-content-center position-relative"
                                            style="transition: all 0.3s; border: 1px solid #ddd;">
                                            <div id="preview-content" class="text-center p-2"
                                                style="border: 1px dashed #0d6efd; background: rgba(13, 110, 253, 0.05); width: 85%; transition: all 0.2s;">
                                                <div class="fw-bold mb-1" style="font-size: 14px; color: #000;">คุณใจดี
                                                    มีสุข (ทดสอบ)</div>
                                                <div class="text-muted" style="font-size: 11px;">123 ถ.สุขุมวิท
                                                    แขวงคลองเตย ...</div>
                                            </div>
                                            <div class="position-absolute bottom-0 end-0 m-2 badge bg-dark opacity-75"
                                                id="preview-dim">A4</div>
                                        </div>
                                    </div>
                                    <div class="text-muted small">ตัวอย่างสัดส่วนพื้นหลังเทียบกับพื้นที่พิมพ์จริง</div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex justify-content-end gap-2 mb-5">
                                <button type="reset" class="btn btn-light px-4">ล้างค่า</button>
                                <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                    <iconify-icon icon="iconamoon:printer-duotone" class="me-2"></iconify-icon>
                                    เริ่มการพิมพ์ / ดูตัวอย่าง
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

        </div>

        <?php include 'partials/footer.php'; ?>
    </div>
    </div>

    <?php include 'partials/vendor-scripts.php'; ?>

    <script>
        document.getElementById('printAddressForm').addEventListener('submit', function (e) {
            const tables = document.querySelectorAll('input[name="tables[]"]:checked');
            if (tables.length === 0) {
                e.preventDefault();
                alert('กรุณาเลือกข้อมูลอย่างน้อย 1 ปี (ตาราง)');
            }
        });

        const customInputs = document.getElementById('customSizeInputs');
        const customWidth = document.getElementById('customWidth');
        const customHeight = document.getElementById('customHeight');
        const offsetXInput = document.getElementById('offsetX');
        const offsetYInput = document.getElementById('offsetY');
        const labelsOptions = document.getElementById('labelsOptions');

        function updatePreview() {
            const reportMode = document.querySelector('input[name="report_mode"]:checked').value;
            const paperSizeInput = document.querySelector('input[name="paper_size"]:checked');
            const paperSize = paperSizeInput ? paperSizeInput.value : 'A4';
            const orientationInput = document.querySelector('input[name="orientation"]:checked');
            const orientation = orientationInput ? orientationInput.value : 'portrait';
            const offX = parseFloat(offsetXInput.value) || 0;
            const offY = parseFloat(offsetYInput.value) || 0;

            const paper = document.getElementById('preview-paper');
            const content = document.getElementById('preview-content');
            const dimLabel = document.getElementById('preview-dim');

            if (reportMode === 'summary') {
                labelsOptions.style.display = 'none';
                content.innerHTML = `
                    <div style="text-align:left; width:100%; border:1px solid #eee;">
                        <div style="background:#f8f9fa; font-weight:bold; font-size:9px; padding:2px; display:flex; border-bottom:1px solid #eee;">
                            <div style="width:30%;">ชื่อ-สุกล</div><div style="width:70%;">ที่อยู่</div>
                        </div>
                        <div style="font-size:8px; padding:2px; display:flex; border-bottom:1px solid #eee;">
                            <div style="width:30%;">สมชาย...</div><div style="width:70%;">123 ถ.สุขุมวิท...</div>
                        </div>
                    </div>
                `;
                content.style.transform = 'none';
                content.style.width = '90%';
                const w_mm = 210, h_mm = 297, scale = 0.8;
                paper.style.width = (w_mm * scale) + 'px';
                paper.style.height = (h_mm * scale) + 'px';
                dimLabel.textContent = 'A4 (Summary)';
                return;
            }

            labelsOptions.style.display = 'block';
            content.innerHTML = `
                <div class="fw-bold mb-1" style="font-size: 14px; color: #000;">คุณใจดี มีสุข (ทดสอบ)</div>
                <div class="text-muted" style="font-size: 11px;">123 ถ.สุขุมวิท แขวงคลองเตย ...</div>
            `;
            content.style.width = '85%';

            if (paperSize === 'custom') {
                customInputs.style.display = 'flex';
            } else {
                customInputs.style.display = 'none';
            }

            const scale = 0.8;
            let w_mm, h_mm;
            if (paperSize === 'custom') {
                let w_cm = parseFloat(customWidth.value) || 10;
                let h_cm = parseFloat(customHeight.value) || 15;
                if (orientation === 'portrait') { w_mm = w_cm * 10; h_mm = h_cm * 10; }
                else { w_mm = h_cm * 10; h_mm = w_cm * 10; }
                dimLabel.textContent = `Custom (${w_cm}x${h_cm} cm)`;
            } else if (paperSize === 'A4') {
                if (orientation === 'portrait') { w_mm = 210; h_mm = 297; }
                else { w_mm = 297; h_mm = 210; }
                dimLabel.textContent = `${paperSize} (${orientation})`;
            } else {
                if (orientation === 'portrait') { w_mm = 148; h_mm = 210; }
                else { w_mm = 210; h_mm = 148; }
                dimLabel.textContent = `${paperSize} (${orientation})`;
            }
            paper.style.width = (w_mm * scale) + 'px';
            paper.style.height = (h_mm * scale) + 'px';

            let transformStr = `translate(${offX * 10 * scale}px, ${offY * 10 * scale}px)`;
            content.style.transform = transformStr;
        }

        const inputs = document.querySelectorAll('input[name="report_mode"], input[name="paper_size"], input[name="orientation"], #customWidth, #customHeight, #offsetX, #offsetY');
        inputs.forEach(input => input.addEventListener('change', updatePreview));
        inputs.forEach(input => input.addEventListener('input', updatePreview));
        updatePreview();
    </script>
</body>

</html>