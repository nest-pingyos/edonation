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

        // GET /members/stats - สถิติภาพรวม
        if ($id === 'stats') {
            return $this->getStats();
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

        // POST /members/sync - Sync สมาชิกจากใบเสร็จ
        if ($method === 'POST' && $id === 'sync') {
            AuthMiddleware::requireAdmin();
            return $this->sync();
        }

        return Response::error('METHOD_NOT_ALLOWED', 'Method not allowed', 405);
    }

    /**
     * ดึงข้อมูล Transaction รายละเอียดของสมาชิกที่เลือก (สำหรับ Internal Use / admin export_members.php)
     * - ข้อมูลสมาชิกมาจาก edonation_members (canonical)
     * - รองรับใบเสร็จเก่าที่ match ด้วย id_card แทน id_members
     * - กรองใบเสร็จที่ถูกยกเลิก (status = 2)
     */
    public function getTransactionsForExport(array $ids): array
    {
        if (empty($ids)) return [];

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "SELECT
                    m.id_members,
                    m.id_card,
                    m.full_name,
                    m.title,
                    m.first_name,
                    m.last_name,
                    m.phone,
                    m.email,
                    m.occupation,
                    m.full_address      AS address_full,
                    m.address_line,
                    m.province,
                    m.district          AS amphure,
                    m.subdistrict       AS district,
                    m.zip_code,
                    r.issued_at,
                    r.receipt_no        AS receipt_number,
                    r.amount,
                    r.payer_name        AS receipt_name,
                    du.project_name
                FROM edonation_members m
                LEFT JOIN edonation_receipts r
                    ON (
                        r.id_members = m.id_members
                        OR (
                            m.id_card IS NOT NULL AND m.id_card != ''
                            AND r.id_card = m.id_card
                            AND (r.id_members IS NULL OR r.id_members = '')
                        )
                    )
                LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                WHERE m.id_members IN ($placeholders)
                  AND m.is_active = 1
                  AND (r.id IS NULL OR r.status != 2)
                ORDER BY m.id_members ASC, r.issued_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    stats.id_members,
                    LEFT(du.id_card, 13) as id_card,
                    CASE 
                        WHEN du.title IN ('บริษัท', 'ห้างหุ้นส่วน', 'มูลนิธิ', 'สมาคม') THEN 'juristic'
                        ELSE 'individual'
                    END as type,
                    COALESCE(du.title, '') as title,
                    COALESCE(du.first_name, '') as first_name,
                    COALESCE(du.last_name, '') as last_name,
                    LEFT(du.phone, 20) as phone,
                    du.occupation,
                    du.address_line,
                    du.province,
                    du.amphure as district,
                    du.district as subdistrict,
                    du.zip_code,
                    COALESCE(du.receipt_address, du.shipping_address, 
                        CONCAT_WS(' ', du.address_line, du.district, du.amphure, du.province, du.zip_code)
                    ) as full_address,
                    du.shipping_address,
                    stats.total_donated,
                    stats.donation_count,
                    stats.first_donation_date,
                    stats.last_donation_date,
                    CASE 
                        WHEN stats.total_donated >= 1000000 THEN 'มหากุศลาธิยาอา'
                        WHEN stats.total_donated >= 500000 THEN 'กุศลาธิกาอา'
                        WHEN stats.total_donated >= 100000 THEN 'อุดมกุศลา'
                        WHEN stats.total_donated >= 50000 THEN 'มหากุศลา'
                        WHEN stats.total_donated >= 10000 THEN 'กุศลา'
                        ELSE 'ผู้บริจาค'
                    END as benefactor_level
                FROM (
                    -- Subquery: Calculate Totals & Find Latest Receipt ID
                    SELECT 
                        id_members,
                        SUM(amount) as total_donated,
                        COUNT(DISTINCT id) as donation_count,
                        MIN(DATE(issued_at)) as first_donation_date,
                        MAX(DATE(issued_at)) as last_donation_date,
                        SUBSTRING_INDEX(GROUP_CONCAT(id ORDER BY issued_at DESC, id DESC), ',', 1) as latest_receipt_id
                    FROM edonation_receipts
                    WHERE id_members IS NOT NULL 
                      AND id_members != ''
                    GROUP BY id_members
                ) as stats
                JOIN edonation_receipts r ON stats.latest_receipt_id = r.id
                LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                    ON DUPLICATE KEY UPDATE
                        id_card = VALUES(id_card),
                        phone = VALUES(phone),
                        occupation = VALUES(occupation),
                        address_line = VALUES(address_line),
                        province = VALUES(province),
                        district = VALUES(district),
                        subdistrict = VALUES(subdistrict),
                        zip_code = VALUES(zip_code),
                        full_address = VALUES(full_address),
                        shipping_address = VALUES(shipping_address),
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

        } catch (\Exception $e) {
            error_log("Sync Error: " . $e->getMessage());
            error_log("Sync SQL: " . $sql);
            return Response::error('SYNC_ERROR', 'เกิดข้อผิดพลาดในการ Sync: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Strip placeholder text (e.g. "-- เลือกจังหวัด --") from address fields before saving
     */
    private function cleanAddressField(string $val): string
    {
        $val = trim($val);
        if ($val === '') return '';
        // Strip patterns: starts with "--" or contains "เลือก"
        if (str_starts_with($val, '--') || mb_strpos($val, 'เลือก') !== false) {
            return '';
        }
        return $val;
    }

    /**
     * POST /members/:id_members/update
     * แก้ไขข้อมูลสมาชิก (ชื่อ, ที่อยู่, เบอร์โทร)
     * *Update ข้อมูลย้อนหลังทั้งหมดสำหรับ id_members นี้ (Cascade)*
     */
    private function update(string $idMembers): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($data['first_name']))
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุชื่อ');

        try {
            $this->pdo->beginTransaction();

            $title = $data['title'] ?? '';
            $firstName = $data['first_name'];
            $lastName = $data['last_name'] ?? '';
            $fullName = trim("$title $firstName $lastName");
            $idCard = preg_replace('/\D/', '', $data['id_card'] ?? '');

            // Shared address common values — ล้าง placeholder ก่อน save
            $addr = [
                'line' => trim($data['address_line'] ?? ''),
                'prov' => $this->cleanAddressField($data['province'] ?? ''),
                'amp'  => $this->cleanAddressField($data['amphure'] ?? ''),
                'dist' => $this->cleanAddressField($data['district'] ?? ''),
                'zip'  => trim($data['zip_code'] ?? $data['postcode'] ?? ''),
                'full' => trim($data['address'] ?? '')
            ];

            $ship = [
                'line' => trim($data['ship_address_line'] ?? ''),
                'prov' => $this->cleanAddressField($data['ship_province'] ?? ''),
                'amp'  => $this->cleanAddressField($data['ship_district'] ?? ''),
                'dist' => $this->cleanAddressField($data['ship_subdistrict'] ?? ''),
                'zip'  => trim($data['ship_zip_code'] ?? ''),
                'full' => trim($data['shipping_address'] ?? '')
            ];

            // 1. Update Receipts
            $stmt = $this->pdo->prepare("UPDATE edonation_receipts SET payer_name = ?, id_card = ? WHERE id_members = ?");
            $stmt->execute([$fullName, $idCard, $idMembers]);

            // 2. Cascade to donat_user
            $donationIds = $this->pdo->prepare("SELECT donation_id FROM edonation_receipts WHERE id_members = ?");
            $donationIds->execute([$idMembers]);
            $ids = $donationIds->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $sqlDonat = "UPDATE edonation_donat_user SET 
                    title=?, first_name=?, last_name=?, phone=?, occupation=?, id_card=?, 
                    receipt_address=?, address_line=?, district=?, amphure=?, province=?, zip_code=?,
                    shipping_address=?, ship_address_line=?, ship_district=?, ship_amphure=?, ship_province=?, ship_zip_code=?
                    WHERE id IN ($placeholders)";

                $params = [
                    $title,
                    $firstName,
                    $lastName,
                    $data['phone'] ?? '',
                    $data['occupation'] ?? '',
                    $idCard,
                    $addr['full'],
                    $addr['line'],
                    $addr['dist'],
                    $addr['amp'],
                    $addr['prov'],
                    $addr['zip'],
                    $ship['full'],
                    $ship['line'],
                    $ship['dist'],
                    $ship['amp'],
                    $ship['prov'],
                    $ship['zip']
                ];
                $this->pdo->prepare($sqlDonat)->execute(array_merge($params, $ids));
            }

            // 3. Update members table
            $sqlMember = "UPDATE edonation_members SET 
                title=?, first_name=?, last_name=?, id_card=?, phone=?, occupation=?, 
                address_line=?, province=?, district=?, subdistrict=?, zip_code=?, full_address=?,
                ship_address_line=?, ship_province=?, ship_district=?, ship_subdistrict=?, ship_zip_code=?, shipping_address=?
                WHERE id_members = ?";

            $this->pdo->prepare($sqlMember)->execute([
                $title,
                $firstName,
                $lastName,
                $idCard,
                $data['phone'] ?? '',
                $data['occupation'] ?? '',
                $addr['line'],
                $addr['prov'],
                $addr['amp'],
                $addr['dist'],
                $addr['zip'],
                $addr['full'],
                $ship['line'],
                $ship['prov'],
                $ship['amp'],
                $ship['dist'],
                $ship['zip'],
                $ship['full'],
                $idMembers
            ]);

            $this->pdo->commit();
            return Response::success(['id_members' => $idMembers], 'บันทึกข้อมูลเรียบร้อย');
        } catch (Exception $e) {
            if ($this->pdo->inTransaction())
                $this->pdo->rollBack();
            return Response::error('DATABASE_ERROR', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * GET /members
     * รายการสมาชิกทั้งหมด (ดึงจากตาราง edonation_members)
     */

    /**
     * GET /members/stats
     * สถิติภาพรวมสมาชิก — ใช้สำหรับ mini dashboard
     */
    private function getStats(): array
    {
        $currentYear = (int) date('Y');

        $stmt = $this->pdo->query("
            SELECT
                COUNT(*)                                              AS total_members,
                SUM(total_donated)                                    AS total_amount,
                SUM(donation_count)                                   AS total_donations,
                SUM(CASE WHEN YEAR(first_donation_date) = {$currentYear} THEN 1 ELSE 0 END) AS new_this_year
            FROM edonation_members
            WHERE is_active = 1
        ");

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return Response::success([
            'total_members'   => (int)   ($row['total_members']   ?? 0),
            'total_amount'    => (float) ($row['total_amount']    ?? 0),
            'total_donations' => (int)   ($row['total_donations'] ?? 0),
            'new_this_year'   => (int)   ($row['new_this_year']   ?? 0),
            'year'            => $currentYear,
        ]);
    }

    private function index(): array
    {
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(10000, max(1, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        // Build Where Clause
        $where = ["is_active = 1"];
        $params = [];

        // Filter by donation_count
        if (isset($_GET['donation_count']) && $_GET['donation_count'] !== '') {
            $where[] = "donation_count = :donation_count";
            $params[':donation_count'] = intval($_GET['donation_count']);
        }

        // Filter by date range (last_donation_date)
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        if (!empty($startDate) && !empty($endDate)) {
            $where[] = "DATE(last_donation_date) BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $startDate;
            $params[':end_date'] = $endDate;
        }

        // Filter by year
        $year = $_GET['year'] ?? '';
        if (!empty($year)) {
            $where[] = "YEAR(last_donation_date) = :year";
            $params[':year'] = intval($year);
        }

        // Filter by type (Optional backend support for better pagination)
        if (isset($_GET['type']) && $_GET['type'] !== '' && $_GET['type'] !== 'all') {
            $type = $_GET['type'];
            if ($type === 'new') {
                $where[] = "donation_count = 1";
            } elseif ($type === 'repeat') {
                $where[] = "donation_count >= 2";
            } elseif ($type === 'loyal') {
                $where[] = "donation_count >= 10"; // Based on getMemberProfile logic
            }
        }

        $whereSql = implode(" AND ", $where);

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
                WHERE $whereSql
                ORDER BY last_donation_date DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        $stmt->execute();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Count total
        $countSql = "SELECT COUNT(*) as total FROM edonation_members WHERE $whereSql";
        $countStmt = $this->pdo->prepare($countSql);
        foreach ($params as $key => $val) {
            $countStmt->bindValue($key, $val);
        }
        $countStmt->execute();
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
        $query      = trim($_GET['q'] ?? '');
        $searchMode = $_GET['search_mode'] ?? 'all'; // all, name, id_card, payer_name
        $donorType  = $_GET['donor_type'] ?? '';      // new, repeat, loyal
        $limit      = min(10000, max(1, intval($_GET['limit'] ?? 50)));

        $sql = "SELECT
                    m.id,
                    m.id_members,
                    m.id_card,
                    m.type,
                    m.title,
                    m.first_name,
                    m.last_name,
                    m.full_name as name,
                    m.phone,
                    m.email,
                    m.occupation,
                    m.address_line,
                    m.province,
                    m.district,
                    m.subdistrict,
                    m.zip_code,
                    m.full_address as address,
                    m.total_donated as total_amount,
                    m.donation_count as receipt_count,
                    m.benefactor_level,
                    m.last_donation_date
                FROM edonation_members m
                WHERE m.is_active = 1";

        $params = [];

        // Apply donor-type filter (same logic as index())
        if ($donorType === 'new') {
            $sql .= " AND m.donation_count = 1";
        } elseif ($donorType === 'repeat') {
            $sql .= " AND m.donation_count >= 2";
        } elseif ($donorType === 'loyal') {
            $sql .= " AND m.donation_count >= 10";
        }

        if (!empty($query)) {
            $cleanQuery   = str_replace(' ', '', $query);
            $numericQuery = preg_replace('/\D/', '', $query); // digits only (for id_card / phone)
            $searchVal      = '%' . $query . '%';
            $cleanSearchVal = '%' . $cleanQuery . '%';

            switch ($searchMode) {
                case 'name':
                    $sql .= " AND (
                        REPLACE(m.full_name, ' ', '') LIKE :q1
                        OR REPLACE(CONCAT(m.first_name, m.last_name), ' ', '') LIKE :q2
                        OR m.first_name LIKE :q3
                        OR m.last_name LIKE :q4
                        OR EXISTS (SELECT 1 FROM edonation_receipts r WHERE r.id_members = m.id_members AND r.payer_name LIKE :q5)
                    )";
                    $params[':q1'] = $cleanSearchVal;
                    $params[':q2'] = $cleanSearchVal;
                    $params[':q3'] = $searchVal;
                    $params[':q4'] = $searchVal;
                    $params[':q5'] = $searchVal;
                    break;

                case 'id_card':
                    // Non-numeric query cannot match an id_card — return nothing
                    if (empty($numericQuery)) {
                        $sql .= " AND 1=0";
                    } else {
                        $sql .= " AND m.id_card LIKE :q1";
                        $params[':q1'] = '%' . $numericQuery . '%';
                    }
                    break;

                case 'payer_name':
                    $sql .= " AND EXISTS (SELECT 1 FROM edonation_receipts r WHERE r.id_members = m.id_members AND r.payer_name LIKE :q1)";
                    $params[':q1'] = $searchVal;
                    break;

                default: // all fields
                    // Always search text fields
                    $conditions = "
                        REPLACE(m.full_name, ' ', '') LIKE :q1
                        OR REPLACE(CONCAT(m.first_name, m.last_name), ' ', '') LIKE :q2
                        OR m.id_members LIKE :q4
                        OR m.first_name LIKE :q5
                        OR m.last_name LIKE :q6
                        OR EXISTS (SELECT 1 FROM edonation_receipts r WHERE r.id_members = m.id_members AND r.payer_name LIKE :q8)
                    ";
                    $params[':q1'] = $cleanSearchVal;
                    $params[':q2'] = $cleanSearchVal;
                    $params[':q4'] = $searchVal;
                    $params[':q5'] = $searchVal;
                    $params[':q6'] = $searchVal;
                    $params[':q8'] = $searchVal;

                    // Only add numeric searches when the query actually contains digits
                    // — avoids LIKE '%%' which would match ALL records
                    if (!empty($numericQuery)) {
                        $conditions .= " OR m.id_card LIKE :q3 OR m.phone LIKE :q7";
                        $params[':q3'] = '%' . $numericQuery . '%';
                        $params[':q7'] = '%' . $numericQuery . '%';
                    }

                    $sql .= " AND ($conditions)";
            }
        }

        $sql .= " ORDER BY m.last_donation_date DESC LIMIT :limit";

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
        // ดึงข้อมูลจากตาราง edonation_members (explicit columns)
        $sql = "SELECT id, id_members, id_card, type, full_name, title, first_name, last_name, 
                       phone, email, occupation, full_address, address_line, province, district, 
                       subdistrict, zip_code, shipping_address, ship_address_line, ship_province, 
                       ship_district, ship_subdistrict, ship_zip_code, total_donated, donation_count, 
                       first_donation_date, last_donation_date, benefactor_level, is_active, 
                       created_at, updated_at
                FROM edonation_members 
                WHERE id_members = :id_members AND is_active = 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_members' => $idMembers]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$member) {
            return Response::notFound('ไม่พบข้อมูลสมาชิก');
        }

        // ดึงโครงการที่เคยบริจาค (ค้นหาจาก id_members หรือ id_card เพื่อไม่พลาดใบเสร็จเก่า)
        $projectsSql = "SELECT
                            du.project_name,
                            du.project_number,
                            COUNT(*) as count,
                            SUM(r.amount) as total
                        FROM edonation_receipts r
                        LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                        WHERE r.status != 2
                          AND (r.id_members = :id_members
                               OR (r.id_card IS NOT NULL AND r.id_card != '' AND r.id_card = :id_card))
                        GROUP BY du.project_name, du.project_number
                        ORDER BY total DESC
                        LIMIT 5";
        $projectsStmt = $this->pdo->prepare($projectsSql);
        $projectsStmt->execute([':id_members' => $idMembers, ':id_card' => $member['id_card'] ?? '']);
        $projects = $projectsStmt->fetchAll(PDO::FETCH_ASSOC);

        // กำหนดระดับผู้มีอุปการคุณ
        $totalAmount = floatval($member['total_amount']);
        $benefactorLevel = $this->getBenefactorLevel($totalAmount);

        // นับจำนวนปีที่บริจาค (ใช้วัดความต่อเนื่อง)
        $yearsSql = "SELECT DISTINCT YEAR(r.issued_at) as year
                     FROM edonation_receipts r
                     WHERE r.status != 2
                       AND (r.id_members = :id_members
                            OR (r.id_card IS NOT NULL AND r.id_card != '' AND r.id_card = :id_card))
                     ORDER BY year DESC";
        $yearsStmt = $this->pdo->prepare($yearsSql);
        $yearsStmt->execute([':id_members' => $idMembers, ':id_card' => $member['id_card'] ?? '']);
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
            'shipping_address_data' => [
                'full' => $member['shipping_address'],
                'address_line' => $member['ship_address_line'],
                'province' => $member['ship_province'],
                'district' => $member['ship_district'],
                'subdistrict' => $member['ship_subdistrict'],
                'zip_code' => $member['ship_zip_code']
            ],
            'shipping_address' => $member['shipping_address'],
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
                    r.status,
                    r.donation_id,
                    du.project_name,
                    du.project_number,
                    du.payby as payment_method,
                    du.created_at as donation_date
                FROM edonation_receipts r
                LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                WHERE r.id_members = :id_members AND r.status != 2
                ORDER BY r.issued_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_members', $idMembers);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Count active receipts only
        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM edonation_receipts WHERE id_members = :id AND status != 2");
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
        // หา id_card ของสมาชิกเพื่อใช้ค้นหาใบเสร็จเก่าที่อาจไม่มี id_members
        $memberStmt = $this->pdo->prepare("SELECT id_card FROM edonation_members WHERE id_members = :id_members LIMIT 1");
        $memberStmt->execute([':id_members' => $idMembers]);
        $idCard = $memberStmt->fetchColumn() ?: '';

        $sql = "SELECT
                    r.id,
                    r.receipt_no,
                    r.payer_name,
                    r.amount,
                    r.issued_at,
                    r.status,
                    du.project_name,
                    du.project_number,
                    du.fiscal_year
                FROM edonation_receipts r
                LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                WHERE r.status != 2
                  AND (r.id_members = :id_members
                       OR (r.id_card IS NOT NULL AND r.id_card != '' AND r.id_card = :id_card))
                ORDER BY r.issued_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_members' => $idMembers, ':id_card' => $idCard]);
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
        // ยอดรวมเฉพาะใบเสร็จที่ยังใช้งานได้ (ไม่รวมที่ถูกยกเลิก)
        $totalSql = "SELECT
                        SUM(amount) as total_amount,
                        COUNT(*) as total_receipts,
                        MIN(issued_at) as first_date,
                        MAX(issued_at) as last_date
                     FROM edonation_receipts
                     WHERE id_members = :id AND status != 2";
        $totalStmt = $this->pdo->prepare($totalSql);
        $totalStmt->execute([':id' => $idMembers]);
        $totals = $totalStmt->fetch(PDO::FETCH_ASSOC);

        if (!$totals || $totals['total_receipts'] == 0) {
            return Response::notFound('ไม่พบข้อมูลสมาชิก');
        }

        // สรุปตามปี (เฉพาะใบเสร็จที่ใช้งานได้)
        $yearSql = "SELECT
                        du.fiscal_year as year,
                        SUM(r.amount) as amount,
                        COUNT(*) as count
                    FROM edonation_receipts r
                    LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                    WHERE r.id_members = :id AND r.status != 2
                    GROUP BY du.fiscal_year
                    ORDER BY du.fiscal_year DESC";
        $yearStmt = $this->pdo->prepare($yearSql);
        $yearStmt->execute([':id' => $idMembers]);
        $byYear = $yearStmt->fetchAll(PDO::FETCH_ASSOC);

        // สรุปตามโครงการ (เฉพาะใบเสร็จที่ใช้งานได้)
        $projectSql = "SELECT
                            du.project_name,
                            du.project_number,
                            SUM(r.amount) as amount,
                            COUNT(*) as count
                       FROM edonation_receipts r
                       LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                       WHERE r.id_members = :id AND r.status != 2
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
