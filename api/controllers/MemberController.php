<?php
/**
 * Member Controller - API สำหรับสมาชิก (ใช้ id_members เป็นตัวระบุหลัก)
 * 
 * Endpoints:
 * GET  /members                                    - รายการสมาชิกทั้งหมด
 * GET  /members/lookup?id_card=XXXXXXXXXXXXX       - ค้นหาด้วยเลขบัตรประชาชน
 * GET  /members/by-member-id?id=XXXXXXXXXX         - ค้นหาด้วยรหัสสมาชิก
 * GET  /members/:id_members                        - ข้อมูลโปรไฟล์สมาชิก
 * GET  /members/:id_members/donations              - รายการบริจาคของสมาชิก
 * GET  /members/:id_members/receipts               - รายการใบเสร็จของสมาชิก
 * GET  /members/:id_members/summary                - สรุปยอดบริจาค
 */

class MemberController
{
    const VERSION = '3.0';
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function handle(string $method, ?string $id, ?string $action): array
    {
        // GET /members - รายการสมาชิกทั้งหมด
        if ($method === 'GET' && !$id) {
            return $this->index();
        }

        // GET /members/lookup?id_card=xxx
        if ($id === 'lookup') {
            return $this->lookupByIdCard();
        }

        // GET /members/by-member-id?id=xxx
        if ($id === 'by-member-id') {
            return $this->lookupByMemberId();
        }

        // GET /members/search?q=xxx
        if ($id === 'search') {
            return $this->search();
        }

        // GET /members/:id_members/donations
        if ($id && $action === 'donations') {
            return $this->getMemberDonations($id);
        }

        // GET /members/:id_members/receipts
        if ($id && $action === 'receipts') {
            return $this->getMemberReceipts($id);
        }

        // GET /members/:id_members/summary
        if ($id && $action === 'summary') {
            return $this->getMemberSummary($id);
        }

        // GET /members/:id_members - ข้อมูลโปรไฟล์
        if ($method === 'GET' && $id) {
            return $this->getMemberProfile($id);
        }

        // GET /members/:id_members/update (Legacy or mistake? Should be POST)
        // POST /members/:id_members/update - แก้ไขข้อมูลสมาชิก
        if ($method === 'POST' && $id && $action === 'update') {
            AuthMiddleware::requireAdmin();
            return $this->update($id);
        }

        // POST /members/export - Export สมาชิกที่เลือก
        if ($method === 'POST' && $id === 'export') {
            AuthMiddleware::requireAdmin();
            return $this->export();
        }

        // POST /members/sync - Sync สมาชิกจากใบเสร็จ
        if ($method === 'POST' && $id === 'sync') {
            AuthMiddleware::requireAdmin();
            return $this->sync();
        }

        return Response::error('METHOD_NOT_ALLOWED', 'Method not allowed', 405);
    }

    /**
     * POST /members/export
     * Body: { ids: string[] }
     * Return: CSV Content or Download Link
     */
    private function export(): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $ids = $data['ids'] ?? [];

        if (empty($ids)) {
            return Response::error('VALIDATION_ERROR', 'กรุณาเลือกรายการที่ต้องการ Export');
        }

        // Prepare Query
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Similar to index() query but filtered by IDs
        $sql = "SELECT 
                    r.id_members,
                    r.id_card,
                    MIN(r.payer_name) as name,
                    MAX(du.first_name) as first_name,
                    MAX(du.last_name) as last_name,
                    MAX(du.title) as title,
                    MAX(du.phone) as phone,
                    MAX(du.occupation) as occupation,
                    COUNT(r.id) as receipt_count,
                    SUM(r.amount) as total_amount,
                    MAX(r.issued_at) as last_donation,
                    MAX(du.receipt_address) as address,
                    MAX(du.address_line) as address_line,
                    MAX(du.province) as province,
                    MAX(du.amphure) as amphure,
                    MAX(du.district) as district,
                    MAX(du.zip_code) as zip_code
                FROM edonation_receipts r
                LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                WHERE r.id_members IN ($placeholders)
                GROUP BY r.id_members
                ORDER BY r.id_members ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($ids);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Generate CSV
            $output = fopen('php://temp', 'r+');
            // BOM for Excel Thai
            fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            // Header
            fputcsv($output, [
                'รหัสสมาชิก',
                'ชื่อ-นามสกุล',
                'เลขบัตรประชาชน',
                'เบอร์โทรศัพท์',
                'อาชีพ',
                'ที่อยู่',
                'จังหวัด',
                'อำเภอ',
                'ตำบล',
                'รหัสไปรษณีย์',
                'จำนวนครั้งบริจาค',
                'ยอดรวมบริจาค',
                'บริจาคล่าสุด'
            ]);

            foreach ($members as $m) {
                // Name Logic
                $name = $m['name'];
                if (empty($name) || is_numeric($name)) {
                    $name = trim(($m['title'] ?? '') . ' ' . $m['first_name'] . ' ' . ($m['last_name'] ?? ''));
                }

                // Address Logic
                $addr = $m['address'];
                if (empty($addr)) {
                    $parts = [];
                    if ($m['address_line'])
                        $parts[] = $m['address_line'];
                    if ($m['district'])
                        $parts[] = 'ต.' . $m['district'];
                    if ($m['amphure'])
                        $parts[] = 'อ.' . $m['amphure'];
                    if ($m['province'])
                        $parts[] = 'จ.' . $m['province'];
                    if ($m['zip_code'])
                        $parts[] = $m['zip_code'];
                    $addr = implode(' ', $parts);
                }

                fputcsv($output, [
                    $m['id_members'] . "\t", // Force string in Excel
                    $name,
                    $m['id_card'] . "\t",
                    $m['phone'] . "\t",
                    $m['occupation'] ?? '',
                    $addr,
                    $m['province'],
                    $m['amphure'],
                    $m['district'],
                    $m['zip_code'],
                    $m['receipt_count'],
                    $m['total_amount'],
                    $m['last_donation']
                ]);
            }

            rewind($output);
            $csvContent = stream_get_contents($output);
            fclose($output);

            // Convert to Base64 to send via JSON
            return Response::success([
                'file_name' => 'members_export_' . date('Ymd_His') . '.csv',
                'content_type' => 'text/csv',
                'content_base64' => base64_encode($csvContent)
            ], 'Export สำเร็จ');

        } catch (PDOException $e) {
            error_log("Export Error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'เกิดข้อผิดพลาดในการ Export');
        }
    }

    /**
     * POST /members/sync
     * Sync ข้อมูลสมาชิกจากตาราง edonation_receipts (Full Sync)
     * เฉพาะผู้บริจาคที่มีใบเสร็จเท่านั้น
     */
    private function sync(): array
    {
        try {
            // Count before sync
            $countBefore = $this->pdo->query("SELECT COUNT(*) FROM edonation_members")->fetchColumn();

            // Full Sync: INSERT new members, UPDATE existing members
            $sql = "INSERT INTO edonation_members (
                        id_members,
                        id_card,
                        type,
                        title,
                        first_name,
                        last_name,
                        phone,
                        occupation,
                        address_line,
                        province,
                        district,
                        subdistrict,
                        zip_code,
                        full_address,
                        shipping_address,
                        total_donated,
                        donation_count,
                        first_donation_date,
                        last_donation_date,
                        benefactor_level
                    )
                    SELECT 
                        r.id_members,
                        r.id_card,
                        CASE 
                            WHEN du.title IN ('บริษัท', 'ห้างหุ้นส่วน', 'มูลนิธิ', 'สมาคม') THEN 'juristic'
                            ELSE 'individual'
                        END as type,
                        du.title,
                        du.first_name,
                        du.last_name,
                        MAX(du.phone) as phone,
                        MAX(du.occupation) as occupation,
                        MAX(du.address_line) as address_line,
                        MAX(du.province) as province,
                        MAX(du.amphure) as district,
                        MAX(du.district) as subdistrict,
                        MAX(du.zip_code) as zip_code,
                        MAX(COALESCE(du.receipt_address, du.shipping_address, 
                            CONCAT_WS(' ', du.address_line, du.district, du.amphure, du.province, du.zip_code)
                        )) as full_address,
                        MAX(du.shipping_address) as shipping_address,
                        SUM(r.amount) as total_donated,
                        COUNT(DISTINCT r.id) as donation_count,
                        MIN(DATE(r.issued_at)) as first_donation_date,
                        MAX(DATE(r.issued_at)) as last_donation_date,
                        CASE 
                            WHEN SUM(r.amount) >= 1000000 THEN 'มหากุศลาธิยาอา'
                            WHEN SUM(r.amount) >= 500000 THEN 'กุศลาธิกาอา'
                            WHEN SUM(r.amount) >= 100000 THEN 'อุดมกุศลา'
                            WHEN SUM(r.amount) >= 50000 THEN 'มหากุศลา'
                            WHEN SUM(r.amount) >= 10000 THEN 'กุศลา'
                            ELSE 'ผู้บริจาค'
                        END as benefactor_level
                    FROM edonation_receipts r
                    LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                    WHERE r.id_members IS NOT NULL 
                      AND r.id_members != ''
                    GROUP BY r.id_members, r.id_card, du.title, du.first_name, du.last_name
                    ON DUPLICATE KEY UPDATE
                        id_card = COALESCE(VALUES(id_card), id_card),
                        phone = COALESCE(VALUES(phone), phone),
                        occupation = COALESCE(VALUES(occupation), occupation),
                        address_line = COALESCE(VALUES(address_line), address_line),
                        province = COALESCE(VALUES(province), province),
                        district = COALESCE(VALUES(district), district),
                        subdistrict = COALESCE(VALUES(subdistrict), subdistrict),
                        zip_code = COALESCE(VALUES(zip_code), zip_code),
                        full_address = COALESCE(VALUES(full_address), full_address),
                        shipping_address = COALESCE(VALUES(shipping_address), shipping_address),
                        total_donated = VALUES(total_donated),
                        donation_count = VALUES(donation_count),
                        first_donation_date = VALUES(first_donation_date),
                        last_donation_date = VALUES(last_donation_date),
                        benefactor_level = VALUES(benefactor_level),
                        updated_at = CURRENT_TIMESTAMP";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $affectedRows = $stmt->rowCount();

            // Count after sync
            $countAfter = $this->pdo->query("SELECT COUNT(*) FROM edonation_members")->fetchColumn();
            $newMembers = $countAfter - $countBefore;

            // Get summary stats
            $statsSql = "SELECT 
                            COUNT(*) as total_members,
                            SUM(total_donated) as total_donated,
                            SUM(donation_count) as total_receipts
                         FROM edonation_members";
            $stats = $this->pdo->query($statsSql)->fetch(PDO::FETCH_ASSOC);

            return Response::success([
                'synced' => $affectedRows,
                'new_members' => max(0, $newMembers),
                'updated_members' => max(0, $affectedRows - $newMembers),
                'total_members' => (int) $stats['total_members'],
                'total_donated' => (float) $stats['total_donated'],
                'total_receipts' => (int) $stats['total_receipts'],
                'synced_at' => date('Y-m-d H:i:s')
            ], 'Sync สมาชิกสำเร็จ');

        } catch (PDOException $e) {
            error_log("Sync Error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'เกิดข้อผิดพลาดในการ Sync: ' . $e->getMessage());
        }
    }

    /**
     * POST /members/:id_members/update
     * แก้ไขข้อมูลสมาชิก (ชื่อ, ที่อยู่, เบอร์โทร)
     * *Update ข้อมูลย้อนหลังทั้งหมดสำหรับ id_members นี้ (Cascade)*
     */
    private function update(string $idMembers): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        // Validation
        if (empty($data['first_name'])) {
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุชื่อ');
        }

        try {
            $this->pdo->beginTransaction();

            $title = $data['title'] ?? '';
            $firstName = $data['first_name'];
            $lastName = $data['last_name'] ?? '';
            $fullName = trim("$title $firstName $lastName");

            // 1. Update Receipts (payer_name, id_card)
            $sqlReceipts = "UPDATE edonation_receipts 
                            SET payer_name = :name, 
                                id_card = :id_card
                            WHERE id_members = :id_members";
            $stmtReceipts = $this->pdo->prepare($sqlReceipts);
            $stmtReceipts->execute([
                ':name' => $fullName,
                ':id_card' => preg_replace('/\D/', '', $data['id_card'] ?? ''),
                ':id_members' => $idMembers
            ]);

            // 2. Update Donat Users (Name, Address, Phone) linked to this member
            // ต้องหา donation_id ทั้งหมดก่อน หรือใช้ Join Update (MySQL supports Multi-table update but simple approach is better compatible)

            // Get all donation IDs
            $stmtIds = $this->pdo->prepare("SELECT donation_id FROM edonation_receipts WHERE id_members = :id_members");
            $stmtIds->execute([':id_members' => $idMembers]);
            $donationIds = $stmtIds->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($donationIds)) {
                $idsParams = implode(',', array_fill(0, count($donationIds), '?'));
                $sqlDonat = "UPDATE edonation_donat_user 
                             SET title = ?, 
                                 first_name = ?, 
                                 last_name = ?, 
                                 phone = ?, 
                                 occupation = ?,
                                 id_card = ?, 
                                 receipt_address = ?,
                                 address_line = ?,
                                 district = ?, 
                                 amphure = ?,
                                 province = ?,
                                 zip_code = ?
                             WHERE id IN ($idsParams)";

                $stmtDonat = $this->pdo->prepare($sqlDonat);
                // เรียง params ตามลำดับ ?
                $params = [
                    $title,
                    $firstName,
                    $lastName,
                    $data['phone'] ?? '',
                    $data['occupation'] ?? '', // เพิ่มอาชีพ
                    preg_replace('/\D/', '', $data['id_card'] ?? ''),
                    $data['address'] ?? '',
                    $data['address_line'] ?? '',
                    $data['district'] ?? '', // ตำบล
                    $data['amphure'] ?? '',  // อำเภอ
                    $data['province'] ?? '',
                    $data['zip_code'] ?? ''
                ];

                // Append donation IDs to params
                $params = array_merge($params, $donationIds);
                $stmtDonat->execute($params);
            }

            $this->pdo->commit();
            return Response::success(['id_members' => $idMembers], 'บันทึกข้อมูลเรียบร้อย');

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return Response::error('DATABASE_ERROR', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * GET /members
     * รายการสมาชิกทั้งหมด (ดึงจากตาราง edonation_members)
     */
    private function index(): array
    {
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(100, max(1, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $sql = "SELECT 
                    id,
                    id_members,
                    id_card,
                    type,
                    title,
                    first_name,
                    last_name,
                    full_name as name,
                    phone,
                    email,
                    occupation,
                    full_address as address,
                    total_donated,
                    donation_count as receipt_count,
                    benefactor_level,
                    first_donation_date,
                    last_donation_date,
                    is_active,
                    created_at,
                    updated_at
                FROM edonation_members
                WHERE is_active = 1
                ORDER BY last_donation_date DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Count total
        $countSql = "SELECT COUNT(*) as total FROM edonation_members WHERE is_active = 1";
        $countStmt = $this->pdo->query($countSql);
        $total = (int) $countStmt->fetch()['total'];

        // Format response
        foreach ($members as &$m) {
            $m['id_card_formatted'] = $this->formatIdCard($m['id_card'] ?? '');
            $m['total_amount'] = floatval($m['total_donated']);
            $m['receipt_count'] = (int) $m['receipt_count'];
            $m['is_repeat_donor'] = $m['receipt_count'] > 1;
            $m['donor_type'] = $this->getDonorTypeFromCount($m['receipt_count']);
        }

        return Response::success($members, null, [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ]);
    }

    /**
     * GET /members/lookup?id_card=xxx
     * ค้นหาสมาชิกจากเลขบัตรประชาชน
     */
    private function lookupByIdCard(): array
    {
        $idCard = $_GET['id_card'] ?? '';

        if (empty($idCard)) {
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุเลขบัตรประชาชน');
        }

        $cleanIdCard = preg_replace('/\D/', '', $idCard);

        if (strlen($cleanIdCard) !== 13) {
            return Response::error('VALIDATION_ERROR', 'เลขบัตรประชาชนต้องมี 13 หลัก');
        }

        // ค้นหา id_members จาก id_card
        $stmt = $this->pdo->prepare("SELECT id_members FROM edonation_receipts WHERE id_card = :id LIMIT 1");
        $stmt->execute([':id' => $cleanIdCard]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result || empty($result['id_members'])) {
            return Response::notFound('ไม่พบข้อมูลสมาชิก กรุณาตรวจสอบเลขบัตรประชาชน');
        }

        return $this->getMemberProfile($result['id_members']);
    }

    /**
     * GET /members/by-member-id?id=xxx
     * ค้นหาสมาชิกจาก id_members (10 หลัก)
     */
    private function lookupByMemberId(): array
    {
        $memberId = $_GET['id'] ?? '';

        if (empty($memberId)) {
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุรหัสสมาชิก');
        }

        return $this->getMemberProfile($memberId);
    }

    /**
     * GET /members/search?q=xxx
     * ค้นหาสมาชิกด้วยชื่อ, เลขบัตร, หรือรหัสสมาชิก (ดึงจากตาราง edonation_members)
     */
    private function search(): array
    {
        $query = trim($_GET['q'] ?? '');
        $type = $_GET['type'] ?? 'all'; // all, name, id_card
        $limit = min(50, max(1, intval($_GET['limit'] ?? 20)));

        $sql = "SELECT 
                    id,
                    id_members,
                    id_card,
                    type,
                    title,
                    first_name,
                    last_name,
                    full_name as name,
                    phone,
                    email,
                    occupation,
                    address_line,
                    province,
                    district,
                    subdistrict,
                    zip_code,
                    full_address as address,
                    total_donated as total_amount,
                    donation_count as receipt_count,
                    benefactor_level,
                    last_donation_date
                FROM edonation_members
                WHERE is_active = 1";

        $params = [];

        if (!empty($query)) {
            switch ($type) {
                case 'name':
                    $sql .= " AND (
                        full_name LIKE :q1 
                        OR first_name LIKE :q2
                        OR last_name LIKE :q3
                    )";
                    $searchVal = '%' . $query . '%';
                    $params[':q1'] = $searchVal;
                    $params[':q2'] = $searchVal;
                    $params[':q3'] = $searchVal;
                    break;

                case 'id_card':
                    $sql .= " AND id_card LIKE :q1";
                    $params[':q1'] = '%' . preg_replace('/\D/', '', $query) . '%';
                    break;

                default: // all
                    $sql .= " AND (
                        full_name LIKE :q1 
                        OR id_card LIKE :q2 
                        OR id_members LIKE :q3
                        OR first_name LIKE :q4
                        OR last_name LIKE :q5
                        OR phone LIKE :q6
                    )";
                    $searchVal = '%' . $query . '%';
                    $params[':q1'] = $searchVal;
                    $params[':q2'] = '%' . preg_replace('/\D/', '', $query) . '%';
                    $params[':q3'] = $searchVal;
                    $params[':q4'] = $searchVal;
                    $params[':q5'] = $searchVal;
                    $params[':q6'] = '%' . preg_replace('/\D/', '', $query) . '%';
            }
        }

        $sql .= " ORDER BY last_donation_date DESC LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as &$m) {
            $m['id_card_formatted'] = $this->formatIdCard($m['id_card'] ?? '');
            $m['total_amount'] = floatval($m['total_amount']);
            $m['receipt_count'] = (int) $m['receipt_count'];
        }

        return Response::success($results, null, [
            'count' => count($results),
            'query' => $query,
            'type' => $type
        ]);
    }

    /**
     * GET /members/:id_members
     * ดึงข้อมูลโปรไฟล์สมาชิกจาก id_members (ดึงจากตาราง edonation_members)
     */
    private function getMemberProfile(string $idMembers): array
    {
        // ดึงข้อมูลจากตาราง edonation_members
        $sql = "SELECT * FROM edonation_members WHERE id_members = :id_members AND is_active = 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_members' => $idMembers]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$member) {
            return Response::notFound('ไม่พบข้อมูลสมาชิก');
        }

        // ดึงโครงการที่เคยบริจาค
        $projectsSql = "SELECT 
                            du.project_name,
                            du.project_number,
                            COUNT(*) as count,
                            SUM(r.amount) as total
                        FROM edonation_receipts r
                        LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                        WHERE r.id_members = :id_members
                        GROUP BY du.project_name, du.project_number
                        ORDER BY total DESC
                        LIMIT 5";
        $projectsStmt = $this->pdo->prepare($projectsSql);
        $projectsStmt->execute([':id_members' => $idMembers]);
        $projects = $projectsStmt->fetchAll(PDO::FETCH_ASSOC);

        // กำหนดระดับผู้มีอุปการคุณ
        $totalAmount = floatval($member['total_amount']);
        $benefactorLevel = $this->getBenefactorLevel($totalAmount);

        // นับจำนวนปีที่บริจาค (ใช้วัดความต่อเนื่อง)
        $yearsSql = "SELECT DISTINCT YEAR(r.issued_at) as year 
                     FROM edonation_receipts r 
                     WHERE r.id_members = :id_members 
                     ORDER BY year DESC";
        $yearsStmt = $this->pdo->prepare($yearsSql);
        $yearsStmt->execute([':id_members' => $idMembers]);
        $donationYears = $yearsStmt->fetchAll(PDO::FETCH_COLUMN);

        // คำนวณความถี่การบริจาค
        $receiptCount = (int) $member['donation_count'];
        $isRepeatDonor = $receiptCount > 1;
        $donorType = 'new';
        if ($receiptCount >= 10) {
            $donorType = 'loyal'; // ผู้บริจาคประจำ
        } elseif ($receiptCount >= 5) {
            $donorType = 'regular'; // ผู้บริจาคสม่ำเสมอ
        } elseif ($receiptCount >= 2) {
            $donorType = 'repeat'; // ผู้บริจาคซ้ำ
        }

        $totalAmount = floatval($member['total_donated']);

        return Response::success([
            'id' => $member['id'],
            'id_members' => $member['id_members'],
            'id_card' => $member['id_card'],
            'id_card_formatted' => $this->formatIdCard($member['id_card'] ?? ''),
            'type' => $member['type'],
            'name' => $member['full_name'] ?? trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')),
            'title' => $member['title'],
            'first_name' => $member['first_name'],
            'last_name' => $member['last_name'],
            'phone' => $member['phone'],
            'email' => $member['email'],
            'occupation' => $member['occupation'] ?? '',
            'address' => [
                'full' => $member['full_address'],
                'address_line' => $member['address_line'],
                'province' => $member['province'],
                'district' => $member['district'],
                'subdistrict' => $member['subdistrict'],
                'zip_code' => $member['zip_code']
            ],
            'statistics' => [
                'receipt_count' => $receiptCount,
                'total_amount' => $totalAmount,
                'first_donation_date' => $member['first_donation_date'],
                'last_donation_date' => $member['last_donation_date'],
                'donation_years' => $donationYears,
                'years_active' => count($donationYears)
            ],
            'donation_frequency' => [
                'is_repeat_donor' => $isRepeatDonor,
                'donation_count' => $receiptCount,
                'donor_type' => $donorType,
                'donor_type_label' => $this->getDonorTypeLabel($donorType)
            ],
            'benefactor_level' => $member['benefactor_level'] ?? $this->getBenefactorLevel($totalAmount),
            'top_projects' => $projects,
            'api_version' => self::VERSION
        ], 'พบข้อมูลสมาชิก');
    }

    /**
     * GET /members/:id_members/donations
     * รายการบริจาคของสมาชิก
     */
    private function getMemberDonations(string $idMembers): array
    {
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(100, max(1, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $sql = "SELECT 
                    r.id as receipt_id,
                    r.receipt_no,
                    r.amount,
                    r.issued_at,
                    r.donation_id,
                    du.project_name,
                    du.project_number,
                    du.payby as payment_method,
                    du.created_at as donation_date
                FROM edonation_receipts r
                LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                WHERE r.id_members = :id_members
                ORDER BY r.issued_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_members', $idMembers);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Count total
        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM edonation_receipts WHERE id_members = :id");
        $countStmt->execute([':id' => $idMembers]);
        $total = (int) $countStmt->fetchColumn();

        foreach ($donations as &$d) {
            $d['amount'] = floatval($d['amount']);
        }

        return Response::success($donations, null, [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit),
            'id_members' => $idMembers
        ]);
    }

    /**
     * GET /members/:id_members/receipts
     * รายการใบเสร็จของสมาชิก
     */
    private function getMemberReceipts(string $idMembers): array
    {
        $sql = "SELECT 
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
                WHERE r.id_members = :id_members
                ORDER BY r.issued_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_members' => $idMembers]);
        $receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($receipts as &$r) {
            $r['amount'] = floatval($r['amount']);
            $r['can_download'] = true;
        }

        return Response::success($receipts, null, [
            'count' => count($receipts),
            'id_members' => $idMembers
        ]);
    }

    /**
     * GET /members/:id_members/summary
     * สรุปยอดบริจาคของสมาชิก
     */
    private function getMemberSummary(string $idMembers): array
    {
        // ยอดรวมทั้งหมด
        $totalSql = "SELECT 
                        SUM(amount) as total_amount,
                        COUNT(*) as total_receipts,
                        MIN(issued_at) as first_date,
                        MAX(issued_at) as last_date
                     FROM edonation_receipts 
                     WHERE id_members = :id";
        $totalStmt = $this->pdo->prepare($totalSql);
        $totalStmt->execute([':id' => $idMembers]);
        $totals = $totalStmt->fetch(PDO::FETCH_ASSOC);

        if (!$totals || $totals['total_receipts'] == 0) {
            return Response::notFound('ไม่พบข้อมูลสมาชิก');
        }

        // สรุปตามปี
        $yearSql = "SELECT 
                        du.fiscal_year as year,
                        SUM(r.amount) as amount,
                        COUNT(*) as count
                    FROM edonation_receipts r
                    LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                    WHERE r.id_members = :id
                    GROUP BY du.fiscal_year
                    ORDER BY du.fiscal_year DESC";
        $yearStmt = $this->pdo->prepare($yearSql);
        $yearStmt->execute([':id' => $idMembers]);
        $byYear = $yearStmt->fetchAll(PDO::FETCH_ASSOC);

        // สรุปตามโครงการ
        $projectSql = "SELECT 
                            du.project_name,
                            du.project_number,
                            SUM(r.amount) as amount,
                            COUNT(*) as count
                       FROM edonation_receipts r
                       LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                       WHERE r.id_members = :id
                       GROUP BY du.project_name, du.project_number
                       ORDER BY amount DESC";
        $projectStmt = $this->pdo->prepare($projectSql);
        $projectStmt->execute([':id' => $idMembers]);
        $byProject = $projectStmt->fetchAll(PDO::FETCH_ASSOC);

        $totalAmount = floatval($totals['total_amount']);

        return Response::success([
            'id_members' => $idMembers,
            'total_amount' => $totalAmount,
            'total_receipts' => (int) $totals['total_receipts'],
            'first_donation_date' => $totals['first_date'],
            'last_donation_date' => $totals['last_date'],
            'by_fiscal_year' => $byYear,
            'by_project' => $byProject,
            'benefactor_level' => $this->getBenefactorLevel($totalAmount)
        ], 'สรุปยอดบริจาค');
    }

    /**
     * Format ID card with dashes (X-XXXX-XXXXX-XX-X)
     */
    private function formatIdCard(string $idCard): string
    {
        $clean = preg_replace('/\D/', '', $idCard);
        if (strlen($clean) !== 13) {
            return $idCard;
        }
        return substr($clean, 0, 1) . '-' .
            substr($clean, 1, 4) . '-' .
            substr($clean, 5, 5) . '-' .
            substr($clean, 10, 2) . '-' .
            substr($clean, 12, 1);
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

        return null;
    }

    /**
     * ชื่อประเภทผู้บริจาคเป็นภาษาไทย
     */
    private function getDonorTypeLabel(string $type): string
    {
        $labels = [
            'new' => 'ผู้บริจาคใหม่',
            'repeat' => 'ผู้บริจาคซ้ำ',
            'regular' => 'ผู้บริจาคสม่ำเสมอ',
            'loyal' => 'ผู้บริจาคประจำ'
        ];
        return $labels[$type] ?? 'ผู้บริจาค';
    }

    /**
     * กำหนดประเภทผู้บริจาคจากจำนวนครั้ง
     */
    private function getDonorTypeFromCount(int $count): string
    {
        if ($count >= 10)
            return 'loyal';
        if ($count >= 5)
            return 'regular';
        if ($count >= 2)
            return 'repeat';
        return 'new';
    }

    /**
     * แยกคำนำหน้าจากชื่อเต็ม
     */
    private function extractTitleFromName(string $name): ?string
    {
        $titles = ['นาย', 'นาง', 'นางสาว', 'ด.ช.', 'ด.ญ.', 'บริษัท', 'ห้างหุ้นส่วน', 'มูลนิธิ', 'สมาคม', 'Mr.', 'Mrs.', 'Miss', 'Ms.'];

        foreach ($titles as $title) {
            if (strpos($name, $title) === 0) {
                return $title;
            }
        }

        return null;
    }
}
