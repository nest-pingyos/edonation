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
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Prompt', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
        }

        .login-wrapper {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 16px;
            padding: 48px 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
                0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .logo {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo img {
            height: 48px;
        }

        .logo-text {
            font-size: 24px;
            font-weight: 600;
            color: #1e293b;
            margin-top: 12px;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            font-size: 14px;
            margin-bottom: 32px;
        }

        .alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 24px;
        }

        .btn-cmu {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 14px 24px;
            background: #7c3aed;
            color: white;
            border: none;
            border-radius: 10px;
            font-family: inherit;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-cmu:hover {
            background: #6d28d9;
            transform: translateY(-1px);
        }

        .btn-cmu:disabled {
            background: #a78bfa;
            cursor: not-allowed;
            transform: none;
        }

        .btn-cmu svg {
            width: 20px;
            height: 20px;
        }

        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 24px 0;
            color: #94a3b8;
            font-size: 13px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span {
            padding: 0 12px;
        }

        .alt-login {
            text-align: center;
            font-size: 14px;
            color: #64748b;
        }

        .alt-login a {
            color: #7c3aed;
            text-decoration: none;
            font-weight: 500;
        }

        .alt-login a:hover {
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: #94a3b8;
        }

        <?php if (defined('APP_ENV') && APP_ENV === 'development'): ?>
            .dev-login {
                margin-top: 16px;
            }

            .btn-dev {
                width: 100%;
                padding: 12px;
                background: transparent;
                border: 1px solid #e2e8f0;
                color: #64748b;
                border-radius: 8px;
                font-family: inherit;
                font-size: 13px;
                cursor: pointer;
                transition: all 0.2s;
            }

            .btn-dev:hover {
                background: #f8fafc;
                border-color: #cbd5e1;
            }

        <?php endif; ?>
    </style>
</head>

<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="logo">
                <img src="<?= $basePath ?>/assets/images/logo/logo.svg" alt="eDonation"
                    onerror="this.style.display='none'">
                <div class="logo-text">eDonation</div>
            </div>

            <p class="subtitle">ระบบจัดการการบริจาค คณะพยาบาลศาสตร์ มช.</p>

            <?php if ($error): ?>
                <div class="alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <button type="button" id="btnLogin" class="btn-cmu" onclick="loginWithCmu()">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                </svg>
                <span id="btnText">Sign in with CMU Account</span>
            </button>

            <div class="dev-login">
                <button type="button" class="btn-dev" onclick="devLogin()">
                    Developer Login (admin / admin123)
                </button>
            </div>
        </div>
        © <?= date('Y') ?> Faculty of Nursing, Chiang Mai University
        </p>
    </div>

    <script>
        const API_BASE = '<?= $apiBaseV1 ?>';

        async function loginWithCmu() {
            const btn = document.getElementById('btnLogin');
            const text = document.getElementById('btnText');

            btn.disabled = true;
            text.innerHTML = '<span class="spinner"></span> กำลังเชื่อมต่อ...';

            try {
                const res = await fetch(`${API_BASE}/auth/oauth/login`);
                const data = await res.json();

                if (data.success && data.data?.auth_url) {
                    window.location.href = data.data.auth_url;
                } else {
                    throw new Error(data.error?.message || 'Connection failed');
                }
            } catch (e) {
                alert('เกิดข้อผิดพลาด: ' + e.message);
                btn.disabled = false;
                text.textContent = 'Sign in with CMU Account';
            }
        }

        function devLogin() {
            window.location.href = 'dev-login.php';
        }
    </script>
</body>

</html>