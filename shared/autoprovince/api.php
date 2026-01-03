<?php
/**
 * AutoProvince API Endpoint
 * API สำหรับดึงข้อมูลจังหวัด อำเภอ ตำบล
 * 
 * Usage in web:  include from web/config/database.php
 * Usage in admin: include from admin/src/services/database.php
 * 
 * Endpoints:
 *   GET ?action=get_provinces         - รายการจังหวัด
 *   POST ?action=get_districts        - รายการอำเภอ (province_id)
 *   POST ?action=get_subdistricts     - รายการตำบล (district_id)
 *   POST ?action=get_address          - ข้อมูลที่อยู่แบบเต็ม (subdistrict_id)
 *   GET ?action=search&q=xxx          - ค้นหาที่อยู่
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Determine which database config to use
// Auto-detect based on calling context
$dbConfigPaths = [
    __DIR__ . '/../../config/database.php',           // From shared/autoprovince
    __DIR__ . '/../../api/config/database.php',       // From API context
    __DIR__ . '/../../../config/database.php',        // Alternative path
];

$pdo = null;

foreach ($dbConfigPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        if (class_exists('Database')) {
            $pdo = Database::getInstance();
        }
        break;
    }
}

// Fallback: Use .env config directly
if (!$pdo) {
    $envPath = __DIR__ . '/../../.env';
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        $lines = explode("\n", $envContent);
        $env = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0)
                continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $env[trim($key)] = trim($value);
            }
        }

        try {
            $pdo = new PDO(
                "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4",
                $env['DB_USER'],
                $env['DB_PASS'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Database connection failed'
            ]);
            exit;
        }
    }
}

if (!$pdo) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Could not establish database connection'
    ]);
    exit;
}

require_once __DIR__ . '/AutoProvince.php';
$autoProv = new AutoProvince($pdo);

$response = ['status' => 'error', 'message' => 'Invalid Request', 'data' => []];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_provinces':
            $data = $autoProv->getProvinces();
            $response = ['status' => 'success', 'data' => $data];
            break;

        case 'get_districts':
            $provinceId = (int) ($_POST['province_id'] ?? $_GET['province_id'] ?? 0);
            if (!$provinceId)
                throw new Exception('Missing Province ID');

            $data = $autoProv->getDistricts($provinceId);
            $response = ['status' => 'success', 'data' => $data];
            break;

        case 'get_subdistricts':
            $districtId = (int) ($_POST['district_id'] ?? $_GET['district_id'] ?? 0);
            if (!$districtId)
                throw new Exception('Missing District ID');

            $data = $autoProv->getSubdistricts($districtId);
            $response = ['status' => 'success', 'data' => $data];
            break;

        case 'get_address':
            $subdistrictId = (int) ($_POST['subdistrict_id'] ?? $_GET['subdistrict_id'] ?? 0);
            if (!$subdistrictId)
                throw new Exception('Missing Subdistrict ID');

            $data = $autoProv->getFullAddress($subdistrictId);
            if (!$data)
                throw new Exception('Address not found');

            $response = ['status' => 'success', 'data' => $data];
            break;

        case 'search':
            $keyword = trim($_GET['q'] ?? '');
            if (strlen($keyword) < 2)
                throw new Exception('Keyword too short (min 2 chars)');

            $limit = min(50, max(10, (int) ($_GET['limit'] ?? 20)));
            $data = $autoProv->search($keyword, $limit);
            $response = ['status' => 'success', 'data' => $data, 'count' => count($data)];
            break;

        default:
            throw new Exception('Action not found. Available: get_provinces, get_districts, get_subdistricts, get_address, search');
    }
} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
