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
            'pdf_url' => "{$basePath}/web/receipts/pdf_maker.php?id={$id}&token={$accessToken}",
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
                    du.address_line,
                    du.province,
                    du.amphure,
                    du.district,
                    du.zip_code,
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
            'address' => $address, // ยังคงส่ง full address ไปเผื่อใช้
            'address_line' => $receipt['address_line'] ?? '',
            'province' => $receipt['province'] ?? '',
            'amphure' => $receipt['amphure'] ?? '',
            'district' => $receipt['district'] ?? '',
            'zip_code' => $receipt['zip_code'] ?? '',
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
                    COALESCE(
                        NULLIF(r.payer_name, ''), 
                        CONCAT(COALESCE(du.first_name, ''), ' ', COALESCE(du.last_name, '')),
                        'ไม่ระบุชื่อ'
                    ) as payer_name,
                    r.amount,
                    r.issued_at,
                    r.bank_transaction_id,
                    r.donation_id,
                    du.project_name,
                    du.first_name,
                    du.last_name,
                    du.status_donat,
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

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format payer_name properly
        foreach ($results as &$row) {
            $name = trim($row['payer_name'] ?? '');
            if (empty($name) || $name === ' ') {
                // Try to build from first_name + last_name
                $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            }
            $row['payer_name'] = !empty($name) ? $name : 'ไม่ระบุชื่อ';
        }

        // Count total
        $countStmt = $this->pdo->query("SELECT COUNT(*) FROM edonation_receipts");
        $total = (int) $countStmt->fetchColumn();

        return Response::success($results, null, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit),
            'api_version' => self::VERSION
        ]);
    }

    /**
     * POST /receipts/generate (Admin)
     * สร้างใบเสร็จใหม่ - รองรับทั้งเชื่อมกับ donation ที่มีอยู่ และสร้าง donation ใหม่
     */
    private function generate(): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        // Validate required fields
        $requiredFields = ['first_name', 'last_name', 'id_card', 'address', 'project_number', 'amount'];
        $missing = [];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
        }
        if (!empty($missing)) {
            return Response::error('VALIDATION_ERROR', 'กรุณากรอกข้อมูลให้ครบ: ' . implode(', ', $missing));
        }

        // Validate ID card format
        if (strlen(preg_replace('/\D/', '', $data['id_card'])) !== 13) {
            return Response::error('VALIDATION_ERROR', 'เลขบัตรประชาชนต้องมี 13 หลัก');
        }

        // Validate amount
        if (floatval($data['amount']) <= 0) {
            return Response::error('VALIDATION_ERROR', 'จำนวนเงินต้องมากกว่า 0');
        }

        try {
            $this->pdo->beginTransaction();

            $donationId = $data['donation_id'] ?? null;
            $payerName = trim(($data['title'] ?? '') . ' ' . $data['first_name'] . ' ' . $data['last_name']);

            // If no donation_id, create new donation record
            if (!$donationId) {
                // Get project name
                $projectStmt = $this->pdo->prepare("SELECT project_name FROM edonation_projects WHERE project_number = :pn");
                $projectStmt->execute([':pn' => $data['project_number']]);
                $project = $projectStmt->fetch();
                $projectName = $project['project_name'] ?? $data['project_name'] ?? $data['project_number'];

                // Generate billPaymentRef1
                $year = date('Y') + 543;
                $rand = rand(10000, 99999);
                $projNumRaw = preg_replace('/\D/', '', $data['project_number']);
                $projNum = str_pad(substr($projNumRaw, 0, 6), 6, '0', STR_PAD_LEFT);
                $ref1 = $year . $projNum . $rand;

                // Create donation record
                $donationStmt = $this->pdo->prepare("
                    INSERT INTO edonation_donat_user (
                        billPaymentRef1, project_number, project_name, type, phone, amount, 
                        fiscal_year, status_donat, payby, receiptDate,
                        need_receipt, first_name, last_name, id_card, receipt_address, shipping_address
                    ) VALUES (
                        :ref1, :project_number, :project_name, :type, :phone, :amount, 
                        :fiscal_year, 'completed', :payby, :receipt_date,
                        1, :first_name, :last_name, :id_card, :address, :address
                    )
                ");

                $donationStmt->execute([
                    ':ref1' => $ref1,
                    ':project_number' => $data['project_number'],
                    ':project_name' => $projectName,
                    ':type' => $data['type'] ?? 'manual',
                    ':phone' => $data['phone'] ?? '',
                    ':amount' => $data['amount'],
                    ':fiscal_year' => $year,
                    ':payby' => $data['payment_method'] ?? 'เงินสด',
                    ':receipt_date' => $data['donation_date'] ?? date('Y-m-d'),
                    ':first_name' => $data['first_name'],
                    ':last_name' => $data['last_name'],
                    ':id_card' => preg_replace('/\D/', '', $data['id_card']),
                    ':address' => $data['address']
                ]);

                $donationId = $this->pdo->lastInsertId();
            }

            // Generate receipt number Format: YYYY-EXXXX
            $year = date('Y') + 543; // พ.ศ.
            $prefix = $year . '-E';

            // Lock for safe increment
            $maxStmt = $this->pdo->prepare("SELECT MAX(receipt_no) as max_no FROM edonation_receipts WHERE receipt_no LIKE :prefix FOR UPDATE");
            $maxStmt->execute([':prefix' => $prefix . '%']);
            $maxRow = $maxStmt->fetch();

            $nextNum = 1;
            if ($maxRow && $maxRow['max_no']) {
                $numPart = preg_replace('/^\d{4}-E/', '', $maxRow['max_no']);
                $nextNum = intval($numPart) + 1;
            }
            $receiptNo = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            // Create receipt record
            $receiptStmt = $this->pdo->prepare("
                INSERT INTO edonation_receipts (donation_id, bank_transaction_id, receipt_no, payer_name, amount, issued_at)
                VALUES (:donation_id, :bank_transaction_id, :receipt_no, :payer_name, :amount, NOW())
            ");

            $receiptStmt->execute([
                ':donation_id' => $donationId,
                ':bank_transaction_id' => $data['bank_transaction_id'] ?? null,
                ':receipt_no' => $receiptNo,
                ':payer_name' => $payerName,
                ':amount' => $data['amount']
            ]);

            $receiptId = $this->pdo->lastInsertId();

            // Store email for later if provided and send_email is true
            if (!empty($data['email']) && !empty($data['send_email'])) {
                // TODO: Queue email sending
                error_log("Receipt {$receiptNo} created for {$data['email']} - email would be sent");
            }

            $this->pdo->commit();

            // Create access token for PDF
            $accessToken = bin2hex(random_bytes(32));
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['pdf_access_tokens'][$receiptId] = [
                'token' => $accessToken,
                'expire_at' => time() + 3600 // 1 hour for Admin
            ];

            $basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';
            return Response::success([
                'id' => (int) $receiptId,
                'donation_id' => (int) $donationId,
                'receipt_no' => $receiptNo,
                'payer_name' => $payerName,
                'amount' => floatval($data['amount']),
                'pdf_url' => "{$basePath}/web/receipts/pdf_maker.php?id={$receiptId}&token={$accessToken}",
                'access_token' => $accessToken,
                'api_version' => self::VERSION
            ], 'ออกใบเสร็จสำเร็จ');

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Receipt generate error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'ไม่สามารถออกใบเสร็จได้: ' . $e->getMessage(), 500);
        }
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
