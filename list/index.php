<!DOCTYPE html>
<html lang="en">

<?php
include_once('../config/head.php');
?>

<body>
    <div class="wrapper">
        <?php
        include_once('../config/header.php');
        ?>
        <section>
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <form class="contact-panel__form" method="post">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h4 class="contact-panel__title">ค้นหาใบเสร็จ</h4>
                                    <p class="contact-panel__desc mb-30">กรอกข้อเพื่อค้นหาใบเสร็จรับเงิน เลขบัตรประชาชน หรือ เลขที่ใบเสร็จที่ต้องการค้นหา</p>
                                </div>
                                <div class="col-sm-6 col-md-4 col-lg-2">
                                    <div class="form-group">
                                        <select class="form-control" id="yearSelect" name="year">
                                            <option value="2569" selected>2569</option>
                                            <option value="2568">2568</option>
                                            <option value="2567">2567</option>
                                            <option value="2566">2566</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-8 col-lg-10">
                                    <div class="form-group">
                                        <i class="icon-news form-group-icon"></i>
                                        <input type="text" class="form-control" placeholder="กรอกข้อเพื่อค้นหาใบเสร็จรับเงิน ชื่อ-สกุล หรือ เลขบัตรประชาชนที่ หรือ เลขที่ใบเสร็จที่ต้องการค้นหา" id="keyword" name="keyword">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn__secondary btn__rounded btn__block btn__xhight mt-10">
                                        <span>ค้นหาใบเสร็จ</span> <i class="icon-arrow-right"></i>
                                    </button>
                                    <div class="contact-result"></div>
                                </div>
                            </div>
                        </form>
                        <?php include_once('search.php'); ?>
                    </div>
                </div>
            </div>
        </section>
        <?php include_once('../config/footer.php'); ?>
        <button id="scrollTopBtn"><i class="fas fa-long-arrow-alt-up"></i></button>
    </div><!-- /.wrapper -->

    <script src="../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>

</body>

</html>