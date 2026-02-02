<?php include 'partials/main.php' ?>

<head>
    <?php $title = "Forbidden - 403";
    include 'partials/title-meta.php' ?>

    <?php include 'partials/head-css.php' ?>
</head>

<body class="authentication-bg">
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-12">
                    <div class="card auth-card">
                        <div class="card-body p-0">
                            <div class="row align-items-center g-0">
                                <div class="col-lg-6 d-none d-lg-inline-block border-end">
                                    <div class="auth-page-sidebar">
                                        <img src="https://cdn.dribbble.com/users/761395/screenshots/4915714/access_denied.gif"
                                            alt="403 Forbidden" class="img-fluid" />
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="p-4">
                                        <div class="mx-auto mb-4 text-center">
                                            <div class="mx-auto text-center auth-logo">
                                                <a href="index.php" class="logo-dark">
                                                    <img src="assets/images/logo-sm.png" height="30" class="me-1"
                                                        alt="logo sm" />
                                                    <img src="assets/images/logo-dark.png" height="24"
                                                        alt="logo dark" />
                                                </a>

                                                <a href="index.php" class="logo-light">
                                                    <img src="assets/images/logo-sm.png" height="30" class="me-1"
                                                        alt="logo sm" />
                                                    <img src="assets/images/logo-light.png" height="24"
                                                        alt="logo light" />
                                                </a>
                                            </div>

                                            <h1 class="mt-5 mb-3 fw-bold fs-60" style="color: #d9534f;">
                                                403
                                            </h1>
                                            <h2 class="fs-22 lh-base">
                                                Access Forbidden
                                            </h2>
                                            <p class="text-muted mt-1 mb-4">
                                                คุณไม่มีสิทธิ์เข้าถึงหน้านี้
                                                <br />
                                                กรุณาติดต่อผู้ดูแลระบบหากต้องการความช่วยเหลือ
                                            </p>

                                            <div class="text-center">
                                                <a href="index.php" class="btn btn-primary">กลับหน้าหลัก</a>
                                                <a href="login.php"
                                                    class="btn btn-outline-secondary ms-2">เข้าสู่ระบบ</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- end col -->
                            </div>
                            <!-- end row -->
                        </div>
                        <!-- end card-body -->
                    </div>
                    <!-- end card -->
                </div>
                <!-- end col -->
            </div>
            <!-- end row -->
        </div>
    </div>

    <?php include 'partials/vendor-scripts.php' ?>
</body>

</html>