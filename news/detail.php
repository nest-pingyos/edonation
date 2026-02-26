<!DOCTYPE html>
<html lang="th">

<?php
// Get news ID from URL
$newsId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Set default page info (will be updated by JS)
$pageTitle = "รายละเอียดข่าว";
$pageDesc = "ข่าวประชาสัมพันธ์จากคณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่";
include_once('../config/head.php');
?>

<!-- News Detail Page Styles -->
<link rel="stylesheet" href="../assets/css/news-detail.css">

<body>
    <div class="wrapper">
        <?php include_once('../config/header.php'); ?>

        <section class="news-detail-section">
            <div class="container">
                <!-- Loading State -->
                <div id="loadingState" class="news-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3">กำลังโหลดข้อมูล...</p>
                </div>

                <!-- Error State -->
                <div id="errorState" class="news-error" style="display: none;">
                    <h4>ไม่พบข่าวที่ต้องการ</h4>
                    <p>ข่าวนี้อาจถูกลบหรือไม่มีอยู่ในระบบ</p>
                    <a href="index.php" class="nav-btn">
                        <i class="fas fa-arrow-left"></i> กลับไปหน้ารายการข่าว
                    </a>
                </div>

                <!-- Article Content -->
                <div id="articleContent" style="display: none;">
                    <!-- Breadcrumb -->
                    <nav class="news-breadcrumb">
                        <a href="../home/">หน้าแรก</a>
                        <span>/</span>
                        <a href="index.php">ข่าวประชาสัมพันธ์</a>
                        <span>/</span>
                        <span class="current" id="breadcrumbTitle">รายละเอียดข่าว</span>
                    </nav>

                    <div class="row">
                        <!-- Main Content -->
                        <div class="col-lg-8">
                            <article class="news-article">
                                <!-- Featured Image / Gallery -->
                                <div class="news-article-image">
                                    <div id="imageGallery" class="news-gallery">
                                        <img id="articleImage" src="../assets/images/blog/grid/default.jpg" alt="">
                                    </div>
                                    <span class="news-article-category" id="articleCategory">ข่าวสาร</span>
                                </div>

                                <!-- Content -->
                                <div class="news-article-content">
                                    <!-- Meta Info -->
                                    <div class="news-article-meta">
                                        <div class="news-meta-item">
                                            <i class="far fa-calendar-alt"></i>
                                            <span id="articleDate">-</span>
                                        </div>
                                        <div class="news-meta-item">
                                            <i class="far fa-user"></i>
                                            <span id="articleAuthor">-</span>
                                        </div>
                                        <div class="news-meta-item">
                                            <i class="far fa-eye"></i>
                                            <span id="articleViews">0</span> เข้าชม
                                        </div>
                                    </div>

                                    <!-- Title -->
                                    <h1 class="news-article-title" id="articleTitle">หัวข้อข่าว</h1>

                                    <!-- Excerpt -->
                                    <div class="news-article-excerpt" id="articleExcerpt" style="display: none;">
                                        เนื้อหาย่อ
                                    </div>

                                    <!-- Body -->
                                    <div class="news-article-body" id="articleBody">
                                        <p>เนื้อหา...</p>
                                    </div>

                                    <!-- Album Gallery -->
                                    <div id="albumSection" style="display: none; margin-top: 40px;">
                                        <h5 class="fw-bold mb-3"><i class="far fa-images me-1"></i> รูปภาพเพิ่มเติม</h5>
                                        <div id="albumGallery" class="row g-3">
                                            <!-- Album items will be rendered as col-md-4 -->
                                        </div>
                                    </div>
                                </div> <!-- news-article-content -->
                            </article> <!-- news-article -->

                            <!-- Recommended News Grid (Bottom) -->
                            <div class="recommended-news-section mt-5">
                                <h4 class="sidebar-title mb-4">ข่าวแนะนำสำหรับคุณ</h4>
                                <div id="recommendedNewsGrid" class="row g-4">
                                    <!-- Recommended news will be loaded here -->
                                </div>
                            </div>

                            <!-- Back Navigation -->
                            <div class="news-navigation mt-5">
                                <a href="index.php" class="nav-btn secondary">
                                    <i class="fas fa-arrow-left"></i> กลับไปรายการข่าว
                                </a>
                            </div>
                        </div> <!-- col-lg-8 -->

                        <!-- Sidebar -->
                        <div class="col-lg-4">
                            <aside class="news-sidebar">
                                <h4 class="sidebar-title">ข่าวที่น่าสนใจ</h4>
                                <div id="relatedNews">
                                    <!-- Related news will be loaded here -->
                                </div>
                            </aside>
                        </div>
                    </div> <!-- row -->
                </div> <!-- articleContent -->
            </div> <!-- container -->
        </section>

        <style>
            .gallery-item img {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                cursor: pointer;
                width: 100%;
                height: 200px;
                object-fit: cover;
                border-radius: 8px;
            }

            .gallery-item img:hover {
                transform: scale(1.02);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            }

            .recommended-card {
                display: block;
                text-decoration: none;
                color: inherit;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                background-color: #fff;
                height: 100%;
                border: 1px solid #eee;
            }

            .recommended-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
                border-color: #FB974E;
            }

            .recommended-card-img {
                width: 100%;
                height: 160px;
                object-fit: cover;
            }

            .recommended-card-content {
                padding: 15px;
            }

            .recommended-card-title {
                font-size: 1rem;
                font-weight: 600;
                margin-bottom: 8px;
                color: #1a3a5c;
                line-height: 1.4;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .recommended-card-date {
                font-size: 0.8rem;
                color: #888;
            }
        </style>

        <?php include_once('../config/footer.php'); ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/main.js"></script>

    <script>
        // Get API_BASE from meta tag (set by PHP head.php)
        const API_BASE = document.querySelector('meta[name="api-base"]')?.content || '/edonation/api/v1';
        const newsId = <?php echo $newsId; ?>;
        let currentNews = null;

        document.addEventListener('DOMContentLoaded', function () {
            if (newsId > 0) {
                loadNewsDetail();
                loadRelatedNews();
            } else {
                showError();
            }
        });

        async function loadNewsDetail() {
            const loadingState = document.getElementById('loadingState');
            const errorState = document.getElementById('errorState');
            const articleContent = document.getElementById('articleContent');

            try {
                const response = await fetch(`${API_BASE}/news/${newsId}`);
                const result = await response.json();

                loadingState.style.display = 'none';

                if (result.success && result.data) {
                    currentNews = result.data;
                    renderNewsDetail(result.data);
                    articleContent.style.display = 'block';
                } else {
                    showError();
                }
            } catch (error) {
                console.error('Error loading news:', error);
                showError();
            }
        }

        function renderNewsDetail(news) {
            const categoryLabels = {
                'general': 'ข่าวทั่วไป',
                'announcement': 'ประกาศ',
                'thank': 'ขอบคุณ'
            };

            // Update page title
            document.title = news.title + ' | คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่';

            // Breadcrumb
            document.getElementById('breadcrumbTitle').textContent = truncateText(news.title, 50);

            // Image
            const image = document.getElementById('articleImage');
            image.src = news.image_url;
            image.alt = news.title;
            image.onerror = function () {
                this.src = '../assets/images/blog/grid/default.jpg';
            };

            // Category
            document.getElementById('articleCategory').textContent = categoryLabels[news.category] || news.category || 'ข่าวสาร';


            // Meta
            document.getElementById('articleDate').textContent = news.published_at_formatted || '-';
            document.getElementById('articleAuthor').textContent = news.author || 'ผู้ดูแลระบบ';
            document.getElementById('articleViews').textContent = news.view_count || 0;

            // Title
            document.getElementById('articleTitle').textContent = news.title;

            // Excerpt
            if (news.excerpt) {
                const excerptEl = document.getElementById('articleExcerpt');
                excerptEl.textContent = news.excerpt;
                excerptEl.style.display = 'block';
            }

            // Body
            const bodyEl = document.getElementById('articleBody');
            if (news.content) {
                // Quill provides HTML, we show it directly to support styles/colors
                bodyEl.innerHTML = news.content;
            } else if (news.excerpt) {
                bodyEl.innerHTML = `<p>${escapeHtml(news.excerpt)}</p>`;
            } else {
                bodyEl.innerHTML = '<p>ไม่มีเนื้อหา</p>';
            }

            // Main Cover
            const coverImg = document.getElementById('articleImage');
            if (news.cover_url) {
                coverImg.src = news.cover_url;
            } else if (news.image_url) {
                coverImg.src = news.image_url;
            }

            // Image Album Setup
            const albumSection = document.getElementById('albumSection');
            const albumGallery = document.getElementById('albumGallery');

            if (news.album_urls && news.album_urls.length > 0) {
                albumSection.style.display = 'block';
                albumGallery.innerHTML = news.album_urls.map(url => `
                    <div class="col-6 col-md-4 gallery-item">
                        <img src="${url}" alt="Album Image" 
                             onerror="this.src='../assets/images/blog/grid/default.jpg'">
                    </div>
                `).join('');
            } else {
                albumSection.style.display = 'none';
            }
        }

        async function loadRelatedNews() {
            try {
                const response = await fetch(`${API_BASE}/news?limit=10`);
                const result = await response.json();

                if (result.success && result.data) {
                    // Filter out current news
                    const allOthers = result.data.filter(item => item.id != newsId);

                    // Sidebar: 4 items
                    renderRelatedNews(allOthers.slice(0, 4));

                    // Bottom Recommended: 3 items (different from sidebar)
                    renderRecommendedGrid(allOthers.slice(4, 7));
                }
            } catch (error) {
                console.error('Error loading related news:', error);
            }
        }

        function renderRecommendedGrid(news) {
            const container = document.getElementById('recommendedNewsGrid');
            if (!container) return;

            if (news.length === 0) {
                container.innerHTML = '<div class="col-12"><p class="text-muted">กำลังเตรียมข่าวแนะนำ...</p></div>';
                return;
            }

            container.innerHTML = news.map(item => `
                <div class="col-md-4">
                    <a href="detail.php?id=${item.id}" class="recommended-card">
                        <img src="${item.image_url}" class="recommended-card-img" alt="${escapeHtml(item.title)}"
                             onerror="this.src='../assets/images/blog/grid/default.jpg'">
                        <div class="recommended-card-content">
                            <h5 class="recommended-card-title">${escapeHtml(item.title)}</h5>
                            <div class="recommended-card-date">
                                <i class="far fa-calendar-alt"></i> ${item.published_at_formatted || '-'}
                            </div>
                        </div>
                    </a>
                </div>
            `).join('');
        }

        function renderRelatedNews(news) {
            const container = document.getElementById('relatedNews');

            if (news.length === 0) {
                container.innerHTML = '<p class="text-muted">ยังไม่มีข่าวอื่น</p>';
                return;
            }

            let html = '';
            news.forEach(item => {
                html += `
                <a href="detail.php?id=${item.id}" class="related-news-item">
                    <div class="related-news-image">
                        <img src="${item.image_url}" alt="${escapeHtml(item.title)}"
                             onerror="this.src='../assets/images/blog/grid/default.jpg'">
                    </div>
                    <div class="related-news-content">
                        <h5 class="related-news-title">${escapeHtml(item.title)}</h5>
                        <span class="related-news-date">
                            <i class="far fa-calendar-alt"></i> ${item.published_at_formatted || '-'}
                        </span>
                    </div>
                </a>
            `;
            });

            container.innerHTML = html;
        }

        function showError() {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('errorState').style.display = 'block';
        }

        function truncateText(text, maxLength) {
            if (text.length <= maxLength) return text;
            return text.substring(0, maxLength) + '...';
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Share functions
        function shareToFacebook() {
            const url = encodeURIComponent(window.location.href);
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
        }

        function shareToTwitter() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent(currentNews?.title || 'ข่าวประชาสัมพันธ์');
            window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank', 'width=600,height=400');
        }

        function shareToLine() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent(currentNews?.title || 'ข่าวประชาสัมพันธ์');
            window.open(`https://social-plugins.line.me/lineit/share?url=${url}&text=${text}`, '_blank', 'width=600,height=400');
        }

        function copyLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('คัดลอกลิงก์แล้ว!');
            }).catch(() => {
                // Fallback
                const input = document.createElement('input');
                input.value = window.location.href;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
                alert('คัดลอกลิงก์แล้ว!');
            });
        }
    </script>
</body>

</html>