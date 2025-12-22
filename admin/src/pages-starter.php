<?php include 'partials/main.php' ?>

<head>
        <?php $title = "Welcome";
        include 'partials/title-meta.php' ?>

    <?php include 'partials/head-css.php' ?>
</head>

<body>
    <!-- START Wrapper -->
    <div class="wrapper">
        <?php include 'partials/menu.php' ?>

        <!-- ==================================================== -->
        <!-- Start right Content here -->
        <!-- ==================================================== -->
        <div class="page-content">
            <!-- Start Container -->
            <div class="container-xxl">

                <?php
                $subTitle = "Pages";
                $pageTitle = "Welcome";
                include 'partials/page-title.php' ?>

                <!-- Start here.... -->
            </div>
            <!-- End Container -->

            <?php include  'partials/footer.php' ?>
        </div>
        <!-- ==================================================== -->
        <!-- End Page Content -->
        <!-- ==================================================== -->
    </div>
    <!-- END Wrapper -->

    <?php include 'partials/vendor-scripts.php' ?>
</body>

</html>