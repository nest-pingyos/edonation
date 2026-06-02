<!DOCTYPE html>
<html lang="en">

<?php
$pageTitle = "หน้าแรก";
$pageDesc = "ร่วมบริจาคกับคณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่ เพื่อสนับสนุนการศึกษา วิจัย และพัฒนาคุณภาพชีวิต";
include_once('../config/head.php');
?>

<!-- Home Page Styles -->
<link rel="stylesheet" href="../assets/css/home.css">

<body>
    <div class="wrapper">
        <div class="preloader">
            <div class="loading"><span></span><span></span><span></span><span></span></div>
        </div>
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
            // Load data on page load
            document.addEventListener('DOMContentLoaded', function () {
                loadProjects();
                loadNews();
            });

            // Load projects from API
            function loadProjects() {
                const container = document.getElementById('projectsContainer');

                fetch(`${API_BASE}/projects?limit=3&status=active&sort=asc`)
                    .then(response => {
                        if (!response.ok) throw new Error('API Error: ' + response.status);
                        return response.json();
                    })
                    .then(result => {
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

                container.innerHTML = news.map(item => {
                    const categoryLabel = NEWS_CATEGORY_LABELS[item.category] || item.category || 'ข่าวสาร';

                    // Check if news is newer than 7 days
                    const pubDate = item.published_at ? new Date(item.published_at) : new Date();
                    const isNew = (new Date() - pubDate) / (1000 * 60 * 60 * 24) <= 7;

                    return `
                    <div class="news-slide">
                        <div class="news-card">
                            <div class="news-card-image">
                                <img src="${item.image_url}" alt="${escapeHtml(item.title)}" 
                                     onerror="this.src='../assets/images/blog/grid/default.jpg'">
                                <span class="news-card-category">${escapeHtml(categoryLabel)}</span>
                                ${isNew ? '<span class="news-badge-new">ใหม่</span>' : ''}
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
                                    <a href="../news/detail.php?id=${item.id}" class="btn__link">
                                        <span>อ่านเพิ่มเติม</span>
                                        <i class="fas fa-arrow-right"></i>
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

        </script>

        <?php
        include_once('../config/footer.php');
        ?>

        <button id="scrollTopBtn"><i class="fas fa-long-arrow-alt-up"></i></button>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/utils.js"></script>

</body>

</html>