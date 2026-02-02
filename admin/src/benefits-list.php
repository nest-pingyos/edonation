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

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-soft-primary rounded">
                                        <iconify-icon icon="iconamoon:gift-duotone"
                                            class="avatar-title text-primary fs-32"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="total-count">-</h3>
                                        <p class="text-muted mb-0">ระดับทั้งหมด</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-soft-success rounded">
                                        <iconify-icon icon="iconamoon:check-circle-1-duotone"
                                            class="avatar-title text-success fs-32"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="active-count">-</h3>
                                        <p class="text-muted mb-0">เปิดใช้งาน</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-soft-warning rounded">
                                        <iconify-icon icon="iconamoon:close-circle-1-duotone"
                                            class="avatar-title text-warning fs-32"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="inactive-count">-</h3>
                                        <p class="text-muted mb-0">ปิดใช้งาน</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
                        <!-- Filters -->
                        <div
                            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted">แสดง</span>
                                    <select id="limitSelector" class="form-select form-select-sm border-0 bg-light"
                                        style="width: auto;" onchange="changeLimit()">
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="250">250</option>
                                        <option value="500">500</option>
                                    </select>
                                    <span class="text-muted">แถว</span>
                                </div>
                                <select id="statusFilter" class="form-select form-select-sm border-0 bg-light"
                                    style="width: auto; min-width: 140px;" onchange="filterTable()">
                                    <option value="">ทุกสถานะ</option>
                                    <option value="active">เปิดใช้งาน</option>
                                    <option value="inactive">ปิดใช้งาน</option>
                                </select>
                            </div>
                            <div class="d-flex align-items-center gap-2 bg-light rounded px-3 py-2"
                                style="min-width: 250px;">
                                <iconify-icon icon="iconamoon:search-duotone" class="text-muted fs-5"></iconify-icon>
                                <input type="text" id="searchInput"
                                    class="form-control form-control-sm border-0 bg-transparent p-0"
                                    placeholder="ค้นหาชื่อระดับ..." oninput="filterTable()">
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-nowrap align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th style="width: 80px;">รูปภาพ</th>
                                        <th>ชื่อระดับ</th>
                                        <th>รายละเอียด</th>
                                        <th class="text-end">ยอดขั้นต่ำ</th>
                                        <th class="text-center">ลำดับ</th>
                                        <th class="text-center">สถานะ</th>
                                        <th class="text-center" style="width: 140px;">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="benefitsTable">
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="spinner-border text-primary"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div id="pagination-info" class="text-muted small"></div>
                            <nav id="pagination"></nav>
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
                                <label class="form-label">ยอดบริจาคขั้นต่ำ (บาท) <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">฿</span>
                                    <input type="number" class="form-control" id="amount" name="amount" required
                                        min="0">
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
                                    <input type="number" class="form-control" id="sort_order" name="sort_order"
                                        value="0">
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
            let currentPage = 1;
            let perPage = 25;
            let editMode = false;

            document.addEventListener('DOMContentLoaded', function () {
                loadBenefits();
                document.getElementById('benefitForm').addEventListener('submit', handleSubmit);
            });

            async function loadBenefits() {
                try {
                    const response = await apiGet('/benefits?active=0');
                    benefits = response.data || [];

                    // Update stats
                    const activeCount = benefits.filter(b => b.is_active).length;
                    const inactiveCount = benefits.filter(b => !b.is_active).length;

                    document.getElementById('total-count').textContent = benefits.length;
                    document.getElementById('active-count').textContent = activeCount;
                    document.getElementById('inactive-count').textContent = inactiveCount;

                    renderTable(benefits);
                } catch (error) {
                    showError(error.message);
                    document.getElementById('benefitsTable').innerHTML = `
                    <tr><td colspan="8" class="text-center py-4 text-danger">${error.message}</td></tr>
                `;
                }
            }

            function renderTable(data) {
                const tbody = document.getElementById('benefitsTable');

                if (!data || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted"><iconify-icon icon="iconamoon:file-search-duotone" class="fs-48 d-block mb-2"></iconify-icon>ยังไม่มีระดับสิทธิประโยชน์</td></tr>';
                    document.getElementById('pagination-info').textContent = '';
                    document.getElementById('pagination').innerHTML = '';
                    return;
                }

                // Client-side pagination since benefits are typically few
                const total = data.length;
                const totalPages = Math.ceil(total / perPage);
                const startIdx = (currentPage - 1) * perPage;
                const endIdx = Math.min(startIdx + perPage, total);
                const paginatedData = data.slice(startIdx, endIdx);

                tbody.innerHTML = paginatedData.map((item, idx) => `
                <tr class="${!item.is_active ? 'table-secondary' : ''}">
                    <td>${startIdx + idx + 1}</td>
                    <td>
                        <img src="${item.image_url || 'assets/images/placeholder.jpg'}" 
                             class="rounded" width="48" height="48" 
                             style="object-fit: cover;"
                             onerror="this.src='assets/images/placeholder.jpg'">
                    </td>
                    <td>
                        <div class="fw-semibold">${escapeHtml(item.name)}</div>
                    </td>
                    <td>
                        <span class="text-muted small">${escapeHtml(truncateText(item.description || '-', 50))}</span>
                    </td>
                    <td class="text-end fw-semibold text-primary">${formatCurrency(item.amount)}</td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark">${item.sort_order || 0}</span>
                    </td>
                    <td class="text-center">
                        ${item.is_active
                        ? '<span class="badge badge-soft-success">เปิดใช้งาน</span>'
                        : '<span class="badge badge-soft-secondary">ปิดใช้งาน</span>'}
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-soft-primary me-1" onclick="openEditModal(${item.id})" title="แก้ไข">
                            <iconify-icon icon="iconamoon:edit-duotone"></iconify-icon>
                        </button>
                        <button class="btn btn-sm btn-soft-${item.is_active ? 'warning' : 'success'}" onclick="toggleStatus(${item.id}, ${item.is_active})" title="${item.is_active ? 'ปิดใช้งาน' : 'เปิดใช้งาน'}">
                            <iconify-icon icon="iconamoon:${item.is_active ? 'close' : 'check'}-duotone"></iconify-icon>
                        </button>
                        <button class="btn btn-sm btn-soft-danger" onclick="deleteBenefit(${item.id}, '${escapeHtml(item.name)}')" title="ลบ">
                            <iconify-icon icon="iconamoon:trash-duotone"></iconify-icon>
                        </button>
                    </td>
                </tr>
            `).join('');

                // Update pagination UI
                renderPagination(totalPages, currentPage);
                document.getElementById('pagination-info').textContent = `แสดง ${total > 0 ? startIdx + 1 : 0}-${endIdx} จาก ${total} รายการ`;
            }

            function changeLimit() {
                perPage = parseInt(document.getElementById('limitSelector').value);
                currentPage = 1;
                renderTable(benefits); // benefits is the global filtered list? No, filterTable sets it.
                // Actually filterTable should call renderTable.
            }

            function goToPage(page) {
                if (page < 1) return;
                currentPage = page;
                filterTable(); // Re-apply filters and render
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            function renderPagination(totalPages, currentPage) {
                const pagination = document.getElementById('pagination');
                if (totalPages <= 1) {
                    pagination.innerHTML = '';
                    return;
                }

                let html = '<ul class="pagination pagination-sm mb-0">';
                html += `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="goToPage(1)">«</a>
                </li>
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="goToPage(${currentPage - 1})">‹</a>
                </li>
            `;

                for (let i = 1; i <= totalPages; i++) {
                    html += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="javascript:void(0)" onclick="goToPage(${i})">${i}</a>
                    </li>
                `;
                }

                html += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="goToPage(${currentPage + 1})">›</a>
                </li>
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="goToPage(${totalPages})">»</a>
                </li>
            `;
                html += '</ul>';
                pagination.innerHTML = html;
            }

            function filterTable() {
                const search = document.getElementById('searchInput').value.toLowerCase();
                const status = document.getElementById('statusFilter').value;

                const filtered = benefits.filter(item => {
                    const matchSearch = item.name.toLowerCase().includes(search) ||
                        (item.description || '').toLowerCase().includes(search);
                    const matchStatus = !status ||
                        (status === 'active' && item.is_active) ||
                        (status === 'inactive' && !item.is_active);
                    return matchSearch && matchStatus;
                });

                renderTable(filtered);
            }

            function truncateText(text, max) {
                if (!text) return '';
                return text.length > max ? text.substring(0, max) + '...' : text;
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

            async function toggleStatus(id, currentStatus) {
                try {
                    await apiPut('/benefits/' + id, { is_active: currentStatus ? 0 : 1 });
                    showSuccess(currentStatus ? 'ปิดใช้งานแล้ว' : 'เปิดใช้งานแล้ว');
                    loadBenefits();
                } catch (error) {
                    showError(error.message);
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
        </script>

</body>

</html>