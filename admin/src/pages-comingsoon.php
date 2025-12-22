<?php include 'partials/main.php' ?>

<head>
    <?php $title = "Coming Soon";
    include 'partials/title-meta.php' ?>

    <?php include 'partials/head-css.php' ?>
</head>

<body class="authentication-bg">
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card auth-card text-center">
                        <div class="card-body">
                            <div class="mx-auto text-center auth-logo my-5">
                                <a href="index.php" class="logo-dark">
                                    <img src="assets/images/logo-sm.png" height="30" class="me-1" alt="logo sm" />
                                    <img src="assets/images/logo-dark.png" height="24" alt="logo dark" />
                                </a>

                                <a href="index.php" class="logo-light">
                                    <img src="assets/images/logo-sm.png" height="30" class="me-1" alt="logo sm" />
                                    <img src="assets/images/logo-light.png" height="24" alt="logo light" />
                                </a>
                            </div>

                            <h2 class="fw-semibold">
                                We Are Launching Soon...
                            </h2>
                            <p class="lead mt-3 w-75 mx-auto pb-4 fst-italic">
                                Exciting news is on the horizon! We're
                                thrilled to announce that something
                                incredible is coming your way very soon. Our
                                team has been hard at work behind the
                                scenes, crafting something special just for
                                you.
                            </p>

                            <div class="row my-5">
                                <div class="col">
                                    <h3 id="days" class="fw-bold fs-60">
                                        00
                                    </h3>
                                    <p class="text-uppercase fw-semibold">
                                        Days
                                    </p>
                                </div>
                                <div class="col">
                                    <h3 id="hours" class="fw-bold fs-60">
                                        00
                                    </h3>
                                    <p class="text-uppercase fw-semibold">
                                        Hours
                                    </p>
                                </div>
                                <div class="col">
                                    <h3 id="minutes" class="fw-bold fs-60">
                                        00
                                    </h3>
                                    <p class="text-uppercase fw-semibold">
                                        Minutes
                                    </p>
                                </div>
                                <div class="col">
                                    <h3 id="seconds" class="fw-bold fs-60">
                                        00
                                    </h3>
                                    <p class="text-uppercase fw-semibold">
                                        Seconds
                                    </p>
                                </div>
                            </div>

                            <a href="pages-contact-us.php" class="btn btn-success mb-5">Contact Us</a>
                        </div>
                    </div>
                </div>
                <!-- end col -->
            </div>
            <!-- end row -->
        </div>
    </div>

    <?php include 'partials/vendor-scripts.php' ?>

    <!-- Page Js -->
    <script src="assets/js/pages/coming-soon.js"></script>
</body>

</html>