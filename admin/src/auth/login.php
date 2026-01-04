<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['backend_user']) && $_SESSION['backend_user']['logged_in'] === true) {
    header('Location: ../index.php');
    exit();
}

// Get error message if any
$error = $_SESSION['auth_error'] ?? null;
unset($_SESSION['auth_error']);
?>

<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ | eDonation Admin</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="../assets/images/favicon.ico">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #667eea;
            --accent-color: #764ba2;
            --cmu-purple: #6d28d9;
        }

        * {
            font-family: 'Prompt', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 1000px;
        }

        .login-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .login-sidebar {
            background: linear-gradient(135deg, #1a3a5c 0%, #2c5282 100%);
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            min-height: 500px;
        }

        .login-sidebar img {
            max-width: 200px;
            margin-bottom: 30px;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.3));
        }

        .login-sidebar h2 {
            color: #fff;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .login-sidebar p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
            line-height: 1.7;
        }

        .login-content {
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-logo img {
            height: 50px;
        }

        .login-title {
            text-align: center;
            margin-bottom: 10px;
        }

        .login-title h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a3a5c;
        }

        .login-subtitle {
            text-align: center;
            color: #6c757d;
            margin-bottom: 40px;
        }

        .alert {
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 25px;
        }

        .btn-cmu-oauth {
            background: linear-gradient(135deg, var(--cmu-purple) 0%, #8b5cf6 100%);
            border: none;
            color: #fff;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-cmu-oauth:hover {
            background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(109, 40, 217, 0.4);
            color: #fff;
        }

        .btn-cmu-oauth svg {
            width: 24px;
            height: 24px;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 30px 0;
            color: #adb5bd;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #dee2e6;
        }

        .divider span {
            padding: 0 15px;
            font-size: 0.9rem;
        }

        .login-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }

        .login-info p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .login-info a {
            color: var(--cmu-purple);
            font-weight: 600;
        }

        .footer-text {
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 30px;
            font-size: 0.9rem;
        }

        @media (max-width: 991px) {
            .login-sidebar {
                display: none;
            }

            .login-content {
                padding: 40px 30px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="row g-0">
                <!-- Sidebar -->
                <div class="col-lg-5">
                    <div class="login-sidebar">
                        <img src="../assets/images/logo-light.png" alt="eDonation" onerror="this.style.display='none'">
                        <h2>eDonation Admin</h2>
                        <p>
                            ระบบจัดการการบริจาค<br>
                            คณะพยาบาลศาสตร์<br>
                            มหาวิทยาลัยเชียงใหม่
                        </p>
                    </div>
                </div>

                <!-- Login Form -->
                <div class="col-lg-7">
                    <div class="login-content">
                        <div class="login-logo">
                            <img src="../assets/images/logo-dark.png" alt="Logo"
                                onerror="this.innerHTML='<h3>eDonation</h3>'">
                        </div>

                        <div class="login-title">
                            <h1>เข้าสู่ระบบ</h1>
                        </div>

                        <p class="login-subtitle">
                            เข้าสู่ระบบด้วยบัญชี CMU Account
                        </p>

                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <!-- CMU OAuth Login Button -->
                        <a href="callback.php" class="btn btn-cmu-oauth">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                            </svg>
                            เข้าสู่ระบบด้วย CMU Account
                        </a>

                        <div class="divider">
                            <span>หรือ</span>
                        </div>

                        <div class="login-info">
                            <p>
                                หากต้องการเข้าสู่ระบบด้วยอีเมลและรหัสผ่าน<br>
                                <a href="../auth-signin.php">คลิกที่นี่</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p class="footer-text">
            ©
            <?php echo date('Y'); ?> eDonation - คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่
        </p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>