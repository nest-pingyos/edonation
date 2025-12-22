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
                    <?php
                    require_once '../config/connect.php';
                    $stmt = $pdo->prepare("SELECT * FROM service_donat");
                    $stmt->execute();
                    $result = $stmt->fetchAll();

                    foreach ($result as $t1) {
                        $imagePath = !empty($t1['img_file']) ? "../assets/images/products/" . $t1['img_file'] : "../assets/images/products/default.jpg";
                    ?>
                        <div class="col-sm-6 col-md-6 col-lg-4">
                            <div class="post-item">
                                <div class="post__img">
                                    <a>
                                        <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($t1['name']) ?>" loading="lazy">
                                    </a>
                                </div>
                                <div class="post__body">
                                    <div class="post__meta-cat">
                                        <a><?= htmlspecialchars($t1['amount']); ?></a>
                                    </div>
                                    <div class="post__meta d-flex">
                                        <span class="post__meta-date">Jan 30, 2022</span>
                                        <a class="post__meta-author" href="#">คงเหลือ <?= htmlspecialchars($t1['quantity']); ?> ชิ้น</a>
                                    </div>
                                    <h4 class="post__title"><a href="#"><?= htmlspecialchars($t1['name']); ?></a></h4>

                                    <p class="post__desc">
                                        <?= nl2br(htmlspecialchars(str_replace('-', "\n", $t1['desc']))); ?>
                                    </p>
                                    <a href="#" class="btn btn__secondary btn__link btn__rounded" onclick="checkLogin(event)">
                                        <span>เลือก</span>
                                        <i class="icon-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                </div>

            </div>
        </section>

        <?php
        include_once('../config/footer.php');
        ?>

        <button id="scrollTopBtn"><i class="fas fa-long-arrow-alt-up"></i></button>
    </div>

    <script src="../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function checkLogin(event) {
            event.preventDefault(); // ป้องกันลิงก์ทำงานทันที

            <?php if (!isset($_SESSION['user_login'])): ?>
                // ถ้ายังไม่ได้ล็อกอิน
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณาเข้าสู่ระบบ!',
                    text: 'คุณต้องเข้าสู่ระบบก่อนทำรายการ',
                    confirmButtonColor: '#ffaa00',
                    confirmButtonText: 'เข้าสู่ระบบ'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '../oauth/'; // ไปยังหน้าล็อกอิน
                    }
                });
            <?php else: ?>
                window.location.href = '../error/';
            <?php endif; ?>
        }
    </script>
</body>

</html>