<!DOCTYPE html>
<html lang="th">

<?php
$pageTitle = "สิทธิประโยชน์ผู้บริจาค";
$pageDesc = "เครื่องราชอิสริยาภรณ์อันเป็นที่สรรเสริญยิ่งดิเรกคุณาภรณ์ คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่";
include_once('../config/head.php');
?>

<!-- Benefits Page Styles -->
<link rel="stylesheet" href="../assets/css/benefits.css">

<body>
    <div class="wrapper">
        <?php include_once('../config/header.php'); ?>

        <!-- Benefits Section -->
        <section class="benefits-section">
            <div class="container">
                <!-- Royal Header -->
                <div class="royal-header">
                    <h1 class="royal-title">เครื่องราชอิสริยาภรณ์อันเป็นที่สรรเสริญยิ่งดิเรกคุณาภรณ์</h1>
                    <div class="royal-divider"></div>
                </div>

                <p class="royal-intro">
                    เครื่องราชอิสริยาภรณ์อันเป็นที่สรรเสริญยิ่งดิเรกคุณาภรณ์ เป็นเครื่องราชอิสริยาภรณ์ที่
                    <strong>พระบาทสมเด็จพระบรมชนกาธิเบศร มหาภูมิพลอดุลยเดชมหาราช บรมนาถบพิตร</strong>
                    พระราชทานพระบรมราชานุญาตให้สร้างขึ้น สำหรับพระราชทานแก่ผู้กระทำความดีความชอบ
                    อันเป็นประโยชน์แก่ประเทศ ศาสนา และประชาชน ตามที่ทรงพระราชดำริเห็นสมควร
                </p>

                <!-- Loading State -->
                <div id="loadingState" class="benefits-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3">กำลังโหลดข้อมูล...</p>
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="benefits-empty" style="display: none;">
                    <h4>ไม่พบข้อมูล</h4>
                    <p>ยังไม่มีข้อมูลระดับผู้มีอุปการคุณในระบบ</p>
                </div>

                <!-- Benefits Grid -->
                <div id="benefitsGrid" class="benefits-grid" style="display: none;">
                    <!-- Cards will be loaded via JavaScript -->
                </div>


            </div>
        </section>

        <?php include_once('../config/footer.php'); ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/main.js"></script>

    <script>
        const API_BASE = document.querySelector('meta[name="api-base"]')?.content || '/edonation/api/v1';

        document.addEventListener('DOMContentLoaded', function () {
            loadBenefits();
        });

        async function loadBenefits() {
            const loadingState = document.getElementById('loadingState');
            const emptyState = document.getElementById('emptyState');
            const benefitsGrid = document.getElementById('benefitsGrid');

            try {
                const response = await fetch(`${API_BASE}/benefits`);
                const result = await response.json();

                loadingState.style.display = 'none';

                if (result.success && result.data && result.data.length > 0) {
                    benefitsGrid.style.display = 'grid';
                    renderBenefits(result.data);
                } else {
                    emptyState.style.display = 'block';
                }
            } catch (error) {
                console.error('Error loading benefits:', error);
                loadingState.style.display = 'none';
                emptyState.style.display = 'block';
                emptyState.innerHTML = `
                    <h4>เกิดข้อผิดพลาด</h4>
                    <p>ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่อีกครั้ง</p>
                `;
            }
        }

        function renderBenefits(benefits) {
            const benefitsGrid = document.getElementById('benefitsGrid');

            let html = '';
            benefits.forEach((item) => {
                // Display order: 1.img_file, 2.name, 3.description + amount
                html += `
                    <div class="benefit-card">
                        <div class="benefit-card-image">
                            <img src="${item.image_url}" alt="${escapeHtml(item.name)}" 
                                 onerror="this.src='../assets/images/benefits/1.jpg'">
                        </div>
                        <div class="benefit-card-content">
                            <h3 class="benefit-card-title">${escapeHtml(item.name)}</h3>
                            ${item.description ? `<p class="benefit-card-desc">${escapeHtml(item.description)}</p>` : ''}
                            <div class="benefit-card-amount">
                                ${formatAmount(item.amount)}
                            </div>
                        </div>
                    </div>
                `;
            });

            benefitsGrid.innerHTML = html;
        }

        function formatAmount(amount) {
            if (typeof amount === 'string') {
                return amount + ' บาทขึ้นไป';
            }
            return new Intl.NumberFormat('th-TH', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount) + ' บาทขึ้นไป';
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