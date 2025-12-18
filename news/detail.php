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

<style>
    /* News Detail Page Styles */
    .news-detail-section {
        padding: 60px 0 80px;
        background: #ffffff;
        min-height: 100vh;
    }

    /* Breadcrumb */
    .news-breadcrumb {
        margin-bottom: 30px;
    }

    .news-breadcrumb a {
        color: #666;
        text-decoration: none;
        transition: color 0.3s;
    }

    .news-breadcrumb a:hover {
        color: #FB974E;
    }

    .news-breadcrumb span {
        color: #999;
        margin: 0 10px;
    }

    .news-breadcrumb .current {
        color: #1a3a5c;
        font-weight: 500;
    }

    /* Main Article Container */
    .news-article {
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.08);
    }

    /* Featured Image */
    .news-article-image {
        width: 100%;
        height: 400px;
        overflow: hidden;
        position: relative;
    }

    @media (max-width: 768px) {
        .news-article-image {
            height: 250px;
        }
    }

    .news-article-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .news-article-category {
        position: absolute;
        top: 20px;
        left: 20px;
        background: #FB974E;
        color: #fff;
        padding: 8px 20px;
        border-radius: 25px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .news-article-featured {
        position: absolute;
        top: 20px;
        right: 20px;
        background: #FB974E;
        color: #fff;
        padding: 8px 20px;
        border-radius: 25px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    /* Article Content */
    .news-article-content {
        padding: 40px;
    }

    @media (max-width: 768px) {
        .news-article-content {
            padding: 25px;
        }
    }

    /* Meta Info */
    .news-article-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 25px;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }

    .news-meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #666;
        font-size: 0.9rem;
    }

    .news-meta-item i {
        color: #FB974E;
    }

    /* Title */
    .news-article-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1a3a5c;
        line-height: 1.4;
        margin-bottom: 30px;
    }

    @media (max-width: 768px) {
        .news-article-title {
            font-size: 1.5rem;
        }
    }

    .news-article-excerpt {
        font-size: 1.15rem;
        color: #555;
        line-height: 1.8;
        margin-bottom: 30px;
        padding: 20px;
        background: #fff8f4;
        border-left: 4px solid #FB974E;
        border-radius: 0 10px 10px 0;
    }

    /* Content Body */
    .news-article-body {
        font-size: 1.05rem;
        color: #444;
        line-height: 1.9;
    }

    .news-article-body p {
        margin-bottom: 20px;
    }

    .news-article-body h2,
    .news-article-body h3 {
        color: #1a3a5c;
        margin: 30px 0 15px;
    }

    .news-article-body img {
        max-width: 100%;
        border-radius: 10px;
        margin: 20px 0;
    }

    .news-article-body a {
        color: #FB974E;
    }

    /* Share Section */
    .news-share {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-top: 40px;
        padding-top: 30px;
        border-top: 1px solid #eee;
    }

    .news-share-label {
        font-weight: 600;
        color: #1a3a5c;
    }

    .share-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        color: #fff;
        cursor: pointer;
        transition: transform 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .share-btn:hover {
        transform: scale(1.1);
    }

    .share-btn.facebook {
        background: #1877f2;
    }

    .share-btn.twitter {
        background: #1da1f2;
    }

    .share-btn.line {
        background: #00c300;
    }

    .share-btn.copy {
        background: #666;
    }

    /* Navigation */
    .news-navigation {
        display: flex;
        justify-content: space-between;
        margin-top: 40px;
    }

    .nav-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px 25px;
        background: #FB974E;
        color: #fff;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
    }

    .nav-btn:hover {
        background: #e87d2e;
        color: #fff;
        text-decoration: none;
    }

    .nav-btn.secondary {
        background: #1a3a5c;
    }

    .nav-btn.secondary:hover {
        background: #2c5282;
    }

    /* Related News Sidebar */
    .news-sidebar {
        position: sticky;
        top: 100px;
    }

    .sidebar-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1a3a5c;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #FB974E;
    }

    .related-news-item {
        display: flex;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid #eee;
        text-decoration: none;
        transition: all 0.3s;
    }

    .related-news-item:hover {
        opacity: 0.8;
        text-decoration: none;
    }

    .related-news-image {
        width: 80px;
        height: 60px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
    }

    .related-news-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .related-news-content {
        flex: 1;
    }

    .related-news-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #1a3a5c;
        line-height: 1.4;
        margin-bottom: 5px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .related-news-date {
        font-size: 0.8rem;
        color: #888;
    }

    /* Loading State */
    .news-loading {
        text-align: center;
        padding: 100px 20px;
    }

    .news-loading .spinner-border {
        width: 3rem;
        height: 3rem;
    }

    /* Error State */
    .news-error {
        text-align: center;
        padding: 100px 20px;
    }

    .news-error h4 {
        color: #1a3a5c;
        margin-bottom: 15px;
    }

    .news-error p {
        color: #666;
        margin-bottom: 20px;
    }
</style>

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
                                <!-- Featured Image -->
                                <div class="news-article-image">
                                    <img id="articleImage" src="../assets/images/blog/grid/default.jpg" alt="">
                                    <span class="news-article-category" id="articleCategory">ข่าวสาร</span>
                                    <span class="news-article-featured" id="articleFeatured"
                                        style="display: none;">ข่าวเด่น</span>
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

                                    <!-- Share -->
                                    <div class="news-share">
                                        <span class="news-share-label">แชร์:</span>
                                        <button class="share-btn facebook" onclick="shareToFacebook()"
                                            title="แชร์ไปยัง Facebook">
                                            <i class="fab fa-facebook-f"></i>
                                        </button>
                                        <button class="share-btn twitter" onclick="shareToTwitter()"
                                            title="แชร์ไปยัง Twitter">
                                            <i class="fab fa-twitter"></i>
                                        </button>
                                        <button class="share-btn line" onclick="shareToLine()" title="แชร์ไปยัง LINE">
                                            <i class="fab fa-line"></i>
                                        </button>
                                        <button class="share-btn copy" onclick="copyLink()" title="คัดลอกลิงก์">
                                            <i class="fas fa-link"></i>
                                        </button>
                                    </div>
                                </div>
                            </article>

                            <!-- Navigation -->
                            <div class="news-navigation">
                                <a href="index.php" class="nav-btn secondary">
                                    <i class="fas fa-arrow-left"></i> กลับไปรายการข่าว
                                </a>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="col-lg-4">
                            <aside class="news-sidebar">
                                <h4 class="sidebar-title">ข่าวที่น่าสนใจ</h4>
                                <div id="relatedNews">
                                    <!-- Related news will be loaded here -->
                                </div>
                            </aside>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php include_once('../config/footer.php'); ?>
    </div>

    <script src="../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/main.js"></script>

    <script>
        const API_BASE = '/appdev/edonation/api/v1';
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

            // Featured badge
            if (news.is_featured == 1) {
                document.getElementById('articleFeatured').style.display = 'block';
            }

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
                // Convert newlines to paragraphs
                const paragraphs = news.content.split('\n').filter(p => p.trim());
                bodyEl.innerHTML = paragraphs.map(p => `<p>${escapeHtml(p)}</p>`).join('');
            } else if (news.excerpt) {
                bodyEl.innerHTML = `<p>${escapeHtml(news.excerpt)}</p>`;
            } else {
                bodyEl.innerHTML = '<p>ไม่มีเนื้อหา</p>';
            }
        }

        async function loadRelatedNews() {
            try {
                const response = await fetch(`${API_BASE}/news?limit=5`);
                const result = await response.json();

                if (result.success && result.data) {
                    // Filter out current news
                    const related = result.data.filter(item => item.id != newsId).slice(0, 4);
                    renderRelatedNews(related);
                }
            } catch (error) {
                console.error('Error loading related news:', error);
            }
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