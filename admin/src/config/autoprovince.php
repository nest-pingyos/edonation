<?php
/**
 * AutoProvince Helper for Admin
 * Include ไฟล์นี้เพื่อใช้งาน AutoProvince ใน admin pages
 * 
 * Usage in admin pages:
 * <?php require_once __DIR__ . '/config/autoprovince.php'; ?>
 * 
 * Then in your page:
 * <?php echo getAdminProvinceOptions($selectedId); ?>
 * 
 * Or use JavaScript:
 * AutoProvince.init({ apiPath: '<?php echo AUTOPROVINCE_API_PATH; ?>' });
 */

// Ensure config is loaded
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/config.php';
}

// Define paths for AutoProvince
define('AUTOPROVINCE_BASE', dirname(ADMIN_ROOT, 1) . '/shared/autoprovince');
define('AUTOPROVINCE_API_PATH', BASE_PATH . '/shared/autoprovince/api.php');
define('AUTOPROVINCE_CSS_PATH', BASE_PATH . '/shared/autoprovince/assets/autoprovince.css');
define('AUTOPROVINCE_JS_PATH', BASE_PATH . '/shared/autoprovince/assets/autoprovince.js');

// Include the class
require_once AUTOPROVINCE_BASE . '/AutoProvince.php';

// Include database service
require_once ADMIN_ROOT . '/services/database.php';

/**
 * Get AutoProvince instance for Admin
 * @return AutoProvince
 */
function getAdminAutoProvince(): AutoProvince
{
    static $instance = null;
    if ($instance === null) {
        $pdo = DatabaseService::getInstance();
        $instance = new AutoProvince($pdo);
    }
    return $instance;
}

/**
 * Get province options HTML for Admin
 * @param int|null $selectedId
 * @return string
 */
function getAdminProvinceOptions(?int $selectedId = null): string
{
    return getAdminAutoProvince()->getProvinceOptions($selectedId);
}

/**
 * Get all provinces as array for Admin
 * @return array
 */
function getAdminProvinces(): array
{
    return getAdminAutoProvince()->getProvinces();
}

/**
 * Get districts by province ID for Admin
 * @param int $provinceId
 * @return array
 */
function getAdminDistricts(int $provinceId): array
{
    return getAdminAutoProvince()->getDistricts($provinceId);
}

/**
 * Get subdistricts by district ID for Admin
 * @param int $districtId
 * @return array
 */
function getAdminSubdistricts(int $districtId): array
{
    return getAdminAutoProvince()->getSubdistricts($districtId);
}

/**
 * Get full address by subdistrict ID for Admin
 * @param int $subdistrictId
 * @return array|null
 */
function getAdminFullAddress(int $subdistrictId): ?array
{
    return getAdminAutoProvince()->getFullAddress($subdistrictId);
}

/**
 * Format address string for Admin
 * @param int $subdistrictId
 * @return string
 */
function formatAdminAddress(int $subdistrictId): string
{
    return getAdminAutoProvince()->formatAddress($subdistrictId);
}

/**
 * Output AutoProvince CSS link tag for Admin
 */
function adminAutoprovinceCss(): void
{
    echo '<link rel="stylesheet" href="' . AUTOPROVINCE_CSS_PATH . '">';
}

/**
 * Output AutoProvince JS script tag for Admin
 */
function adminAutoprovinceJs(): void
{
    echo '<script src="' . AUTOPROVINCE_JS_PATH . '?v=2.1"></script>';
}

/**
 * Output complete AutoProvince initialization script for Admin
 * @param array $options
 */
function adminAutoprovinceInit(array $options = []): void
{
    $defaultOptions = [
        'apiPath' => AUTOPROVINCE_API_PATH,
        'useSelect2' => true,
        'select2Theme' => 'bootstrap-5'
    ];
    $options = array_merge($defaultOptions, $options);
    $optionsJson = json_encode($options);

    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof AutoProvince !== 'undefined') {
            AutoProvince.init({$optionsJson});
        }
    });
    </script>";
}

/**
 * Generate complete AutoProvince form fields HTML
 * @param array $values Current values (province_id, district_id, subdistrict_id, postcode)
 * @param string $prefix Field name prefix
 * @return string HTML
 */
function renderAdminAddressForm(array $values = [], string $prefix = ''): string
{
    $provinceId = $values['province_id'] ?? null;
    $districtId = $values['district_id'] ?? null;
    $subdistrictId = $values['subdistrict_id'] ?? null;
    $postcode = $values['postcode'] ?? '';

    $prefixName = $prefix ? $prefix . '_' : '';
    $prefixId = $prefix ? $prefix . '-' : '';

    $html = '<div class="address-section autoprovince-container">';
    $html .= '<div class="autoprovince-loader" id="' . $prefixId . 'loader">';
    $html .= '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>';
    $html .= '</div>';

    $html .= '<div class="row">';

    // Province
    $html .= '<div class="col-md-6 mb-3">';
    $html .= '<label class="form-label" for="' . $prefixId . 'province">จังหวัด</label>';
    $html .= '<select class="form-select" id="' . $prefixId . 'province" name="' . $prefixName . 'province_id">';
    $html .= getAdminProvinceOptions($provinceId);
    $html .= '</select>';
    $html .= '</div>';

    // District
    $html .= '<div class="col-md-6 mb-3">';
    $html .= '<label class="form-label" for="' . $prefixId . 'district">อำเภอ/เขต</label>';
    $html .= '<select class="form-select" id="' . $prefixId . 'district" name="' . $prefixName . 'district_id" disabled>';
    $html .= '<option value="">-- เลือกอำเภอ --</option>';
    if ($provinceId && $districtId) {
        $districts = getAdminDistricts($provinceId);
        foreach ($districts as $d) {
            $selected = ($d['id'] == $districtId) ? 'selected' : '';
            $html .= '<option value="' . $d['id'] . '" ' . $selected . '>' . htmlspecialchars($d['name']) . '</option>';
        }
    }
    $html .= '</select>';
    $html .= '</div>';

    // Subdistrict
    $html .= '<div class="col-md-6 mb-3">';
    $html .= '<label class="form-label" for="' . $prefixId . 'subdistrict">ตำบล/แขวง</label>';
    $html .= '<select class="form-select" id="' . $prefixId . 'subdistrict" name="' . $prefixName . 'subdistrict_id" disabled>';
    $html .= '<option value="">-- เลือกตำบล --</option>';
    if ($districtId && $subdistrictId) {
        $subdistricts = getAdminSubdistricts($districtId);
        foreach ($subdistricts as $s) {
            $selected = ($s['id'] == $subdistrictId) ? 'selected' : '';
            $html .= '<option value="' . $s['id'] . '" data-postcode="' . $s['postcode'] . '" ' . $selected . '>' . htmlspecialchars($s['name']) . '</option>';
        }
    }
    $html .= '</select>';
    $html .= '</div>';

    // Postcode
    $html .= '<div class="col-md-6 mb-3">';
    $html .= '<label class="form-label" for="' . $prefixId . 'postcode">รหัสไปรษณีย์</label>';
    $html .= '<input type="text" class="form-control" id="' . $prefixId . 'postcode" name="' . $prefixName . 'postcode" value="' . htmlspecialchars($postcode) . '" readonly>';
    $html .= '</div>';

    $html .= '</div></div>';

    return $html;
}
