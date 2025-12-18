<!DOCTYPE html>
<html lang="en">

<?php
$pageTitle = "หน้าแรก";
$pageDesc = "ร่วมบริจาคกับคณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่ เพื่อสนับสนุนการศึกษา วิจัย และพัฒนาคุณภาพชีวิต";
include_once('../config/head.php');
?>


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
                <div class="row" id="newsContainer">
                    <!-- Sample News Items -->
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="post-item">
                            <div class="post__img">
                                <img src="../assets/images/blog/grid/default.jpg" alt="News" loading="lazy">
                            </div>
                            <div class="post__body">
                                <div class="post__meta-cat">ข่าวสาร</div>
                                <h4 class="post__title">ขอเชิญร่วมบริจาคกองทุนพัฒนาคณะพยาบาลศาสตร์</h4>
                                <p class="post__desc">คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่ ขอเชิญชวนศิษย์เก่า บุคลากร และผู้มีจิตศรัทธา...</p>
                                <a href="#" class="btn btn__secondary btn__link btn__rounded">
                                    <span>อ่านเพิ่มเติม</span>
                                    <i class="icon-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="post-item">
                            <div class="post__img">
                                <img src="../assets/images/blog/grid/default.jpg" alt="News" loading="lazy">
                            </div>
                            <div class="post__body">
                                <div class="post__meta-cat">กิจกรรม</div>
                                <h4 class="post__title">รายงานยอดบริจาคประจำเดือนพฤศจิกายน 2567</h4>
                                <p class="post__desc">คณะพยาบาลศาสตร์ขอขอบคุณผู้บริจาคทุกท่านที่ร่วมสนับสนุนกองทุนพัฒนา...</p>
                                <a href="#" class="btn btn__secondary btn__link btn__rounded">
                                    <span>อ่านเพิ่มเติม</span>
                                    <i class="icon-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="post-item">
                            <div class="post__img">
                                <img src="../assets/images/blog/grid/default.jpg" alt="News" loading="lazy">
                            </div>
                            <div class="post__body">
                                <div class="post__meta-cat">ประกาศ</div>
                                <h4 class="post__title">ประกาศรายชื่อผู้ได้รับเครื่องราชอิสริยาภรณ์ ประจำปี 2567</h4>
                                <p class="post__desc">ตรวจสอบรายชื่อผู้ได้รับเครื่องราชอิสริยาภรณ์และสิทธิประโยชน์ต่างๆ...</p>
                                <a href="#" class="btn btn__secondary btn__link btn__rounded">
                                    <span>อ่านเพิ่มเติม</span>
                                    <i class="icon-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <script>
        // Load projects from API
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('projectsContainer');
            
            fetch('/appdev/edonation/api/v1/projects?limit=3')
                .then(response => {
                    if (!response.ok) throw new Error('API Error: ' + response.status);
                    return response.json();
                })
                .then(result => {
                    console.log('API Response:', result);
                    if (result.success && result.data && result.data.length > 0) {
                        renderProjects(result.data);
                    } else {
                        container.innerHTML = '<div class="col-12 text-center"><p>ยังไม่มีโครงการในขณะนี้</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading projects:', error);
                    container.innerHTML = '<div class="col-12 text-center"><p>ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่</p></div>';
                });
        });

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
                <div class="col-sm-12 col-md-6 col-lg-4 project-col">
                    <div class="post-item">
                        <div class="post__img">
                            <img src="${project.image_url}" 
                                 alt="${project.project_name || 'Project'}" 
                                 loading="lazy"
                                 onerror="this.src='../assets/images/projects/pro-1.jpg'">
                        </div>
                        <div class="post__body">
                            <h4 class="post__title">${project.project_name_web || project.project_name || 'โครงการ'}</h4>
                            <p class="post__desc">${project.description || project.short_description || project.project_description || project.project_tex || 'รายละเอียดโครงการ...'}</p>
                            
                            <div class="post__footer">
                                <a href="../donat/${project.project_number}${langParam}" class="btn-donate-link">
                                    <span>บริจาค</span>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
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