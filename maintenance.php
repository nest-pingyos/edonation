<?php
/**
 * Simple System Maintenance Page
 * 
 * @author Antigravity AI
 * @version 1.1.0
 */

// Set header to 503 Service Unavailable for SEO
header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
header('Retry-After: 3600');

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ปิดปรับปรุงระบบ | eDonation</title>

    <!-- Google Fonts: Sarabun for Thai compatibility and clean look -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #ffffff;
            --text-main: #1d1d1f;
            --text-muted: #86868b;
            --accent: #0066cc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Sarabun', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 500px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon-box {
            margin-bottom: 30px;
        }

        .icon-box img {
            width: 120px;
            height: auto;
            opacity: 0.9;
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }

        p {
            font-size: 1rem;
            color: var(--text-muted);
            line-height: 1.6;
            font-weight: 400;
        }

        .divider {
            width: 40px;
            height: 2px;
            background: #e5e5e5;
            margin: 30px auto;
        }

        .footer-text {
            font-size: 0.85rem;
            color: #b1b1b3;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="icon-box">
            <!-- Using the maintenance.png in the root directory as per user's previous manual change -->
            <img src="maintenance.png" alt="Maintenance Icon">
        </div>

        <h1>ปิดปรับปรุงระบบชั่วคราว</h1>
        <p>
            ขออภัยในความไม่สะดวก ขณะนี้ระบบ eDonation กำลังอยู่ระหว่างการเพิ่มประสิทธิภาพและปรับปรุงความปลอดภัย
            เราจะกลับมาเปิดให้บริการใหม่อีกครั้งในเร็วๆ นี้
        </p>

        <div class="divider"></div>

        <div class="footer-text">
            ©
            <?= date('Y') ?> eDonation System
        </div>
    </div>

</body>

</html>