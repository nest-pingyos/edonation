<!DOCTYPE html>
<html lang="th">
<?php include_once('../config/head.php'); ?>

<style>
.steps-section {
    padding: 80px 0;
    background: #ffffff;
}

.steps-container {
    max-width: 900px;
    margin: 0 auto;
}

/* Step Item */
.step-item {
    display: flex;
    gap: 30px;
    position: relative;
    padding-bottom: 40px;
}

.step-item:last-child {
    padding-bottom: 0;
}

/* Step Number */
.step-number {
    width: 60px;
    height: 60px;
    min-width: 60px;
    background: linear-gradient(135deg, #00a651, #00c853);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    color: #ffffff;
    position: relative;
    z-index: 2;
}

/* Connector Line */
.step-item:not(:last-child) .step-number::after {
    content: '';
    position: absolute;
    top: 60px;
    left: 50%;
    transform: translateX(-50%);
    width: 3px;
    height: calc(100% + 40px);
    background: linear-gradient(to bottom, #00a651, #e9ecef);
    z-index: 1;
}

/* Step Content */
.step-content {
    flex: 1;
    padding-top: 10px;
}

.step-content h3 {
    font-size: 1.3rem;
    font-weight: 600;
    color: #1a3a5c;
    margin-bottom: 10px;
}

.step-content p {
    color: #6c757d;
    line-height: 1.7;
    margin-bottom: 0;
}

/* Info Box */
.info-box {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 25px;
    margin-top: 60px;
    border-left: 4px solid #00a651;
}

.info-box h4 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1a3a5c;
    margin-bottom: 15px;
}

.info-box ul {
    margin: 0;
    padding-left: 20px;
    color: #6c757d;
}

.info-box li {
    margin-bottom: 8px;
    line-height: 1.6;
}

.info-box li:last-child {
    margin-bottom: 0;
}

/* CTA Button */
.cta-section {
    text-align: center;
    margin-top: 50px;
}

.cta-section .btn {
    padding: 15px 40px;
    font-size: 1.1rem;
}

@media (max-width: 576px) {
    .step-item {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .step-number {
        margin: 0 auto;
    }
    
    .step-item:not(:last-child) .step-number::after {
        display: none;
    }
    
    .step-content {
        padding-top: 0;
    }
}
</style>

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
                                <p>เข้าสู่หน้าแรกของเว็บไซต์ และเลือกโครงการที่ท่านต้องการร่วมบริจาค หรือกดปุ่ม "ร่วมบริจาค" เพื่อดูรายละเอียดโครงการ</p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="step-item">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h3>กรอกข้อมูลการบริจาค</h3>
                                <p>ระบุจำนวนเงินที่ต้องการบริจาค เลือกประเภทผู้บริจาค และกรอกเบอร์โทรศัพท์ หากต้องการใบเสร็จรับเงินกรุณากรอกข้อมูลชื่อ-นามสกุล และเลขบัตรประชาชนให้ครบถ้วน</p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="step-item">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h3>ชำระเงินผ่าน QR Code</h3>
                                <p>สแกน QR Code ที่แสดงบนหน้าจอด้วยแอปพลิเคชันธนาคารของท่าน และทำการชำระเงินตามจำนวนที่ระบุ</p>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="step-item">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h3>รอการยืนยันการชำระเงิน</h3>
                                <p>ระบบจะตรวจสอบและยืนยันการชำระเงินโดยอัตโนมัติ เมื่อชำระเงินสำเร็จ หน้าจอจะแสดงข้อความ "ชำระเงินสำเร็จ"</p>
                            </div>
                        </div>

                        <!-- Step 5 -->
                        <div class="step-item">
                            <div class="step-number">5</div>
                            <div class="step-content">
                                <h3>รับใบเสร็จรับเงิน</h3>
                                <p>ท่านสามารถดาวน์โหลดใบเสร็จรับเงินได้ทันทีหลังชำระเงินสำเร็จ หรือเข้าไปค้นหาใบเสร็จได้ที่เมนู "ค้นหาใบเสร็จ" โดยใช้เลขบัตรประชาชน</p>
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

    <script src="../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
