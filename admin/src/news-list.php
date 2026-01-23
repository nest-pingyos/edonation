<?php include 'partials/main.php'; ?>
<?php requireAuth(); ?>

<!doctype html>
<html lang="th">

<head>
    <?php
    $title = "จัดการข่าวสาร";
    include 'partials/title-meta.php'; ?>

    <?php include 'partials/head-css.php'; ?>

    <!-- Quill Editor -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
</head>

<body>
    <div class="wrapper">
        <?php include 'partials/edonation-nav.php'; ?>

        <div class="page-content">
            <?php include 'partials/edonation-topbar.php'; ?>

            <div class="container-xxl">
                <?php
                $pageTitle = "จัดการข่าวสาร";
                $subTitle = "ข่าวสารและเนื้อหา";
                include 'partials/page-title.php'; ?>

                <!-- Stats -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-soft-info rounded">
                                        <iconify-icon icon="iconamoon:news-duotone"
                                            class="avatar-title text-info fs-32"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="total-news">-</h3>
                                        <p class="text-muted mb-0">ข่าวทั้งหมด</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-soft-success rounded">
                                        <iconify-icon icon="iconamoon:eye-duotone"
                                            class="avatar-title text-success fs-32"></iconify-icon>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="mb-0" id="published-news">-</h3>
                                        <p class="text-muted mb-0">เผยแพร่แล้ว</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Card -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">รายการข่าวสาร</h4>
                        <button class="btn btn-primary" onclick="openCreateModal()">
                            <iconify-icon icon="iconamoon:sign-plus-duotone" class="me-1"></iconify-icon>
                            เพิ่มข่าว
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-3 g-2 align-items-center">
                            <div class="col-md-2">
                                <div class="input-group">
                                    <span class="input-group-text bg-light">แสดง</span>
                                    <select id="limitSelector" class="form-select border-0 bg-light"
                                        onchange="changeLimit()">
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="250">250</option>
                                        <option value="500">500</option>
                                    </select>
                                    <span class="input-group-text bg-light">แถว</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        ค้นหา
                                    </span>
                                    <input type="text" id="searchInput" class="form-control"
                                        placeholder="ค้นหาโครงการ...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select id="categoryFilter" class="form-select">
                                    <option value="">ทุกหมวดหมู่</option>
                                    <option value="ข่าวประชาสัมพันธ์">ข่าวประชาสัมพันธ์</option>
                                    <option value="กิจกรรม">กิจกรรม</option>
                                    <option value="รายงานผล">รายงานผล</option>
                                </select>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 80px;">รูป</th>
                                        <th>หัวข้อข่าว</th>
                                        <th style="width: 140px;">หมวดหมู่</th>
                                        <th style="width: 140px;">วันที่</th>
                                        <th style="width: 100px;">สถานะ</th>
                                        <th style="width: 120px;" class="text-center">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="newsTable">
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
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

                    <?php include 'partials/footer.php'; ?>
                </div>
            </div>

            <!-- News Modal -->
            <div class="modal fade" id="newsModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitle">เพิ่มข่าวใหม่</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="newsForm" enctype="multipart/form-data">
                            <div class="modal-body">
                                <input type="hidden" id="newsId" name="id">

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="form-label">หัวข้อข่าว <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="news_name" name="news_name"
                                                required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">เนื้อหา</label>
                                            <div id="editor" style="height: 250px;"></div>
                                            <input type="hidden" id="news_detail" name="news_detail">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">รูปภาพ</label>
                                            <div class="border rounded p-3 text-center" id="imagePreviewContainer">
                                                <img src="assets/images/placeholder.jpg" id="imagePreview"
                                                    class="img-fluid rounded mb-2" style="max-height: 150px;">
                                                <input type="file" class="form-control" id="news_image"
                                                    accept="image/*">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">หมวดหมู่</label>
                                            <select class="form-select" id="news_category" name="news_category">
                                                <option value="ข่าวประชาสัมพันธ์">ข่าวประชาสัมพันธ์</option>
                                                <option value="กิจกรรม">กิจกรรม</option>
                                                <option value="รายงานผล">รายงานผล</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">สถานะ</label>
                                            <select class="form-select" id="news_status" name="news_status">
                                                <option value="published">เผยแพร่</option>
                                                <option value="draft">ฉบับร่าง</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">วันที่เผยแพร่</label>
                                            <input type="date" class="form-control" id="created_at" name="created_at">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <span class="spinner-border spinner-border-sm me-1 d-none"
                                        id="submitSpinner"></span>
                                    บันทึก
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php include 'partials/vendor-scripts.php'; ?>
            <script src="assets/js/api-helper.js"></script>

            <!-- Quill Editor -->
            <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

            <script>
                let news = [];
                let currentPage = 1;
                let perPage = 25;
                let editMode = false;
                let quill;

                document.addEventListener('DOMContentLoaded', function () {
                    // Initialize Quill editor
                    quill = new Quill('#editor', {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                ['bold', 'italic', 'underline'],
                                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                                ['link', 'image'],
                                ['clean']
                            ]
                        }
                    });

                    loadNews();

                    // Search & filter handlers
                    document.getElementById('searchInput').addEventListener('input', debounce(() => {
                        currentPage = 1;
                        loadNews();
                    }, 500));
                    document.getElementById('categoryFilter').addEventListener('change', () => {
                        currentPage = 1;
                        loadNews();
                    });

                    // Form submit
                    document.getElementById('newsForm').addEventListener('submit', handleSubmit);

                    // Image preview
                    document.getElementById('news_image').addEventListener('change', function (e) {
                        const file = e.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function (e) {
                                document.getElementById('imagePreview').src = e.target.result;
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                });

                async function loadNews() {
                    try {
                        const search = document.getElementById('searchInput').value;
                        const category = document.getElementById('categoryFilter').value;
                        const params = new URLSearchParams();
                        params.append('page', currentPage);
                        params.append('limit', perPage);
                        params.append('active', '0'); // Admin sees all
                        params.append('offset', (currentPage - 1) * perPage);

                        if (search) params.append('search', search);
                        if (category) params.append('category', category);

                        const response = await apiGet('/news?' + params.toString());
                        news = response.data || [];
                        const meta = response.meta || {};

                        document.getElementById('total-news').textContent = meta.total || news.length;
                        document.getElementById('published-news').textContent = news.filter(n => n.is_active).length;

                        renderTable(news);

                        // Update pagination
                        const total = meta.total || news.length;
                        const totalPages = Math.ceil(total / perPage);
                        renderPagination(totalPages, currentPage);

                        const from = total > 0 ? (currentPage - 1) * perPage + 1 : 0;
                        const to = Math.min(currentPage * perPage, total);
                        document.getElementById('pagination-info').textContent = `แสดง ${from}-${to} จาก ${total} รายการ`;

                    } catch (error) {
                        showError(error.message);
                        document.getElementById('newsTable').innerHTML = `
            <tr><td colspan="6" class="text-center py-4 text-danger">${error.message}</td></tr>
        `;
                    }
                }

                function renderTable(data) {
                    const tbody = document.getElementById('newsTable');

                    if (!data || data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">ไม่พบข้อมูล</td></tr>';
                        return;
                    }

                    tbody.innerHTML = data.map((item, index) => `
        <tr>
            <td>
                <img src="${item.image_url || 'assets/images/placeholder.jpg'}" 
                     class="rounded" width="60" height="45" style="object-fit: cover;"
                     onerror="this.src='assets/images/placeholder.jpg'">
            </td>
            <td>
                <span class="fw-medium">${escapeHtml(item.title || item.news_name)}</span>
                ${item.excerpt ? `<br><small class="text-muted">${escapeHtml(item.excerpt.substring(0, 60))}...</small>` : ''}
            </td>
            <td><span class="badge bg-light text-dark">${escapeHtml(item.category || item.news_category || '-')}</span></td>
            <td>${formatThaiDateShort(item.published_at || item.created_at || item.news_date)}</td>
            <td>${getStatusBadge(item.is_active ? 'published' : 'draft')}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-soft-primary me-1" onclick="openEditModal(${item.id})" title="แก้ไข">
                    <iconify-icon icon="iconamoon:edit-duotone"></iconify-icon>
                </button>
                <button class="btn btn-sm btn-soft-danger" onclick="deleteNews(${item.id}, '${escapeHtml(item.title || item.news_name)}')" title="ลบ">
                    <iconify-icon icon="iconamoon:trash-duotone"></iconify-icon>
                </button>
            </td>
        </tr>
    `).join('');
                }

                function changeLimit() {
                    perPage = parseInt(document.getElementById('limitSelector').value);
                    currentPage = 1;
                    loadNews();
                }

                function goToPage(page) {
                    if (page < 1) return;
                    currentPage = page;
                    loadNews();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }

                function renderPagination(totalPages, currentPage) {
                    const pagination = document.getElementById('pagination');
                    if (totalPages <= 1) {
                        pagination.innerHTML = '';
                        return;
                    }

                    let html = '<ul class="pagination pagination-sm mb-0">';

                    // First & Previous
                    html += `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="goToPage(1)" title="หน้าแรก">«</a>
                </li>
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="goToPage(${currentPage - 1})" title="ก่อนหน้า">‹</a>
                </li>
            `;

                    // Calculate range
                    let start = Math.max(1, currentPage - 2);
                    let end = Math.min(totalPages, start + 4);
                    if (end - start < 4) start = Math.max(1, end - 4);

                    for (let i = start; i <= end; i++) {
                        html += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="javascript:void(0)" onclick="goToPage(${i})">${i}</a>
                    </li>
                `;
                    }

                    // Next & Last
                    html += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="goToPage(${currentPage + 1})" title="ถัดไป">›</a>
                </li>
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="goToPage(${totalPages})" title="หน้าสุดท้าย">»</a>
                </li>
            `;

                    html += '</ul>';
                    pagination.innerHTML = html;
                }

                function openCreateModal() {
                    editMode = false;
                    document.getElementById('modalTitle').textContent = 'เพิ่มข่าวใหม่';
                    document.getElementById('newsForm').reset();
                    document.getElementById('newsId').value = '';
                    document.getElementById('imagePreview').src = 'assets/images/placeholder.jpg';
                    quill.root.innerHTML = '';
                    document.getElementById('created_at').value = new Date().toISOString().split('T')[0];

                    new bootstrap.Modal(document.getElementById('newsModal')).show();
                }

                function openEditModal(id) {
                    const item = news.find(n => n.id == id);
                    if (!item) return;

                    editMode = true;
                    document.getElementById('modalTitle').textContent = 'แก้ไขข่าว';
                    document.getElementById('newsId').value = item.id;
                    document.getElementById('news_name').value = item.news_name || '';
                    quill.root.innerHTML = item.news_detail || '';
                    document.getElementById('news_category').value = item.news_category || 'ข่าวประชาสัมพันธ์';
                    document.getElementById('news_status').value = item.news_status || 'published';
                    document.getElementById('imagePreview').src = item.image_url || 'assets/images/placeholder.jpg';

                    if (item.created_at) {
                        document.getElementById('created_at').value = item.created_at.split(' ')[0];
                    }

                    new bootstrap.Modal(document.getElementById('newsModal')).show();
                }

                async function handleSubmit(e) {
                    e.preventDefault();

                    const submitBtn = document.getElementById('submitBtn');
                    const spinner = document.getElementById('submitSpinner');

                    submitBtn.disabled = true;
                    spinner.classList.remove('d-none');

                    try {
                        // Get editor content
                        document.getElementById('news_detail').value = quill.root.innerHTML;

                        const formData = {
                            news_name: document.getElementById('news_name').value,
                            news_detail: quill.root.innerHTML,
                            news_category: document.getElementById('news_category').value,
                            news_status: document.getElementById('news_status').value,
                            created_at: document.getElementById('created_at').value
                        };

                        if (editMode) {
                            const id = document.getElementById('newsId').value;
                            await apiPut('/news/' + id, formData);
                            showSuccess('อัปเดตข่าวสำเร็จ');
                        } else {
                            await apiPost('/news', formData);
                            showSuccess('เพิ่มข่าวสำเร็จ');
                        }

                        bootstrap.Modal.getInstance(document.getElementById('newsModal')).hide();
                        loadNews();

                    } catch (error) {
                        showError(error.message);
                    } finally {
                        submitBtn.disabled = false;
                        spinner.classList.add('d-none');
                    }
                }

                async function deleteNews(id, name) {
                    const result = await confirmDelete(name);
                    if (!result.isConfirmed) return;

                    try {
                        await apiDelete('/news/' + id);
                        showSuccess('ลบข่าวสำเร็จ');
                        loadNews();
                    } catch (error) {
                        showError(error.message);
                    }
                }

                function getStatusBadge(status) {
                    if (status === 'draft') return '<span class="badge badge-soft-warning">ฉบับร่าง</span>';
                    return '<span class="badge badge-soft-success">เผยแพร่</span>';
                }

                function stripHtml(html) {
                    const tmp = document.createElement('DIV');
                    tmp.innerHTML = html;
                    return tmp.textContent || tmp.innerText || '';
                }

                function escapeHtml(str) {
                    if (!str) return '';
                    const div = document.createElement('div');
                    div.textContent = str;
                    return div.innerHTML;
                }

                function debounce(func, wait) {
                    let timeout;
                    return function (...args) {
                        clearTimeout(timeout);
                        timeout = setTimeout(() => func.apply(this, args), wait);
                    };
                }
            </script>

</body>

</html>