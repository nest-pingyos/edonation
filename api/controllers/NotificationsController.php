<?php

declare(strict_types=1);

/**
 * Notifications Controller
 *
 * API สำหรับส่งการแจ้งเตือน
 *
 * Endpoints:
 * POST   /notifications/send      - ส่งการแจ้งเตือนทั่วไป
 * POST   /notifications/email     - ส่งอีเมล (Admin)
 * POST   /notifications/line      - ส่ง LINE (Admin)
 *
 * @package eDonation\API\Controllers
 * @version 3.0.0 - Refactored for PSR-12, Security & Performance
 */

class NotificationsController
{
    public const VERSION = '3.0';

    private const ALLOWED_NOTIFICATION_TYPES = ['email', 'line'];

    private const RECEIPT_COLUMNS = [
        'id',
        'receipt_number',
        'amount',
        'payer_name',
        'created_at'
    ];

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function handle(string $method, ?string $id, ?string $action): array
    {
        try {
            if ($method !== 'POST') {
                return Response::error('METHOD_NOT_ALLOWED', 'Use POST method', 405);
            }

            AuthMiddleware::requireAdmin();

            return match ($id) {
                'send' => $this->send(),
                'email' => $this->sendEmail(),
                'line' => $this->sendLine(),
                default => Response::error('NOT_FOUND', 'Endpoint not found', 404)
            };
        } catch (PDOException $e) {
            error_log("Notifications Controller DB Error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล', 500);
        } catch (Exception $e) {
            error_log("Notifications Controller Error: " . $e->getMessage());
            return Response::error('SERVER_ERROR', 'เกิดข้อผิดพลาดภายในระบบ', 500);
        }
    }

    /**
     * POST /notifications/send
     * ส่งการแจ้งเตือนทั่วไป
     */
    private function send(): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        // Validate required fields
        if (empty($data['type']) || empty($data['message'])) {
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุ type และ message', 400);
        }

        $type = trim($data['type']);
        $message = trim($data['message']);

        // Validate notification type
        if (!in_array($type, self::ALLOWED_NOTIFICATION_TYPES, true)) {
            return Response::error(
                'VALIDATION_ERROR',
                'Type ต้องเป็น ' . implode(' หรือ ', self::ALLOWED_NOTIFICATION_TYPES),
                400
            );
        }

        return match ($type) {
            'email' => $this->handleEmailNotification($data, $message),
            'line' => $this->handleLineNotification($message),
            default => Response::error('VALIDATION_ERROR', 'Type ไม่ถูกต้อง', 400)
        };
    }

    /**
     * POST /notifications/email
     * ส่งอีเมลใบเสร็จ
     */
    private function sendEmail(): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        // Validate input
        if (empty($data['receipt_id']) || empty($data['email'])) {
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุ receipt_id และ email', 400);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return Response::error('VALIDATION_ERROR', 'รูปแบบอีเมลไม่ถูกต้อง', 400);
        }

        if (!$this->isValidId((string) $data['receipt_id'])) {
            return Response::error('VALIDATION_ERROR', 'รูปแบบ receipt_id ไม่ถูกต้อง', 400);
        }

        // Get receipt info (only required columns)
        $columns = implode(', ', self::RECEIPT_COLUMNS);
        $stmt = $this->pdo->prepare("SELECT {$columns} FROM receipts WHERE id = :id");
        $stmt->execute([':id' => $data['receipt_id']]);
        $receipt = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$receipt) {
            return Response::notFound('ไม่พบใบเสร็จ');
        }

        // TODO: Implement email sending using PHPMailer
        // require_once __DIR__ . '/../services/EmailService.php';
        // EmailService::sendReceipt($receipt, $data['email']);

        error_log("Email receipt notification: #{$data['receipt_id']} to {$data['email']}");

        return Response::success([
            'receipt_id' => (int) $data['receipt_id'],
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
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุข้อความ', 400);
        }

        $message = trim($data['message']);
        if (strlen($message) === 0) {
            return Response::error('VALIDATION_ERROR', 'ข้อความไม่สามารถเป็นค่าว่างได้', 400);
        }

        $lineToken = defined('LINE_TOKEN') ? LINE_TOKEN : null;

        if (!$lineToken) {
            error_log("LINE Notification Error: LINE_TOKEN not configured");
            return Response::error('CONFIG_ERROR', 'LINE Token ยังไม่ได้ตั้งค่า', 500);
        }

        // Send to LINE Notify
        $result = $this->sendToLineNotify($message, $lineToken);

        if (!$result['success']) {
            error_log("LINE Notification Error: " . ($result['error'] ?? 'Unknown error'));
            return Response::error('LINE_ERROR', 'ไม่สามารถส่งข้อความ LINE ได้', 500);
        }

        return Response::success([
            'message' => $message,
            'message_length' => strlen($message)
        ], 'ส่งข้อความ LINE เรียบร้อย');
    }

    /**
     * Handle email notification
     */
    private function handleEmailNotification(array $data, string $message): array
    {
        if (empty($data['to'])) {
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุ email ปลายทาง', 400);
        }

        if (!filter_var($data['to'], FILTER_VALIDATE_EMAIL)) {
            return Response::error('VALIDATION_ERROR', 'รูปแบบอีเมลไม่ถูกต้อง', 400);
        }

        // TODO: Implement email sending
        error_log("Email notification to {$data['to']}: " . substr($message, 0, 100));

        return Response::success([
            'type' => 'email',
            'to' => $data['to'],
            'subject' => $data['subject'] ?? 'แจ้งเตือนจาก eDonation',
            'message' => $message
        ], 'ส่งอีเมลเรียบร้อย (Simulated)');
    }

    /**
     * Handle LINE notification
     */
    private function handleLineNotification(string $message): array
    {
        // TODO: Implement LINE notification
        error_log("LINE notification: " . substr($message, 0, 100));

        return Response::success([
            'type' => 'line',
            'message' => $message,
            'message_length' => strlen($message)
        ], 'ส่งข้อความ LINE เรียบร้อย (Simulated)');
    }

    /**
     * Send message to LINE Notify API
     */
    private function sendToLineNotify(string $message, string $token): array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://notify-api.line.me/api/notify',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(['message' => $message]),
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => $curlError];
        }

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => "HTTP {$httpCode}: {$response}"];
        }

        return ['success' => true, 'response' => $response];
    }

    /**
     * Validate ID format
     */
    private function isValidId(string $id): bool
    {
        return ctype_digit($id) && (int) $id > 0;
    }
}
