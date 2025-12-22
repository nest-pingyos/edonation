<!DOCTYPE html>
<html lang="th">

<?php
$pageTitle = "ข่าวประชาสัมพันธ์";
$pageDesc = "ข่าวประชาสัมพันธ์และกิจกรรมล่าสุดจากคณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่";
include_once('../config/head.php');
?>

<style>
    /* News List Page Styles */
    .news-section {
        padding: 80px 0;
        background: #ffffff;
        min-height: 100vh;
    }

    .news-header {
        text-align: center;
        margin-bottom: 60px;
    }

    .news-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1a3a5c;
        margin-bottom: 15px;
    }

    .news-header p {
        font-size: 1.1rem;
        color: #6c757d;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Category Filter */
    .news-filter {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 40px;
    }

    .filter-btn {
        padding: 10px 24px;
        border: 2px solid #e0e0e0;
        border-radius: 50px;
        background: #fff;
        color: #666;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-btn:hover,
    .filter-btn.active {
        background: #FB974E;
        border-color: #FB974E;
        color: #fff;
    }

    /* Search Box */
    .news-search {
        max-width: 500px;
        margin: 0 auto 30px;
    }

    .search-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-input-wrapper input {
        width: 100%;
        padding: 14px 50px 14px 20px;
        border: 2px solid #e0e0e0;
        border-radius: 50px;
        font-size: 1rem;
        transition: all 0.3s ease;
        outline: none;
    }

    .search-input-wrapper input:focus {
        border-color: #FB974E;
        box-shadow: 0 0 0 3px rgba(251, 151, 78, 0.15);
    }

    .search-input-wrapper input::placeholder {
        color: #999;
    }

    .search-btn {
        position: absolute;
        right: 5px;
        width: 42px;
        height: 42px;
        border: none;
        background: #FB974E;
        color: #fff;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .search-btn:hover {
        background: #e87d2e;
        transform: scale(1.05);
    }

    .search-clear {
        position: absolute;
        right: 55px;
        background: none;
        border: none;
        color: #999;
        cursor: pointer;
        padding: 5px;
        display: none;
    }

    .search-clear.visible {
        display: block;
    }

    .search-clear:hover {
        color: #666;
    }

    /* News Grid */
    .news-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }

    @media (max-width: 992px) {
        .news-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .news-grid {
            grid-template-columns: 1fr;
        }
    }

    /* News Card */
    .news-card {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .news-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }

    /* Card Image */
    .news-card-image {
        width: 100%;
        height: 200px;
        overflow: hidden;
        position: relative;
    }

    .news-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .news-card:hover .news-card-image img {
        transform: scale(1.05);
    }

    .news-card-category {
        position: absolute;
        top: 15px;
        left: 15px;
        background: #FB974E;
        color: #fff;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .news-card-featured {
        position: absolute;
        top: 15px;
        right: 15px;
        background: #FB974E;
        color: #fff;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* Card Content */
    .news-card-content {
        padding: 24px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .news-card-date {
        font-size: 0.85rem;
        color: #888;
        margin-bottom: 10px;
    }

    .news-card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1a3a5c;
        margin-bottom: 12px;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 3.3em;
        /* Fixed height for 2 lines */
    }

    .news-card-excerpt {
        font-size: 0.9rem;
        color: #666;
        line-height: 1.6;
        margin-bottom: 16px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 4.8em;
        /* Fixed height for 3 lines */
    }

    .news-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 16px;
        border-top: 1px solid #f0f0f0;
        margin-top: auto;
        /* Push footer to bottom */
    }

    .news-card-views {
        font-size: 0.8rem;
        color: #888;
    }

    .news-card-link {
        color: #FB974E;
        font-weight: 600;
        text-decoration: none;
        font-size: 0.9rem;
        transition: color 0.3s ease;
    }

    .news-card-link:hover {
        color: #e87d2e;
    }

    /* Loading & Empty States */
    .news-loading,
    .news-empty {
        text-align: center;
        padding: 60px 20px;
    }

    .news-loading .spinner-border {
        width: 3rem;
        height: 3rem;
    }

    /* Pagination */
    .news-pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 50px;
    }

    .page-btn {
        padding: 10px 20px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        background: #fff;
        color: #666;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .page-btn:hover:not(:disabled) {
        background: #FB974E;
        border-color: #FB974E;
        color: #fff;
    }

    .page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .page-info {
        display: flex;
        align-items: center;
        color: #666;
    }
</style>

<body>
    <div class="wrapper">
        <?php include_once('../config/header.php'); ?>

        <section class="news-section">
            <div class="container">
                <!-- Header -->
                <div class="news-header">
                    <h1>ข่าวประชาสัมพันธ์</h1>
                    <p>ติดตามข่าวสารและกิจกรรมล่าสุดจากคณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่</p>
                </div>

                <!-- Category Filter -->
                <div class="news-filter" id="newsFilter">
                    <button class="filter-btn active" data-category="">ทั้งหมด</button>
                    <button class="filter-btn" data-category="general">ข่าวทั่วไป</button>
                    <button class="filter-btn" data-category="announcement">ประกาศ</button>
                    <button class="filter-btn" data-category="thank">ขอบคุณ</button>
                </div>

                <!-- Search Box -->
                <div class="news-search">
                    <div class="search-input-wrapper">
                        <input type="text" id="searchInput" placeholder="ค้นหาข่าว..." autocomplete="off">
                        <button type="button" class="search-clear" id="searchClear">
                            <i class="fas fa-times"></i>
                        </button>
                        <button type="button" class="search-btn" id="searchBtn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="loadingState" class="news-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3">กำลังโหลดข้อมูล...</p>
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="news-empty" style="display: none;">
                    <h4>ไม่พบข่าว</h4>
                    <p>ยังไม่มีข่าวในหมวดหมู่นี้</p>
                </div>

                <!-- News Grid -->
                <div id="newsGrid" class="news-grid" style="display: none;">
                    <!-- Cards will be loaded via JavaScript -->
                </div>

                <!-- Pagination -->
                <div id="newsPagination" class="news-pagination" style="display: none;">
                    <button class="page-btn" id="prevBtn" disabled>
                        <i class="fas fa-chevron-left"></i> ก่อนหน้า
                    </button>
                    <span class="page-info" id="pageInfo">หน้า 1</span>
                    <button class="page-btn" id="nextBtn">
                        ถัดไป <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </section>

        <?php include_once('../config/footer.php'); ?>
    </div>

    <script src="../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/main.js"></script>

    <script>
        // Get API_BASE from meta tag (set by PHP head.php)
        const API_BASE = document.querySelector('meta[name="api-base"]')?.content || '/edonation/api/v1';
        let currentPage = 0;
        let currentCategory = '';
        let searchQuery = '';
        const itemsPerPage = 12; // 4 rows x 3 columns

        document.addEventListener('DOMContentLoaded', function () {
            loadNews();
            setupFilters();
            setupPagination();
            setupSearch();
        });

        function setupSearch() {
            const searchInput = document.getElementById('searchInput');
            const searchBtn = document.getElementById('searchBtn');
            const searchClear = document.getElementById('searchClear');

            // Search on button click
            searchBtn.addEventListener('click', () => {
                performSearch();
            });

            // Search on Enter key
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });

            // Show/hide clear button
            searchInput.addEventListener('input', () => {
                if (searchInput.value.trim()) {
                    searchClear.classList.add('visible');
                } else {
                    searchClear.classList.remove('visible');
                }
            });

            // Clear search
            searchClear.addEventListener('click', () => {
                searchInput.value = '';
                searchClear.classList.remove('visible');
                searchQuery = '';
                currentPage = 0;
                loadNews();
            });
        }

        function performSearch() {
            const searchInput = document.getElementById('searchInput');
            searchQuery = searchInput.value.trim();
            currentPage = 0;
            loadNews();
        }

        function setupFilters() {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentCategory = this.dataset.category;
                    currentPage = 0;
                    loadNews();
                });
            });
        }

        function setupPagination() {
            document.getElementById('prevBtn').addEventListener('click', () => {
                if (currentPage > 0) {
                    currentPage--;
                    loadNews();
                }
            });

            document.getElementById('nextBtn').addEventListener('click', () => {
                currentPage++;
                loadNews();
            });
        }

        async function loadNews() {
            const loadingState = document.getElementById('loadingState');
            const emptyState = document.getElementById('emptyState');
            const newsGrid = document.getElementById('newsGrid');
            const pagination = document.getElementById('newsPagination');

            // Show loading
            loadingState.style.display = 'block';
            emptyState.style.display = 'none';
            newsGrid.style.display = 'none';

            try {
                let url = `${API_BASE}/news?limit=${itemsPerPage}&offset=${currentPage * itemsPerPage}`;
                if (currentCategory) {
                    url += `&category=${currentCategory}`;
                }
                if (searchQuery) {
                    url += `&search=${encodeURIComponent(searchQuery)}`;
                }

                const response = await fetch(url);
                const result = await response.json();

                loadingState.style.display = 'none';

                if (result.success && result.data && result.data.length > 0) {
                    newsGrid.style.display = 'grid';
                    renderNews(result.data);
                    updatePagination(result.meta);
                } else {
                    emptyState.style.display = 'block';
                    pagination.style.display = 'none';
                }
            } catch (error) {
                console.error('Error loading news:', error);
                loadingState.style.display = 'none';
                emptyState.innerHTML = `
    <h4>เกิดข้อผิดพลาด</h4>
    <p>ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่อีกครั้ง</p>
    `;
                emptyState.style.display = 'block';
            }
        }

        function renderNews(news) {
            const newsGrid = document.getElementById('newsGrid');

            const categoryLabels = {
                'general': 'ข่าวทั่วไป',
                'announcement': 'ประกาศ',
                'thank': 'ขอบคุณ'
            };

            let html = '';
            news.forEach(item => {
                const categoryLabel = categoryLabels[item.category] || item.category || 'ข่าวสาร';

                html += `
    <div class="news-card">
        <div class="news-card-image">
            <img src="${item.image_url}" alt="${escapeHtml(item.title)}"
                onerror="this.src='../assets/images/blog/grid/default.jpg'">
            <span class="news-card-category">${escapeHtml(categoryLabel)}</span>
            ${item.is_featured == 1 ? '<span class="news-card-featured">ข่าวเด่น</span>' : ''}
        </div>
        <div class="news-card-content">
            <div class="news-card-date">
                <i class="far fa-calendar-alt"></i> ${item.published_at_formatted || '-'}
            </div>
            <h3 class="news-card-title">${escapeHtml(item.title)}</h3>
            <p class="news-card-excerpt">${escapeHtml(item.excerpt || '')}</p>
            <div class="news-card-footer">
                <span class="news-card-views">
                    <i class="far fa-eye"></i> ${item.view_count || 0} เข้าชม
                </span>
                <a href="detail.php?id=${item.id}" class="news-card-link">
                    อ่านต่อ <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    `;
            });

            newsGrid.innerHTML = html;
        }

        function updatePagination(meta) {
            const pagination = document.getElementById('newsPagination');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const pageInfo = document.getElementById('pageInfo');

            if (meta.total > itemsPerPage) {
                pagination.style.display = 'flex';

                prevBtn.disabled = currentPage === 0;

                const totalPages = Math.ceil(meta.total / itemsPerPage);
                nextBtn.disabled = (currentPage + 1) >= totalPages;

                pageInfo.textContent = `หน้า ${currentPage + 1} จาก ${totalPages}`;
            } else {
                pagination.style.display = 'none';
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>

</html>