<?php
/**
 * API Router - eDonation
 * Main entry point for all API requests
 * 
 * รองรับ API แยก domain พร้อม CORS
 * 
 * @version 2.0
 */

// Error handling
if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Autoload first to get config
require_once __DIR__ . '/config/bootstrap.php';

// Set content type
header('Content-Type: application/json; charset=UTF-8');

// Handle CORS with dynamic origins
handleCors();

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


// Get request info
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ใช้ BASE_PATH จาก env config (รองรับทั้ง Production และ Development)
// Production default: /edonation, Development: /appdev/edonation
$apiBasePath = (defined('BASE_PATH') ? BASE_PATH : '/edonation') . '/api';
$uri = str_replace($apiBasePath, '', $uri);
$uri = trim($uri, '/');
$segments = $uri ? explode('/', $uri) : [];

// Simple Router
$version = $segments[0] ?? 'v1';
$resource = $segments[1] ?? '';
$id = $segments[2] ?? null;
$action = $segments[3] ?? null;

// Route to controller
try {
    $response = null;

    switch ($resource) {
        case 'projects':
            require_once __DIR__ . '/controllers/ProjectController.php';
            $controller = new ProjectController();
            $response = $controller->handle($method, $id, $action);
            break;

        case 'donations':
            require_once __DIR__ . '/controllers/DonationController.php';
            $controller = new DonationController();
            $response = $controller->handle($method, $id, $action);
            break;

        case 'receipts':
            require_once __DIR__ . '/controllers/ReceiptController.php';
            $controller = new ReceiptController();
            $response = $controller->handle($method, $id, $action);
            break;

        case 'auth':
            require_once __DIR__ . '/controllers/AuthController.php';
            $controller = new AuthController();
            $response = $controller->handle($method, $id, $action);
            break;

        case 'payments':
            require_once __DIR__ . '/controllers/PaymentController.php';
            $controller = new PaymentController();
            $response = $controller->handle($method, $id, $action);
            break;

        case 'signatures':
            require_once __DIR__ . '/controllers/SignatureController.php';
            $controller = new SignatureController();
            $response = $controller->handle($method, $id, $action);
            break;

        case 'benefits':
            require_once __DIR__ . '/controllers/BenefitsController.php';
            $controller = new BenefitsController();
            $response = $controller->handle($method, $id, $action);
            break;

        case 'news':
            require_once __DIR__ . '/controllers/NewsController.php';
            $controller = new NewsController();
            $response = $controller->handle($method, $id, $action);
            break;

        case 'notifications':
            require_once __DIR__ . '/controllers/NotificationsController.php';
            $controller = new NotificationsController();
            $response = $controller->handle($method, $id, $action);
            break;

        case 'members':
            require_once __DIR__ . '/controllers/MemberController.php';
            $controller = new MemberController();
            $response = $controller->handle($method, $id, $action);
            break;

        case 'reports':
            require_once __DIR__ . '/controllers/ReportController.php';
            $controller = new ReportController();
            $response = $controller->handle($method, $id, $action);
            break;

        case 'admin-users':
            require_once __DIR__ . '/controllers/AdminUserController.php';
            $controller = new AdminUserController();

            // Route based on method and id
            if ($method === 'GET' && $id === 'check') {
                $response = $controller->check();
            } elseif ($method === 'GET' && $id) {
                $response = $controller->show($id);
            } elseif ($method === 'GET') {
                $response = $controller->index();
            } elseif ($method === 'POST') {
                $response = $controller->create();
            } elseif ($method === 'PUT' && $id) {
                if ($action === 'activate') {
                    $response = $controller->activate($id);
                } elseif ($action === 'deactivate') {
                    $response = $controller->deactivate($id);
                } else {
                    $response = $controller->update($id);
                }
            } elseif ($method === 'DELETE' && $id) {
                $response = $controller->delete($id);
            } else {
                http_response_code(400);
                $response = ['success' => false, 'error' => ['message' => 'Invalid request']];
            }
            break;

        case 'services':
            require_once __DIR__ . '/controllers/ServicesController.php';
            $controller = new ServicesController();
            $response = $controller->handle($method, $id, $action);
            break;

        default:
            $response = [
                'success' => true,
                'message' => 'eDonation API v1',
                'endpoints' => [
                    'projects' => '/api/v1/projects',
                    'donations' => '/api/v1/donations',
                    'receipts' => '/api/v1/receipts',
                    'auth' => '/api/v1/auth',
                    'notifications' => '/api/v1/notifications',
                    'payments' => '/api/v1/payments/callback',
                    'benefits' => '/api/v1/benefits',
                    'news' => '/api/v1/news',
                    'members' => '/api/v1/members'
                ]
            ];
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'SERVER_ERROR',
            'message' => 'เกิดข้อผิดพลาดภายในระบบ'
        ]
    ], JSON_UNESCAPED_UNICODE);
    error_log($e->getMessage());
}
