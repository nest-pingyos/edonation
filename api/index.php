<?php
/**
 * API Router - eDonation
 * Main entry point for all API requests
 */

// Error handling - TEMP DEBUG
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Headers
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Autoload
require_once __DIR__ . '/config/bootstrap.php';

// Get request info
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/appdev/edonation/api', '', $uri);
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
            
        case 'notifications':
            require_once __DIR__ . '/controllers/NotificationController.php';
            $controller = new NotificationController();
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
                    'benefits' => '/api/v1/benefits'
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
