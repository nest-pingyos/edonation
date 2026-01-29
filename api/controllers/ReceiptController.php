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
    const VERSION = '2.0';

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
                case 'admin_pdf':
                    // Admin สามารถเปิด PDF ได้โดยไม่ต้องยืนยันตัวตน
                    AuthMiddleware::requireAdmin();
                    return $this->getAdminPdf($id);
                case 'verify':
                    return $this->verifyTaxId($id);
                case 'details':
                    return $this->getDetails($id); // สำหรับ pdf_completed.php
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
            case 'PUT':
                AuthMiddleware::requireAdmin();
                if (!$id)
                    return Response::error('VALIDATION_ERROR', 'กรุณาระบุ ID ใบเสร็จ');
                return $this->update($id);
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
            // ค้นหาจาก billPaymentRef2 หรือ id_card
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
                        bt.billPaymentRef2,
                        du.id_card
                    FROM edonation_receipts r
                    LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                    LEFT JOIN edonation_bank_transactions bt ON r.bank_transaction_id = bt.id
                    WHERE bt.billPaymentRef2 = :keyword OR du.id_card = :keyword2
                    ORDER BY r.receipt_no DESC 
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
                    ORDER BY r.receipt_no DESC 
                    LIMIT 50";
            $searchValue = $keyword;
        }

        try {
            $stmt = $this->pdo->prepare($sql);

            // ถ้าเป็นการค้นหาด้วยเลขบัตร ต้องส่ง 2 parameters
            if ($isIdCard) {
                $stmt->execute([':keyword' => $searchValue, ':keyword2' => $searchValue]);
            } else {
                $stmt->execute([':keyword' => $searchValue]);
            }

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
     * ดึง billPaymentRef2 จาก bank_transactions หรือ id_card จาก donat_user
     */
    private function verifyTaxId(string $id): array
    {
        $inputTaxId = $_GET['tax_id'] ?? '';

        if (empty($inputTaxId)) {
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุเลขประจำตัวผู้เสียภาษี');
        }

        // ดึงเลขผู้เสียภาษีจาก bank_transactions หรือ id_card จาก donat_user
        $sql = "SELECT 
                    bt.billPaymentRef2,
                    du.id_card
                FROM edonation_receipts r
                LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                LEFT JOIN edonation_bank_transactions bt ON r.bank_transaction_id = bt.id
                WHERE r.id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return Response::notFound('ไม่พบใบเสร็จ');
        }

        // ใช้ billPaymentRef2 ก่อน ถ้าไม่มีให้ใช้ id_card
        $correctTaxId = $result['billPaymentRef2'] ?? $result['id_card'] ?? '';

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
            $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            // เก็บ token ใน database แทน session
            try {
                // ลบ token เก่าที่หมดอายุ
                $this->pdo->exec("DELETE FROM edonation_pdf_access_tokens WHERE expires_at < NOW()");

                // ลบ token เก่าของ receipt นี้
                $deleteStmt = $this->pdo->prepare("DELETE FROM edonation_pdf_access_tokens WHERE receipt_id = :receipt_id");
                $deleteStmt->execute([':receipt_id' => $id]);

                // Insert token ใหม่
                $insertStmt = $this->pdo->prepare("
                    INSERT INTO edonation_pdf_access_tokens (receipt_id, token, expires_at) 
                    VALUES (:receipt_id, :token, :expires_at)
                ");
                $insertStmt->execute([
                    ':receipt_id' => $id,
                    ':token' => $accessToken,
                    ':expires_at' => $expiresAt
                ]);
            } catch (PDOException $e) {
                error_log("Failed to store PDF token: " . $e->getMessage());
                return Response::error('SERVER_ERROR', 'ไม่สามารถสร้าง token ได้', 500);
            }

            return Response::success([
                'verified' => true,
                'receipt_id' => (int) $id,
                'access_token' => $accessToken,
                'expires_in' => 300
            ], 'ยืนยันตัวตนสำเร็จ');
        }

        error_log("Verify FAILED for receipt {$id}: input={$inputClean}, correct={$correctClean}");
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

        // ตรวจสอบ token จาก database
        $tokenStmt = $this->pdo->prepare("
            SELECT id, receipt_id, token, expires_at, used 
            FROM edonation_pdf_access_tokens 
            WHERE receipt_id = :receipt_id AND token = :token
        ");
        $tokenStmt->execute([':receipt_id' => $id, ':token' => $accessToken]);
        $tokenData = $tokenStmt->fetch(PDO::FETCH_ASSOC);

        if (!$tokenData) {
            return Response::error('INVALID_TOKEN', 'Access token ไม่ถูกต้อง', 401);
        }

        if (strtotime($tokenData['expires_at']) < time()) {
            // ลบ token ที่หมดอายุ
            $this->pdo->prepare("DELETE FROM edonation_pdf_access_tokens WHERE id = :id")->execute([':id' => $tokenData['id']]);
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
        $pdfFile = ((int) ($receipt['status'] ?? 0) === 2) ? 'pdf_cancelled.php' : 'pdf_completed.php';

        return Response::success([
            'pdf_url' => "{$basePath}/receipts/{$pdfFile}?token={$accessToken}",
            'receipt_no' => $receipt['receipt_no'],
            'api_version' => self::VERSION
        ]);
    }

    /**
     * GET /receipts/:id/admin_pdf (Admin Only)
     * ดึง URL สำหรับดาวน์โหลด PDF โดยไม่ต้องยืนยันตัวตน
     * สร้าง token ให้อัตโนมัติ
     */
    private function getAdminPdf(string $id): array
    {
        // ตรวจสอบว่ามีใบเสร็จอยู่หรือไม่
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

        // สร้าง admin token ใหม่สำหรับเปิด PDF
        $accessToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        try {
            // ลบ token เก่าของ receipt นี้
            $deleteStmt = $this->pdo->prepare("DELETE FROM edonation_pdf_access_tokens WHERE receipt_id = :receipt_id");
            $deleteStmt->execute([':receipt_id' => $id]);

            // Insert token ใหม่
            $insertStmt = $this->pdo->prepare("
                INSERT INTO edonation_pdf_access_tokens (receipt_id, token, expires_at) 
                VALUES (:receipt_id, :token, :expires_at)
            ");
            $insertStmt->execute([
                ':receipt_id' => $id,
                ':token' => $accessToken,
                ':expires_at' => $expiresAt
            ]);
        } catch (PDOException $e) {
            error_log("Failed to create admin PDF token: " . $e->getMessage());
            return Response::error('SERVER_ERROR', 'ไม่สามารถสร้าง token ได้', 500);
        }

        // ส่ง receipt URL พร้อม token
        $basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';
        $pdfFile = ((int) ($receipt['status'] ?? 0) === 2) ? 'pdf_cancelled.php' : 'pdf_completed.php';

        return Response::success([
            'pdf_url' => "{$basePath}/receipts/{$pdfFile}?token={$accessToken}",
            'receipt_no' => $receipt['receipt_no'],
            'api_version' => self::VERSION
        ]);
    }

    /**
     * GET /receipts/:id/details
     * ดึงข้อมูลใบเสร็จครบถ้วนสำหรับ pdf_completed.php
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
                    r.id_card,
                    r.id_members,
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
                    bt.billPaymentRef1,
                    bt.payerAccountName,
                    r.status
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
            'address_line' => $receipt['address_line'] ?? '',
            'province' => $receipt['province'] ?? '',
            'amphure' => $receipt['amphure'] ?? '',
            'district' => $receipt['district'] ?? '',
            'zip_code' => $receipt['zip_code'] ?? '',
            'billPaymentRef2' => $receipt['billPaymentRef2'] ?? '',
            'id_card' => $receipt['id_card'] ?? '',
            'id_members' => $receipt['id_members'] ?? '',
            'status' => ((int) ($receipt['status'] ?? 1) === 2) ? 'cancelled' : 'issued',
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
                    r.status,
                    r.id_card,
                    r.id_members,
                    du.project_name,
                    du.project_number,
                    du.payby,
                    du.title,
                    du.first_name,
                    du.last_name,
                    du.phone,
                    du.occupation,
                    du.receiptDate,
                    du.receipt_address,
                    du.address_line,
                    du.province,
                    du.amphure,
                    du.district,
                    du.zip_code,
                    du.email,
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
            'donation_id' => $receipt['donation_id'],
            'project_name' => $receipt['project_name'] ?? '',
            'project_number' => $receipt['project_number'] ?? '',
            'pay_by' => $receipt['payby'] ?? 'QR PromptPay',
            'title' => $receipt['title'] ?? '',
            'first_name' => $receipt['first_name'] ?? '',
            'last_name' => $receipt['last_name'] ?? '',
            'id_card' => $receipt['id_card'] ?? '',
            'phone' => $receipt['phone'] ?? '',
            'occupation' => $receipt['occupation'] ?? '',
            'receiptDate' => $receipt['receiptDate'] ?? '',
            'receipt_address' => $receipt['receipt_address'] ?? '',
            'address_line' => $receipt['address_line'] ?? '',
            'province' => $receipt['province'] ?? '',
            'amphure' => $receipt['amphure'] ?? '',
            'district' => $receipt['district'] ?? '',
            'zip_code' => $receipt['zip_code'] ?? '',
            'email' => $receipt['email'] ?? '',
            'status' => ((int) ($receipt['status'] ?? 1) === 2) ? 'cancelled' : 'issued',
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
        $limit = min(1000, max(1, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        if (!empty($_GET['search'])) {
            $where[] = "(r.receipt_no LIKE :s1 OR r.payer_name LIKE :s2 OR du.first_name LIKE :s3 OR du.last_name LIKE :s4 OR bt.billPaymentRef2 LIKE :s5 OR du.project_number LIKE :s6 OR du.project_name LIKE :s7)";
            $searchVal = '%' . $_GET['search'] . '%';
            $params[':s1'] = $searchVal;
            $params[':s2'] = $searchVal;
            $params[':s3'] = $searchVal;
            $params[':s4'] = $searchVal;
            $params[':s5'] = $searchVal;
            $params[':s6'] = $searchVal;
            $params[':s7'] = $searchVal;
        }

        // Status filter
        if (!empty($_GET['status'])) {
            $statusVal = ($_GET['status'] === 'cancelled') ? 2 : 1;
            $where[] = "r.status = :status";
            $params[':status'] = $statusVal;
        }

        // Year filter (Buddhist Era)
        if (!empty($_GET['year'])) {
            $where[] = "YEAR(r.issued_at) = :year";
            $params[':year'] = intval($_GET['year']) - 543;
        }

        // Donation ID filter
        if (!empty($_GET['donation_id'])) {
            $where[] = "r.donation_id = :donation_id";
            $params[':donation_id'] = intval($_GET['donation_id']);
        }

        // Project filter
        if (!empty($_GET['project'])) {
            $where[] = "du.project_number = :project";
            $params[':project'] = $_GET['project'];
        }

        // Date filters
        if (!empty($_GET['from'])) {
            $where[] = "r.issued_at >= :from";
            $params[':from'] = $_GET['from'] . ' 00:00:00';
        }
        if (!empty($_GET['to'])) {
            $where[] = "r.issued_at <= :to";
            $params[':to'] = $_GET['to'] . ' 23:59:59';
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

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
                    r.id_card,
                    r.id_members,
                    du.project_name,
                    du.first_name,
                    du.last_name,
                    du.status_donat,
                    r.status,
                    bt.billPaymentRef2,
                    du.receipt_address,
                    du.shipping_address,
                    du.address_line,
                    du.province,
                    du.amphure,
                    du.district,
                    du.zip_code
                FROM edonation_receipts r
                LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                LEFT JOIN edonation_bank_transactions bt ON r.bank_transaction_id = bt.id
                $whereClause
                ORDER BY r.receipt_no DESC 
                LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format payer_name and address properly
            foreach ($results as &$row) {
                // Name
                $name = trim($row['payer_name'] ?? '');
                if (empty($name) || $name === ' ') {
                    $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                }
                $row['payer_name'] = !empty($name) ? $name : 'ไม่ระบุชื่อ';

                // Address logic (Priority: Shipping > Receipt > Components)
                $addr = $row['shipping_address'] ?? '';
                if (empty($addr)) {
                    $addr = $row['receipt_address'] ?? '';
                }

                if (empty($addr)) {
                    $parts = [];
                    if (!empty($row['address_line']))
                        $parts[] = $row['address_line'];
                    if (!empty($row['district']))
                        $parts[] = 'อ.' . $row['district']; // หรือ แขวง/ตำบล แล้วแต่ field
                    if (!empty($row['amphure']))
                        $parts[] = 'อ.' . $row['amphure'];
                    if (!empty($row['province']))
                        $parts[] = 'จ.' . $row['province'];
                    if (!empty($row['zip_code']))
                        $parts[] = $row['zip_code'];
                    $addr = implode(' ', $parts);
                }
                $row['full_address'] = $addr;
                $row['status'] = ((int) $row['status'] === 2) ? 'cancelled' : 'issued';
            }

            // Count filtered totals and stats
            $summarySql = "SELECT 
                        COUNT(*) as filtered_total, 
                        SUM(CASE WHEN r.status = 1 THEN r.amount ELSE 0 END) as filtered_amount,
                        SUM(CASE WHEN r.status = 1 THEN 1 ELSE 0 END) as issued_count,
                        SUM(CASE WHEN r.status = 2 THEN 1 ELSE 0 END) as cancelled_count
                      FROM edonation_receipts r 
                      LEFT JOIN edonation_donat_user du ON r.donation_id = du.id 
                      LEFT JOIN edonation_bank_transactions bt ON r.bank_transaction_id = bt.id 
                      $whereClause";
            $summaryStmt = $this->pdo->prepare($summarySql);
            foreach ($params as $key => $val) {
                $summaryStmt->bindValue($key, $val);
            }
            $summaryStmt->execute();
            $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

            if (!$summary) {
                $summary = ['filtered_total' => 0, 'filtered_amount' => 0, 'issued_count' => 0, 'cancelled_count' => 0];
            }

            $total = (int) $summary['filtered_total'];
            $filteredAmount = (float) $summary['filtered_amount'];
            $issuedCount = (int) $summary['issued_count'];
            $cancelledCount = (int) $summary['cancelled_count'];

            // Calculate Grand Total Amount (Unfiltered, but excluding cancelled)
            $grandTotalStmt = $this->pdo->query("SELECT SUM(r.amount) FROM edonation_receipts r WHERE r.status = 1");
            $grandTotalAmount = ($grandTotalStmt) ? (float) $grandTotalStmt->fetchColumn() : 0;

            return Response::success($results, null, [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit),
                'total_amount' => $filteredAmount,
                'issued' => $issuedCount,
                'cancelled' => $cancelledCount,
                'grand_total' => $grandTotalAmount,
                'api_version' => self::VERSION
            ]);

        } catch (PDOException $e) {
            error_log("Receipt index error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /receipts/generate (Admin)
     * สร้างใบเสร็จใหม่ - รองรับทั้งเชื่อมกับ donation ที่มีอยู่ และสร้าง donation ใหม่
     */
    private function generate(): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        // Validate required fields - บังคับแค่ ชื่อ, โครงการ, จำนวนเงิน
        $requiredFields = ['first_name', 'project_number', 'amount'];
        $missing = [];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
        }

        // ถ้าเป็นบุคคลธรรมดา ต้องมี last_name
        $donorType = $data['donor_type'] ?? 'person';
        if ($donorType !== 'juristic' && empty($data['last_name'])) {
            $missing[] = 'last_name';
        }

        if (!empty($missing)) {
            return Response::error('VALIDATION_ERROR', 'กรุณากรอกข้อมูลให้ครบ: ' . implode(', ', $missing));
        }

        // Validate ID card format (if provided)
        $idCard = $data['id_card'] ?? '';
        if (!empty($idCard) && strlen(preg_replace('/\D/', '', $idCard)) !== 13) {
            return Response::error('VALIDATION_ERROR', 'เลขบัตรประชาชนต้องมี 13 หลัก');
        }

        // Validate amount
        if (floatval($data['amount']) <= 0) {
            return Response::error('VALIDATION_ERROR', 'จำนวนเงินต้องมากกว่า 0');
        }

        try {
            $this->pdo->beginTransaction();

            $donationId = $data['donation_id'] ?? null;
            $payerName = trim(($data['title'] ?? '') . ' ' . $data['first_name'] . ' ' . ($data['last_name'] ?? ''));

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
                        1, :first_name, :last_name, :id_card, :address, :shipping_address
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
                    ':last_name' => $data['last_name'] ?? '',
                    ':id_card' => !empty($data['id_card']) ? preg_replace('/\D/', '', $data['id_card']) : '',
                    ':address' => $data['address'] ?? '',
                    ':shipping_address' => $data['shipping_address'] ?? $data['address'] ?? ''
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

            // Check for duplicate receipt_no (safety check)
            $checkDupe = $this->pdo->prepare("SELECT COUNT(*) FROM edonation_receipts WHERE receipt_no = :rno");
            $checkDupe->execute([':rno' => $receiptNo]);
            if ($checkDupe->fetchColumn() > 0) {
                // Find next available number
                $safetyLoop = 0;
                do {
                    $nextNum++;
                    $receiptNo = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
                    $checkDupe->execute([':rno' => $receiptNo]);
                    $safetyLoop++;
                } while ($checkDupe->fetchColumn() > 0 && $safetyLoop < 100);
            }

            // Manage id_members
            $idCardClean = preg_replace('/\D/', '', $data['id_card']);
            $checkMember = $this->pdo->prepare("SELECT id_members FROM edonation_receipts WHERE id_card = :id LIMIT 1");
            $checkMember->execute([':id' => $idCardClean]);
            $member = $checkMember->fetch();

            if ($member && !empty($member['id_members'])) {
                $idMembers = $member['id_members'];
            } else {
                // Generate 10-digit random number
                $idMembers = '';
                for ($i = 0; $i < 10; $i++) {
                    $idMembers .= rand(0, 9);
                }
            }

            // Create receipt record
            $receiptStmt = $this->pdo->prepare("
                INSERT INTO edonation_receipts (donation_id, bank_transaction_id, receipt_no, payer_name, amount, issued_at, id_card, id_members, status)
                VALUES (:donation_id, :bank_transaction_id, :receipt_no, :payer_name, :amount, NOW(), :id_card, :id_members, 1)
            ");

            $receiptStmt->execute([
                ':donation_id' => $donationId,
                ':bank_transaction_id' => $data['bank_transaction_id'] ?? null,
                ':receipt_no' => $receiptNo,
                ':payer_name' => $payerName,
                ':amount' => $data['amount'],
                ':id_card' => $idCardClean,
                ':id_members' => $idMembers
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
            $_SESSION['edonation_pdf_access_tokens'][$receiptId] = [
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
                'pdf_url' => "{$basePath}/receipts/pdf_completed.php?id={$receiptId}&token={$accessToken}",
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
     * ยกเลิกใบเสร็จ - เก็บประวัติไว้แต่ update สถานะ
     */
    private function cancel(string $id): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $reason = $data['reason'] ?? 'ไม่ระบุเหตุผล';

        // Check if receipt exists
        $checkStmt = $this->pdo->prepare("SELECT id, donation_id, receipt_no FROM edonation_receipts WHERE id = :id");
        $checkStmt->execute([':id' => $id]);
        $receipt = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$receipt) {
            return Response::notFound('ไม่พบใบเสร็จ');
        }

        try {
            $this->pdo->beginTransaction();

            // Update the receipt status instead of deleting
            $stmt = $this->pdo->prepare("UPDATE edonation_receipts SET status = 2 WHERE id = :id");
            $stmt->execute([':id' => $id]);

            // Update donation status back to cancelled/voided
            if ($receipt['donation_id']) {
                $updateStmt = $this->pdo->prepare("UPDATE edonation_donat_user SET status_donat = 'cancelled', updated_at = NOW() WHERE id = :id");
                $updateStmt->execute([':id' => $receipt['donation_id']]);
            }

            $this->pdo->commit();

            // Log the void action
            error_log("Receipt {$receipt['receipt_no']} (ID: {$id}) voided by admin. Reason: {$reason}");

            return Response::success([
                'voided_receipt_no' => $receipt['receipt_no'],
                'reason' => $reason,
                'api_version' => self::VERSION
            ], 'ยกเลิกใบเสร็จเรียบร้อย');

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Cancel receipt error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'ไม่สามารถยกเลิกใบเสร็จได้: ' . $e->getMessage(), 500);
        }
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

    /**
     * PUT /receipts/:id (Admin)
     * แก้ไขข้อมูลใบเสร็จ
     */
    private function update(string $id): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        // ดึงข้อมูลใบเสร็จเดิม
        $stmt = $this->pdo->prepare("
            SELECT r.*, d.status_donat 
            FROM edonation_receipts r
            LEFT JOIN edonation_donat_user d ON r.donation_id = d.id
            WHERE r.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $receipt = $stmt->fetch();

        if (!$receipt) {
            return Response::notFound('ไม่พบใบเสร็จ');
        }

        // ตรวจสอบว่าไม่ใช่ใบเสร็จที่ยกเลิกแล้ว
        if ($receipt['status_donat'] === 'cancelled') {
            return Response::error('VALIDATION_ERROR', 'ไม่สามารถแก้ไขใบเสร็จที่ยกเลิกแล้วได้');
        }

        try {
            $this->pdo->beginTransaction();

            // อัปเดตตาราง edonation_receipts
            $receiptFields = [];
            $receiptParams = [':id' => $id];

            if (isset($data['payer_name'])) {
                $receiptFields[] = 'payer_name = :payer_name';
                $receiptParams[':payer_name'] = $data['payer_name'];
            }
            if (isset($data['amount'])) {
                $receiptFields[] = 'amount = :amount';
                $receiptParams[':amount'] = $data['amount'];
            }
            if (isset($data['id_card'])) {
                $idCard = preg_replace('/\D/', '', $data['id_card']);
                $receiptFields[] = 'id_card = :id_card';
                $receiptParams[':id_card'] = $idCard;
            }

            if (!empty($receiptFields)) {
                $sql = "UPDATE edonation_receipts SET " . implode(', ', $receiptFields) . " WHERE id = :id";
                $updateStmt = $this->pdo->prepare($sql);
                $updateStmt->execute($receiptParams);
            }

            // อัปเดตตาราง edonation_donat_user (ถ้ามี donation_id)
            if ($receipt['donation_id']) {
                $donationFields = [];
                $donationParams = [':id' => $receipt['donation_id']];

                $allowedDonationFields = [
                    'title',
                    'first_name',
                    'last_name',
                    'id_card',
                    'phone',
                    'email',
                    'occupation',
                    'receipt_address',
                    'amount',
                    'project_number',
                    'project_name',
                    'receiptDate',
                    'address_line',
                    'province',
                    'amphure',
                    'district',
                    'zip_code',
                    'payby'
                ];

                foreach ($allowedDonationFields as $field) {
                    if (isset($data[$field])) {
                        $value = $data[$field];
                        if ($field === 'id_card') {
                            $value = preg_replace('/\D/', '', $value);
                        }
                        $donationFields[] = "{$field} = :{$field}";
                        $donationParams[":{$field}"] = $value;
                    }
                }

                if (!empty($donationFields)) {
                    $sql = "UPDATE edonation_donat_user SET " . implode(', ', $donationFields) . " WHERE id = :id";
                    $updateDonation = $this->pdo->prepare($sql);
                    $updateDonation->execute($donationParams);
                }
            }

            $this->pdo->commit();

            return Response::success([
                'id' => (int) $id,
                'receipt_no' => $receipt['receipt_no']
            ], 'แก้ไขใบเสร็จสำเร็จ');

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Update receipt error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'ไม่สามารถแก้ไขใบเสร็จได้: ' . $e->getMessage(), 500);
        }
    }
}
