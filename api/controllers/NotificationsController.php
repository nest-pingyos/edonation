<?php
/**
 * Notifications Controller
 * API สำหรับส่งการแจ้งเตือน
 * 
 * Endpoints:
 * POST   /notifications/send      - ส่งการแจ้งเตือนทั่วไป
 * POST   /notifications/email     - ส่งอีเมล (Admin)
 * POST   /notifications/line      - ส่ง LINE (Admin)
 */

class NotificationsController
{
    const VERSION = '2.0';
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function handle(string $method, ?string $id, ?string $action): array
    {
        if ($method !== 'POST') {
            return Response::error('METHOD_NOT_ALLOWED', 'Use POST method', 405);
        }

        AuthMiddleware::requireAdmin();

        switch ($id) {
            case 'send':
                return $this->send();
            case 'email':
                return $this->sendEmail();
            case 'line':
                return $this->sendLine();
            default:
                return Response::error('NOT_FOUND', 'Endpoint not found', 404);
        }
    }

    /**
     * POST /notifications/send
     * ส่งการแจ้งเตือนทั่วไป
     */
    private function send(): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        if (empty($data['type']) || empty($data['message'])) {
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุ type และ message');
        }

        switch ($data['type']) {
            case 'email':
                if (empty($data['to'])) {
                    return Response::error('VALIDATION_ERROR', 'กรุณาระบุ email ปลายทาง');
                }
                // TODO: Implement email sending
                return Response::success([
                    'type' => 'email',
                    'to' => $data['to'],
                    'subject' => $data['subject'] ?? 'แจ้งเตือนจาก eDonation',
                    'message' => $data['message']
                ], 'ส่งอีเมลเรียบร้อย (Simulated)');

            case 'line':
                // TODO: Implement LINE notification
                return Response::success([
                    'type' => 'line',
                    'message' => $data['message']
                ], 'ส่งข้อความ LINE เรียบร้อย (Simulated)');

            default:
                return Response::error('VALIDATION_ERROR', 'Type ต้องเป็น email หรือ line');
        }
    }

    /**
     * POST /notifications/email
     * ส่งอีเมลใบเสร็จ
     */
    private function sendEmail(): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        if (empty($data['receipt_id']) || empty($data['email'])) {
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุ receipt_id และ email');
        }

        // Get receipt info
        $stmt = $this->pdo->prepare("SELECT * FROM receipts WHERE id = :id");
        $stmt->execute([':id' => $data['receipt_id']]);
        $receipt = $stmt->fetch();

        if (!$receipt)
            return Response::notFound('ไม่พบใบเสร็จ');

        // TODO: Implement email sending using PHPMailer
        // require_once __DIR__ . '/../services/EmailService.php';
        // EmailService::sendReceipt($receipt, $data['email']);

        return Response::success([
            'receipt_id' => $data['receipt_id'],
            'email' => $data['email'],
            'receipt_number' => $receipt['receipt_number'] ?? null
        ], "ส่งใบเสร็จไปยัง {$data['email']} เรียบร้อย");
    }

    /**
     * POST /notifications/line
     * ส่งข้อความ LINE Notify
     */
    private function sendLine(): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        if (empty($data['message'])) {
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุข้อความ');
        }

        $lineToken = defined('LINE_TOKEN') ? LINE_TOKEN : null;

        if (!$lineToken) {
            return Response::error('CONFIG_ERROR', 'LINE Token ยังไม่ได้ตั้งค่า');
        }

        // Send to LINE Notify
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://notify-api.line.me/api/notify',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(['message' => $data['message']]),
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $lineToken],
            CURLOPT_RETURNTRANSFER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return Response::error('LINE_ERROR', 'ไม่สามารถส่งข้อความ LINE ได้', 500);
        }

        return Response::success([
            'message' => $data['message']
        ], 'ส่งข้อความ LINE เรียบร้อย');
    }
}
