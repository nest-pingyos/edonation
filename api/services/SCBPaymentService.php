<?php
/**
 * SCB Payment Service
 * สำหรับเรียก SCB Open Banking API
 * 
 * Features:
 * - OAuth 2.0 Token Management
 * - Dynamic QR Code Generation
 * - Single-use QR with Expiry
 * 
 * Usage:
 * $scb = new SCBPaymentService();
 * $qr = $scb->createQRCode($amount, $ref1, $ref2);
 */

require_once __DIR__ . '/../config/scb.php';

class SCBPaymentService
{
    private ?string $accessToken = null;
    private ?int $tokenExpiry = null;

    /**
     * Generate unique 32-character request UID
     */
    private function generateRequestUID(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Get OAuth Access Token
     * Token จะถูก cache ไว้จนกว่าจะหมดอายุ
     */
    public function getAccessToken(): ?string
    {
        // ถ้ามี token อยู่และยังไม่หมดอายุ ใช้ต่อได้
        if ($this->accessToken && $this->tokenExpiry && time() < $this->tokenExpiry) {
            return $this->accessToken;
        }

        $requestUID = $this->generateRequestUID();

        $headers = [
            'Content-Type: application/json',
            'requestUId: ' . $requestUID,
            'resourceOwnerId: ' . SCB_API_KEY
        ];

        $body = [
            'applicationKey' => SCB_API_KEY,
            'applicationSecret' => SCB_API_SECRET
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => SCB_OAUTH_URL,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("SCBPaymentService: OAuth cURL error - $error");
            return null;
        }

        $result = json_decode($response, true);

        if ($httpCode === 200 && isset($result['data']['accessToken'])) {
            $this->accessToken = $result['data']['accessToken'];
            // Token มีอายุ 30 นาที แต่เราจะ refresh ก่อน 5 นาที
            $expiresIn = $result['data']['expiresIn'] ?? 1800;
            $this->tokenExpiry = time() + $expiresIn - 300;

            error_log("SCBPaymentService: OAuth token obtained successfully");
            return $this->accessToken;
        }

        error_log("SCBPaymentService: OAuth failed - HTTP $httpCode - " . $response);
        return null;
    }

    /**
     * Create Dynamic QR Code
     * 
     * @param float $amount ยอดเงิน
     * @param string $ref1 เลขอ้างอิงการบริจาค (billPaymentRef1)
     * @param string $ref2 เลขประจำตัวผู้เสียภาษี (Tax ID)
     * @return array|null QR Code data หรือ null ถ้าล้มเหลว
     */
    public function createQRCode(float $amount, string $ref1, string $ref2 = ''): ?array
    {
        // Get access token
        $token = $this->getAccessToken();
        if (!$token) {
            error_log("SCBPaymentService: Cannot create QR - No access token");
            return null;
        }

        $requestUID = $this->generateRequestUID();

        // Calculate expiry date (30 minutes from now)
        $expiryDate = date('Y-m-d H:i:s', strtotime('+' . SCB_QR_EXPIRY_MINUTES . ' minutes'));

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'requestUId: ' . $requestUID,
            'resourceOwnerId: ' . SCB_API_KEY
        ];

        $body = [
            'qrType' => SCB_QR_TYPE,
            'ppType' => SCB_PP_TYPE,
            'ppId' => SCB_BILLER_ID,
            'amount' => number_format($amount, 2, '.', ''),
            'ref1' => $ref1,
            'ref2' => $ref2,
            'ref3' => SCB_REF3,
            'numberOfTimes' => SCB_QR_NUMBER_OF_TIMES,
            'expiryDate' => $expiryDate
        ];

        error_log("SCBPaymentService: Creating QR - Ref1: $ref1, Ref2: $ref2, Amount: $amount");

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => SCB_QR_CREATE_URL,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("SCBPaymentService: QR cURL error - $error");
            return null;
        }

        $result = json_decode($response, true);

        if ($httpCode === 200 && isset($result['data']['qrImage'])) {
            error_log("SCBPaymentService: QR created successfully - Ref1: $ref1");

            return [
                'success' => true,
                'qr_image' => $result['data']['qrImage'],        // Base64 encoded QR image
                'qr_raw_data' => $result['data']['qrRawData'] ?? null,  // Raw QR payload
                'ref1' => $ref1,
                'ref2' => $ref2,
                'amount' => $amount,
                'expiry_date' => $expiryDate,
                'transaction_id' => $result['data']['transactionId'] ?? null
            ];
        }

        // Handle specific errors
        $errorCode = $result['status']['code'] ?? 'UNKNOWN';
        $errorMessage = $result['status']['description'] ?? 'Unknown error';

        error_log("SCBPaymentService: QR creation failed - HTTP $httpCode - Code: $errorCode - $errorMessage");

        return [
            'success' => false,
            'error_code' => $errorCode,
            'error_message' => $errorMessage
        ];
    }

    /**
     * Get QR Code as Data URI (สำหรับแสดงใน <img> tag)
     */
    public function getQRCodeDataURI(float $amount, string $ref1, string $ref2 = ''): ?string
    {
        $result = $this->createQRCode($amount, $ref1, $ref2);

        if ($result && $result['success'] && isset($result['qr_image'])) {
            // SCB returns Base64 image, prepend data URI prefix
            return 'data:image/png;base64,' . $result['qr_image'];
        }

        return null;
    }

    /**
     * Validate SCB Webhook Signature (if needed)
     */
    public function validateWebhook(string $payload, string $signature): bool
    {
        // SCB อาจมีการส่ง signature มาให้ตรวจสอบ
        // ถ้ามี ให้ implement ที่นี่
        return true;
    }
}
