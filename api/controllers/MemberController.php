<?php
/**
 * Member Controller - API สำหรับสมาชิก (ผู้บริจาคที่ชำระเงินแล้ว)
 * 
 * Endpoints:
 * GET  /members/lookup?id_card=XXXXXXXXXXXXX    - ค้นหาประวัติด้วยเลขบัตรประชาชน
 * GET  /members/:id_card/donations              - รายการบริจาคทั้งหมดของสมาชิก
 * GET  /members/:id_card/receipts               - รายการใบเสร็จทั้งหมดของสมาชิก
 * GET  /members/:id_card/summary                - สรุปยอดบริจาคทั้งหมด
 */

class MemberController
{
    const VERSION = '2.0';
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function handle(string $method, ?string $id, ?string $action): array
    {
        // id ในที่นี้คือ id_card หรือ action เฉพาะ

        // GET /members/lookup?id_card=xxx
        if ($id === 'lookup') {
            return $this->lookup();
        }

        // GET /members/search?q=xxx - ค้นหาด้วยชื่อ, เลขบัตร, หรือคำค้น
        if ($id === 'search') {
            return $this->search();
        }

        // GET /members/:id_card/donations
        if ($id && $action === 'donations') {
            return $this->getDonations($id);
        }

        // GET /members/:id_card/receipts
        if ($id && $action === 'receipts') {
            return $this->getReceipts($id);
        }

        // GET /members/:id_card/summary
        if ($id && $action === 'summary') {
            return $this->getSummary($id);
        }

        // GET /members/:id_card - ข้อมูลสมาชิก
        if ($method === 'GET' && $id) {
            return $this->getMember($id);
        }

        return Response::error('METHOD_NOT_ALLOWED', 'Method not allowed', 405);
    }

    /**
     * GET /members/lookup?id_card=xxx
     * ค้นหาสมาชิกจากเลขบัตรประชาชน
     */
    private function lookup(): array
    {
        $idCard = $_GET['id_card'] ?? '';

        if (empty($idCard)) {
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุเลขบัตรประชาชน');
        }

        // Remove dashes
        $cleanIdCard = preg_replace('/\D/', '', $idCard);

        if (strlen($cleanIdCard) !== 13) {
            return Response::error('VALIDATION_ERROR', 'เลขบัตรประชาชนต้องมี 13 หลัก');
        }

        return $this->getMember($cleanIdCard);
    }

    /**
     * GET /members/search?q=xxx&type=name|id_card|all
     * ค้นหาผู้บริจาคด้วยชื่อ, เลขบัตรประชาชน, หรือคำค้นทั่วไป
     */
    private function search(): array
    {
        $query = trim($_GET['q'] ?? '');
        $type = $_GET['type'] ?? 'all';
        $limit = min(50, max(1, intval($_GET['limit'] ?? 20)));

        // Removed empty query validation to allow listing

        $results = [];

        // ค้นหาจาก edonation_donat_user (รายการบริจาคใหม่)
        try {
            $sql = "SELECT 
                        id,
                        CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as name,
                        first_name,
                        last_name,
                        id_card,
                        phone,
                        receipt_address as address,
                        project_name,
                        project_number,
                        amount,
                        status_donat,
                        created_at as donation_date,
                        payby as payment_method,
                        'donat_user' as source
                    FROM edonation_donat_user 
                    WHERE status_donat = 'completed' ";

            $params = [];
            $conditions = [];

            if (!empty($query)) {
                $sql .= " AND (";

                if ($type === 'name' || $type === 'all') {
                    $conditions[] = "CONCAT(first_name, ' ', last_name) LIKE :name_query";
                    $params[':name_query'] = '%' . $query . '%';
                }

                if ($type === 'id_card' || $type === 'all') {
                    $cleanQuery = preg_replace('/\D/', '', $query);
                    if (!empty($cleanQuery)) {
                        $conditions[] = "id_card LIKE :id_query";
                        $params[':id_query'] = '%' . $cleanQuery . '%';
                    }
                }

                if (empty($conditions)) {
                    $conditions[] = "1=0"; // No match
                }

                $sql .= implode(' OR ', $conditions) . ") ";
            }

            $sql .= "ORDER BY created_at DESC LIMIT :limit";

            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $donatResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($donatResults as $row) {
                $results[] = [
                    'id' => (int) $row['id'],
                    'name' => trim($row['name']),
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'id_card' => $row['id_card'],
                    'id_card_formatted' => $this->formatIdCard($row['id_card'] ?? ''),
                    'phone' => $row['phone'],
                    'address' => $row['address'],
                    'project_name' => $row['project_name'],
                    'project_number' => $row['project_number'],
                    'amount' => floatval($row['amount']),
                    'donation_date' => $row['donation_date'],
                    'payment_method' => $row['payment_method'],
                    'source' => 'donat_user',
                    'source_label' => 'รายการบริจาค'
                ];
            }
        } catch (PDOException $e) {
            error_log("Search donat_user error: " . $e->getMessage());
        }

        // ค้นหาจาก bank_transactions (เฉพาะเมื่อค้นหาด้วยชื่อหรือเลขบัตร)
        if (!empty($query)) {
            try {
                $sql = "SELECT 
                            bt.id,
                            bt.payerAccountName as name,
                            bt.billPaymentRef2 as id_card,
                            bt.amount,
                            bt.transactionDateandTime as donation_date,
                            du.project_name,
                            du.project_number,
                            du.phone,
                            du.receipt_address as address,
                            'bank' as source
                        FROM edonation_bank_transactions bt
                        LEFT JOIN edonation_donat_user du ON bt.billPaymentRef1 COLLATE utf8mb4_unicode_ci = du.billPaymentRef1
                        WHERE ";

                $params = [];
                $conditions = [];

                if ($type === 'name' || $type === 'all') {
                    $conditions[] = "bt.payerAccountName LIKE :name_query";
                    $params[':name_query'] = '%' . $query . '%';
                }

                if ($type === 'id_card' || $type === 'all') {
                    $cleanQuery = preg_replace('/\D/', '', $query);
                    if (!empty($cleanQuery)) {
                        $conditions[] = "bt.billPaymentRef2 LIKE :id_query";
                        $params[':id_query'] = '%' . $cleanQuery . '%';
                    }
                }

                if (empty($conditions)) {
                    $conditions[] = "1=0";
                }

                $sql .= "(" . implode(' OR ', $conditions) . ") ORDER BY bt.transactionDateandTime DESC LIMIT :limit";

                $stmt = $this->pdo->prepare($sql);
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();

                $bankResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($bankResults as $row) {
                    // หลีกเลี่ยงข้อมูลซ้ำ
                    $exists = false;
                    foreach ($results as $r) {
                        if (!empty($row['id_card']) && $r['id_card'] === $row['id_card']) {
                            $exists = true;
                            break;
                        }
                    }

                    if (!$exists) {
                        $results[] = [
                            'id' => 'bank_' . $row['id'],
                            'name' => trim($row['name']),
                            'first_name' => '',
                            'last_name' => '',
                            'id_card' => $row['id_card'],
                            'id_card_formatted' => $this->formatIdCard($row['id_card'] ?? ''),
                            'phone' => $row['phone'],
                            'address' => $row['address'],
                            'project_name' => $row['project_name'],
                            'project_number' => $row['project_number'],
                            'amount' => floatval($row['amount']),
                            'donation_date' => $row['donation_date'],
                            'payment_method' => 'bank_transfer',
                            'source' => 'bank_transaction',
                            'source_label' => 'รายการธนาคาร'
                        ];
                    }
                }
            } catch (PDOException $e) {
                error_log("Search bank error: " . $e->getMessage());
            }
        }

        return Response::success($results, null, [
            'count' => count($results),
            'limit' => $limit,
            'query' => $query
        ]);
    }

    /**
     * GET /members/:id_card
     * ดึงข้อมูลสมาชิกจากเลขบัตรประชาชน
     */
    private function getMember(string $idCard): array
    {
        $cleanIdCard = preg_replace('/\D/', '', $idCard);

        // ค้นหาจากหลายตาราง:
        // 1. bank_transactions.billPaymentRef2 (Tax ID)
        // 2. donat_user.id_card
        // 3. receipt_2566, receipt_2567.billPaymentRef2

        $memberData = null;
        $sources = [];

        // ค้นหาจาก bank_transactions (ข้อมูลล่าสุด)
        $bankSql = "SELECT 
                        payerAccountName as name,
                        payerName,
                        billPaymentRef2 as id_card,
                        COUNT(*) as transaction_count,
                        SUM(amount) as total_amount,
                        MAX(transactionDateandTime) as last_donation_date
                    FROM edonation_bank_transactions 
                    WHERE billPaymentRef2 = :id_card
                    GROUP BY payerAccountName, payerName, billPaymentRef2";

        $stmt = $this->pdo->prepare($bankSql);
        $stmt->execute([':id_card' => $cleanIdCard]);
        $bankResult = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($bankResult) {
            $memberData = [
                'name' => $bankResult['payerAccountName'] ?: $bankResult['payerName'],
                'id_card' => $this->formatIdCard($cleanIdCard),
                'id_card_raw' => $cleanIdCard,
                'transaction_count' => (int) $bankResult['transaction_count'],
                'total_amount' => floatval($bankResult['total_amount']),
                'last_donation_date' => $bankResult['last_donation_date']
            ];
            $sources[] = 'bank_transactions';
        }

        // ค้นหาจาก donat_user
        $donatSql = "SELECT 
                        CONCAT(first_name, ' ', last_name) as name,
                        id_card,
                        phone,
                        COUNT(*) as donation_count,
                        SUM(amount) as total_amount,
                        MAX(created_at) as last_donation_date
                     FROM edonation_donat_user 
                     WHERE id_card = :id_card AND status_donat = 'completed'
                     GROUP BY first_name, last_name, id_card, phone";

        $stmt = $this->pdo->prepare($donatSql);
        $stmt->execute([':id_card' => $cleanIdCard]);
        $donatResult = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($donatResult) {
            if (!$memberData) {
                $memberData = [
                    'name' => trim($donatResult['name']),
                    'id_card' => $this->formatIdCard($cleanIdCard),
                    'id_card_raw' => $cleanIdCard,
                    'phone' => $donatResult['phone'],
                    'transaction_count' => (int) $donatResult['donation_count'],
                    'total_amount' => floatval($donatResult['total_amount']),
                    'last_donation_date' => $donatResult['last_donation_date']
                ];
            } else {
                // Merge data
                $memberData['phone'] = $donatResult['phone'];
                $memberData['transaction_count'] += (int) $donatResult['donation_count'];
                $memberData['total_amount'] += floatval($donatResult['total_amount']);
            }
            $sources[] = 'donat_user';
        }

        if (!$memberData) {
            return Response::notFound('ไม่พบข้อมูลสมาชิก กรุณาตรวจสอบเลขบัตรประชาชน');
        }

        $memberData['data_sources'] = $sources;
        $memberData['is_member'] = true;

        return Response::success($memberData, 'พบข้อมูลสมาชิก');
    }

    /**
     * GET /members/:id_card/donations
     * รายการบริจาคทั้งหมดของสมาชิก
     */
    private function getDonations(string $idCard): array
    {
        $cleanIdCard = preg_replace('/\D/', '', $idCard);
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(100, max(1, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $donations = [];

        // 1. ดึงจาก bank_transactions
        $bankSql = "SELECT 
                        'bank' as source,
                        bt.id,
                        bt.transactionId,
                        bt.amount,
                        bt.payerAccountName as payer_name,
                        bt.sendingBankCode as bank_code,
                        bt.transactionDateandTime as donation_date,
                        du.project_name,
                        du.project_number,
                        r.receipt_no
                    FROM edonation_bank_transactions bt
                    LEFT JOIN edonation_donat_user du ON bt.billPaymentRef1 COLLATE utf8mb4_unicode_ci = du.billPaymentRef1
                    LEFT JOIN edonation_receipts r ON bt.id = r.bank_transaction_id
                    WHERE bt.billPaymentRef2 = :id_card
                    ORDER BY bt.transactionDateandTime DESC";

        $stmt = $this->pdo->prepare($bankSql);
        $stmt->execute([':id_card' => $cleanIdCard]);
        $bankDonations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($bankDonations as $d) {
            $donations[] = [
                'id' => 'bank_' . $d['id'],
                'source' => 'bank_transactions',
                'transaction_id' => $d['transactionId'],
                'amount' => floatval($d['amount']),
                'payer_name' => $d['payer_name'],
                'project_name' => $d['project_name'],
                'project_number' => $d['project_number'],
                'receipt_no' => $d['receipt_no'],
                'donation_date' => $d['donation_date'],
                'bank_code' => $d['bank_code']
            ];
        }

        // Sort by date descending
        usort($donations, function ($a, $b) {
            return strtotime($b['donation_date'] ?? '1970-01-01') - strtotime($a['donation_date'] ?? '1970-01-01');
        });

        // Paginate
        $total = count($donations);
        $donations = array_slice($donations, $offset, $limit);

        return Response::success($donations, null, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit),
            'id_card' => $this->formatIdCard($cleanIdCard)
        ]);
    }

    /**
     * GET /members/:id_card/receipts
     * รายการใบเสร็จทั้งหมดของสมาชิก
     */
    private function getReceipts(string $idCard): array
    {
        $cleanIdCard = preg_replace('/\D/', '', $idCard);
        $receipts = [];

        // 1. ดึงจาก edonation_receipts (ใบเสร็จใหม่)
        $receiptsSql = "SELECT 
                            r.id,
                            r.receipt_no,
                            r.payer_name,
                            r.amount,
                            r.issued_at,
                            du.project_name,
                            du.project_number,
                            du.fiscal_year
                        FROM edonation_receipts r
                        LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                        LEFT JOIN edonation_bank_transactions bt ON r.bank_transaction_id = bt.id
                        WHERE bt.billPaymentRef2 = :id_card
                        ORDER BY r.issued_at DESC";

        $stmt = $this->pdo->prepare($receiptsSql);
        $stmt->execute([':id_card' => $cleanIdCard]);
        $newReceipts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($newReceipts as $r) {
            $receipts[] = [
                'id' => $r['id'],
                'source' => 'receipts',
                'receipt_no' => $r['receipt_no'],
                'payer_name' => $r['payer_name'],
                'amount' => floatval($r['amount']),
                'project_name' => $r['project_name'],
                'project_number' => $r['project_number'],
                'fiscal_year' => $r['fiscal_year'],
                'issued_at' => $r['issued_at'],
                'can_download' => true
            ];
        }

        // Sort by issued_at descending
        usort($receipts, function ($a, $b) {
            return strtotime($b['issued_at'] ?? '1970-01-01') - strtotime($a['issued_at'] ?? '1970-01-01');
        });

        return Response::success($receipts, null, [
            'count' => count($receipts),
            'id_card' => $this->formatIdCard($cleanIdCard)
        ]);
    }

    /**
     * GET /members/:id_card/summary
     * สรุปยอดบริจาคทั้งหมดของสมาชิก
     */
    private function getSummary(string $idCard): array
    {
        $cleanIdCard = preg_replace('/\D/', '', $idCard);

        $summary = [
            'id_card' => $this->formatIdCard($cleanIdCard),
            'total_amount' => 0,
            'total_donations' => 0,
            'by_fiscal_year' => [],
            'by_project' => [],
            'first_donation_date' => null,
            'last_donation_date' => null
        ];

        $allDonations = [];

        // 1. ดึงจาก bank_transactions
        // Use COLLATE to fix collation mismatch between tables
        $bankSql = "SELECT 
                        bt.amount,
                        bt.transactionDateandTime as donation_date,
                        du.project_name,
                        du.project_number,
                        du.fiscal_year
                    FROM edonation_bank_transactions bt
                    LEFT JOIN edonation_donat_user du ON bt.billPaymentRef1 COLLATE utf8mb4_unicode_ci = du.billPaymentRef1
                    WHERE bt.billPaymentRef2 = :id_card";

        $stmt = $this->pdo->prepare($bankSql);
        $stmt->execute([':id_card' => $cleanIdCard]);
        $bankDonations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($bankDonations as $d) {
            $allDonations[] = [
                'amount' => floatval($d['amount']),
                'date' => $d['donation_date'],
                'fiscal_year' => $d['fiscal_year'] ?? date('Y') + 543,
                'project' => $d['project_name'] ?? 'ไม่ระบุ',
                'project_number' => $d['project_number'] ?? ''
            ];
        }

        if (empty($allDonations)) {
            return Response::notFound('ไม่พบประวัติการบริจาค');
        }

        // คำนวณสรุป
        $byYear = [];
        $byProject = [];
        $dates = [];

        foreach ($allDonations as $d) {
            $summary['total_amount'] += $d['amount'];
            $summary['total_donations']++;

            // By fiscal year
            $year = $d['fiscal_year'];
            if (!isset($byYear[$year])) {
                $byYear[$year] = ['year' => $year, 'amount' => 0, 'count' => 0];
            }
            $byYear[$year]['amount'] += $d['amount'];
            $byYear[$year]['count']++;

            // By project
            $project = $d['project'];
            if (!isset($byProject[$project])) {
                $byProject[$project] = [
                    'project_name' => $project,
                    'project_number' => $d['project_number'],
                    'amount' => 0,
                    'count' => 0
                ];
            }
            $byProject[$project]['amount'] += $d['amount'];
            $byProject[$project]['count']++;

            // Dates
            if ($d['date']) {
                $dates[] = $d['date'];
            }
        }

        // Sort and format
        krsort($byYear);
        usort($byProject, function ($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });

        $summary['by_fiscal_year'] = array_values($byYear);
        $summary['by_project'] = array_values($byProject);

        if (!empty($dates)) {
            sort($dates);
            $summary['first_donation_date'] = $dates[0];
            $summary['last_donation_date'] = end($dates);
        }

        // กำหนดระดับผู้มีอุปการคุณ
        $summary['benefactor_level'] = $this->getBenefactorLevel($summary['total_amount']);

        return Response::success($summary, 'สรุปยอดบริจาค');
    }

    /**
     * Format ID card with dashes (X-XXXX-XXXXX-XX-X)
     */
    private function formatIdCard(string $idCard): string
    {
        if (strlen($idCard) !== 13) {
            return $idCard;
        }
        return substr($idCard, 0, 1) . '-' .
            substr($idCard, 1, 4) . '-' .
            substr($idCard, 5, 5) . '-' .
            substr($idCard, 10, 2) . '-' .
            substr($idCard, 12, 1);
    }

    /**
     * กำหนดระดับผู้มีอุปการคุณจากยอดบริจาครวม
     */
    private function getBenefactorLevel(float $totalAmount): ?array
    {
        $levels = [
            ['min' => 30000000, 'name' => 'ขั้นที่ 1 ปฐมดิเรกคุณากรณ์', 'level' => 1],
            ['min' => 14000000, 'name' => 'ขั้นที่ 2 ทุติยดิเรกคุณาภรณ์', 'level' => 2],
            ['min' => 6000000, 'name' => 'ขั้นที่ 3 ตติยดิเรกคุณาภรณ์', 'level' => 3],
            ['min' => 1500000, 'name' => 'ขั้นที่ 4 จตุตถดิเรกคุณาภรณ์', 'level' => 4],
            ['min' => 500000, 'name' => 'ขั้นที่ 5 เบญจมดิเรกคุณาภรณ์', 'level' => 5],
            ['min' => 200000, 'name' => 'ขั้นที่ 6 เหรียญทองแดงดิเรกคุณาภรณ์', 'level' => 6],
            ['min' => 100000, 'name' => 'ขั้นที่ 7 เหรียญเงินดิเรกคุณาภรณ์', 'level' => 7]
        ];

        foreach ($levels as $level) {
            if ($totalAmount >= $level['min']) {
                return [
                    'level' => $level['level'],
                    'name' => $level['name'],
                    'min_amount' => $level['min']
                ];
            }
        }

        return null; // ยังไม่ถึงระดับใดๆ
    }
}
