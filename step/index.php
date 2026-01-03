<!DOCTYPE html>
<html lang="th">
<?php include_once('../config/head.php'); ?>

<!-- Steps Page Styles -->
<link rel="stylesheet" href="../assets/css/steps.css">

<body>
    <div class="wrapper">
        <?php include_once('../config/header.php'); ?>

        <section class="team-layout1 pb-80 pt-80">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-6 offset-lg-3">
                        <div class="heading text-center mb-60">
                            <h3 class="heading__title">ขั้นตอนการให้บริการ</h3>
                            <p class="heading__desc">ขั้นตอนการบริจาคเงินออนไลน์ผ่านระบบ eDonation</p>
                        </div>
                    </div>
                </div>

                <div class="steps-section">
                    <div class="steps-container">
                        <!-- Step 1 -->
                        <div class="step-item">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h3>เลือกโครงการที่ต้องการบริจาค</h3>
                                <p>เข้าสู่หน้าแรกของเว็บไซต์ และเลือกโครงการที่ท่านต้องการร่วมบริจาค หรือกดปุ่ม
                                    "ร่วมบริจาค" เพื่อดูรายละเอียดโครงการ</p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="step-item">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h3>กรอกข้อมูลการบริจาค</h3>
                                <p>ระบุจำนวนเงินที่ต้องการบริจาค เลือกประเภทผู้บริจาค และกรอกเบอร์โทรศัพท์
                                    หากต้องการใบเสร็จรับเงินกรุณากรอกข้อมูลชื่อ-นามสกุล และเลขบัตรประชาชนให้ครบถ้วน</p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="step-item">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h3>ชำระเงินผ่าน QR Code</h3>
                                <p>สแกน QR Code ที่แสดงบนหน้าจอด้วยแอปพลิเคชันธนาคารของท่าน
                                    และทำการชำระเงินตามจำนวนที่ระบุ</p>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="step-item">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h3>รอการยืนยันการชำระเงิน</h3>
                                <p>ระบบจะตรวจสอบและยืนยันการชำระเงินโดยอัตโนมัติ เมื่อชำระเงินสำเร็จ หน้าจอจะแสดงข้อความ
                                    "ชำระเงินสำเร็จ"</p>
                            </div>
                        </div>

                        <!-- Step 5 -->
                        <div class="step-item">
                            <div class="step-number">5</div>
                            <div class="step-content">
                                <h3>รับใบเสร็จรับเงิน</h3>
                                <p>ท่านสามารถดาวน์โหลดใบเสร็จรับเงินได้ทันทีหลังชำระเงินสำเร็จ
                                    หรือเข้าไปค้นหาใบเสร็จได้ที่เมนู "ค้นหาใบเสร็จ" โดยใช้เลขบัตรประชาชน</p>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="info-box">
                            <h4>หมายเหตุ</h4>
                            <ul>
                                <li>ใบเสร็จรับเงินสามารถนำไปลดหย่อนภาษีได้ 2 เท่า ตามประกาศกรมสรรพากร</li>
                                <li>หากมีปัญหาในการชำระเงิน กรุณาติดต่อเจ้าหน้าที่ โทร. 053-949075, 053-949127</li>
                                <li>เวลาทำการ: วันจันทร์ - วันศุกร์ เวลา 08:30 น. - 16:30 น.</li>
                            </ul>
                        </div>

                        <!-- CTA -->
                        <div class="cta-section">
                            <a href="../donat/" class="btn btn__primary btn__rounded">
                                <span>ร่วมบริจาค</span>
                                <i class="icon-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php include_once('../config/footer.php'); ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/main.js"></script>
</body>

</html>