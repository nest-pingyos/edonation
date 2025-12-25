<!DOCTYPE html>
<html lang="th">

<?php
$pageTitle = "สิทธิประโยชน์ผู้บริจาค";
$pageDesc = "เครื่องราชอิสริยาภรณ์อันเป็นที่สรรเสริญยิ่งดิเรกคุณาภรณ์ คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่";
include_once('../config/head.php');
?>

<style>
    /* Benefits Section Styles */
    .benefits-section {
        padding: 80px 0;
        background: #f8fafc;
        min-height: 60vh;
    }

    /* Royal Header */
    .royal-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .royal-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1a3a5c;
        margin-bottom: 20px;
        line-height: 1.5;
    }

    .royal-divider {
        width: 100px;
        height: 5px;
        background: linear-gradient(90deg, #c9a227, #ffd700, #c9a227);
        margin: 0 auto;
        border-radius: 3px;
    }

    .royal-intro {
        font-size: 1.05rem;
        color: #4a5568;
        line-height: 1.9;
        text-align: center;
        max-width: 900px;
        margin: 30px auto 50px;
    }

    .royal-intro strong {
        color: #1a3a5c;
    }

    /* Benefits Grid - 4 cards per row */
    .benefits-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 25px;
    }

    @media (max-width: 1200px) {
        .benefits-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 992px) {
        .benefits-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .benefits-grid {
            grid-template-columns: 1fr;
        }

        .royal-title {
            font-size: 1.5rem;
        }
    }

    /* Benefit Card - Original Style */
    .benefit-card {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .benefit-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }

    /* Card Image */
    .benefit-card-image {
        width: 100%;
        height: 200px;
        overflow: hidden;
        background: #f0f0f0;
    }

    .benefit-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .benefit-card:hover .benefit-card-image img {
        transform: scale(1.05);
    }

    /* Card Content */
    .benefit-card-content {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .benefit-card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1a3a5c;
        margin-bottom: 10px;
        line-height: 1.4;
    }

    .benefit-card-desc {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 12px;
        line-height: 1.5;
    }

    .benefit-card-amount {
        font-size: 0.95rem;
        color: #FB974E;
        font-weight: 600;
        margin-top: auto;
        padding-top: 10px;
        border-top: 1px solid #eee;
    }

    /* Loading State */
    .benefits-loading {
        text-align: center;
        padding: 60px 20px;
    }

    .benefits-loading .spinner-border {
        width: 3rem;
        height: 3rem;
    }

    /* Empty State */
    .benefits-empty {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    /* Note */
    .royal-note {
        max-width: 900px;
        margin: 40px auto 0;
        padding: 20px;
        background: #e8f4fd;
        border-radius: 10px;
        border-left: 4px solid #3182ce;
        font-size: 0.9rem;
        color: #4a5568;
        line-height: 1.6;
        text-align: center;
    }

    .royal-note i {
        color: #3182ce;
        margin-right: 10px;
    }
</style>

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