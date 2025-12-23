<!DOCTYPE html>
<html lang="th">

<?php include_once('../config/head.php'); ?>

<style>
/* Benefits Section Styles */
.benefits-section {
    padding: 80px 0;
    background: #ffffff;
    min-height: 100vh;
}

.benefits-header {
    text-align: center;
    margin-bottom: 60px;
}

.benefits-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1a3a5c;
    margin-bottom: 15px;
}

.benefits-header p {
    font-size: 1.1rem;
    color: #6c757d;
    max-width: 600px;
    margin: 0 auto;
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
}

/* Benefit Card */
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
    background: #ffffff;
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
    font-size: 1rem;
    font-weight: 600;
    color: #1a3a5c;
    margin-bottom: 10px;
    line-height: 1.4;
}

.benefit-card-amount {
    font-size: 0.9rem;
    color: #FB974E;
    font-weight: 500;
    margin-top: auto;
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
</style>

<body>
    <div class="wrapper">
        <?php include_once('../config/header.php'); ?>

        <section class="benefits-section">
            <div class="container">
                <!-- Header -->
                <div class="benefits-header">
                    <h1>ระดับผู้มีอุปการคุณ</h1>
                    <p>ขอบคุณสำหรับความกรุณาและน้ำใจของท่านผู้มีอุปการคุณทุกท่าน ที่ร่วมสนับสนุนคณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่</p>
                </div>

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
    // Get API_BASE from meta tag (set by PHP head.php)
    const API_BASE = document.querySelector('meta[name="api-base"]')?.content || '/edonation/api/v1';

    document.addEventListener('DOMContentLoaded', function() {
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
        benefits.forEach((item, index) => {
            html += `
                <div class="benefit-card">
                    <div class="benefit-card-image">
                        <img src="${item.image_url}" alt="${item.name}" 
                             onerror="this.src='../assets/images/benefits/default.jpg'">
                    </div>
                    <div class="benefit-card-content">
                        <h3 class="benefit-card-title">${escapeHtml(item.name.replace(/ขั้นที่\s*\d+\s*/, ''))}</h3>
                        <div class="benefit-card-amount">
                            ${escapeHtml(item.amount)}
                        </div>
                    </div>
                </div>
            `;
        });

        benefitsGrid.innerHTML = html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    </script>
</body>
</html>
