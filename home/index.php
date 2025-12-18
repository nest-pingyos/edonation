<!DOCTYPE html>
<html lang="en">

<?php
$pageTitle = "หน้าแรก";
$pageDesc = "ร่วมบริจาคกับคณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่ เพื่อสนับสนุนการศึกษา วิจัย และพัฒนาคุณภาพชีวิต";
include_once('../config/head.php');
?>

<style>
    /* News Card Styles for Home */
    .news-card {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .news-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }

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

    /* Project Card Styles */
    .project-card {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .project-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }

    .project-card-image {
        width: 100%;
        height: 200px;
        overflow: hidden;
        position: relative;
    }

    .project-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .project-card:hover .project-card-image img {
        transform: scale(1.05);
    }

    .project-card-content {
        padding: 24px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .project-card-title {
        font-size: 1.15rem;
        font-weight: 600;
        color: #1a3a5c;
        margin-bottom: 12px;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 3.45em;
        /* Fixed height for 2 lines */
    }

    .project-card-desc {
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

    .project-card-footer {
        padding-top: 16px;
        border-top: 1px solid #f0f0f0;
        margin-top: auto;
        /* Push footer to bottom */
    }

    .btn-donate {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 24px;
        background: linear-gradient(135deg, #FB974E 0%, #e87d2e 100%);
        color: #fff;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        width: 100%;
    }

    .btn-donate:hover {
        background: linear-gradient(135deg, #e87d2e 0%, #d06820 100%);
        color: #fff;
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(251, 151, 78, 0.4);
    }

    .btn-donate i {
        transition: transform 0.3s ease;
    }

    .btn-donate:hover i:last-child {
        transform: translateX(3px);
    }

    /* View More Button - Modern & Compact */
    .view-more-container {
        text-align: center;
        margin-top: 35px;
    }

    .btn-view-more {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 24px;
        background: transparent;
        color: #1a3a5c;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        border: 2px solid #e0e0e0;
    }

    .btn-view-more:hover {
        background: #FB974E;
        border-color: #FB974E;
        color: #fff;
        text-decoration: none;
    }

    .btn-view-more i {
        font-size: 0.8rem;
        transition: transform 0.3s ease;
    }

    .btn-view-more:hover i {
        transform: translateX(4px);
    }

    /* News Carousel Styles */
    .news-carousel-container {
        position: relative;
        padding: 0 50px;
    }

    .news-carousel {
        margin: 0 -15px;
    }

    /* Equal height for slick slides */
    .news-carousel .slick-track {
        display: flex !important;
    }

    .news-carousel .slick-slide {
        height: inherit !important;
    }

    .news-carousel .slick-slide>div {
        height: 100%;
    }

    .news-carousel .news-slide {
        padding: 0 15px;
        height: 100%;
    }

    .news-carousel .news-slide .news-card {
        height: 100%;
    }

    .news-carousel .slick-prev,
    .news-carousel .slick-next {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 45px;
        height: 45px;
        background: #fff;
        border-radius: 50%;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        border: none;
        z-index: 10;
        transition: all 0.3s ease;
    }

    .news-carousel .slick-prev:hover,
    .news-carousel .slick-next:hover {
        background: #00a651;
        color: #fff;
    }

    .news-carousel .slick-prev {
        left: -50px;
    }

    .news-carousel .slick-next {
        right: -50px;
    }

    .news-carousel .slick-prev:before,
    .news-carousel .slick-next:before {
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        font-size: 16px;
        color: #1a3a5c;
    }

    .news-carousel .slick-prev:hover:before,
    .news-carousel .slick-next:hover:before {
        color: #fff;
    }

    .news-carousel .slick-prev:before {
        content: '\f053';
    }

    .news-carousel .slick-next:before {
        content: '\f054';
    }

    @media (max-width: 768px) {
        .news-carousel-container {
            padding: 0 15px;
        }

        .news-carousel .slick-prev {
            left: -10px;
        }

        .news-carousel .slick-next {
            right: -10px;
        }
    }
</style>

<body>
    <div class="wrapper">
        <!-- <div class="preloader">
            <div class="loading"><span></span><span></span><span></span><span></span></div>
        </div> -->
        <?php
        include_once('../config/header.php');
        ?>

        <section class="slider">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="slick-carousel m-slides-0"
                    data-slick='{"slidesToShow": 1, "arrows": true, "dots": false, "speed": 700,"fade": true,"cssEase": "linear"}'>
                    <div class="slide-item align-v-h">
                        <div class="bg-img"><img src="../assets/images/sliders/1.jpg" alt="slide img"></div>
                    </div>
                    <div class="slide-item align-v-h">
                        <div class="bg-img"><img src="../assets/images/sliders/2.png" alt="slide img"></div>
                    </div>
                    <div class="slide-item align-v-h">
                        <div class="bg-img"><img src="../assets/images/sliders/1.png" alt="slide img"></div>
                    </div>
                </div>
            </div>
        </section>


        <section class="services-layout1 services-carousel">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-6 offset-lg-3">
                        <div class="heading text-center mb-60">
                            <h3 class="heading__title">โครงการ</h3>
                        </div>
                    </div>
                </div>
                <div class="row" id="projectsContainer">
                    <!-- Projects will be loaded via API -->
                    <div class="col-12 text-center">
                        <div class="loading"><span></span><span></span><span></span></div>
                    </div>
                </div>
                <!-- View More Button for Projects -->
                <div class="view-more-container" id="projectsViewMore" style="display: none;">
                    <a href="../projects/" class="btn-view-more">
                        <span>ดูโครงการทั้งหมด</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- News Section -->
        <section class="blog-layout1 pt-80 pb-80" style="background: #f8f9fa;">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-6 offset-lg-3">
                        <div class="heading text-center mb-60">
                            <h3 class="heading__title">ข่าวประชาสัมพันธ์</h3>
                        </div>
                    </div>
                </div>
                <!-- News Carousel -->
                <div class="news-carousel-container">
                    <div class="news-carousel" id="newsContainer">
                        <!-- News will be loaded via API -->
                    </div>
                </div>
                <!-- View More Button for News -->
                <div class="view-more-container" id="newsViewMore" style="display: none;">
                    <a href="../news/" class="btn-view-more">
                        <span>ดูข่าวทั้งหมด</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>

        <script>
            const API_BASE = '/appdev/edonation/api/v1';

            // Load data on page load
            document.addEventListener('DOMContentLoaded', function () {
                loadProjects();
                loadNews();
            });

            // Load projects from API
            function loadProjects() {
                const container = document.getElementById('projectsContainer');

                fetch(`${API_BASE}/projects?limit=3`)
                    .then(response => {
                        if (!response.ok) throw new Error('API Error: ' + response.status);
                        return response.json();
                    })
                    .then(result => {
                        console.log('Projects API Response:', result);
                        if (result.success && result.data && result.data.length > 0) {
                            renderProjects(result.data);
                            document.getElementById('projectsViewMore').style.display = 'block';
                        } else {
                            container.innerHTML = '<div class="col-12 text-center"><p>ยังไม่มีโครงการในขณะนี้</p></div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading projects:', error);
                        container.innerHTML = '<div class="col-12 text-center"><p>ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่</p></div>';
                    });
            }

            function renderProjects(projects) {
                const container = document.getElementById('projectsContainer');
                if (!projects || projects.length === 0) {
                    container.innerHTML = '<div class="col-12 text-center"><p>ยังไม่มีโครงการในขณะนี้</p></div>';
                    return;
                }

                // Get current lang parameter
                const urlParams = new URLSearchParams(window.location.search);
                const lang = urlParams.get('lang');
                const langParam = lang ? `?lang=${lang}` : '';

                container.innerHTML = projects.map(project => `
                <div class="col-sm-12 col-md-6 col-lg-4 mb-4">
                    <div class="project-card">
                        <div class="project-card-image">
                            <img src="${project.image_url}" 
                                 alt="${project.project_name || 'Project'}" 
                                 loading="lazy"
                                 onerror="this.src='../assets/images/projects/pro-1.jpg'">
                        </div>
                        <div class="project-card-content">
                            <h3 class="project-card-title">${project.project_name_web || project.project_name || 'โครงการ'}</h3>
                            <p class="project-card-desc">${project.description || project.short_description || project.project_description || project.project_tex || 'รายละเอียดโครงการ...'}</p>
                            <div class="project-card-footer">
                                <a href="../donat/${project.project_number}${langParam}" class="btn-donate">
                                    <i class="fas fa-heart"></i>
                                    <span>บริจาคเลย</span>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
            }

            // Load news from API
            function loadNews() {
                const container = document.getElementById('newsContainer');
                container.innerHTML = '<div class="text-center py-5"><div class="loading"><span></span><span></span><span></span></div></div>';

                fetch(`${API_BASE}/news?limit=6`)
                    .then(response => {
                        if (!response.ok) throw new Error('API Error: ' + response.status);
                        return response.json();
                    })
                    .then(result => {
                        console.log('News API Response:', result);
                        if (result.success && result.data && result.data.length > 0) {
                            renderNews(result.data);
                            document.getElementById('newsViewMore').style.display = 'block';
                        } else {
                            container.innerHTML = '<div class="text-center"><p>ยังไม่มีข่าวในขณะนี้</p></div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading news:', error);
                        container.innerHTML = '<div class="text-center"><p>ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่</p></div>';
                    });
            }

            function renderNews(news) {
                const container = document.getElementById('newsContainer');

                const categoryLabels = {
                    'general': 'ข่าวทั่วไป',
                    'announcement': 'ประกาศ',
                    'thank': 'ขอบคุณ'
                };

                container.innerHTML = news.map(item => {
                    const categoryLabel = categoryLabels[item.category] || item.category || 'ข่าวสาร';

                    return `
                    <div class="news-slide">
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
                                    <a href="../news/detail.php?id=${item.id}" class="btn btn__secondary btn__link btn__rounded">
                                        <span>อ่านเพิ่มเติม</span>
                                        <i class="icon-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                }).join('');

                // Initialize slick carousel after content is loaded
                setTimeout(() => {
                    $('.news-carousel').slick({
                        slidesToShow: 3,
                        slidesToScroll: 1,
                        arrows: true,
                        dots: false,
                        infinite: true,
                        autoplay: true,
                        autoplaySpeed: 5000,
                        responsive: [
                            {
                                breakpoint: 992,
                                settings: {
                                    slidesToShow: 2
                                }
                            },
                            {
                                breakpoint: 576,
                                settings: {
                                    slidesToShow: 1
                                }
                            }
                        ]
                    });
                }, 100);
            }

            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        </script>

        <?php
        include_once('../config/footer.php');
        ?>

        <button id="scrollTopBtn"><i class="fas fa-long-arrow-alt-up"></i></button>
    </div>

    <script src="../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/main.js"></script>
    <!-- <script src="phpscript.php"></script> -->

</body>

</html>