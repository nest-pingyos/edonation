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
                        <img src="<?= BASE_PATH ?>/assets/images/logo/logo.svg" alt="NurseCMU" class="footer-logo">
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
                        <p><i class="fas fa-envelope"></i> <a
                                href="mailto:donate@nurse.cmu.ac.th">donate@nurse.cmu.ac.th</a></p>
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

<!-- Footer Styles -->
<link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/footer.css">