<?php include 'partials/main.php'; ?>
<?php
// Redirect if already logged in
if (isLoggedIn() && !isSessionExpired()) {
    header('Location: index.php');
    exit();
}

$_SESSION['error'] = null;
$_SESSION['success'] = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email)) {
        $_SESSION['error'] = "กรุณากรอกอีเมล";
    } elseif (empty($password)) {
        $_SESSION['error'] = "กรุณากรอกรหัสผ่าน";
    } else {
        $result = checkAuth($email, $password);
        if ($result === true) {
            header('Location: index.php');
            exit();
        } else {
            $_SESSION['error'] = $result;
        }
    }
}
?>

<!doctype html>
<html lang="th">
<head>
    <?php
    $title = "เข้าสู่ระบบ";
    include 'partials/title-meta.php'; ?>

    <?php include 'partials/head-css.php'; ?>
    
    <style>
        .auth-page-sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .auth-page-sidebar img {
            max-width: 80%;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.2));
        }
        .brand-logo {
            max-height: 60px;
        }
    </style>
</head>

<body class="authentication-bg">
<div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-12">
                <div class="card auth-card">
                    <div class="card-body p-0">
                        <div class="row align-items-center g-0">
                            <div class="col-lg-6 d-none d-lg-inline-block border-end">
                                <div class="auth-page-sidebar">
                                    <div class="text-center text-white">
                                        <img src="assets/images/sign-in.svg" alt="auth" class="img-fluid mb-4" />
                                        <h3 class="text-white">eDonation Admin</h3>
                                        <p class="opacity-75">ระบบจัดการการบริจาค<br>มหาวิทยาลัยเชียงใหม่</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-4">
                                    <div class="mx-auto mb-4 text-center auth-logo">
                                        <a href="index.php" class="logo-dark">
                                            <img src="assets/images/logo-dark.png" height="40" alt="logo dark" />
                                        </a>
                                        <a href="index.php" class="logo-light">
                                            <img src="assets/images/logo-light.png" height="40" alt="logo light" />
                                        </a>
                                    </div>
                                    
                                    <h2 class="fw-bold text-center fs-18">เข้าสู่ระบบ</h2>
                                    <p class="text-muted text-center mt-1 mb-4">
                                        กรุณาใส่อีเมลและรหัสผ่านเพื่อเข้าสู่ระบบจัดการ
                                    </p>

                                    <?php if (!empty($_SESSION['error'])): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="iconify" data-icon="mdi:alert-circle"></i>
                                        <?php echo htmlspecialchars($_SESSION['error']); ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($_SESSION['success'])): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="iconify" data-icon="mdi:check-circle"></i>
                                        <?php echo htmlspecialchars($_SESSION['success']); ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                    <?php endif; ?>

                                    <div class="row justify-content-center">
                                        <div class="col-12 col-md-10">
                                            <form method="POST" class="authentication-form">
                                                <div class="mb-3">
                                                    <label class="form-label" for="email">อีเมล</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light">
                                                            <i class="iconify" data-icon="mdi:email-outline"></i>
                                                        </span>
                                                        <input
                                                            type="email"
                                                            id="email"
                                                            name="email"
                                                            class="form-control"
                                                            placeholder="กรอกอีเมล"
                                                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                                            required
                                                        />
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <a href="auth-password.php" class="float-end text-muted text-unline-dashed ms-1">
                                                        ลืมรหัสผ่าน?
                                                    </a>
                                                    <label class="form-label" for="password">รหัสผ่าน</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light">
                                                            <i class="iconify" data-icon="mdi:lock-outline"></i>
                                                        </span>
                                                        <input
                                                            type="password"
                                                            id="password"
                                                            name="password"
                                                            class="form-control"
                                                            placeholder="กรอกรหัสผ่าน"
                                                            required
                                                        />
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <div class="form-check">
                                                        <input
                                                            type="checkbox"
                                                            class="form-check-input"
                                                            id="remember"
                                                            name="remember"
                                                        />
                                                        <label class="form-check-label" for="remember">
                                                            จดจำการเข้าสู่ระบบ
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="mb-1 text-center d-grid">
                                                    <button class="btn btn-primary" type="submit">
                                                        <i class="iconify me-1" data-icon="mdi:login"></i>
                                                        เข้าสู่ระบบ
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-white mb-0 text-center mt-3">
                    © <?php echo date('Y'); ?> eDonation - มหาวิทยาลัยเชียงใหม่
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/vendor-scripts.php'; ?>

</body>
</html>
