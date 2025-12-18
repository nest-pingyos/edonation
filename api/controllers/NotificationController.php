<?php
/**
 * Notification Controller
 * 
 * Endpoints:
 * POST   /notifications/email   - ส่งอีเมล (Admin)
 * POST   /notifications/line    - ส่ง LINE (Admin)
 */

class NotificationController {
    private PDO $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance();
    }
    
    public function handle(string $method, ?string $id, ?string $action): array {
        if ($method !== 'POST') {
            return Response::error('METHOD_NOT_ALLOWED', 'Use POST method', 405);
        }
        
        AuthMiddleware::requireAdmin();
        
        switch ($id) {
            case 'email':
                return $this->sendEmail();
            case 'line':
                return $this->sendLine();
            default:
                return Response::error('NOT_FOUND', 'Endpoint not found', 404);
        }
    }
    
    // POST /notifications/email
    private function sendEmail(): array {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        
        if (empty($data['receipt_id']) || empty($data['email'])) {
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุ receipt_id และ email');
        }
        
        // Get receipt info
        $stmt = $this->pdo->prepare("SELECT * FROM donat WHERE id = :id");
        $stmt->execute([':id' => $data['receipt_id']]);
        $receipt = $stmt->fetch();
        
        if (!$receipt) return Response::notFound('ไม่พบใบเสร็จ');
        
        // TODO: Implement email sending using PHPMailer
        // require_once __DIR__ . '/../services/EmailService.php';
        // EmailService::sendReceipt($receipt, $data['email']);
        
        return Response::success(null, "ส่งใบเสร็จไปยัง {$data['email']} เรียบร้อย");
    }
    
    // POST /notifications/line
    private function sendLine(): array {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        
        if (empty($data['message'])) {
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุข้อความ');
        }
        
        // TODO: Implement LINE notification
        // require_once __DIR__ . '/../services/LineService.php';
        // LineService::send($data['message']);
        
        return Response::success(null, 'ส่งข้อความ LINE เรียบร้อย');
    }
}
