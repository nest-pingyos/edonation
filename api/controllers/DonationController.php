<?php
/**
 * Donation Controller
 * 
 * Endpoints:
 * POST   /donations              - สร้างรายการบริจาค
 * GET    /donations/:id/qr       - QR Code
 * GET    /donations/:id/status   - ตรวจสอบสถานะ
 * GET    /donations              - รายการทั้งหมด (Admin)
 * GET    /donations/:id          - รายละเอียด (Admin)
 * PUT    /donations/:id          - แก้ไข (Admin)
 */

class DonationController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function handle(string $method, ?string $id, ?string $action): array
    {
        // Handle special actions
        if ($id && $action) {
            switch ($action) {
                case 'qr':
                    return $this->getQr($id);
                case 'status':
                    return $this->getStatus($id);
            }
        }

        // Handle POST /donations/admin - Admin creates donation + receipt
        if ($id === 'admin' && $method === 'POST') {
            AuthMiddleware::requireAdmin();
            return $this->createFromAdmin();
        }

        switch ($method) {
            case 'GET':
                if ($id) {
                    AuthMiddleware::requireAdmin();
                    return $this->show($id);
                }
                AuthMiddleware::requireAdmin();
                return $this->index();
            case 'POST':
                return $this->create();
            case 'PUT':
                AuthMiddleware::requireAdmin();
                return $this->update($id);
            case 'DELETE':
                AuthMiddleware::requireAdmin();
                return $this->delete($id);
            default:
                return Response::error('METHOD_NOT_ALLOWED', 'Method not allowed', 405);
        }
    }

    // POST /donations - สร้างรายการบริจาค
    private function create(): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $v = new Validator($data);
        $v->required('project_number')
            ->required('phone')
            ->required('amount')
            ->required('type')
            ->numeric('amount')
            ->min('amount', 1);

        // If need receipt, validate additional fields
        if (!empty($data['needReceipt'])) {
            $v->required('firstName')
                ->required('lastName')
                ->required('idCard')
                ->required('receiptAddress')
                ->required('shippingAddress');
        }

        if (!$v->passes())
            return Response::validation($v->errors());

        // Generate billPaymentRef1 (15 digits numeric only)
        // Format: YYYY(4) + PROJ(6) + RAND(5) = 15 Digits
        // Requirement: ปี, เลขโครงการ, ต่อด้วยเลขอีก 5 หลัก (รวมต้อง 15 หลัก)

        $year = date('Y') + 543; // 4 digits (e.g., 2568)
        $rand = rand(10000, 99999); // 5 digits (e.g., 12345)

        // Project Number processing
        $projNumRaw = preg_replace('/\D/', '', $data['project_number']); // Remove non-digits
        // Calculate needed length for project number to make total 15
        // 15 - 4 (Year) - 5 (Rand) = 6 digits for Project
        $projNum = str_pad(substr($projNumRaw, 0, 6), 6, '0', STR_PAD_LEFT);

        $ref1 = $year . $projNum . $rand;



        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO edonation_donat_user (
                    billPaymentRef1, project_number, project_name, type, phone, amount, 
                    fiscal_year, status_donat, payby, receiptDate,
                    need_receipt, title, first_name, last_name, id_card, receipt_address, shipping_address,
                    address_line, province, amphure, district, zip_code
                ) VALUES (
                    :ref1, :project_number, :project_name, :type, :phone, :amount, 
                    :fiscal_year, 'pending', 'QR PromptPay', CURDATE(),
                    :need_receipt, :title, :first_name, :last_name, :id_card, :receipt_address, :shipping_address,
                    :address_line, :province, :amphure, :district, :zip_code
                )"
            );

            // Get project name
            $projectStmt = $this->pdo->prepare("SELECT project_name FROM edonation_projects WHERE project_number = :pn");
            $projectStmt->execute([':pn' => $data['project_number']]);
            $project = $projectStmt->fetch();

            $stmt->execute([
                ':ref1' => $ref1,
                ':project_number' => $data['project_number'],
                ':project_name' => $project['project_name'] ?? $data['project_name'] ?? '',
                ':type' => $data['type'],
                ':phone' => $data['phone'],
                ':amount' => $data['amount'],
                ':fiscal_year' => (date('Y') + 543),
                ':need_receipt' => !empty($data['needReceipt']) ? 1 : 0,
                ':title' => $data['title'] ?? null,
                ':first_name' => $data['firstName'] ?? null,
                ':last_name' => $data['lastName'] ?? null,
                ':id_card' => $data['idCard'] ?? null,
                ':receipt_address' => $data['receiptAddress'] ?? null,
                ':shipping_address' => $data['shippingAddress'] ?? null,
                ':address_line' => $data['addressLine'] ?? null,
                ':province' => $data['province'] ?? null,
                ':amphure' => $data['amphure'] ?? null,
                ':district' => $data['district'] ?? null,
                ':zip_code' => $data['zipCode'] ?? null
            ]);

            $id = $this->pdo->lastInsertId();

            return Response::success([
                'id' => $id,
                'billPaymentRef1' => $ref1,
                'amount' => floatval($data['amount']),
                'status' => 'pending',
                'qr_url' => "/api/v1/donations/{$id}/qr",
                'expires_at' => date('c', strtotime('+30 minutes'))
            ], 'สร้างรายการบริจาคสำเร็จ');

        } catch (PDOException $e) {
            error_log("Donation create error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'ไม่สามารถบันทึกข้อมูลได้: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /donations/admin - สร้างรายการบริจาคจาก Admin
     * Flow: 
     * 1. บันทึกข้อมูลไป edonation_donat_user (เหมือน web)
     * 2. ตั้ง status_donat = 'completed' (เพราะเป็น manual จาก admin)
     * 3. สร้างใบเสร็จอัตโนมัติ
     */
    private function createFromAdmin(): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        // Validate required fields
        $requiredFields = ['first_name', 'id_card', 'address', 'project_number', 'amount']; // Removed last_name from required by default

        // Check for Juristic Person
        $juristicTitles = ['บริษัท', 'ห้างหุ้นส่วน', 'มูลนิธิ', 'สมาคม'];
        $title = $data['title'] ?? '';
        $isJuristic = in_array($title, $juristicTitles);

        if (!$isJuristic) {
            $requiredFields[] = 'last_name';
        }

        $missing = [];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
        }
        if (!empty($missing)) {
            return Response::error('VALIDATION_ERROR', 'กรุณากรอกข้อมูลให้ครบ: ' . implode(', ', $missing));
        }

        // Validate ID card format (13 digits)
        $idCard = preg_replace('/\D/', '', $data['id_card']);
        if (strlen($idCard) !== 13) {
            return Response::error('VALIDATION_ERROR', 'เลขบัตรประชาชนต้องมี 13 หลัก');
        }

        // Validate amount
        if (floatval($data['amount']) <= 0) {
            return Response::error('VALIDATION_ERROR', 'จำนวนเงินต้องมากกว่า 0');
        }

        try {
            $this->pdo->beginTransaction();

            // Step 1: Generate billPaymentRef1 (same format as web)
            $year = date('Y') + 543; // พ.ศ.
            $rand = rand(10000, 99999);
            $projNumRaw = preg_replace('/\D/', '', $data['project_number']);
            $projNum = str_pad(substr($projNumRaw, 0, 6), 6, '0', STR_PAD_LEFT);
            $ref1 = $year . $projNum . $rand;

            // Get project name
            $projectStmt = $this->pdo->prepare("SELECT project_name FROM edonation_projects WHERE project_number = :pn");
            $projectStmt->execute([':pn' => $data['project_number']]);
            $project = $projectStmt->fetch();
            $projectName = $data['project_name'] ?? $project['project_name'] ?? $data['project_number'];

            // Payer name
            $payerName = trim(($data['title'] ?? '') . ' ' . $data['first_name'] . ' ' . $data['last_name']);

            // Payer name construction logic might change if we use title separately, 
            // but for now, we just save title.
            // Note: The original code combined title+first+last into payerName for Receipts but DonationUser table uses separate fields.
            // We should save title to donation user table too.

            $donationStmt = $this->pdo->prepare("
                INSERT INTO edonation_donat_user (
                    billPaymentRef1, project_number, project_name, type, phone, amount, 
                    fiscal_year, status_donat, payby, receiptDate,
                    need_receipt, title, first_name, last_name, id_card, receipt_address, shipping_address,
                    address_line, province, amphure, district, zip_code
                ) VALUES (
                    :ref1, :project_number, :project_name, :type, :phone, :amount, 
                    :fiscal_year, 'completed', :payby, :receipt_date,
                    1, :title, :first_name, :last_name, :id_card, :receipt_address, :shipping_address,
                    :address_line, :province, :amphure, :district, :zip_code
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
                ':title' => $data['title'] ?? null,
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':id_card' => $idCard,
                ':receipt_address' => $data['address'],
                ':shipping_address' => $data['address'],
                ':address_line' => $data['address_line'] ?? null,
                ':province' => $data['province'] ?? null,
                ':amphure' => $data['amphure'] ?? null,
                ':district' => $data['district'] ?? null,
                ':zip_code' => $data['zip_code'] ?? null
            ]);

            $donationId = $this->pdo->lastInsertId();

            // Step 3: Since status_donat = 'completed', auto-generate receipt
            // Generate receipt number Format: YYYY-EXXXX
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

            // Insert receipt
            $receiptStmt = $this->pdo->prepare("
                INSERT INTO edonation_receipts (donation_id, receipt_no, payer_name, amount, issued_at)
                VALUES (:donation_id, :receipt_no, :payer_name, :amount, NOW())
            ");

            $receiptStmt->execute([
                ':donation_id' => $donationId,
                ':receipt_no' => $receiptNo,
                ':payer_name' => $payerName,
                ':amount' => $data['amount']
            ]);

            $receiptId = $this->pdo->lastInsertId();

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
                'donation_id' => (int) $donationId,
                'billPaymentRef1' => $ref1,
                'receipt_id' => (int) $receiptId,
                'receipt_no' => $receiptNo,
                'payer_name' => $payerName,
                'amount' => floatval($data['amount']),
                'project_name' => $projectName,
                'status' => 'completed',
                'pdf_url' => "{$basePath}/web/receipts/pdf_maker.php?id={$receiptId}&token={$accessToken}",
                'access_token' => $accessToken
            ], 'บันทึกข้อมูลและออกใบเสร็จสำเร็จ');

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Admin donation create error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'ไม่สามารถบันทึกข้อมูลได้: ' . $e->getMessage(), 500);
        }
    }

    // GET /donations/:id/qr
    private function getQr(string $id): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM edonation_donat_user WHERE id = :id"
            );
            $stmt->execute([':id' => $id]);
            $donation = $stmt->fetch();

            if (!$donation)
                return Response::notFound('ไม่พบรายการบริจาค');

            return Response::success([
                'id' => $id,
                'amount' => floatval($donation['amount']),
                'billPaymentRef1' => $donation['billPaymentRef1'],
                'billPaymentRef2' => $donation['id_card'] ?? '',  // Tax ID
                'project_number' => $donation['project_number'],
                'project_name' => $donation['project_name'] ?? '',
                'type' => $donation['type'] ?? '',
                'phone' => $donation['phone'] ?? '',
                'first_name' => $donation['first_name'] ?? '',
                'last_name' => $donation['last_name'] ?? '',
                'need_receipt' => $donation['need_receipt'] == 1,
                'status' => $donation['status_donat'] ?? 'pending',
                'created_at' => $donation['created_at'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Get QR error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'ไม่สามารถดึงข้อมูลได้: ' . $e->getMessage(), 500);
        }
    }

    // GET /donations/:id/status
    private function getStatus(string $id): array
    {
        try {
            // First get the donation record with user details
            $donationStmt = $this->pdo->prepare("SELECT id, billPaymentRef1, status_donat, need_receipt, first_name, last_name, amount FROM edonation_donat_user WHERE id = :id");
            $donationStmt->execute([':id' => $id]);
            $donation = $donationStmt->fetch();

            if (!$donation) {
                return Response::notFound('ไม่พบรายการบริจาค');
            }

            // Check bank_transactions for payment confirmation
            $bankStmt = $this->pdo->prepare("
                SELECT transactionId, transactionDateandTime, payerName, payerAccountName,
                       sendingBankCode, amount, confirmId, created_at
                FROM edonation_bank_transactions 
                WHERE billPaymentRef1 = :ref1 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $bankStmt->execute([':ref1' => $donation['billPaymentRef1']]);
            $bankTxn = $bankStmt->fetch();

            if ($bankTxn || $donation['status_donat'] === 'completed') {
                // Payment confirmed

                // 1. Amount Validation (Crucial: Prevent Underpayment)
                // Only check if we are relying on bank transaction
                if ($bankTxn) {
                    $paidAmount = floatval($bankTxn['amount'] ?? 0);
                    $donatedAmount = floatval($donation['amount']);

                    // Allow tiny floating point diff or strict >=
                    if ($paidAmount < $donatedAmount) {
                        // Underpayment detected!
                        return Response::success([
                            'id' => $id,
                            'status' => 'pending', // Keep pending or introduce 'partial'
                            'message' => 'ยอดเงินที่ชำระไม่ครบตามจำนวน'
                        ]);
                    }
                }

                $receipt = null;
                $payerNameFromBank = $bankTxn['payerAccountName'] ?? $bankTxn['payerName'] ?? 'ผู้บริจาค';

                try {
                    $this->pdo->beginTransaction();

                    // Update status to completed if not already
                    if ($donation['status_donat'] !== 'completed') {
                        $upd = $this->pdo->prepare("UPDATE edonation_donat_user SET status_donat = 'completed' WHERE id = :id");
                        $upd->execute([':id' => $id]);
                    }

                    // --- Receipt Generation Logic ---
                    // Check if receipt exists
                    $receiptStmt = $this->pdo->prepare("SELECT id, receipt_no FROM edonation_receipts WHERE donation_id = :did");
                    $receiptStmt->execute([':did' => $donation['id']]);
                    $receipt = $receiptStmt->fetch();

                    if (!$receipt) {
                        try {
                            // 1. Determine Payer Name
                            $finalPayerName = $payerNameFromBank;
                            if ($donation['need_receipt'] == 1 && !empty($donation['first_name'])) {
                                $finalPayerName = trim($donation['first_name'] . ' ' . $donation['last_name']);
                            }

                            // 2. Generate Receipt No (YYYY-EXXXX)
                            $buddhistYear = date('Y') + 543;
                            $prefix = $buddhistYear . '-E';

                            // Lock rows for reading max number (SAFE)
                            $maxStmt = $this->pdo->prepare("SELECT MAX(receipt_no) as max_no FROM edonation_receipts WHERE receipt_no LIKE :prefix FOR UPDATE");
                            $maxStmt->execute([':prefix' => $prefix . '%']);
                            $maxRow = $maxStmt->fetch();

                            $nextNum = 1;
                            if ($maxRow && $maxRow['max_no']) {
                                // แยกเลขออกจาก 2568-E0005 -> 0005
                                $numPart = preg_replace('/^\d{4}-E/', '', $maxRow['max_no']);
                                $nextNum = intval($numPart) + 1;
                            }
                            $receiptNo = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

                            // 3. Insert Receipt
                            $insReceipt = $this->pdo->prepare("INSERT INTO edonation_receipts (donation_id, receipt_no, payer_name, amount, issued_at) VALUES (:did, :rno, :pname, :amt, NOW())");
                            $insReceipt->execute([
                                ':did' => $donation['id'],
                                ':rno' => $receiptNo,
                                ':pname' => $finalPayerName,
                                ':amt' => $donation['amount']
                            ]);

                            $receipt = [
                                'id' => $this->pdo->lastInsertId(),
                                'receipt_no' => $receiptNo
                            ];
                        } catch (PDOException $ex) {
                            // If dup entry for donation_id (Race condition caught by DB), ignore
                            if ($ex->getCode() == 23000) {
                                // Re-fetch existing receipt
                                $receiptStmt->execute([':did' => $donation['id']]);
                                $receipt = $receiptStmt->fetch();
                            } else {
                                throw $ex; // Re-throw other errors
                            }
                        }
                    }

                    $this->pdo->commit();
                } catch (Exception $e) {
                    $this->pdo->rollBack();
                    error_log("Transaction failed: " . $e->getMessage());
                    // Don't fail the request, just return status without receipt if failed
                    // But usually we should return error
                }

                // Get bank name from code (Display Logic)
                $bankNames = [
                    '002' => 'ธนาคารกรุงเทพ',
                    '004' => 'ธนาคารกสิกรไทย',
                    '006' => 'ธนาคารกรุงไทย',
                    '011' => 'ธนาคารทหารไทยธนชาต',
                    '014' => 'ธนาคารไทยพาณิชย์',
                    '025' => 'ธนาคารกรุงศรีอยุธยา',
                    '030' => 'ธนาคารออมสิน',
                    '034' => 'ธนาคาร ธ.ก.ส.'
                ];
                $bankName = $bankNames[$bankTxn['sendingBankCode'] ?? ''] ?? 'ธนาคาร';

                // สร้าง access_token สำหรับเปิดใบเสร็จ (ถ้ามี receipt)
                $accessToken = null;
                $pdfUrl = null;
                if ($receipt && isset($receipt['id'])) {
                    $accessToken = bin2hex(random_bytes(32));
                    $expireAt = time() + 600; // หมดอายุใน 10 นาที

                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $_SESSION['pdf_access_tokens'][$receipt['id']] = [
                        'token' => $accessToken,
                        'expire_at' => $expireAt
                    ];

                    // Use BASE_PATH from config
                    $basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';
                    $pdfUrl = "{$basePath}/receipts/pdf_maker.php?id={$receipt['id']}&token={$accessToken}";
                }

                return Response::success([
                    'id' => $id,
                    'status' => 'completed',
                    'payer_name' => $payerNameFromBank,
                    'receipt_id' => $receipt['id'] ?? null,
                    'receipt_no' => $receipt['receipt_no'] ?? null,
                    'access_token' => $accessToken,
                    'pdf_url' => $pdfUrl,
                    'bank_name' => $bankName,
                    'bank_code' => $bankTxn['sendingBankCode'] ?? null,
                    'amount' => floatval($bankTxn['amount'] ?? 0),
                    'transaction_id' => $bankTxn['transactionId'] ?? null,
                    'confirm_id' => $bankTxn['confirmId'] ?? null,
                    'paid_at' => $bankTxn['transactionDateandTime'] ?? $bankTxn['created_at'] ?? null
                ]);
            }

            // Still pending
            return Response::success([
                'id' => $id,
                'status' => 'pending'
            ]);

        } catch (PDOException $e) {
            error_log("Get status error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'ไม่สามารถตรวจสอบสถานะได้', 500);
        }
    }

    // GET /donations (Admin)
    private function index(): array
    {
        try {
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = min(100, max(1, intval($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;

            $where = [];
            $params = [];

            // Search filter
            if (!empty($_GET['search'])) {
                $where[] = "(first_name LIKE :s1 OR last_name LIKE :s2 OR billPaymentRef1 LIKE :s3 OR id_card LIKE :s4)";
                $searchVal = '%' . $_GET['search'] . '%';
                $params[':s1'] = $searchVal;
                $params[':s2'] = $searchVal;
                $params[':s3'] = $searchVal;
                $params[':s4'] = $searchVal;
            }

            // Status filter
            if (!empty($_GET['status'])) {
                $statusMap = [
                    'CONFIRMED' => 'completed',
                    'PENDING' => 'pending',
                    'CANCELLED' => 'cancelled'
                ];
                $dbStatus = $statusMap[$_GET['status']] ?? $_GET['status'];
                $where[] = "status_donat = :status";
                $params[':status'] = $dbStatus;
            }

            // Project filter
            if (!empty($_GET['project'])) {
                $where[] = "project_number = :project";
                $params[':project'] = $_GET['project'];
            }

            // Date filters
            if (!empty($_GET['from'])) {
                $where[] = "created_at >= :from";
                $params[':from'] = $_GET['from'] . ' 00:00:00';
            }
            if (!empty($_GET['to'])) {
                $where[] = "created_at <= :to";
                $params[':to'] = $_GET['to'] . ' 23:59:59';
            }

            $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

            // Count total for pagination
            $countSql = "SELECT COUNT(*) as total FROM edonation_donat_user $whereClause";
            $countStmt = $this->pdo->prepare($countSql);
            $countStmt->execute($params);
            $total = (int) $countStmt->fetch()['total'];

            // Get summary stats
            $statsSql = "SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN status_donat = 'completed' THEN 1 ELSE 0 END) as confirmed,
                            SUM(CASE WHEN status_donat = 'pending' THEN 1 ELSE 0 END) as pending,
                            SUM(amount) as totalAmount
                         FROM edonation_donat_user";
            $statsStmt = $this->pdo->prepare($statsSql);
            $statsStmt->execute();
            $stats = $statsStmt->fetch();

            // Fetch results
            $sql = "SELECT id, billPaymentRef1, first_name, last_name, id_card, 
                           amount, project_number, project_name, status_donat as status, 
                           phone, receiptDate, created_at, receipt_address
                    FROM edonation_donat_user 
                    $whereClause
                    ORDER BY id DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll();

            // Map outcomes
            foreach ($results as &$row) {
                $row['donor_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                $row['transaction_date'] = $row['created_at'];
                // Normalize status
                if ($row['status'] === 'completed')
                    $row['status'] = 'CONFIRMED';
                else if ($row['status'] === 'pending')
                    $row['status'] = 'PENDING';
                else if ($row['status'] === 'cancelled')
                    $row['status'] = 'CANCELLED';
            }

            return Response::success($results, null, [
                'total' => $total,
                'totalPages' => ceil($total / $limit),
                'currentPage' => $page,
                'limit' => $limit,
                'confirmed' => (int) $stats['confirmed'],
                'pending' => (int) $stats['pending'],
                'totalAmount' => (float) $stats['totalAmount']
            ]);
        } catch (PDOException $e) {
            error_log("Donations index error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'ไม่สามารถดึงข้อมูลได้: ' . $e->getMessage(), 500);
        }
    }

    // GET /donations/:id (Admin)
    private function show(string $id): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM edonation_donat_user WHERE id = :id OR billPaymentRef1 = :ref");
            $stmt->execute([':id' => $id, ':ref' => $id]);
            $donation = $stmt->fetch();

            if (!$donation)
                return Response::notFound('ไม่พบรายการบริจาค');

            // Add donor_name for frontend
            $donation['donor_name'] = trim(($donation['first_name'] ?? '') . ' ' . ($donation['last_name'] ?? ''));
            $donation['transaction_date'] = $donation['created_at'];

            return Response::success($donation);
        } catch (PDOException $e) {
            error_log("Donation show error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'ไม่สามารถดึงข้อมูลได้: ' . $e->getMessage(), 500);
        }
    }

    // PUT /donations/:id (Admin)
    private function update(string $id): array
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? [];

            // Fields that can be updated in edonation_donat_user table
            $allowedFields = ['first_name', 'last_name', 'phone', 'receipt_address', 'shipping_address', 'status_donat'];
            $fields = [];
            $params = [':id' => $id];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $data[$field];
                }
            }

            if (empty($fields))
                return Response::error('NO_DATA', 'ไม่มีข้อมูลที่จะอัปเดต');

            $sql = "UPDATE edonation_donat_user SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return Response::success(null, 'อัปเดตสำเร็จ');
        } catch (PDOException $e) {
            error_log("Donation update error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'ไม่สามารถอัปเดตได้: ' . $e->getMessage(), 500);
        }
    }

    // DELETE /donations/:id (Admin)
    private function delete(string $id): array
    {
        try {
            $this->pdo->beginTransaction();

            // Get donation details first (for Ref1 if needed)
            $stmt = $this->pdo->prepare("SELECT billPaymentRef1 FROM edonation_donat_user WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $donation = $stmt->fetch();

            if (!$donation) {
                return Response::notFound('ไม่พบรายการบริจาค');
            }

            // 1. Delete associated receipts
            $sqlReceipts = "DELETE FROM edonation_receipts WHERE donation_id = :id";
            $stmtReceipts = $this->pdo->prepare($sqlReceipts);
            $stmtReceipts->execute([':id' => $id]);

            // 2. Delete the donation itself
            $sqlDonation = "DELETE FROM edonation_donat_user WHERE id = :id";
            $stmtDonation = $this->pdo->prepare($sqlDonation);
            $stmtDonation->execute([':id' => $id]);

            $this->pdo->commit();
            return Response::success(null, 'ลบรายการบริจาคสำเร็จ');
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Donation delete error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'ไม่สามารถลบรายการได้: ' . $e->getMessage(), 500);
        }
    }
}
