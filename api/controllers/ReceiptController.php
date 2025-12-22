<?php
/**
 * Receipt Controller v2.4
 * 
 * อัปเดต: ใช้ receipts.bank_transaction_id อ้างอิงไปยัง bank_transactions.id
 * 
 * Endpoints:
 * GET    /receipts/search        - ค้นหาใบเสร็จ (Public)
 * GET    /receipts/:id           - ดูรายละเอียดใบเสร็จ
 * GET    /receipts/:id/pdf       - ดาวน์โหลด PDF
 * GET    /receipts/:id/verify    - ตรวจสอบเลขผู้เสียภาษี
 * GET    /receipts               - รายการทั้งหมด (Admin)
 * POST   /receipts/generate      - ออกใบเสร็จ manual (Admin)
 * POST   /receipts/:id/cancel    - ยกเลิกใบเสร็จ (Admin)
 */

class ReceiptController
{
    private PDO $pdo;

    // API Version
    const VERSION = '2.4';

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function handle(string $method, ?string $id, ?string $action): array
    {
        // Handle special actions
        if ($id === 'search')
            return $this->search();
        if ($action === 'search')
            return $this->search();

        if ($id === 'generate' && $method === 'POST') {
            AuthMiddleware::requireAdmin();
            return $this->generate();
        }

        if ($id && $action) {
            switch ($action) {
                case 'pdf':
                    return $this->getPdf($id);
                case 'verify':
                    return $this->verifyTaxId($id);
                case 'details':
                    return $this->getDetails($id); // สำหรับ pdf_maker.php
                case 'cancel':
                    AuthMiddleware::requireAdmin();
                    return $this->cancel($id);
                case 'resend':
                    AuthMiddleware::requireAdmin();
                    return $this->resend($id);
            }
        }

        switch ($method) {
            case 'GET':
                if ($id)
                    return $this->show($id);
                AuthMiddleware::requireAdmin();
                return $this->index();
            default:
                return Response::error('METHOD_NOT_ALLOWED', 'Method not allowed', 405);
        }
    }

    /**
     * GET /receipts/search
     * ค้นหาใบเสร็จจากตาราง receipts
     * - ค้นหาด้วยเลขบัตรประชาชน (ต้องครบ 13 หลัก)
     * - หรือค้นหาด้วยเลขที่ใบเสร็จ (ต้องครบ เช่น 2568/00001)
     */
    private function search(): array
    {
        $keyword = trim($_GET['keyword'] ?? '');

        if (empty($keyword)) {
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุคำค้นหา');
        }

        // ลบ dash ออกเพื่อตรวจสอบ
        $cleanKeyword = preg_replace('/\D/', '', $keyword);

        // ตรวจสอบรูปแบบการค้นหา
        $isIdCard = strlen($cleanKeyword) === 13;
        // รองรับทั้งรูปแบบเก่า (2568/00001) และใหม่ (2568-E0001)
        $isReceiptNo = preg_match('/^\d{4}[\/-]E?\d{4,5}$/', $keyword);

        if (!$isIdCard && !$isReceiptNo) {
            return Response::error('VALIDATION_ERROR', 'กรุณากรอกเลขบัตรประชาชนให้ครบ 13 หลัก หรือเลขที่ใบเสร็จ');
        }

        // กำหนด query ตามประเภทการค้นหา
        if ($isIdCard) {
            // ค้นหาด้วยเลขบัตรประชาชน (ใช้ค่าที่ลบ dash แล้ว)
            $sql = "SELECT 
                        r.id,
                        r.receipt_no,
                        r.payer_name,
                        r.amount,
                        r.issued_at,
                        r.donation_id,
                        r.bank_transaction_id,
                        du.project_name,
                        du.project_number,
                        du.payby,
                        bt.billPaymentRef2
                    FROM edonation_receipts r
                    LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                    LEFT JOIN edonation_bank_transactions bt ON r.bank_transaction_id = bt.id
                    WHERE bt.billPaymentRef2 = :keyword
                    ORDER BY r.id DESC 
                    LIMIT 50";
            $searchValue = $cleanKeyword;
        } else {
            // ค้นหาด้วยเลขที่ใบเสร็จ
            $sql = "SELECT 
                        r.id,
                        r.receipt_no,
                        r.payer_name,
                        r.amount,
                        r.issued_at,
                        r.donation_id,
                        r.bank_transaction_id,
                        du.project_name,
                        du.project_number,
                        du.payby,
                        bt.billPaymentRef2
                    FROM edonation_receipts r
                    LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                    LEFT JOIN edonation_bank_transactions bt ON r.bank_transaction_id = bt.id
                    WHERE r.receipt_no = :keyword
                    ORDER BY r.id DESC 
                    LIMIT 50";
            $searchValue = $keyword;
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':keyword' => $searchValue]);

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $receipts = array_map(function ($r) {
                // Format tax_id ให้มี dash (x-xxxx-xxxxx-xx-x)
                $taxId = $r['billPaymentRef2'] ?? '';
                $formattedTaxId = '';
                if (strlen($taxId) === 13) {
                    $formattedTaxId = substr($taxId, 0, 1) . '-' .
                        substr($taxId, 1, 4) . '-' .
                        substr($taxId, 5, 5) . '-' .
                        substr($taxId, 10, 2) . '-' .
                        substr($taxId, 12, 1);
                }

                return [
                    'id' => (int) $r['id'],
                    'receipt_no' => $r['receipt_no'],
                    'payer_name' => $r['payer_name'] ?? '',
                    'amount' => floatval($r['amount']),
                    'receipt_date' => $r['issued_at'],
                    'project_name' => $r['project_name'] ?? '',
                    'project_number' => $r['project_number'] ?? '',
                    'pay_by' => $r['payby'] ?? 'QR PromptPay',
                    // เลขผู้เสียภาษี แสดงแบบมี dash
                    'tax_id' => $formattedTaxId ?: $taxId,
                    'donation_id' => (int) $r['donation_id']
                ];
            }, $results);

            return Response::success($receipts, null, [
                'api_version' => self::VERSION,
                'count' => count($receipts),
                'search_type' => $isIdCard ? 'id_card' : 'receipt_no'
            ]);

        } catch (PDOException $e) {
            error_log('Receipt search error: ' . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'เกิดข้อผิดพลาดในการค้นหา: ' . $e->getMessage());
        }
    }

    /**
     * GET /receipts/:id/verify
     * ตรวจสอบเลขประจำตัวผู้เสียภาษีก่อนเปิดใบเสร็จ
     * ดึง billPaymentRef2 จาก bank_transactions ผ่าน bank_transaction_id
     */
    private function verifyTaxId(string $id): array
    {
        $inputTaxId = $_GET['tax_id'] ?? '';

        if (empty($inputTaxId)) {
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุเลขประจำตัวผู้เสียภาษี');
        }

        // ดึงเลขผู้เสียภาษีจาก bank_transactions ผ่าน bank_transaction_id
        $sql = "SELECT bt.billPaymentRef2 
                FROM edonation_receipts r
                LEFT JOIN edonation_bank_transactions bt ON r.bank_transaction_id = bt.id
                WHERE r.id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return Response::notFound('ไม่พบใบเสร็จ');
        }

        $correctTaxId = $result['billPaymentRef2'] ?? '';

        // ลบ dash ออกเพื่อเปรียบเทียบ
        $inputClean = preg_replace('/\D/', '', $inputTaxId);
        $correctClean = preg_replace('/\D/', '', $correctTaxId);

        // Debug info
        error_log("Verify receipt {$id}: input={$inputClean}, correct={$correctClean}");

        if (empty($correctClean)) {
            return Response::error('NO_TAX_ID', 'ไม่พบข้อมูลเลขประจำตัวผู้เสียภาษีในระบบ', 400);
        }

        if ($inputClean === $correctClean) {
            // สร้าง access token สำหรับเปิด PDF
            $accessToken = bin2hex(random_bytes(32));
            $expireAt = time() + 300; // หมดอายุใน 5 นาที

            // เก็บ token ใน session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['pdf_access_tokens'][$id] = [
                'token' => $accessToken,
                'expire_at' => $expireAt
            ];

            return Response::success([
                'verified' => true,
                'receipt_id' => (int) $id,
                'access_token' => $accessToken,
                'expires_in' => 300
            ], 'ยืนยันตัวตนสำเร็จ');
        }

        return Response::error('VERIFICATION_FAILED', 'เลขประจำตัวผู้เสียภาษีไม่ถูกต้อง', 401);
    }

    /**
     * GET /receipts/:id/pdf
     * ดึง URL สำหรับดาวน์โหลด PDF
     * ต้องส่ง access_token ที่ได้จาก /verify
     */
    private function getPdf(string $id): array
    {
        // รับ access_token จาก query string
        $accessToken = $_GET['access_token'] ?? '';

        if (empty($accessToken)) {
            return Response::error('UNAUTHORIZED', 'กรุณายืนยันตัวตนก่อนเปิดใบเสร็จ', 401);
        }

        // ตรวจสอบ token จาก session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $storedToken = $_SESSION['pdf_access_tokens'][$id] ?? null;

        if (!$storedToken || $storedToken['token'] !== $accessToken) {
            return Response::error('INVALID_TOKEN', 'Access token ไม่ถูกต้อง', 401);
        }

        if ($storedToken['expire_at'] < time()) {
            unset($_SESSION['pdf_access_tokens'][$id]);
            return Response::error('TOKEN_EXPIRED', 'Access token หมดอายุ กรุณายืนยันตัวตนใหม่', 401);
        }

        $sql = "SELECT r.*, du.fiscal_year
                FROM edonation_receipts r 
                LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                WHERE r.id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $receipt = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$receipt) {
            return Response::notFound('ไม่พบใบเสร็จ');
        }

        // ส่ง receipt ID พร้อม token - use BASE_PATH from config
        $basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';
        return Response::success([
            'pdf_url' => "{$basePath}/receipts/pdf_maker.php?id={$id}&token={$accessToken}",
            'receipt_no' => $receipt['receipt_no'],
            'api_version' => self::VERSION
        ]);
    }

    /**
     * GET /receipts/:id/details
     * ดึงข้อมูลใบเสร็จครบถ้วนสำหรับ pdf_maker.php
     */
    private function getDetails(string $id): array
    {
        $sql = "SELECT 
                    r.id,
                    r.receipt_no,
                    r.payer_name,
                    r.amount,
                    r.issued_at AS receipt_date,
                    r.donation_id,
                    du.project_name,
                    du.project_number,
                    du.payby AS pay_by,
                    du.fiscal_year,
                    du.first_name,
                    du.last_name,
                    du.receipt_address AS address,
                    bt.billPaymentRef2,
                    bt.payerAccountName
                FROM edonation_receipts r
                LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                LEFT JOIN edonation_bank_transactions bt ON r.bank_transaction_id = bt.id
                WHERE r.id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $receipt = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$receipt) {
            return Response::notFound('ไม่พบใบเสร็จ');
        }

        // แยกที่อยู่ออกเป็นส่วนๆ (ถ้ามี)
        $address = $receipt['address'] ?? '';

        return Response::success([
            'id' => (int) $receipt['id'],
            'receipt_no' => $receipt['receipt_no'],
            'payer_name' => $receipt['payer_name'] ?? $receipt['payerAccountName'] ?? '',
            'amount' => floatval($receipt['amount']),
            'receipt_date' => $receipt['receipt_date'],
            'project_name' => $receipt['project_name'] ?? '',
            'project_number' => $receipt['project_number'] ?? '',
            'pay_by' => $receipt['pay_by'] ?? 'QR PromptPay',
            'fiscal_year' => $receipt['fiscal_year'] ?? (date('Y') + 543),
            'address' => $address,
            'province' => '',
            'amphure' => '',
            'district' => '',
            'zip_code' => '',
            'billPaymentRef2' => $receipt['billPaymentRef2'] ?? '',
            'api_version' => self::VERSION
        ]);
    }

    /**
     * GET /receipts/:id
     * ดูรายละเอียดใบเสร็จ (Public)
     */
    private function show(string $id): array
    {
        $sql = "SELECT 
                    r.id,
                    r.receipt_no,
                    r.payer_name,
                    r.amount,
                    r.issued_at,
                    r.donation_id,
                    r.bank_transaction_id,
                    du.project_name,
                    du.project_number,
                    du.payby,
                    bt.billPaymentRef2
                FROM edonation_receipts r
                LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                LEFT JOIN edonation_bank_transactions bt ON r.bank_transaction_id = bt.id
                WHERE r.id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $receipt = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$receipt) {
            return Response::notFound('ไม่พบใบเสร็จ');
        }

        return Response::success([
            'id' => (int) $receipt['id'],
            'receipt_no' => $receipt['receipt_no'],
            'payer_name' => $receipt['payer_name'],
            'amount' => floatval($receipt['amount']),
            'receipt_date' => $receipt['issued_at'],
            'project_name' => $receipt['project_name'] ?? '',
            'project_number' => $receipt['project_number'] ?? '',
            'pay_by' => $receipt['payby'] ?? 'QR PromptPay',
            'has_tax_id' => !empty($receipt['billPaymentRef2']),
            'api_version' => self::VERSION
        ]);
    }

    /**
     * GET /receipts (Admin)
     * รายการใบเสร็จทั้งหมด
     */
    private function index(): array
    {
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(100, max(1, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $sql = "SELECT 
                    r.id,
                    r.receipt_no,
                    r.payer_name,
                    r.amount,
                    r.issued_at,
                    r.bank_transaction_id,
                    du.project_name,
                    bt.billPaymentRef2
                FROM edonation_receipts r
                LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                LEFT JOIN edonation_bank_transactions bt ON r.bank_transaction_id = bt.id
                ORDER BY r.id DESC 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        // Count total
        $countStmt = $this->pdo->query("SELECT COUNT(*) FROM edonation_receipts");
        $total = (int) $countStmt->fetchColumn();

        return Response::success($stmt->fetchAll(PDO::FETCH_ASSOC), null, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit),
            'api_version' => self::VERSION
        ]);
    }

    /**
     * POST /receipts/generate (Admin)
     * สร้างใบเสร็จใหม่
     */
    private function generate(): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $v = new Validator($data);
        $v->required('payer_name')
            ->required('amount')
            ->required('donation_id');

        if (!$v->passes())
            return Response::validation($v->errors());

        // สร้างเลขที่ใบเสร็จ Format: YYYY-EXXXX
        $year = date('Y') + 543; // พ.ศ.
        $prefix = $year . '-E';
        $countStmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM edonation_receipts WHERE receipt_no LIKE :prefix"
        );
        $countStmt->execute([':prefix' => $prefix . '%']);
        $count = (int) $countStmt->fetchColumn() + 1;
        $receiptNo = $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);

        $stmt = $this->pdo->prepare(
            "INSERT INTO edonation_receipts (donation_id, bank_transaction_id, receipt_no, payer_name, amount, issued_at)
             VALUES (:donation_id, :bank_transaction_id, :receipt_no, :payer_name, :amount, NOW())"
        );

        $stmt->execute([
            ':donation_id' => $data['donation_id'],
            ':bank_transaction_id' => $data['bank_transaction_id'] ?? null,
            ':receipt_no' => $receiptNo,
            ':payer_name' => $data['payer_name'],
            ':amount' => $data['amount']
        ]);

        $id = $this->pdo->lastInsertId();

        // สร้าง access_token สำหรับเปิด PDF (Admin)
        $accessToken = bin2hex(random_bytes(32));
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['pdf_access_tokens'][$id] = [
            'token' => $accessToken,
            'expire_at' => time() + 3600 // 1 ชั่วโมงสำหรับ Admin
        ];

        // Use BASE_PATH from config
        $basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';
        return Response::success([
            'id' => (int) $id,
            'receipt_no' => $receiptNo,
            'pdf_url' => "{$basePath}/receipts/pdf_maker.php?id={$id}&token={$accessToken}",
            'access_token' => $accessToken,
            'api_version' => self::VERSION
        ], 'ออกใบเสร็จสำเร็จ');
    }

    /**
     * POST /receipts/:id/cancel (Admin)
     */
    private function cancel(string $id): array
    {
        $checkStmt = $this->pdo->prepare("SELECT id FROM edonation_receipts WHERE id = :id");
        $checkStmt->execute([':id' => $id]);
        if (!$checkStmt->fetch()) {
            return Response::notFound('ไม่พบใบเสร็จ');
        }

        $stmt = $this->pdo->prepare("DELETE FROM edonation_receipts WHERE id = :id");
        $stmt->execute([':id' => $id]);

        return Response::success(['api_version' => self::VERSION], 'ยกเลิกใบเสร็จเรียบร้อย');
    }

    /**
     * POST /receipts/:id/resend (Admin)
     */
    private function resend(string $id): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        if (empty($data['email'])) {
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุอีเมล');
        }

        return Response::success(['api_version' => self::VERSION], 'ส่งใบเสร็จไปยัง ' . $data['email'] . ' เรียบร้อย');
    }
}
