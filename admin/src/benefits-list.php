<?php include 'partials/main.php'; ?>
<?php requireAuth(); ?>

<!doctype html>
<html lang="th">

<head>
    <?php
    $title = "จัดการสิทธิประโยชน์";
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
                $pageTitle = "ระดับผู้มีอุปการคุณ";
                $subTitle = "จัดการโครงการ";
                include 'partials/page-title.php'; ?>

                <!-- Main Card -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">รายการระดับสิทธิประโยชน์</h4>
                        <button class="btn btn-primary" onclick="openCreateModal()">
                            <iconify-icon icon="iconamoon:sign-plus-duotone" class="me-1"></iconify-icon>
                            เพิ่มระดับใหม่
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Benefits Grid -->
                        <div class="row" id="benefitsGrid">
                            <div class="col-12 text-center py-5">
                                <div class="spinner-border text-primary"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <!-- Benefits Modal -->
    <div class="modal fade" id="benefitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">เพิ่มระดับใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="benefitForm">
                    <div class="modal-body">
                        <input type="hidden" id="benefitId" name="id">

                        <div class="mb-3">
                            <label class="form-label">ชื่อระดับ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required
                                placeholder="เช่น ผู้มีอุปการคุณระดับทอง">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ยอดบริจาคขั้นต่ำ (บาท) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">฿</span>
                                <input type="number" class="form-control" id="amount" name="amount" required min="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">รายละเอียด/สิทธิประโยชน์</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                placeholder="รายละเอียดสิทธิประโยชน์ที่จะได้รับ"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ลำดับการแสดง</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">สถานะ</label>
                                <select class="form-select" id="is_active" name="is_active">
                                    <option value="1">เปิดใช้งาน</option>
                                    <option value="0">ปิดใช้งาน</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="spinner-border spinner-border-sm me-1 d-none" id="submitSpinner"></span>
                            บันทึก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'partials/vendor-scripts.php'; ?>
<script src="assets/js/api-helper.js"></script>

    <script>
        let benefits = [];
        let editMode = false;

        document.addEventListener('DOMContentLoaded', function () {
            loadBenefits();
            document.getElementById('benefitForm').addEventListener('submit', handleSubmit);
        });

        async function loadBenefits() {
            try {
                const response = await apiGet('/benefits?active=0');
                benefits = response.data || [];
                renderGrid(benefits);
            } catch (error) {
                showError(error.message);
                document.getElementById('benefitsGrid').innerHTML = `
            <div class="col-12 text-center py-5 text-danger">${error.message}</div>
        `;
            }
        }

        function renderGrid(data) {
            const grid = document.getElementById('benefitsGrid');

            if (!data || data.length === 0) {
                grid.innerHTML = '<div class="col-12 text-center py-5 text-muted">ยังไม่มีระดับสิทธิประโยชน์</div>';
                return;
            }

            grid.innerHTML = data.map(item => `
        <div class="col-md-4 col-lg-3 mb-4">
            <div class="card border h-100 ${!item.is_active ? 'opacity-50' : ''}">
                <div class="card-body text-center">
                    <div class="avatar-lg bg-soft-primary rounded-circle mx-auto mb-3">
                        <img src="${item.image_url || 'assets/images/placeholder.jpg'}" 
                             class="rounded-circle" width="64" height="64" 
                             style="object-fit: cover;"
                             onerror="this.src='assets/images/placeholder.jpg'">
                    </div>
                    <h5 class="mb-2">${escapeHtml(item.name)}</h5>
                    <h3 class="text-primary mb-2">${formatCurrency(item.amount)}</h3>
                    <p class="text-muted small mb-3">${escapeHtml(item.description || '-')}</p>
                    
                    <div class="d-flex justify-content-center gap-2">
                        ${item.is_active ?
                    '<span class="badge badge-soft-success">เปิดใช้งาน</span>' :
                    '<span class="badge badge-soft-secondary">ปิดใช้งาน</span>'}
                    </div>
                </div>
                <div class="card-footer bg-transparent text-center">
                    <button class="btn btn-sm btn-soft-primary me-1" onclick="openEditModal(${item.id})">
                        <iconify-icon icon="iconamoon:edit-duotone"></iconify-icon> แก้ไข
                    </button>
                    <button class="btn btn-sm btn-soft-danger" onclick="deleteBenefit(${item.id}, '${escapeHtml(item.name)}')">
                        <iconify-icon icon="iconamoon:trash-duotone"></iconify-icon>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
        }

        function openCreateModal() {
            editMode = false;
            document.getElementById('modalTitle').textContent = 'เพิ่มระดับใหม่';
            document.getElementById('benefitForm').reset();
            document.getElementById('benefitId').value = '';
            new bootstrap.Modal(document.getElementById('benefitModal')).show();
        }

        function openEditModal(id) {
            const item = benefits.find(b => b.id == id);
            if (!item) return;

            editMode = true;
            document.getElementById('modalTitle').textContent = 'แก้ไขระดับ';
            document.getElementById('benefitId').value = item.id;
            document.getElementById('name').value = item.name || '';
            document.getElementById('amount').value = item.amount || 0;
            document.getElementById('description').value = item.description || '';
            document.getElementById('sort_order').value = item.sort_order || 0;
            document.getElementById('is_active').value = item.is_active ? '1' : '0';

            new bootstrap.Modal(document.getElementById('benefitModal')).show();
        }

        async function handleSubmit(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.getElementById('submitSpinner');

            submitBtn.disabled = true;
            spinner.classList.remove('d-none');

            try {
                const formData = {
                    name: document.getElementById('name').value,
                    amount: parseFloat(document.getElementById('amount').value),
                    description: document.getElementById('description').value,
                    sort_order: parseInt(document.getElementById('sort_order').value),
                    is_active: parseInt(document.getElementById('is_active').value)
                };

                if (editMode) {
                    const id = document.getElementById('benefitId').value;
                    await apiPut('/benefits/' + id, formData);
                    showSuccess('อัปเดตสำเร็จ');
                } else {
                    await apiPost('/benefits', formData);
                    showSuccess('เพิ่มระดับใหม่สำเร็จ');
                }

                bootstrap.Modal.getInstance(document.getElementById('benefitModal')).hide();
                loadBenefits();

            } catch (error) {
                showError(error.message);
            } finally {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        async function deleteBenefit(id, name) {
            const result = await confirmDelete(name);
            if (!result.isConfirmed) return;

            try {
                await apiDelete('/benefits/' + id);
                showSuccess('ลบสำเร็จ');
                loadBenefits();
            } catch (error) {
                showError(error.message);
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    </script>

</body>

</html>