<?php
/**
 * eDonation Admin - CSS & Styles
 * 
 * ตรวจสอบว่ามี local CSS files หรือไม่
 * ถ้า build แล้ว จะใช้ local files
 * ถ้ายังไม่ build จะใช้ CDN fallback
 */

$localCssExists = file_exists(__DIR__ . '/../assets/css/app.min.css');
$basePath = '';  // เปลี่ยนถ้าต้องการ
?>

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Wix+Madefor+Text:ital,wght@0,400..800;1,400..800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<?php if ($localCssExists): ?>
<!-- Local CSS Files (Built) -->
<link href="<?php echo $basePath; ?>assets/css/vendor.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $basePath; ?>assets/css/icons.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $basePath; ?>assets/css/app.min.css" rel="stylesheet" type="text/css"/>
<script src="<?php echo $basePath; ?>assets/js/config.js"></script>

<?php else: ?>
<!-- CDN Fallback (Before Build) -->

<!-- Bootstrap 5.3.3 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Boxicons -->
<link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<!-- Iconify -->
<script src="https://cdn.jsdelivr.net/npm/iconify-icon@2.1.0/dist/iconify-icon.min.js"></script>

<!-- Simplebar -->
<link href="https://cdn.jsdelivr.net/npm/simplebar@6.2.6/dist/simplebar.min.css" rel="stylesheet">

<!-- SweetAlert2 -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.11.0/dist/sweetalert2.min.css" rel="stylesheet">

<!-- Flatpickr -->
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

<!-- Choices.js -->
<link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">

<!-- Dropzone -->
<link href="https://cdn.jsdelivr.net/npm/dropzone@6.0.0-beta.2/dist/dropzone.css" rel="stylesheet">

<!-- Reback Admin Custom Styles (CDN Version) -->
<style>
/* ============================================
   Reback Admin Template - CDN Version
   Primary Color: #1c84ee (Blue)
   ============================================ */

:root {
    --bs-primary: #1c84ee;
    --bs-primary-rgb: 28, 132, 238;
    --bs-secondary: #5d7186;
    --bs-success: #22c55e;
    --bs-info: #4ecac2;
    --bs-warning: #f9b931;
    --bs-danger: #ef5f5f;
    --bs-light: #eef2f7;
    --bs-dark: #323a46;
    --bs-body-bg: #f8f8fa;
    --bs-body-color: #5d7186;
    --bs-border-color: #eaedf1;
    --bs-headings-color: #323a46;
    --bs-font-sans-serif: 'Wix Madefor Text', 'Noto Sans Thai', sans-serif;
}

body {
    font-family: var(--bs-font-sans-serif);
    background-color: var(--bs-body-bg);
    color: var(--bs-body-color);
    font-size: 0.875rem;
    line-height: 1.5;
}

h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
    color: var(--bs-headings-color);
    font-weight: 500;
    line-height: 1.1;
}

a { color: var(--bs-body-color); text-decoration: none; }
a:hover { color: var(--bs-primary); }

/* Wrapper */
.wrapper { display: flex; }

/* Sidebar */
.main-nav {
    width: 260px;
    min-width: 260px;
    background: linear-gradient(180deg, #1e293b 0%, #293548 100%);
    min-height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 1050;
    transition: all 0.3s ease-in-out;
}

.main-nav .logo-box {
    padding: 18px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    align-items: center;
    gap: 8px;
}

.main-nav .logo-box .logo-sm { height: 32px; }
.main-nav .logo-box .logo-lg { height: 24px; }
.main-nav .logo-dark { display: none; }
.main-nav .logo-light { display: flex; align-items: center; gap: 8px; }
.main-nav .scrollbar { height: calc(100vh - 70px); overflow-y: auto; }
.button-sm-hover { display: none; }

/* Nav Items */
.navbar-nav { list-style: none; padding: 12px 0; margin: 0; }
.menu-title {
    color: #7b8594;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 20px 20px 8px;
    margin: 0;
}

.nav-item { margin: 2px 8px; }
.nav-item .nav-link {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    color: rgba(255, 255, 255, 0.7);
    border-radius: 6px;
    transition: all 0.2s;
    text-decoration: none;
}

.nav-item .nav-link:hover,
.nav-item .nav-link.active {
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
}

.nav-item .nav-link .nav-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    font-size: 20px;
    opacity: 0.85;
}

.nav-item .nav-link .nav-text { flex: 1; font-size: 13.5px; font-weight: 500; }
.nav-item .nav-link .badge { font-size: 10px; padding: 4px 8px; }

/* Sub Nav */
.sub-navbar-nav { list-style: none; padding: 4px 0 4px 36px; margin: 0; }
.sub-nav-link {
    display: block;
    padding: 8px 12px;
    color: rgba(255, 255, 255, 0.55);
    font-size: 13px;
    border-radius: 4px;
    text-decoration: none;
    transition: all 0.2s;
}
.sub-nav-link:hover, .sub-nav-link.active { color: #fff; }

/* Menu Arrow */
.menu-arrow::after {
    content: '';
    width: 6px;
    height: 6px;
    border: 1px solid;
    border-width: 0 1px 1px 0;
    transform: rotate(-45deg);
    margin-left: auto;
    opacity: 0.5;
    transition: transform 0.2s;
}
.menu-arrow[aria-expanded="true"]::after { transform: rotate(45deg); }

/* Page Content */
.page-content {
    flex: 1;
    margin-left: 260px;
    min-height: 100vh;
    transition: all 0.3s;
}

.container-xxl { max-width: 1400px; padding: 0 24px; }

/* Topbar */
.topbar {
    background: #fff;
    padding: 12px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    position: sticky;
    top: 0;
    z-index: 1040;
}

/* Page Title */
.page-title-box { padding: 20px 0; }
.page-title-box h4 { font-size: 16px; font-weight: 600; margin: 0; }
.breadcrumb { margin: 0; padding: 0; background: transparent; font-size: 13px; }
.breadcrumb-item + .breadcrumb-item::before { content: "›"; }

/* Cards */
.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 3px 4px rgba(0, 0, 0, 0.03);
    margin-bottom: 24px;
    background: #fff;
}
.card-header { background: transparent; padding: 16px 20px; border-bottom: 1px solid var(--bs-border-color); }
.card-body { padding: 20px; }
.card-title { font-size: 15px; font-weight: 600; margin: 0; color: var(--bs-headings-color); }

/* Buttons */
.btn { font-weight: 500; font-size: 14px; padding: 0.5rem 1rem; border-radius: 6px; transition: all 0.2s; }
.btn-primary { background: var(--bs-primary); border-color: var(--bs-primary); }
.btn-primary:hover { background: #1574d4; border-color: #1574d4; }
.btn-soft-primary { background: rgba(28, 132, 238, 0.1); color: var(--bs-primary); border: none; }
.btn-soft-primary:hover { background: var(--bs-primary); color: #fff; }
.btn-outline-light { border-color: var(--bs-border-color); color: var(--bs-body-color); }
.btn-outline-light:hover { background: var(--bs-light); border-color: var(--bs-border-color); }

/* Forms */
.form-control { font-size: 14px; padding: 0.5rem 1rem; border-color: var(--bs-border-color); border-radius: 6px; }
.form-control:focus { border-color: var(--bs-primary); box-shadow: 0 0 0 3px rgba(28, 132, 238, 0.1); }
.form-label { font-weight: 500; font-size: 13px; color: var(--bs-headings-color); margin-bottom: 6px; }
.input-group-text { border-color: var(--bs-border-color); background: #f8f9fa; }

/* Tables */
.table { font-size: 14px; }
.table > :not(caption) > * > * { padding: 0.85rem; border-color: var(--bs-border-color); }
.table thead th { font-weight: 600; color: var(--bs-headings-color); background: var(--bs-light); }
.table-hover tbody tr:hover { background-color: rgba(0, 0, 0, 0.02); }

/* Avatars */
.avatar-xs { width: 24px; height: 24px; }
.avatar-sm { width: 32px; height: 32px; }
.avatar-md { width: 48px; height: 48px; }
.avatar-lg { width: 64px; height: 64px; }
.avatar-xl { width: 80px; height: 80px; }
.avatar-md, .avatar-sm, .avatar-xs, .avatar-lg, .avatar-xl {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}
.avatar-title { display: flex; align-items: center; justify-content: center; }

/* Badges */
.badge { font-weight: 500; padding: 5px 10px; font-size: 11px; border-radius: 4px; }
.badge-soft-success { background: rgba(34, 197, 94, 0.15); color: #22c55e; }
.badge-soft-danger { background: rgba(239, 95, 95, 0.15); color: #ef5f5f; }
.badge-soft-warning { background: rgba(249, 185, 49, 0.15); color: #f9b931; }
.badge-soft-info { background: rgba(78, 202, 194, 0.15); color: #4ecac2; }
.badge-soft-primary { background: rgba(28, 132, 238, 0.15); color: #1c84ee; }
.badge-soft-secondary { background: rgba(93, 113, 134, 0.15); color: #5d7186; }

/* Progress */
.progress { height: 6px; border-radius: 3px; background: #eef2f7; }
.progress-soft { background: rgba(0, 0, 0, 0.04); }
.progress-sm { height: 4px; }

/* Dropdown */
.dropdown-menu { border: 1px solid var(--bs-border-color); border-radius: 8px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); padding: 8px; }
.dropdown-item { padding: 8px 16px; border-radius: 4px; font-size: 14px; }
.dropdown-item:hover { background: var(--bs-light); }

/* Alerts */
.alert { border: none; border-radius: 8px; font-size: 14px; }

/* Auth Page */
.authentication-bg { background: linear-gradient(135deg, #1c84ee 0%, #6366f1 100%); min-height: 100vh; }
.auth-card { border-radius: 16px; overflow: hidden; max-width: 1000px; margin: 0 auto; }
.auth-page-sidebar {
    background: linear-gradient(135deg, #1c84ee 0%, #6366f1 100%);
    min-height: 500px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
}
.auth-page-sidebar img { max-width: 280px; filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.15)); }
.auth-logo img { max-height: 45px; }

/* Utilities */
.fs-10 { font-size: 10px !important; }
.fs-11 { font-size: 11px !important; }
.fs-12 { font-size: 12px !important; }
.fs-13 { font-size: 13px !important; }
.fs-14 { font-size: 14px !important; }
.fs-15 { font-size: 15px !important; }
.fs-16 { font-size: 16px !important; }
.fs-18 { font-size: 18px !important; }
.fs-20 { font-size: 20px !important; }
.fs-22 { font-size: 22px !important; }
.fs-24 { font-size: 24px !important; }
.fs-32 { font-size: 32px !important; }

.fw-medium { font-weight: 500 !important; }
.fw-semibold { font-weight: 600 !important; }

.text-muted { color: #8486a7 !important; }
.text-dark { color: var(--bs-headings-color) !important; }

.bg-soft-primary { background: rgba(28, 132, 238, 0.1) !important; }
.bg-soft-success { background: rgba(34, 197, 94, 0.1) !important; }
.bg-soft-danger { background: rgba(239, 95, 95, 0.1) !important; }
.bg-soft-warning { background: rgba(249, 185, 49, 0.1) !important; }
.bg-soft-info { background: rgba(78, 202, 194, 0.1) !important; }

.border-dashed { border-style: dashed !important; }

/* Footer */
.footer { padding: 20px 24px; border-top: 1px solid var(--bs-border-color); background: #fff; margin-top: auto; }

/* Right Sidebar */
.right-sidebar {
    position: fixed;
    right: -320px;
    top: 0;
    width: 320px;
    height: 100vh;
    background: #fff;
    box-shadow: -5px 0 20px rgba(0, 0, 0, 0.1);
    z-index: 1060;
    transition: right 0.3s;
}
.right-sidebar.show { right: 0; }

/* Responsive */
@media (max-width: 1199.98px) {
    .main-nav { transform: translateX(-100%); }
    .main-nav.show { transform: translateX(0); }
    .page-content { margin-left: 0; }
}

/* Simplebar */
.simplebar-scrollbar::before { background: rgba(255, 255, 255, 0.15); }

/* Apex Charts */
.apex-charts { min-height: 200px; }

/* Animation */
.fade-in { animation: fadeIn 0.3s ease-in; }
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
<?php endif; ?>

<!-- Page-specific Thai font override -->
<style>
body, .form-control, .btn, .dropdown-item, .nav-link, .card-title, h1, h2, h3, h4, h5, h6 {
    font-family: 'Noto Sans Thai', 'Wix Madefor Text', sans-serif !important;
}
</style>
