<?php
/**
 * System Maintenance Page
 * 
 * @author Antigravity AI
 * @version 1.0.0
 */

// Set header to 503 Service Unavailable for SEO
header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
header('Retry-After: 3600'); // 1 hour

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ปิดปรับปรุงระบบชั่วคราว | eDonation System</title>

    <!-- SEO Meta Tags -->
    <meta name="description"
        content="ขออภัย ระบบ eDonation กำลังอยู่ระหว่างการฟื้นฟูและพัฒนาเพื่อประสิทธิภาพที่ดียิ่งขึ้น เราจะกลับมาให้บริการในเร็วๆ นี้">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Sarabun:wght@200;400;600&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --secondary: #a855f7;
            --dark: #0f172a;
            --light: #f8fafc;
            --text-main: #334155;
            --text-muted: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', 'Sarabun', sans-serif;
            background-color: var(--dark);
            color: var(--light);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Abstract Background Particles */
        .bg-blobs {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            filter: blur(80px);
            opacity: 0.4;
        }

        .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            background: var(--primary);
            border-radius: 50%;
            animation: move 20s infinite alternate;
        }

        .blob-2 {
            background: var(--secondary);
            right: 0;
            bottom: 0;
            animation: move 25s infinite alternate-reverse;
        }

        @keyframes move {
            from {
                transform: translate(-10%, -10%) scale(1);
            }

            to {
                transform: translate(20%, 20%) scale(1.2);
            }
        }

        .container {
            max-width: 800px;
            text-align: center;
            padding: 40px;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .illustration {
            width: 320px;
            height: auto;
            margin-bottom: 30px;
            filter: drop-shadow(0 0 20px rgba(99, 102, 241, 0.3));
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-15px);
            }
        }

        h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 20px;
            background: linear-gradient(to right, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
        }

        p {
            font-size: 1.1rem;
            color: #94a3b8;
            max-width: 600px;
            margin: 0 auto 30px auto;
            line-height: 1.8;
            font-weight: 300;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 100px;
            color: var(--primary-light);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .pulse {
            width: 8px;
            height: 8px;
            background: var(--primary-light);
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 rgba(99, 102, 241, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(99, 102, 241, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(99, 102, 241, 0);
            }
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 40px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 30px;
        }

        .info-card h4 {
            font-size: 0.8rem;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .info-card span {
            font-weight: 600;
            color: #f1f5f9;
        }

        .contact-btn {
            display: inline-block;
            margin-top: 40px;
            padding: 12px 30px;
            background: white;
            color: var(--dark);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .contact-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .illustration {
                width: 240px;
            }

            .container {
                padding: 30px 20px;
                margin: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="bg-blobs">
        <div class="blob"></div>
        <div class="blob blob-2"></div>
    </div>

    <div class="container">
        <div class="status-badge">
            <span class="pulse"></span>
            Maintenance in Progress
        </div>

        <img src="assets/images/maintenance.png" alt="Maintenance" class="illustration">

        <h1>ปิดปรับปรุงระบบชั่วคราว</h1>
        <p>
            ขออภัยในความไม่สะดวก ขณะนี้ระบบ eDonation กำลังอยู่ระหว่างการเพิ่มประสิทธิภาพและปรับปรุงความปลอดภัย
            เราจะกลับมาเปิดให้บริการใหม่อีกครั้งในเร็วๆ นี้
        </p>

        <div class="info-grid">
            <div class="info-card">
                <h4>Status</h4>
                <span>UPGRADING</span>
            </div>
            <div class="info-card">
                <h4>Expected Return</h4>
                <span>Today, 14:00 PM</span>
            </div>
            <div class="info-card">
                <h4>Need Help?</h4>
                <span>admin@edonation.com</span>
            </div>
        </div>

        <a href="mailto:admin@edonation.com" class="contact-btn">ติดต่อเจ้าหน้าที่</a>
    </div>

    <script>
        // Optional: Auto-refresh check or countdown logic could go here
    </script>
</body>

</html>