<?php include 'partials/main.php' ?>

<head>
    <?php $title = "Apex Polar Area Charts";
    include 'partials/title-meta.php' ?>

    <?php include 'partials/head-css.php' ?>
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">
        <?php include 'partials/menu.php' ?>

        <!-- ==================================================== -->
        <!-- Start Page Content here -->
        <!-- ==================================================== -->
        <div class="page-content">
            <!-- Start Content-->
            <div class="container-xxl">

                <?php
                $subTitle = "Charts";
                $pageTitle = "Polar Area Charts";
                include 'partials/page-title.php' ?>

                <div class="row">
                    <div class="col-xl-9">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title anchor mb-1" id="overview">
                                    Overview
                                </h5>

                                <p class="mb-0">
                                    <span class="fw-medium">Find the JS file for the following
                                        chart at:</span>
                                    <code>
                                        ../src/assets/js/components/apexchart-polar-area.js</code>
                                </p>
                            </div>
                            <!-- end card-body -->
                        </div>
                        <!-- end card -->

                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-3 anchor" id="basic">
                                    Basic Polar Area Chart
                                </h4>
                                <div dir="ltr">
                                    <div id="basic-polar-area" class="apex-charts"></div>
                                </div>
                            </div>
                            <!-- end card body-->
                        </div>
                        <!-- end card -->

                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-3 anchor" id="monochrome">
                                    Monochrome Polar Area
                                </h4>
                                <div dir="ltr">
                                    <div id="monochrome-polar-area" class="apex-charts"></div>
                                </div>
                            </div>
                            <!-- end card body-->
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->

                    <div class="col-xl-3">
                        <div class="card docs-nav">
                            <ul class="nav bg-transparent flex-column">
                                <li class="nav-item">
                                    <a href="#overview" class="nav-link">Overview</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#basic" class="nav-link">Basic Polar Area Chart</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#monochrome" class="nav-link">Monochrome Polar Area</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- end row -->
            </div>
            <!-- container -->

            <?php include  'partials/footer.php' ?>
        </div>
        <!-- ==================================================== -->
        <!-- End Page content -->
        <!-- ==================================================== -->
    </div>
    <!-- END wrapper -->

    <?php include 'partials/vendor-scripts.php' ?>

    <!-- Apex Chart Polar Area Demo js -->
    <script src="assets/js/components/apexchart-polar-area.js"></script>
</body>

</html>