<?php
// Get current language from URL
$currentLang = isset($_GET['lang']) ? $_GET['lang'] : 'th';
$langParam = $currentLang !== 'th' ? '?lang=' . $currentLang : '';
?>

<footer class="footer footer-modern">
    <div class="footer-main">
        <div class="container">
            <div class="row">
                <!-- Logo & Description -->
                <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                    <div class="footer-brand">
                        <img src="../assets/images/logo/logo-nurse.png" alt="NurseCMU" class="footer-logo">
                        <p class="footer-tagline">ร่วมสร้างความเปลี่ยนแปลง<br>ทุกการให้ ยิ่งใหญ่เสมอ</p>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                    <h6 class="footer-title">ลิงก์ด่วน</h6>
                    <div class="footer-links-grid">
                        <a href="../home/<?= $langParam ?>">หน้าหลัก</a>
                        <a href="../step/<?= $langParam ?>">ขั้นตอนการบริการ</a>
                        <a href="../benefits/<?= $langParam ?>">สิทธิประโยชน์</a>
                        <a href="../receipts/<?= $langParam ?>">ค้นหาใบเสร็จ</a>
                        <a href="../contact/<?= $langParam ?>">ติดต่อเรา</a>
                    </div>
                </div>
                
                <!-- Contact -->
                <div class="col-lg-4 col-md-12">
                    <h6 class="footer-title">ติดต่อเรา</h6>
                    <div class="footer-contact">
                        <p><i class="fas fa-map-marker-alt"></i> คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่</p>
                        <p><i class="fas fa-phone"></i> <a href="tel:053-949075">053-949075</a></p>
                        <p><i class="fas fa-envelope"></i> <a href="mailto:donate@nurse.cmu.ac.th">donate@nurse.cmu.ac.th</a></p>
                    </div>
                    <div class="footer-lang">
                        <a href="?lang=th" class="<?= $currentLang === 'th' ? 'active' : '' ?>">TH</a>
                        <span>|</span>
                        <a href="?lang=en" class="<?= $currentLang === 'en' ? 'active' : '' ?>">EN</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <p>&copy; 2024 Faculty of Nursing, Chiang Mai University. All rights reserved.</p>
        </div>
    </div>
</footer>

<style>
.footer-modern {
    background: linear-gradient(135deg, #1b3a5f 0%, #0d2137 100%);
    color: #fff;
}

.footer-main {
    padding: 60px 0 40px;
}

.footer-logo {
    height: 50px;
    margin-bottom: 15px;
}

.footer-tagline {
    color: rgba(255,255,255,0.7);
    font-size: 14px;
    line-height: 1.6;
}

.footer-title {
    color: #ff9800;
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 20px;
}

.footer-links-grid {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.footer-links-grid a {
    color: rgba(255,255,255,0.7);
    text-decoration: none;
    font-size: 14px;
    transition: color 0.2s;
}

.footer-links-grid a:hover {
    color: #fff;
}

.footer-contact p {
    color: rgba(255,255,255,0.7);
    font-size: 14px;
    margin-bottom: 10px;
}

.footer-contact i {
    color: #ff9800;
    width: 20px;
    margin-right: 8px;
}

.footer-contact a {
    color: rgba(255,255,255,0.7);
    text-decoration: none;
}

.footer-contact a:hover {
    color: #fff;
}

.footer-lang {
    margin-top: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.footer-lang a {
    color: rgba(255,255,255,0.5);
    text-decoration: none;
    font-weight: 600;
    font-size: 13px;
}

.footer-lang a.active {
    color: #ff9800;
}

.footer-lang span {
    color: rgba(255,255,255,0.3);
}

.footer-bottom {
    background: rgba(0,0,0,0.2);
    padding: 20px 0;
    text-align: center;
}

.footer-bottom p {
    color: rgba(255,255,255,0.5);
    font-size: 13px;
    margin: 0;
}
</style>