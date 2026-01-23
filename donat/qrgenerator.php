<!DOCTYPE html>
<html lang="th">

<?php include_once('../config/head.php'); ?>
<!-- Custom Styles to match Theme -->
<!-- Custom Styles to match Theme -->
<link rel="stylesheet" href="../assets/css/qr-generator.css">

<body>
    <div class="wrapper">
        <!-- Optional: Header can be hidden if we want a pure slip look, keeping it for nav -->
        <?php include_once('../config/header.php'); ?>

        <section class="donation-confirmation-section">
            <div class="container">

                <!-- Loading -->
                <div id="loadingState">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>

                <!-- Error -->
                <div id="errorState" style="display: none;">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle mr-2"></i> <span id="errorMessage">Unknow Error</span>
                    </div>
                </div>

                <!-- Main Card -->
                <div id="qrContent" class="single-card" style="display: none;">

                    <!-- 1. Header Logo (Removed) -->

                    <!-- 2. Payment Banners -->
                    <div class="payment-banner-area">
                        <!-- Thai QR Payment Banner -->
                        <div class="thai-qr-banner" style="background: none; height: auto;">
                            <!-- Removed max-width restriction to let CSS control it (100% fill) -->
                            <img src="../assets/images/Thai_QR_Payment_Logo-01.jpg" alt="Thai QR Payment"
                                style="width: 100%; height: auto; border-radius: 4px;">
                        </div>

                        <!-- PromptPay Logo -->
                        <img src="../assets/images/PromptPay2.png" alt="PromptPay" class="promptpay-logo">
                    </div>

                    <!-- 3. QR Code -->
                    <div class="qr-area">
                        <div class="qr-frame">
                            <img id="qrImage" src="" alt="Scan to Pay">
                        </div>
                        <!-- Time or Ref Display below QR -->
                        <div class="ref-time-text" id="currentTime">00:00</div>

                        <!-- Status Badge (Waiting/Paid) -->
                        <div id="statusBox">
                            <span class="badge badge-warning font-weight-normal px-3 py-2">
                                <span class="spinner-grow spinner-grow-sm mr-1" role="status" aria-hidden="true"
                                    style="vertical-align: middle;"></span>
                                รอการชำระเงิน...
                            </span>
                        </div>
                    </div>

                    <!-- 4. Footer Bar -->
                    <div class="slip-footer">
                        <div class="footer-item">
                            <span class="footer-label">หมายเลขอ้างอิง</span>
                            <span class="footer-value" id="refNumber">-</span>
                        </div>
                        <div class="footer-item" style="align-items: flex-end;">
                            <span class="footer-label">ยอดชำระ</span>
                            <span class="amount-highlight"><span id="amountDisplay">-</span> บาท</span>
                        </div>
                    </div>
                </div>


                </script>
</body>

</html>