<!DOCTYPE html>
<html lang="th">

<?php
$pageTitle = "ข่าวประชาสัมพันธ์";
$pageDesc = "ข่าวประชาสัมพันธ์และกิจกรรมล่าสุดจากคณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่";
include_once('../config/head.php');
?>

<!-- News Page Styles -->
<link rel="stylesheet" href="../assets/css/news.css">

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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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