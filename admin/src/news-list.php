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

            <style>
                .news-thumb {
                    width: 60px;
                    height: 45px;
                    object-fit: cover;
                    background: #f1f5f9;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
            </style>
        
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
                                        <label class="form-label">หัวข้อข่าว <span class="text-danger">*</span></label>
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
                                        <label class="form-label">รูปภาพประกอบ</label>
                                        <div class="border rounded p-3 text-center bg-light">
                                            <div id="imagePreviewWrapper" class="mb-2">
                                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='75' viewBox='0 0 100 75'%3E%3Crect width='100%25' height='100%25' fill='%23e2e8f0'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='sans-serif' font-size='10' fill='%2394a3b8'%3ENo Image%3C/text%3E%3C/svg%3E"
                                                    id="imagePreview" class="img-fluid rounded border"
                                                    style="max-height: 160px;">
                                            </div>
                                            <input type="file" class="form-control" id="fileInput" accept="image/*">
                                            <input type="hidden" id="img_file" name="img_file">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">หมวดหมู่</label>
                                        <select class="form-select" id="category" name="category">
                                            <option value="ข่าวประชาสัมพันธ์">ข่าวประชาสัมพันธ์</option>
                                            <option value="กิจกรรม">กิจกรรม</option>
                                            <option value="รายงานผล">รายงานผล</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">สถานะ</label>
                                        <select class="form-select" id="is_active" name="is_active">
                                            <option value="1">เผยแพร่</option>
                                            <option value="0">ฉบับร่าง</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">วันที่เผยแพร่</label>
                                        <input type="date" class="form-control" id="published_at" name="published_at">
                                    </div>
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

        <!-- Quill Editor -->
        <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

        <script>
            let newsList = [];
            let currentPage = 1;
            let perPage = 25;
            let quill;
            const NO_IMAGE = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='75' viewBox='0 0 100 75'%3E%3Crect width='100%25' height='100%25' fill='%23f1f5f9'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='sans-serif' font-size='10' fill='%2394a3b8'%3Eรูปภาพ%3C/text%3E%3C/svg%3E";

            document.addEventListener('DOMContentLoaded', () => {
                quill = new Quill('#editor', {
                    theme: 'snow',
                    modules: { toolbar: [['bold', 'italic', 'underline'], [{ 'list': 'ordered' }, { 'list': 'bullet' }], ['link', 'image'], ['clean']] }
                });

                loadNews();

                document.getElementById('searchInput').addEventListener('input', debounce(loadNews, 500));
                document.getElementById('categoryFilter').addEventListener('change', loadNews);
                document.getElementById('newsForm').addEventListener('submit', handleSubmit);

                document.getElementById('fileInput').addEventListener('change', function (e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (ev) => document.getElementById('imagePreview').src = ev.target.result;
                        reader.readAsDataURL(file);
                    }
                });
            });

            async function loadNews() {
                try {
                    const search = document.getElementById('searchInput').value;
                    const category = document.getElementById('categoryFilter').value;
                    const query = new URLSearchParams({ page: currentPage, limit: perPage, active: 0, offset: (currentPage - 1) * perPage });
                    if (search) query.append('search', search);
                    if (category) query.append('category', category);

                    const { data, meta } = await apiGet(`/news?${query}`);
                    newsList = data || [];

                    document.getElementById('total-news').textContent = meta.total || 0;
                    document.getElementById('published-news').textContent = newsList.filter(n => n.is_active).length;

                    renderTable();
                    renderPagination(meta.total || 0);
                } catch (e) {
                    showError(e.message);
                }
            }

            function renderTable() {
                document.getElementById('newsTable').innerHTML = newsList.length ? newsList.map(item => `
                        <tr>
                            <td>
                                <img src="${item.image_url || NO_IMAGE}" class=" news-thumb rounded" 
                                     onerror="this.src='${NO_IMAGE}'">
                            </td>
                            <td>
                                <div class="fw-medium">${escapeHtml(item.title)}</div>
                                <div class="small text-muted text-truncate" style="max-width: 300px;">${stripHtml(item.content || '').substring(0, 100)}...</div>
                            </td>
                            <td><span class="badge bg-light text-dark">${escapeHtml(item.category || '-')}</span></td>
                            <td>${formatThaiDateShort(item.published_at || item.created_at)}</td>
                            <td>${item.is_active ? '<span class="badge badge-soft-success">เผยแพร่</span>' : '<span class="badge badge-soft-warning">ฉบับร่าง</span>'}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-soft-primary me-1" onclick="openModal(${item.id})"><iconify-icon icon="iconamoon:edit-duotone"></iconify-icon></button>
                                <button class="btn btn-sm btn-soft-danger" onclick="deleteNews(${item.id}, '${escapeHtml(item.title)}')"><iconify-icon icon="iconamoon:trash-duotone"></iconify-icon></button>
                            </td>
                        </tr>
                    `).join('') : '<tr><td colspan="6" class="text-center py-4 text-muted">ไม่พบข้อมูล</td></tr>';
            }

            function openModal(id = null) {
                const form = document.getElementById('newsForm');
                form.reset();
                document.getElementById('newsId').value = id || '';
                document.getElementById('modalTitle').textContent = id ? 'แก้ไขข่าว' : 'เพิ่มข่าวใหม่';
                quill.root.innerHTML = '';
                document.getElementById('imagePreview').src = NO_IMAGE;
                document.getElementById('img_file').value = '';
                document.getElementById('published_at').value = new Date().toISOString().split('T')[0];

                if (id) {
                    const item = newsList.find(n => n.id == id);
                    if (item) {
                        document.getElementById('news_name').value = item.title;
                        quill.root.innerHTML = item.content || '';
                        document.getElementById('category').value = item.category || 'ข่าวประชาสัมพันธ์';
                        document.getElementById('is_active').value = item.is_active ? "1" : "0";
                        document.getElementById('published_at').value = (item.published_at || '').split(' ')[0];
                        document.getElementById('imagePreview').src = item.image_url || NO_IMAGE;
                        document.getElementById('img_file').value = item.img_file || '';
                    }
                }
                new bootstrap.Modal(document.getElementById('newsModal')).show();
            }

            async function handleSubmit(e) {
                e.preventDefault();
                const btn = document.getElementById('submitBtn'), spinner = document.getElementById('submitSpinner');
                btn.disabled = true; spinner.classList.remove('d-none');

                try {
                    let imgFile = document.getElementById('img_file').value;
                    const file = document.getElementById('fileInput').files[0];

                    // Upload image if selected
                    if (file) {
                        const uploadFormData = new FormData();
                        uploadFormData.append('image', file);
                        const uploadRes = await apiUpload('/news/upload', uploadFormData);
                        if (uploadRes.success) imgFile = uploadRes.data.filename;
                    }

                    const payload = {
                        title: document.getElementById('news_name').value,
                        content: quill.root.innerHTML,
                        category: document.getElementById('category').value,
                        is_active: parseInt(document.getElementById('is_active').value),
                        published_at: document.getElementById('published_at').value,
                        img_file: imgFile
                    };

                    const id = document.getElementById('newsId').value;
                    const res = id ? await apiPut(`/news/${id}`, payload) : await apiPost('/news', payload);

                    if (res.success) {
                        showSuccess(id ? 'อัปเดตข่าวสำเร็จ' : 'เพิ่มข่าวสำเร็จ');
                        bootstrap.Modal.getInstance(document.getElementById('newsModal')).hide();
                        loadNews();
                    }
                } catch (e) {
                    showError(e.message);
                } finally {
                    btn.disabled = false; spinner.classList.add('d-none');
                }
            }

            function renderPagination(total) {
                const totalPages = Math.ceil(total / perPage);
                const container = document.getElementById('pagination');
                if (totalPages <= 1) { container.innerHTML = ''; return; }

                let html = '<ul class="pagination pagination-sm mb-0">';
                for (let i = 1; i <= totalPages; i++) {
                    html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" onclick="changePage(${i})">${i}</a></li>`;
                }
                container.innerHTML = html + '</ul>';
                document.getElementById('pagination-info').textContent = `แสดง ${(currentPage - 1) * perPage + 1}-${Math.min(currentPage * perPage, total)} จาก ${total}`;
            }

            function changePage(p) { currentPage = p; loadNews(); window.scrollTo(0, 0); }
            function changeLimit() { perPage = parseInt(document.getElementById('limitSelector').value); currentPage = 1; loadNews(); }
            async function deleteNews(id, name) { if ((await confirmDelete(name)).isConfirmed) { try { await apiDelete(`/news/${id}`); showSuccess('ลบข่าวสำเร็จ'); loadNews(); } catch (e) { showError(e.message); } } }
            function stripHtml(html) { const t = document.createElement('div'); t.innerHTML = html; return t.textContent || t.innerText || ''; }
            function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
            function debounce(f, w) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => f(...a), w); }; }
            function openCreateModal() { openModal(); } // Legacy compatibility
        </script>
        </div>
    </div>
</body>

</html>