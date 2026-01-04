<!DOCTYPE html>
<html lang="en">

<?php
include_once('../config/head.php');
?>

<body>
    <div class="wrapper">
        <div class="preloader">
            <div class="loading"><span></span><span></span><span></span><span></span></div>
        </div>

        <?php
        include_once('../config/header.php');
        ?>

        <section class="shop-grid">
            <div class="container">
                <div class="row" id="servicesContainer">
                    <!-- Services will be loaded via API -->
                    <div class="col-12 text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">กำลังโหลดข้อมูล...</p>
                    </div>
                </div>
            </div>
        </section>

        <?php
        include_once('../config/footer.php');
        ?>

        <button id="scrollTopBtn"><i class="fas fa-long-arrow-alt-up"></i></button>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Get API_BASE from meta tag
        const API_BASE = document.querySelector('meta[name="api-base"]')?.content || '/edonation/api/v1';
        const isLoggedIn = <?php echo isset($_SESSION['user_login']) ? 'true' : 'false'; ?>;

        document.addEventListener('DOMContentLoaded', function () {
            loadServices();
        });

        async function loadServices() {
            const container = document.getElementById('servicesContainer');

            try {
                const response = await fetch(`${API_BASE}/services?type=service_donat`);
                const result = await response.json();

                if (result.success && result.data) {
                    renderServices(result.data);
                } else {
                    container.innerHTML = '<div class="col-12 text-center py-5"><p>ไม่พบข้อมูล</p></div>';
                }
            } catch (error) {
                console.error('Error loading services:', error);
                container.innerHTML = '<div class="col-12 text-center py-5"><p class="text-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</p></div>';
            }
        }

        function renderServices(services) {
            const container = document.getElementById('servicesContainer');

            if (services.length === 0) {
                container.innerHTML = '<div class="col-12 text-center py-5"><p>ไม่มีบริการ</p></div>';
                return;
            }

            let html = '';
            services.forEach(item => {
                const description = item.desc ? item.desc.replace(/-/g, '<br>') : '';

                html += `
                    <div class="col-sm-6 col-md-6 col-lg-4">
                        <div class="post-item">
                            <div class="post__img">
                                <a>
                                    <img src="${item.image_url}" alt="${escapeHtml(item.name)}" loading="lazy"
                                         onerror="this.src='../assets/images/products/default.jpg'">
                                </a>
                            </div>
                            <div class="post__body">
                                <div class="post__meta-cat">
                                    <a>${escapeHtml(item.amount)}</a>
                                </div>
                                <div class="post__meta d-flex">
                                    <span class="post__meta-date">Jan 30, 2022</span>
                                    <a class="post__meta-author" href="#">คงเหลือ ${escapeHtml(item.quantity)} ชิ้น</a>
                                </div>
                                <h4 class="post__title"><a href="#">${escapeHtml(item.name)}</a></h4>
                                <p class="post__desc">${description}</p>
                                <a href="#" class="btn btn__secondary btn__link btn__rounded" onclick="checkLogin(event)">
                                    <span>เลือก</span>
                                    <i class="icon-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function checkLogin(event) {
            event.preventDefault();

            if (!isLoggedIn) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณาเข้าสู่ระบบ!',
                    text: 'คุณต้องเข้าสู่ระบบก่อนทำรายการ',
                    confirmButtonColor: '#ffaa00',
                    confirmButtonText: 'เข้าสู่ระบบ'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '../oauth/';
                    }
                });
            } else {
                window.location.href = '../error/';
            }
        }
    </script>
</body>

</html>