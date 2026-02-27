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
            AuthMiddleware::requireAdmin();

            return match ($id) {
                'self-status' => $method === 'GET' ? $this->getSelfStatus() : Response::error('METHOD_NOT_ALLOWED', 'Use GET', 405),
                'toggle-status' => $method === 'POST' ? $this->toggleSelfStatus() : Response::error('METHOD_NOT_ALLOWED', 'Use POST', 405),
                'send' => $method === 'POST' ? $this->send() : Response::error('METHOD_NOT_ALLOWED', 'Use POST', 405),
                'email' => $method === 'POST' ? $this->sendEmail() : Response::error('METHOD_NOT_ALLOWED', 'Use POST', 405),
                'line' => $method === 'POST' ? $this->sendLine() : Response::error('METHOD_NOT_ALLOWED', 'Use POST', 405),
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

        error_log("Email receipt notification: #{$data['receipt_id']} to {$data['email']}");

        return Response::success([
            'receipt_id' => (int) $data['receipt_id'],
            'email' => $data['email'],
            'receipt_number' => $receipt['receipt_number'] ?? null
        ], "ส่งใบเสร็จไปยัง {$data['email']} เรียบร้อย");
    }

    /**
     * POST /notifications/line
     * ส่งข้อความ LINE ของคณะ (Admin)
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

        // ใช้ LineNotificationService (API คณะ)
        $notifier = new LineNotificationService();
        $result = $notifier->sendCustomNotification('admin_direct', $message);

        if (!$result['success']) {
            return Response::error('LINE_ERROR', $result['message'] ?? 'ไม่สามารถส่งข้อความ LINE ได้', 500);
        }

        return Response::success($result['results'], 'ส่งข้อความ LINE ของคณะเรียบร้อย');
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

        error_log("Email notification to {$data['to']}: " . substr($message, 0, 100));

        return Response::success([
            'type' => 'email',
            'to' => $data['to'],
            'subject' => $data['subject'] ?? 'แจ้งเตือนจาก eDonation',
            'message' => $message
        ], 'ส่งอีเมลเรียบร้อย (Simulated)');
    }

    /**
     * Handle LINE notification (Faculty API)
     */
    private function handleLineNotification(string $message): array
    {
        $notifier = new LineNotificationService();
        $result = $notifier->sendCustomNotification('general', $message);

        return Response::success([
            'type' => 'line',
            'message' => $message,
            'results' => $result['results']
        ], $result['success'] ? 'ส่งข้อความ LINE ของคณะเรียบร้อย' : 'ไม่สามารถส่งข้อความ LINE ได้');
    }

    /**
     * GET /notifications/self-status
     * ตรวจสอบสถานะการแจ้งเตือนของตัวเอง
     */
    private function getSelfStatus(): array
    {
        $user = AuthMiddleware::authenticate();
        if (!$user || empty($user['email'])) {
            return Response::error('UNAUTHORIZED', 'Session expired', 401);
        }

        $email = $user['email'];

        try {
            $stmt = $this->pdo->prepare("
                SELECT is_active 
                FROM edonation_notification_recipients 
                WHERE cmu_account = :email 
                LIMIT 1
            ");
            $stmt->execute([':email' => $email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return Response::success([
                'email' => $email,
                'is_active' => $row ? (bool) $row['is_active'] : false,
                'exists' => (bool) $row
            ]);
        } catch (PDOException $e) {
            return Response::error('DATABASE_ERROR', $e->getMessage(), 500);
        }
    }

    /**
     * POST /notifications/toggle-status
     * ปิด/เปิดการแจ้งเตือนของตัวเอง
     */
    private function toggleSelfStatus(): array
    {
        $user = AuthMiddleware::authenticate();
        if (!$user || empty($user['email'])) {
            return Response::error('UNAUTHORIZED', 'Session expired', 401);
        }

        $email = $user['email'];
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $active = isset($data['is_active']) ? (bool) $data['is_active'] : null;

        if ($active === null) {
            return Response::error('VALIDATION_ERROR', 'Missing is_active parameter', 400);
        }

        try {
            // Check if exists
            $stmt = $this->pdo->prepare("SELECT id FROM edonation_notification_recipients WHERE cmu_account = :email");
            $stmt->execute([':email' => $email]);
            $exists = $stmt->fetch();

            if ($exists) {
                // Update
                $stmt = $this->pdo->prepare("
                    UPDATE edonation_notification_recipients 
                    SET is_active = :active, updated_at = NOW() 
                    WHERE cmu_account = :email
                ");
                $stmt->execute([
                    ':active' => $active ? 1 : 0,
                    ':email' => $email
                ]);
            } else if ($active) {
                // Insert new (defaulting to payment_success if they enable it)
                $stmt = $this->pdo->prepare("
                    INSERT INTO edonation_notification_recipients 
                    (notification_type, recipient_email, cmu_account, is_active, created_at)
                    VALUES ('payment_success', :email, :cmu, 1, NOW())
                ");
                $stmt->execute([
                    ':email' => $email,
                    ':cmu' => $email
                ]);
            }

            return Response::success(['is_active' => $active], 'บันทึกสถานะเรียบร้อย');
        } catch (PDOException $e) {
            return Response::error('DATABASE_ERROR', $e->getMessage(), 500);
        }
    }

    /**
     * Validate ID format
     */
    private function isValidId(string $id): bool
    {
        return ctype_digit($id) && (int) $id > 0;
    }
}
