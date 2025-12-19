<?php
/**
 * LINE Notification Service
 * สำหรับส่งแจ้งเตือนผ่าน LINE OA
 * 
 * การใช้งาน:
 * $notifier = new LineNotificationService();
 * $notifier->sendPaymentSuccessNotification($donationId, $amount, $projectName);
 */

class LineNotificationService
{
    private PDO $pdo;
    private bool $isEnabled = true;

    // ==========================================
    // ตั้งค่าคงที่ (Settings)
    // ==========================================
    private const LINE_API_URL = 'https://mis.nurse.cmu.ac.th/LineConnext/API/SendLineOA';
    private const LINE_API_KEY = 'FON_ConnectAPI01';
    private const PROGRAM_NAME = 'e-Donation';
    private const MESSAGE_COLOR = '#FB974E';

    // ใช้ OFFICE_URL จาก env config (รองรับ Production และ Testing)
    private function getOfficeBaseUrl(): string
    {
        return defined('OFFICE_URL') ? OFFICE_URL : 'https://app.nurse.cmu.ac.th/edonation/office';
    }

    public function __construct()
    {
        // ตั้งค่า Timezone เป็นเวลาไทย
        date_default_timezone_set('Asia/Bangkok');

        $this->pdo = Database::getInstance();
    }

    /**
     * เปิด/ปิดการแจ้งเตือน
     */
    public function setEnabled(bool $enabled): void
    {
        $this->isEnabled = $enabled;
    }

    /**
     * ดึงรายชื่อผู้รับการแจ้งเตือนตามประเภท
     */
    private function getRecipients(string $notificationType): array
    {
        try {
            $sql = "SELECT recipient_email, cmu_account 
                    FROM edonation_notification_recipients 
                    WHERE notification_type = :type AND is_active = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':type' => $notificationType]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('LineNotificationService: Failed to get recipients - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * ส่งแจ้งเตือน LINE
     */
    public function send(string $notificationType, string $message, ?string $weblink = null, ?int $referenceId = null): array
    {
        $results = [];

        // ตรวจสอบว่าเปิดใช้งานหรือไม่
        if (!$this->isEnabled) {
            return [
                'success' => false,
                'message' => 'LINE notification is disabled',
                'results' => []
            ];
        }

        // ดึงรายชื่อผู้รับ
        $recipients = $this->getRecipients($notificationType);

        if (empty($recipients)) {
            error_log('LineNotificationService: No recipients for type: ' . $notificationType);
            return [
                'success' => false,
                'message' => 'No recipients found for notification type: ' . $notificationType,
                'results' => []
            ];
        }

        // ส่งแจ้งเตือนถึงผู้รับแต่ละคน
        foreach ($recipients as $recipient) {
            // ใช้ cmu_account ส่งไป LINE API (field: email)
            $cmuAccount = $recipient['cmu_account'];

            if (empty($cmuAccount)) {
                error_log('LineNotificationService: cmu_account is empty for recipient');
                continue;
            }

            $sendResult = $this->sendToRecipient(
                $cmuAccount,  // ใช้ cmu_account ส่งไป email field ของ API
                $message,
                $weblink
            );

            // บันทึก log (เก็บ cmu_account)
            $this->logNotification(
                $notificationType,
                $cmuAccount,
                $message,
                $sendResult['success'] ? 'sent' : 'failed',
                $sendResult['response'] ?? '',
                $referenceId
            );

            $results[] = [
                'cmu_account' => $cmuAccount,
                'success' => $sendResult['success'],
                'message' => $sendResult['message'] ?? ''
            ];
        }

        $successCount = count(array_filter($results, fn($r) => $r['success']));

        return [
            'success' => $successCount > 0,
            'message' => "Sent to {$successCount}/" . count($results) . " recipients",
            'results' => $results
        ];
    }

    /**
     * ส่งแจ้งเตือนไปยังผู้รับคนเดียว
     * @param string $cmuAccount - CMU Account ที่จะส่งไป LINE API
     */
    private function sendToRecipient(string $cmuAccount, string $message, ?string $weblink): array
    {
        try {
            $postData = [
                'program' => self::PROGRAM_NAME,
                'email' => $cmuAccount,  // ใช้ cmu_account ส่งไป email field
                'message' => $message,
                'color' => self::MESSAGE_COLOR
            ];

            if ($weblink) {
                $postData['weblink'] = $weblink;
            }

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => self::LINE_API_URL,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($postData),
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . self::LINE_API_KEY,
                    'Content-Type: application/json'
                ],
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if (curl_errno($curl)) {
                $error = curl_error($curl);
                curl_close($curl);
                error_log("LineNotificationService: cURL error - {$error}");
                return [
                    'success' => false,
                    'message' => 'cURL error: ' . $error,
                    'response' => $error
                ];
            }

            curl_close($curl);

            // ตรวจสอบ response
            $success = $httpCode >= 200 && $httpCode < 300;

            if ($success) {
                error_log("LineNotificationService: Message sent to {$cmuAccount}");
            } else {
                error_log("LineNotificationService: Failed to send to {$cmuAccount}. HTTP {$httpCode}. Response: {$response}");
            }

            return [
                'success' => $success,
                'message' => $success ? 'Sent successfully' : "HTTP error: {$httpCode}",
                'response' => $response
            ];
        } catch (Exception $e) {
            error_log("LineNotificationService: Exception - " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
                'response' => $e->getMessage()
            ];
        }
    }

    /**
     * บันทึก log การแจ้งเตือน
     */
    private function logNotification(
        string $notificationType,
        string $cmuAccount,
        string $message,
        string $status,
        string $response,
        ?int $referenceId
    ): void {
        try {
            $sql = "INSERT INTO edonation_notification_logs 
                    (notification_type, recipient_email, message, status, response, reference_id)
                    VALUES (:type, :cmu_account, :message, :status, :response, :ref_id)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':type' => $notificationType,
                ':cmu_account' => $cmuAccount,
                ':message' => $message,
                ':status' => $status,
                ':response' => $response,
                ':ref_id' => $referenceId
            ]);
        } catch (Exception $e) {
            error_log('LineNotificationService: Failed to log notification - ' . $e->getMessage());
        }
    }

    // ============================================
    // Convenience Methods สำหรับประเภทต่างๆ
    // ============================================

    /**
     * แจ้งเตือนเมื่อชำระเงินสำเร็จ
     */
    public function sendPaymentSuccessNotification(int $donationId, float $amount, string $projectName, string $donorName = ''): array
    {
        $formattedAmount = number_format($amount, 2);

        $message = "แจ้งเตือนการชำระเงินบริจาค\n";
        $message .= "━━━━━━━━━━━━\n";
        $message .= "โครงการ: {$projectName}\n";
        $message .= "จำนวน: {$formattedAmount} บาท\n";

        if ($donorName) {
            $message .= "ผู้บริจาค: {$donorName}\n";
        }

        // รูปแบบวันที่ไทย: วัน เดือนย่อ ปีพ.ศ. เวลา
        $thaiMonths = [
            '',
            'ม.ค.',
            'ก.พ.',
            'มี.ค.',
            'เม.ย.',
            'พ.ค.',
            'มิ.ย.',
            'ก.ค.',
            'ส.ค.',
            'ก.ย.',
            'ต.ค.',
            'พ.ย.',
            'ธ.ค.'
        ];
        $day = date('j');
        $month = $thaiMonths[(int) date('n')];
        $year = date('Y') + 543;
        $time = date('H:i');
        $message .= "เวลา: {$day} {$month} {$year} {$time}";

        $weblink = $this->getOfficeBaseUrl() . '/finance/donation_detail.php?id=' . urlencode($donationId);

        return $this->send('payment_success', $message, $weblink, $donationId);
    }

    /**
     * แจ้งเตือนเมื่อมีการบริจาคใหม่ (ยังไม่ชำระเงิน)
     */
    public function sendNewDonationNotification(int $donationId, float $amount, string $projectName): array
    {
        $formattedAmount = number_format($amount, 2);

        $message = "มีการบริจาคใหม่!\n";
        $message .= "━━━━━━━━━━━━\n";
        $message .= "โครงการ: {$projectName}\n";
        $message .= "จำนวน: {$formattedAmount} บาท\n";
        $message .= "สถานะ: รอชำระเงิน";

        $weblink = $this->getOfficeBaseUrl() . '/finance/donation_detail.php?id=' . urlencode($donationId);

        return $this->send('new_donation', $message, $weblink, $donationId);
    }

    /**
     * แจ้งเตือนทั่วไป (custom message)
     */
    public function sendCustomNotification(string $notificationType, string $message, ?string $weblink = null, ?int $referenceId = null): array
    {
        return $this->send($notificationType, $message, $weblink, $referenceId);
    }

    // ============================================
    // Helper Methods
    // ============================================

    /**
     * ตรวจสอบว่าระบบแจ้งเตือนเปิดใช้งานอยู่หรือไม่
     */
    public function isNotificationEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * ดึง recipients ทั้งหมด
     */
    public function getAllRecipients(): array
    {
        try {
            $sql = "SELECT * FROM edonation_notification_recipients ORDER BY notification_type, id";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * ดึง logs
     */
    public function getLogs(int $limit = 50, int $offset = 0): array
    {
        try {
            $sql = "SELECT * FROM edonation_notification_logs ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
