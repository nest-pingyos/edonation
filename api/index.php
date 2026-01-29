<?php
/**
 * API Router - eDonation
 * Unified & Simplified Version
 */

// 1. Load Environment & Config
require_once __DIR__ . '/config/bootstrap.php';

// 2. Handle Request
header('Content-Type: application/json; charset=UTF-8');
handleCors();

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 3. Routing Logic
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip base path and /api
if ($basePath && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}
if (strpos($uri, '/api') === 0) {
    $uri = substr($uri, 4);
}

$uri = trim($uri, '/');
$segments = $uri ? explode('/', $uri) : [];

// Route structure: [version]/[resource]/[id]/[action]
$version = $segments[0] ?? 'v1';
$resource = $segments[1] ?? '';
$id = $segments[2] ?? null;
$action = $segments[3] ?? null;

if (!$resource) {
    echo json_encode([
        'success' => true,
        'message' => 'eDonation API ' . $version,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// 4. Controller Mapping
// Map resource names to controller classes
$controllers = [
    'auth' => 'AuthController',
    'projects' => 'ProjectController',
    'donations' => 'DonationController',
    'receipts' => 'ReceiptController',
    'members' => 'MemberController',
    'reports' => 'ReportController',
    'admin-users' => 'AdminUserController',
    'payments' => 'PaymentController',
    'news' => 'NewsController',
    'benefits' => 'BenefitsController',
    'services' => 'ServicesController',
    'signatures' => 'SignatureController',
    'notifications' => 'NotificationsController'
];

if (!isset($controllers[$resource])) {
    error_log("API 404: Resource '$resource' not found in path $uri");
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => "Resource '$resource' not found"]);
    exit;
}

try {
    $controllerClass = $controllers[$resource];
    $controllerFile = __DIR__ . "/controllers/$controllerClass.php";

    if (!file_exists($controllerFile)) {
        error_log("API ERROR: Controller file for '$resource' missing at $controllerFile");
        throw new Exception("Controller file for '$resource' missing");
    }

    require_once $controllerFile;
    $controller = new $controllerClass();

    // Unified handle method for all controllers
    $response = $controller->handle($method, $id, $action);

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    error_log("API EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'SERVER_ERROR',
            'message' => APP_DEBUG ? $e->getMessage() : 'เกิดข้อผิดพลาดภายในระบบ',
            'file' => APP_DEBUG ? $e->getFile() : null,
            'line' => APP_DEBUG ? $e->getLine() : null
        ]
    ], JSON_UNESCAPED_UNICODE);
}
