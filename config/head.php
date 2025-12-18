<?php
// Default SEO Values
$siteName = "NurseCMU e-Donation";
$currentTitle = isset($pageTitle) ? $pageTitle . " | " . $siteName : $siteName;
$currentDesc = isset($pageDesc) ? $pageDesc : "ระบบบริจาคออนไลน์ คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่ สนับสนุนการศึกษาและพัฒนาคุณภาพชีวิต";

// Fix protocol and domain detection for absolute URLs
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$baseImgUrl = "$protocol://$host/appdev/edonation/assets/images";
$currentImage = isset($pageImage) ? $pageImage : "$baseImgUrl/logo/logo-nurse.png";
$currentUrl = "$protocol://$host" . $_SERVER['REQUEST_URI'];
?>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    
    <!-- Primary Meta Tags -->
    <title><?php echo $currentTitle; ?></title>
    <meta name="title" content="<?php echo $currentTitle; ?>">
    <meta name="description" content="<?php echo $currentDesc; ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $currentUrl; ?>">
    <meta property="og:title" content="<?php echo $currentTitle; ?>">
    <meta property="og:description" content="<?php echo $currentDesc; ?>">
    <meta property="og:image" content="<?php echo $currentImage; ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo $currentUrl; ?>">
    <meta property="twitter:title" content="<?php echo $currentTitle; ?>">
    <meta property="twitter:description" content="<?php echo $currentDesc; ?>">
    <meta property="twitter:image" content="<?php echo $currentImage; ?>">

    <!-- Favicon & Mobile Icons -->
    <link rel="icon" href="../assets/images/favicon/favicon.png">
    <link rel="apple-touch-icon" href="../assets/images/favicon/favicon.png">
    <link rel="icon" sizes="32x32" href="../assets/images/favicon/favicon.png">
    <link rel="icon" sizes="192x192" href="../assets/images/favicon/favicon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.3/css/all.css">
    <link rel="stylesheet" href="../assets/css/libraries.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/custom.css"> <!-- Custom Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">

    
    <style>
        .lang-switch {
            display: flex;
            align-items: center;
            background: #f5f5f5;
            border-radius: 20px;
            padding: 5px 12px;
        }
        .lang-switch__btn {
            font-size: 13px;
            font-weight: 600;
            color: #888;
            text-decoration: none;
            padding: 2px 6px;
            transition: color 0.2s;
        }
        .lang-switch__btn:hover {
            color: #333;
        }
        .lang-switch__btn.active {
            color: #00a651;
        }
        .lang-switch__divider {
            color: #ccc;
            font-size: 12px;
            margin: 0 2px;
        }
    </style>
</head>