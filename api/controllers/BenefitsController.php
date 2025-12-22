<?php
/**
 * Benefits Controller
 * API สำหรับจัดการระดับผู้มีอุปการคุณ
 * 
 * Endpoints:
 * GET    /benefits              - รายการทั้งหมด (Public)
 * GET    /benefits/:id          - รายละเอียด
 * POST   /benefits              - เพิ่มใหม่ (Admin)
 * PUT    /benefits/:id          - แก้ไข (Admin)
 * DELETE /benefits/:id          - ลบ (Admin)
 */

class BenefitsController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function handle(string $method, ?string $id, ?string $action): array
    {
        switch ($method) {
            case 'GET':
                if ($id)
                    return $this->show($id);
                return $this->index();
            case 'POST':
                AuthMiddleware::requireAdmin();
                return $this->create();
            case 'PUT':
                AuthMiddleware::requireAdmin();
                if (!$id)
                    return Response::error('VALIDATION_ERROR', 'กรุณาระบุ ID');
                return $this->update($id);
            case 'DELETE':
                AuthMiddleware::requireAdmin();
                if (!$id)
                    return Response::error('VALIDATION_ERROR', 'กรุณาระบุ ID');
                return $this->delete($id);
            default:
                return Response::error('METHOD_NOT_ALLOWED', 'Method not allowed', 405);
        }
    }

    /**
     * GET /benefits
     * รายการระดับผู้มีอุปการคุณทั้งหมด
     */
    private function index(): array
    {
        $activeOnly = isset($_GET['active']) ? $_GET['active'] === '1' : true;

        $sql = "SELECT id, name, amount, img_file, description, sort_order, is_active 
                FROM edonation_benefits";

        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }

        $sql .= " ORDER BY sort_order ASC";

        $stmt = $this->pdo->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // เพิ่ม URL รูปภาพ - use BASE_PATH from config (assets are in web/ folder)
        $baseImageUrl = (defined('BASE_PATH') ? BASE_PATH : '/edonation') . '/web/assets/images/benefits/';
        foreach ($results as &$item) {
            $item['image_url'] = $item['img_file']
                ? $baseImageUrl . $item['img_file']
                : $baseImageUrl . 'default.jpg';
            // Ensure amount is numeric
            $item['amount'] = floatval($item['amount'] ?? 0);
            $item['is_active'] = (bool) $item['is_active'];
        }

        return Response::success($results, null, [
            'count' => count($results)
        ]);
    }

    /**
     * GET /benefits/:id
     * รายละเอียดระดับ
     */
    private function show(string $id): array
    {
        $sql = "SELECT id, name, amount, img_file, description, sort_order, is_active,
                       created_at, updated_at 
                FROM edonation_benefits WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return Response::notFound('ไม่พบข้อมูลระดับนี้');
        }

        // เพิ่ม URL รูปภาพ - use BASE_PATH from config (assets are in web/ folder)
        $baseImageUrl = (defined('BASE_PATH') ? BASE_PATH : '/edonation') . '/web/assets/images/benefits/';
        $result['image_url'] = $result['img_file']
            ? $baseImageUrl . $result['img_file']
            : $baseImageUrl . 'default.jpg';

        return Response::success($result);
    }

    /**
     * POST /benefits
     * เพิ่มระดับใหม่ (Admin)
     */
    private function create(): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $v = new Validator($data);
        $v->required('name')
            ->required('amount');

        if (!$v->passes())
            return Response::validation($v->errors());

        $sql = "INSERT INTO edonation_benefits (name, amount, img_file, description, sort_order, is_active)
                VALUES (:name, :amount, :img_file, :description, :sort_order, :is_active)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':amount' => $data['amount'],
            ':img_file' => $data['img_file'] ?? null,
            ':description' => $data['description'] ?? null,
            ':sort_order' => $data['sort_order'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1
        ]);

        $id = $this->pdo->lastInsertId();

        return Response::success([
            'id' => (int) $id
        ], 'เพิ่มระดับใหม่สำเร็จ');
    }

    /**
     * PUT /benefits/:id
     * แก้ไขระดับ (Admin)
     */
    private function update(string $id): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        // ตรวจสอบว่ามีข้อมูลนี้หรือไม่
        $checkSql = "SELECT id FROM edonation_benefits WHERE id = :id";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([':id' => $id]);

        if (!$checkStmt->fetch()) {
            return Response::notFound('ไม่พบข้อมูลระดับนี้');
        }

        // สร้าง SET clause แบบ dynamic
        $updates = [];
        $params = [':id' => $id];

        $allowedFields = ['name', 'amount', 'img_file', 'description', 'sort_order', 'is_active'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($updates)) {
            return Response::error('VALIDATION_ERROR', 'ไม่มีข้อมูลที่ต้องอัปเดต');
        }

        $sql = "UPDATE edonation_benefits SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return Response::success([
            'id' => (int) $id
        ], 'อัปเดตข้อมูลสำเร็จ');
    }

    /**
     * DELETE /benefits/:id
     * ลบระดับ (Admin)
     */
    private function delete(string $id): array
    {
        // ตรวจสอบว่ามีข้อมูลนี้หรือไม่
        $checkSql = "SELECT id FROM edonation_benefits WHERE id = :id";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([':id' => $id]);

        if (!$checkStmt->fetch()) {
            return Response::notFound('ไม่พบข้อมูลระดับนี้');
        }

        $sql = "DELETE FROM edonation_benefits WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        return Response::success([
            'id' => (int) $id
        ], 'ลบข้อมูลสำเร็จ');
    }
}
