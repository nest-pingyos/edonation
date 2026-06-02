<?php include 'partials/main.php'; ?>
<?php requireAuth(); ?>
<?php require_once __DIR__ . '/../../config/autoprovince.php'; ?>

<?php
$idMembers = isset($_GET['id']) ? htmlspecialchars(trim($_GET['id'])) : '';
$title = "ข้อมูลผู้บริจาค";
?>

<!doctype html>
<html lang="th">

<head>
    <?php include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <?php autoprovinceCss(); ?>
</head>

<body>
    <div class="wrapper">
        <?php include 'partials/edonation-nav.php'; ?>

        <div class="page-content">
            <?php include 'partials/edonation-topbar.php'; ?>

            <div class="container-xxl py-3">

                <?php if (!$idMembers): ?>
                    <div class="alert alert-danger">ไม่พบรหัสสมาชิก</div>
                <?php else: ?>

                <!-- Loading -->
                <div id="pageLoading" class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2 text-muted small">กำลังโหลดข้อมูล...</p>
                </div>

                <!-- Error -->
                <div id="pageError" style="display:none">
                    <div class="alert alert-danger" id="pageErrorMsg">ไม่พบข้อมูลสมาชิก</div>
                    <a href="members-list.php" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> กลับรายชื่อสมาชิก
                    </a>
                </div>

                <!-- Content -->
                <div id="pageContent" style="display:none">

                    <!-- Top bar -->
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <a href="members-list.php" class="btn btn-light btn-sm">
                                <i class="bi bi-arrow-left me-1"></i> รายชื่อสมาชิก
                            </a>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 small">
                                    <li class="breadcrumb-item"><a href="members-list.php">สมาชิก</a></li>
                                    <li class="breadcrumb-item active" id="bc-name">-</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="d-flex gap-2">
                            <a id="btnGenerateReceipt" href="#" class="btn btn-success btn-sm">
                                <i class="bi bi-receipt me-1"></i> ออกใบเสร็จรับเงิน
                            </a>
                            <button class="btn btn-outline-secondary btn-sm" onclick="exportMember()" title="Export ข้อมูลสมาชิก">
                                <i class="bi bi-download me-1"></i> Export
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="openEdit()">
                                <i class="bi bi-pencil me-1"></i> แก้ไขข้อมูล
                            </button>
                        </div>
                    </div>

                    <div class="row g-3">

                        <!-- Left: Profile + Info -->
                        <div class="col-lg-3">
                            <!-- Avatar & Name -->
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <div class="avatar-circle mx-auto mb-3" id="memberAvatar"></div>
                                    <h5 class="mb-1 fw-semibold" id="memberName">-</h5>
                                    <div class="text-muted small font-monospace mb-2" id="memberIdBadge">-</div>
                                    <span class="badge bg-light text-dark border" id="memberTypeBadge">-</span>
                                </div>
                            </div>

                            <!-- Stats -->
                            <div class="card mb-3">
                                <div class="card-body p-3">
                                    <div class="row g-3 text-center">
                                        <div class="col-4">
                                            <div class="fw-bold fs-5 text-primary" id="statCount">-</div>
                                            <div class="text-muted" style="font-size:0.7rem">ครั้ง</div>
                                        </div>
                                        <div class="col-4 border-start border-end">
                                            <div class="fw-bold fs-5 text-success" id="statAmount">-</div>
                                            <div class="text-muted" style="font-size:0.7rem">บาท</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="fw-bold fs-5 text-warning" id="statYears">-</div>
                                            <div class="text-muted" style="font-size:0.7rem">ปี</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Info + Address -->
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="mb-3">
                                        <div class="text-muted small mb-1">รหัสสมาชิก</div>
                                        <div class="font-monospace small" id="infoId">-</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small mb-1">เลขบัตรประชาชน</div>
                                        <div class="font-monospace small" id="infoIdCard">-</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small mb-1">เบอร์โทรศัพท์</div>
                                        <div class="small" id="infoPhone">-</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small mb-1">อาชีพ</div>
                                        <div class="small" id="infoOccupation">-</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small mb-1">บริจาคครั้งแรก</div>
                                        <div class="small" id="infoFirstDate">-</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small mb-1">บริจาคล่าสุด</div>
                                        <div class="small" id="infoLastDate">-</div>
                                    </div>

                                    <hr class="my-2">

                                    <div class="mb-3">
                                        <div class="text-muted small mb-1">ที่อยู่สำหรับใบเสร็จ</div>
                                        <div class="small" id="addrReceipt">-</div>
                                    </div>
                                    <div>
                                        <div class="text-muted small mb-1">ที่อยู่จัดส่ง</div>
                                        <div class="small" id="addrShipping">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Tabs -->
                        <div class="col-lg-9">
                            <div class="card">
                                <div class="card-header p-0">
                                    <ul class="nav nav-tabs border-0 px-3 pt-2">
                                        <li class="nav-item">
                                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-receipts">
                                                ใบเสร็จ <span class="badge bg-secondary ms-1 fw-normal small" id="tab-receipt-count">0</span>
                                            </button>
                                        </li>
                                        <li class="nav-item">
                                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-projects">
                                                โครงการที่สนับสนุน
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-body tab-content p-3">

                                    <!-- Receipts Tab -->
                                    <div class="tab-pane fade show active" id="tab-receipts">
                                        <div class="table-responsive" style="max-height:480px; overflow-y:auto;">
                                            <table class="table table-sm table-hover align-middle mb-0">
                                                <thead class="table-light" style="position:sticky;top:0;z-index:1">
                                                    <tr>
                                                        <th class="text-muted fw-normal">#</th>
                                                        <th class="text-muted fw-normal">เลขที่ใบเสร็จ</th>
                                                        <th class="text-muted fw-normal">โครงการ</th>
                                                        <th class="text-muted fw-normal text-end">จำนวนเงิน</th>
                                                        <th class="text-muted fw-normal text-center">วันที่ออก</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="receiptsBody">
                                                    <tr><td colspan="6" class="text-center text-muted py-4">กำลังโหลด...</td></tr>
                                                </tbody>
                                                <tfoot class="table-light" id="receiptsFoot" style="display:none">
                                                    <tr>
                                                        <td colspan="3" class="small text-muted" id="footCount"></td>
                                                        <td class="text-end fw-semibold" id="footTotal"></td>
                                                        <td colspan="2"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Projects Tab -->
                                    <div class="tab-pane fade" id="tab-projects">
                                        <ul class="list-group list-group-flush" id="projectsList">
                                            <li class="list-group-item text-muted small text-center py-4">กำลังโหลด...</li>
                                        </ul>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <?php include 'partials/vendor-scripts.php'; ?>
    <script src="assets/js/api-helper.js"></script>

    <style>
        .avatar-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #e8f0fe;
            color: #1c84ee;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
        }
    </style>

    <script>
        const idMembers = <?php echo json_encode($idMembers); ?>;

        const THAI_MONTHS = ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
                             'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];

        function toThaiDate(str) {
            if (!str) return '-';
            const d = new Date(str);
            if (isNaN(d)) return str;
            return `${d.getDate()} ${THAI_MONTHS[d.getMonth()]} ${d.getFullYear() + 543}`;
        }

        function esc(str) {
            const d = document.createElement('div');
            d.textContent = str ?? '';
            return d.innerHTML;
        }

        function fmtCurrency(n) {
            return '฿' + parseFloat(n || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function fmtNumber(n) {
            return parseFloat(n || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function setText(id, val) {
            const el = document.getElementById(id);
            if (el) el.textContent = val ?? '-';
        }

        async function loadPage() {
            if (!idMembers) return;

            try {
                const [memberRes, receiptsRes] = await Promise.all([
                    apiGet(`/members/${encodeURIComponent(idMembers)}`),
                    apiGet(`/members/${encodeURIComponent(idMembers)}/receipts`)
                ]);

                const d = memberRes.data || {};
                const receipts = receiptsRes.data || [];

                renderProfile(d, receipts);
                renderReceipts(receipts);
                renderProjects(d.top_projects || []);

                document.getElementById('pageLoading').style.display = 'none';
                document.getElementById('pageContent').style.display = 'block';

            } catch (err) {
                document.getElementById('pageLoading').style.display = 'none';
                document.getElementById('pageErrorMsg').textContent = err.message || 'ไม่พบข้อมูลสมาชิก';
                document.getElementById('pageError').style.display = 'block';
            }
        }

        function renderProfile(d, receipts) {
            const name = d.name || '-';
            const initials = name.trim().split(/\s+/).filter(Boolean).map(w => w[0]).slice(1, 2).join('') || name[0] || '?';
            const totalAmount = receipts.reduce((s, r) => s + parseFloat(r.amount || 0), 0);

            document.getElementById('memberAvatar').textContent = initials.toUpperCase();
            setText('memberName', name);
            setText('memberIdBadge', d.id_members || '-');
            setText('memberTypeBadge', d.donation_frequency?.donor_type_label || 'ผู้บริจาค');
            setText('bc-name', name);

            // Stats
            document.getElementById('statCount').textContent = receipts.length;
            document.getElementById('statAmount').textContent = fmtNumber(totalAmount);
            document.getElementById('statYears').textContent = d.statistics?.years_active ?? '-';

            // Info
            setText('infoId', d.id_members);
            setText('infoIdCard', d.id_card_formatted || d.id_card);
            setText('infoPhone', d.phone || '-');
            setText('infoOccupation', d.occupation || '-');
            setText('infoFirstDate', toThaiDate(d.statistics?.first_donation_date));
            setText('infoLastDate', toThaiDate(d.statistics?.last_donation_date));

            // Address
            setText('addrReceipt', d.address?.full || '-');
            setText('addrShipping', d.shipping_address || d.shipping_address_data?.full || '-');

            document.getElementById('tab-receipt-count').textContent = receipts.length;

            // Wire generate-receipt button
            document.getElementById('btnGenerateReceipt').href =
                `receipts-generate.php?member_id=${encodeURIComponent(d.id_members || '')}`;
        }

        function renderReceipts(receipts) {
            const tbody = document.getElementById('receiptsBody');
            const tfoot = document.getElementById('receiptsFoot');

            if (receipts.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">ไม่พบประวัติการบริจาค</td></tr>';
                return;
            }

            const totalAmount = receipts.reduce((s, r) => s + parseFloat(r.amount || 0), 0);

            tbody.innerHTML = receipts.map((r, i) => `
                <tr>
                    <td class="text-muted small">${i + 1}</td>
                    <td class="font-monospace small">${esc(r.receipt_no || '-')}</td>
                    <td class="small">${esc(r.project_name || '-')}</td>
                    <td class="text-end small">${fmtCurrency(r.amount)}</td>
                    <td class="text-center text-muted small">${toThaiDate(r.issued_at)}</td>
                    <td class="text-center">
                        <a href="receipts-generate.php?receipt_no=${encodeURIComponent(r.receipt_no || '')}"
                           class="btn btn-sm btn-outline-secondary py-0 px-2" title="ดูใบเสร็จ" target="_blank">
                            <i class="bi bi-file-earmark-text"></i>
                        </a>
                    </td>
                </tr>
            `).join('');

            document.getElementById('footCount').textContent = `รวม ${receipts.length} รายการ`;
            document.getElementById('footTotal').textContent = fmtCurrency(totalAmount);
            tfoot.style.display = '';
        }

        function renderProjects(projects) {
            const list = document.getElementById('projectsList');
            if (!projects.length) {
                list.innerHTML = '<li class="list-group-item text-muted small text-center py-4">ไม่พบโครงการ</li>';
                return;
            }
            list.innerHTML = projects.map(p => `
                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                    <div class="small">
                        <span class="text-muted me-1">[${esc(p.project_number || '-')}]</span>${esc(p.project_name || '-')}
                    </div>
                    <div class="text-end ms-3 flex-shrink-0">
                        <div class="small fw-semibold">${fmtCurrency(p.total)}</div>
                        <div class="text-muted" style="font-size:0.72rem">${p.count} ครั้ง</div>
                    </div>
                </li>
            `).join('');
        }

        let apEditReceipt, apEditShipping;

        function updateEditFullAddress() {
            const line = document.getElementById('edit_address_line').value.trim();
            const ap = apEditReceipt ? apEditReceipt.formatAddress() : '';
            document.getElementById('edit_address').value = [line, ap].filter(Boolean).join(' ');
        }

        function updateEditShippingFullAddress() {
            const line = document.getElementById('edit_ship_address_line').value.trim();
            const ap = apEditShipping ? apEditShipping.formatAddress() : '';
            document.getElementById('edit_shipping_address').value = [line, ap].filter(Boolean).join(' ');
        }

        async function copyEditReceiptAddress() {
            document.getElementById('edit_ship_address_line').value =
                document.getElementById('edit_address_line').value;
            const addr = apEditReceipt ? apEditReceipt.getAddress() : null;
            if (addr && addr.province.id) {
                await apEditShipping.set(addr.province.name, addr.district.name, addr.subdistrict.name);
            }
            updateEditShippingFullAddress();
        }

        async function openEdit() {
            const modal = new bootstrap.Modal(document.getElementById('editMemberModal'));

            try {
                const res = await apiGet(`/members/${encodeURIComponent(idMembers)}`);
                const d = res.data || {};

                document.getElementById('edit_id_members').value  = d.id_members   || '';
                document.getElementById('edit_title').value       = d.title         || '';
                document.getElementById('edit_first_name').value  = d.first_name    || '';
                document.getElementById('edit_last_name').value   = d.last_name     || '';
                document.getElementById('edit_id_card').value     = d.id_card       || '';
                document.getElementById('edit_phone').value       = d.phone         || '';
                document.getElementById('edit_occupation').value  = d.occupation    || '';

                // ที่อยู่ใบเสร็จ
                document.getElementById('edit_address_line').value = d.address?.address_line || '';
                document.getElementById('edit_address').value      = d.address?.full || '';

                await apEditReceipt.set(
                    d.address?.province    || '',
                    d.address?.district    || '',
                    d.address?.subdistrict || ''
                );
                updateEditFullAddress();

                // ที่อยู่จัดส่ง
                document.getElementById('edit_ship_address_line').value = d.shipping_address_data?.address_line || '';
                document.getElementById('edit_shipping_address').value  = d.shipping_address_data?.full || '';

                await apEditShipping.set(
                    d.shipping_address_data?.province    || '',
                    d.shipping_address_data?.district    || '',
                    d.shipping_address_data?.subdistrict || ''
                );
                updateEditShippingFullAddress();

                modal.show();
            } catch (err) {
                showError(err.message);
            }
        }

        function exportMember() {
            document.getElementById('exportForm').submit();
        }

        document.addEventListener('DOMContentLoaded', function () {
            apEditReceipt = new AutoProvince({
                provinceSelector:    '#edit_province',
                districtSelector:    '#edit_amphure',
                subdistrictSelector: '#edit_district',
                postcodeSelector:    '#edit_postcode',
                useSelect2: false,
                onAddressComplete: updateEditFullAddress
            });

            apEditShipping = new AutoProvince({
                provinceSelector:    '#edit_ship_province',
                districtSelector:    '#edit_ship_district',
                subdistrictSelector: '#edit_ship_subdistrict',
                postcodeSelector:    '#edit_ship_postcode',
                useSelect2: false,
                onAddressComplete: updateEditShippingFullAddress
            });

            document.getElementById('edit_address_line').addEventListener('input', updateEditFullAddress);
            document.getElementById('edit_ship_address_line').addEventListener('input', updateEditShippingFullAddress);

            document.getElementById('editMemberForm').addEventListener('submit', async function (e) {
                e.preventDefault();
                updateEditFullAddress();
                updateEditShippingFullAddress();
                const formData = new FormData(e.target);
                const data = Object.fromEntries(formData.entries());
                try {
                    await apiPost(`/members/${encodeURIComponent(idMembers)}/update`, data);
                    bootstrap.Modal.getInstance(document.getElementById('editMemberModal')).hide();
                    showSuccess('บันทึกข้อมูลเรียบร้อยแล้ว');
                    loadPage();
                } catch (err) {
                    showError(err.message);
                }
            });

            loadPage();
        });
    </script>

    <!-- Hidden Export Form -->
    <form id="exportForm" action="export_members.php" method="POST" target="_blank" style="display:none">
        <input type="hidden" name="ids" value="<?php echo htmlspecialchars($idMembers); ?>">
    </form>

    <!-- Edit Member Modal -->
    <div class="modal fade" id="editMemberModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editMemberForm">
                    <div class="modal-header">
                        <h5 class="modal-title">แก้ไขข้อมูลสมาชิก</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_id_members" name="id_members">

                        <div class="row g-3 mb-3">
                            <div class="col-md-2">
                                <label class="form-label">คำนำหน้า</label>
                                <input type="text" class="form-control" id="edit_title" name="title"
                                    list="edit_titles_list">
                                <datalist id="edit_titles_list">
                                    <option value="นาย"><option value="นาง">
                                    <option value="นางสาว"><option value="ด.ช.">
                                    <option value="ด.ญ."><option value="บริษัท">
                                </datalist>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">นามสกุล</label>
                                <input type="text" class="form-control" id="edit_last_name" name="last_name">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">เลขบัตรประชาชน</label>
                                <input type="text" class="form-control" id="edit_id_card" name="id_card" maxlength="13">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">เบอร์โทรศัพท์</label>
                                <input type="text" class="form-control" id="edit_phone" name="phone">
                            </div>
                            <div class="col-12">
                                <label class="form-label">อาชีพ</label>
                                <input type="text" class="form-control" id="edit_occupation" name="occupation">
                            </div>
                        </div>

                        <!-- ที่อยู่ใบเสร็จ -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">ที่อยู่สำหรับใบเสร็จ</label>
                            <input type="text" class="form-control mb-2" id="edit_address_line" name="address_line"
                                placeholder="บ้านเลขที่, ซอย, ถนน">
                            <input type="hidden" id="edit_address" name="address">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">จังหวัด</label>
                                    <select class="form-select" id="edit_province" name="province">
                                        <option value="">เลือกจังหวัด</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">อำเภอ/เขต</label>
                                    <select class="form-select" id="edit_amphure" name="amphure" disabled>
                                        <option value="">เลือกอำเภอ/เขต</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">ตำบล/แขวง</label>
                                    <select class="form-select" id="edit_district" name="district" disabled>
                                        <option value="">เลือกตำบล/แขวง</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">รหัสไปรษณีย์</label>
                                    <input type="text" class="form-control" id="edit_postcode" name="zip_code" readonly placeholder="อัตโนมัติ">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- ที่อยู่จัดส่ง -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-semibold mb-0">ที่อยู่จัดส่งใบเสร็จ</label>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="copyEditReceiptAddress()">
                                    ใช้ที่อยู่เดียวกับใบเสร็จ
                                </button>
                            </div>
                            <input type="text" class="form-control mb-2" id="edit_ship_address_line" name="ship_address_line"
                                placeholder="บ้านเลขที่, ซอย, ถนน">
                            <input type="hidden" id="edit_shipping_address" name="shipping_address">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">จังหวัด</label>
                                    <select class="form-select" id="edit_ship_province" name="ship_province">
                                        <option value="">เลือกจังหวัด</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">อำเภอ/เขต</label>
                                    <select class="form-select" id="edit_ship_district" name="ship_district" disabled>
                                        <option value="">เลือกอำเภอ/เขต</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">ตำบล/แขวง</label>
                                    <select class="form-select" id="edit_ship_subdistrict" name="ship_subdistrict" disabled>
                                        <option value="">เลือกตำบล/แขวง</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">รหัสไปรษณีย์</label>
                                    <input type="text" class="form-control" id="edit_ship_postcode" name="ship_zip_code" readonly placeholder="อัตโนมัติ">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php autoprovinceJs(); ?>
</body>

</html>
