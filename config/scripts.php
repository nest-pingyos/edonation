<?php
/**
 * Web Scripts - Common JavaScript includes
 * Include this file at the end of body in web pages
 * 
 * Features:
 * - jQuery via CDN (with version fallback)
 * - Plugins and main.js
 * - Optional: AutoProvince, Select2
 */

// Get base path
$basePath = defined('BASE_PATH') ? BASE_PATH : '/appdev/edonation';
$webAssets = '../assets';
?>

<!-- jQuery (CDN with fallback) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script>window.jQuery || document.write('<script src="<?php echo $webAssets; ?>/js/jquery-3.7.1.min.js"><\/script>')</script>

<!-- Core Plugins -->
<script src="<?php echo $webAssets; ?>/js/plugins.js"></script>
<script src="<?php echo $webAssets; ?>/js/main.js"></script>

<?php
// Optional includes based on page requirements
if (isset($includeSelect2) && $includeSelect2): ?>
    <!-- Select2 for searchable dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<?php endif; ?>

<?php if (isset($includeAutoprovince) && $includeAutoprovince): ?>
    <!-- AutoProvince -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>/shared/autoprovince/assets/autoprovince.css">
    <script src="<?php echo $basePath; ?>/shared/autoprovince/assets/autoprovince.js"></script>
<?php endif; ?>

<?php if (isset($includeSweetAlert) && $includeSweetAlert): ?>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<?php endif; ?>