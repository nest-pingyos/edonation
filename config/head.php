<?php
// Load environment configuration
require_once __DIR__ . '/env.php';

// Default SEO Values
$siteName = "NurseCMU e-Donation";
$currentTitle = isset($pageTitle) ? $pageTitle . " | " . $siteName : $siteName;
$currentDesc = isset($pageDesc) ? $pageDesc : "ระบบบริจาคออนไลน์ คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่ สนับสนุนการศึกษาและพัฒนาคุณภาพชีวิต";
$currentKeywords = isset($pageKeywords) ? $pageKeywords : "บริจาค, พยาบาล, มหาวิทยาลัยเชียงใหม่, eDonation, ทุนการศึกษา, คณะพยาบาลศาสตร์";

// Fix protocol and domain detection for absolute URLs
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// Use BASE_PATH from config instead of hardcoded value
$basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';
$baseUrl = "$protocol://$host" . $basePath;
$baseImgUrl = "$baseUrl/assets/images";
$currentImage = isset($pageImage) ? $pageImage : "$baseImgUrl/logo/logo.svg";
$currentUrl = "$protocol://$host" . $_SERVER['REQUEST_URI'];

// Canonical URL (remove query strings for canonical)
$canonicalUrl = isset($pageCanonical) ? $pageCanonical : strtok($currentUrl, '?');
?>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />

    <!-- Application Config for JavaScript -->
    <meta name="base-path" content="<?php echo htmlspecialchars($basePath); ?>">
    <meta name="api-base"
        content="<?php echo htmlspecialchars(defined('API_BASE') ? API_BASE : $basePath . '/api/v1'); ?>">
    <meta name="api-url"
        content="<?php echo htmlspecialchars(defined('API_BASE') ? API_BASE : $basePath . '/api/v1'); ?>">

    <!-- Primary Meta Tags -->
    <title><?php echo htmlspecialchars($currentTitle); ?></title>
    <meta name="title" content="<?php echo htmlspecialchars($currentTitle); ?>">
    <meta name="description" content="<?php echo htmlspecialchars($currentDesc); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($currentKeywords); ?>">
    <meta name="author" content="คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่">
    <meta name="robots" content="index, follow">
    <meta name="language" content="Thai">
    <meta name="revisit-after" content="7 days">

    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl); ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($currentUrl); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($currentTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($currentDesc); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($currentImage); ?>">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($siteName); ?>">
    <meta property="og:locale" content="th_TH">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo htmlspecialchars($currentUrl); ?>">
    <meta property="twitter:title" content="<?php echo htmlspecialchars($currentTitle); ?>">
    <meta property="twitter:description" content="<?php echo htmlspecialchars($currentDesc); ?>">
    <meta property="twitter:image" content="<?php echo htmlspecialchars($currentImage); ?>">

    <!-- Structured Data - Organization -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่",
        "alternateName": "Faculty of Nursing, Chiang Mai University",
        "url": "<?php echo $baseUrl; ?>",
        "logo": "<?php echo $baseImgUrl; ?>/logo/logo.svg",
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+66-53-935012",
            "contactType": "customer service",
            "areaServed": "TH",
            "availableLanguage": ["Thai", "English"]
        },
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "110/406 ถนนอินทวโรรส ตำบลสุเทพ",
            "addressLocality": "เมืองเชียงใหม่",
            "addressRegion": "เชียงใหม่",
            "postalCode": "50200",
            "addressCountry": "TH"
        }
    }
    </script>

    <!-- Favicon & Mobile Icons -->
    <link rel="icon" href="<?= BASE_PATH ?>/assets/images/favicon/favicon.png">
    <link rel="apple-touch-icon" href="<?= BASE_PATH ?>/assets/images/favicon/favicon.png">
    <link rel="icon" sizes="32x32" href="<?= BASE_PATH ?>/assets/images/favicon/favicon.png">
    <link rel="icon" sizes="192x192" href="<?= BASE_PATH ?>/assets/images/favicon/favicon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.3/css/all.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/libraries.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/custom.css"> <!-- Custom Styles -->
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