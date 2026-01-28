<?php
/**
 * Logout Handler
 * 
 * ล้าง session และ redirect กลับไปหน้า login
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include session service for logoutSession function
require_once __DIR__ . '/services/session.php';

// Destroy server-side session
logoutSession();

// Get base path for redirect
require_once __DIR__ . '/config/config.php';
$basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';
$loginUrl = $basePath . '/admin/src/auth/login.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <title>ออกจากระบบ | eDonation</title>
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: #f8fafc;
        }

        .logout-box {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .logout-box h2 {
            color: #1e293b;
            margin-bottom: 16px;
        }

        .logout-box p {
            color: #64748b;
            margin-bottom: 24px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e2e8f0;
            border-top-color: #7c3aed;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="logout-box">
        <div class="spinner"></div>
        <h2>กำลังออกจากระบบ...</h2>
        <p>กรุณารอสักครู่</p>
    </div>

    <script>
        // Clear client-side storage
        localStorage.removeItem('access_token');
        localStorage.removeItem('refresh_token');
        localStorage.removeItem('user');
        sessionStorage.clear();

        // Redirect to login page after short delay
        setTimeout(function () {
            window.location.href = '<?= $loginUrl ?>';
        }, 1000);
    </script>
</body>

</html>