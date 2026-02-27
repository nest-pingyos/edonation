<?php
// Include session service (this handles session_start and session_name)
require_once __DIR__ . '/services/session.php';

// Redirect if already logged in using unified check
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Get error message
$error = $_SESSION['auth_error'] ?? $_GET['error'] ?? null;
unset($_SESSION['auth_error']);

// Use constants from config.php (already included via session.php)
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
$apiBase = defined('API_BASE_PATH') ? API_BASE_PATH : '/api';
$apiBaseV1 = $apiBase . '/v1';
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ | eDonation Admin</title>
    <link rel="shortcut icon" href="<?= $basePath ?>/assets/images/favicon/favicon.png" type="image/png">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>

    <style>
        :root {
            --primary: #7c3aed;
            --primary-dark: #6d28d9;
            --primary-light: #a78bfa;
            --secondary: #4f46e5;
            --bg-gradient: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            --card-bg: rgba(255, 255, 255, 0.9);
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Prompt', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            overflow: hidden;
            position: relative;
        }

        /* Animated Background Blobs */
        .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.2) 0%, rgba(79, 70, 229, 0.2) 100%);
            filter: blur(80px);
            border-radius: 50%;
            z-index: -1;
            animation: move 20s infinite alternate;
        }

        .blob-1 { top: -100px; right: -100px; animation-delay: 0s; }
        .blob-2 { bottom: -100px; left: -100px; animation-delay: -5s; }

        @keyframes move {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(100px, 100px) scale(1.1); }
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
            padding: 24px;
            z-index: 10;
        }

        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 24px;
            padding: 48px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-container {
            width: 72px;
            height: 72px;
            background: white;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .logo-container img {
            width: 48px;
            height: 48px;
        }

        .logo-text {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.5px;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 15px;
            margin-top: 8px;
        }

        .alert {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #e11d48;
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }

        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }

        .btn-cmu {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            padding: 16px 28px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            border-radius: 14px;
            font-family: inherit;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 15px -3px rgba(124, 58, 237, 0.3);
        }

        .btn-cmu:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(124, 58, 237, 0.4);
            filter: brightness(1.1);
        }

        .btn-cmu:active {
            transform: translateY(0);
        }

        .btn-cmu:disabled {
            background: #cbd5e1;
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
        }

        .btn-cmu iconify-icon {
            font-size: 24px;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .footer-text {
            text-align: center;
            margin-top: 32px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .cmu-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 24px;
            opacity: 0.7;
        }

        .cmu-brand img {
            height: 24px;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 32px 24px;
            }
        }
    </style>
</head>

<body>
    <!-- Background Blobs -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="logo-section">
                <div class="logo-container">
                    <img src="<?= $basePath ?>/assets/images/logo/logo.svg" alt="eDonation"
                        onerror="this.src='https://api.iconify.design/solar:heart-bold-duotone.svg?color=%237c3aed'">
                </div>
                <h1 class="logo-text">eDonation</h1>
                <p class="subtitle">ระบบจัดการการบริจาค คณะพยาบาลศาสตร์ มช.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert">
                    <iconify-icon icon="solar:danger-circle-bold-duotone"></iconify-icon>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <button type="button" id="btnLogin" class="btn-cmu" onclick="loginWithCmu()">
                <iconify-icon id="btnIcon" icon="solar:user-circle-bold-duotone"></iconify-icon>
                <span id="btnText">Sign in with CMU Account</span>
            </button>

            <div class="cmu-brand">
                <iconify-icon icon="solar:shield-check-bold-duotone" style="color: var(--primary);"></iconify-icon>
                <span>Authenticated by CMU IT Account</span>
            </div>
        </div>

        <p class="footer-text">
            © <?= date('Y') ?> Faculty of Nursing, Chiang Mai University<br>
            <span style="font-size: 11px; opacity: 0.8;">Admin Portal v3.0</span>
        </p>
    </div>

    <script>
        const API_BASE = '<?= $apiBaseV1 ?>';

        async function loginWithCmu() {
            const btn = document.getElementById('btnLogin');
            const text = document.getElementById('btnText');
            const icon = document.getElementById('btnIcon');

            btn.disabled = true;
            icon.style.display = 'none';
            text.innerHTML = '<span class="spinner"></span> กำลังเชื่อมต่อ...';

            try {
                const res = await fetch(`${API_BASE}/auth/oauth/login`);
                if (!res.ok) throw new Error('Network response was not ok');
                
                const data = await res.json();

                if (data.success && data.data?.auth_url) {
                    // Pre-redirect delay for smooth transition
                    setTimeout(() => {
                        window.location.href = data.data.auth_url;
                    }, 500);
                } else {
                    throw new Error(data.error?.message || 'การเชื่อมต่อล้มเหลว กรุณาลองใหม่');
                }
            } catch (e) {
                btn.disabled = false;
                icon.style.display = 'block';
                text.textContent = 'Sign in with CMU Account';
                
                // Show error as alert
                const alertContainer = document.createElement('div');
                alertContainer.className = 'alert';
                alertContainer.innerHTML = `
                    <iconify-icon icon="solar:danger-circle-bold-duotone"></iconify-icon>
                    <span>${e.message}</span>
                `;
                
                const logoSection = document.querySelector('.logo-section');
                const existingAlert = document.querySelector('.alert');
                if (existingAlert) existingAlert.remove();
                logoSection.after(alertContainer);
            }
        }
    </script>
</body>

</html>