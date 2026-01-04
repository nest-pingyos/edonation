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
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12">
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

    <script>
        // Get API_BASE from meta tag
        const API_BASE = document.querySelector('meta[name="api-base"]')?.content || '/edonation/api/v1';

        document.addEventListener('DOMContentLoaded', function () {
            loadServices();
        });

        async function loadServices() {
            const container = document.getElementById('servicesContainer');

            try {
                const response = await fetch(`${API_BASE}/services?type=service`);
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
                container.innerHTML = '<div class="col-12 text-center py-5"><p>ไม่มีของที่ระลึก</p></div>';
                return;
            }

            let html = '';
            services.forEach((item, index) => {
                html += `
                    <div class="col-sm-6 col-md-6 col-lg-3">
                        <div class="product-item">
                            <div class="product__img">
                                <img src="${item.image_url}" alt="${escapeHtml(item.name)}" loading="lazy"
                                     onerror="this.src='../assets/images/products/default.jpg'">
                            </div>
                            <div class="product__info">
                                <h4 class="product__title">${escapeHtml(item.name)}</h4>
                                <span class="product__price">${escapeHtml(item.amount)}</span>
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
    </script>
</body>

</html>