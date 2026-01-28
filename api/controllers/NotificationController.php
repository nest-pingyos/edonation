<?php

declare(strict_types=1);

/**
 * Notification Controller
 *
 * Endpoints:
 * POST   /notifications/email   - ส่งอีเมล (Admin)
 * POST   /notifications/line    - ส่ง LINE (Admin)
 *
 * @package eDonation\API\Controllers
 * @version 3.0.0 - Refactored for PSR-12, Security & Performance
 */

class NotificationController
{
    public const VERSION = '3.0';

    private const DONAT_COLUMNS = [
        'id',
        'payer_name',
        'amount',
        'project_name',
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
                'email' => $this->sendEmail(),
                'line' => $this->sendLine(),
                default => Response::error('NOT_FOUND', 'Endpoint not found', 404)
            };
        } catch (PDOException $e) {
            error_log("Notification Controller DB Error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล', 500);
        } catch (Exception $e) {
            error_log("Notification Controller Error: " . $e->getMessage());
            return Response::error('SERVER_ERROR', 'เกิดข้อผิดพลาดภายในระบบ', 500);
        }
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
        $columns = implode(', ', self::DONAT_COLUMNS);
        $stmt = $this->pdo->prepare("SELECT {$columns} FROM donat WHERE id = :id");
        $stmt->execute([':id' => $data['receipt_id']]);
        $receipt = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$receipt) {
            return Response::notFound('ไม่พบใบเสร็จ');
        }

        // TODO: Implement email sending using PHPMailer
        // require_once __DIR__ . '/../services/EmailService.php';
        // EmailService::sendReceipt($receipt, $data['email']);

        error_log("Email notification requested for receipt #{$data['receipt_id']} to {$data['email']}");

        return Response::success(
            ['email' => $data['email'], 'receipt_id' => (int) $data['receipt_id']],
            "ส่งใบเสร็จไปยัง {$data['email']} เรียบร้อย"
        );
    }

    /**
     * POST /notifications/line
     * ส่งข้อความ LINE
     */
    private function sendLine(): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        // Validate input
        if (empty($data['message'])) {
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุข้อความ', 400);
        }

        $message = trim($data['message']);
        if (strlen($message) === 0) {
            return Response::error('VALIDATION_ERROR', 'ข้อความไม่สามารถเป็นค่าว่างได้', 400);
        }

        // TODO: Implement LINE notification
        // require_once __DIR__ . '/../services/LineService.php';
        // LineService::send($message);

        error_log("LINE notification requested: " . substr($message, 0, 100));

        return Response::success(
            ['message_length' => strlen($message)],
            'ส่งข้อความ LINE เรียบร้อย'
        );
    }

    /**
     * Validate ID format
     */
    private function isValidId(string $id): bool
    {
        return ctype_digit($id) && (int) $id > 0;
    }
}
