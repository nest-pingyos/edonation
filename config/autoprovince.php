<?php
/**
 * AutoProvince Helper for Web
 * Include ไฟล์นี้เพื่อใช้งาน AutoProvince ใน web pages
 * 
 * Usage:
 * <?php require_once __DIR__ . '/config/autoprovince.php'; ?>
 * 
 * Then in your page:
 * <?php echo getProvinceOptions($selectedId); ?>
 * 
 * Or use JavaScript:
 * AutoProvince.init({ apiPath: '<?php echo AUTOPROVINCE_API_PATH; ?>' });
 */

// Base path for assets
$basePath = defined('BASE_PATH') ? BASE_PATH : '/appdev/edonation';

// Define paths
define('AUTOPROVINCE_BASE', dirname(__DIR__, 2) . '/shared/autoprovince');
define('AUTOPROVINCE_API_PATH', $basePath . '/shared/autoprovince/api.php');
define('AUTOPROVINCE_CSS_PATH', $basePath . '/shared/autoprovince/assets/autoprovince.css');
define('AUTOPROVINCE_JS_PATH', $basePath . '/shared/autoprovince/assets/autoprovince.js');

// Include the class
require_once AUTOPROVINCE_BASE . '/AutoProvince.php';

// Database connection
require_once __DIR__ . '/database.php';

/**
 * Get AutoProvince instance
 * @return AutoProvince
 */
function getAutoProvince(): AutoProvince
{
    static $instance = null;
    if ($instance === null) {
        $pdo = Database::getInstance();
        $instance = new AutoProvince($pdo);
    }
    return $instance;
}

/**
 * Get province options HTML
 * @param int|null $selectedId
 * @return string
 */
function getProvinceOptions(?int $selectedId = null): string
{
    return getAutoProvince()->getProvinceOptions($selectedId);
}

/**
 * Get all provinces as array
 * @return array
 */
function getProvinces(): array
{
    return getAutoProvince()->getProvinces();
}

/**
 * Get districts by province ID
 * @param int $provinceId
 * @return array
 */
function getDistricts(int $provinceId): array
{
    return getAutoProvince()->getDistricts($provinceId);
}

/**
 * Get subdistricts by district ID
 * @param int $districtId
 * @return array
 */
function getSubdistricts(int $districtId): array
{
    return getAutoProvince()->getSubdistricts($districtId);
}

/**
 * Get full address by subdistrict ID
 * @param int $subdistrictId
 * @return array|null
 */
function getFullAddress(int $subdistrictId): ?array
{
    return getAutoProvince()->getFullAddress($subdistrictId);
}

/**
 * Format address string
 * @param int $subdistrictId
 * @return string
 */
function formatAddress(int $subdistrictId): string
{
    return getAutoProvince()->formatAddress($subdistrictId);
}

/**
 * Output AutoProvince CSS link tag
 */
function autoprovinceCss(): void
{
    echo '<link rel="stylesheet" href="' . AUTOPROVINCE_CSS_PATH . '">';
}

/**
 * Output AutoProvince JS script tag
 */
function autoprovinceJs(): void
{
    echo '<script src="' . AUTOPROVINCE_JS_PATH . '"></script>';
}

/**
 * Output complete AutoProvince initialization script
 * @param array $options
 */
function autoprovinceInit(array $options = []): void
{
    $defaultOptions = [
        'apiPath' => AUTOPROVINCE_API_PATH,
        'useSelect2' => true
    ];
    $options = array_merge($defaultOptions, $options);
    $optionsJson = json_encode($options);

    echo "<script>
    if (typeof AutoProvince !== 'undefined') {
        AutoProvince.init({$optionsJson});
    }
    </script>";
}
