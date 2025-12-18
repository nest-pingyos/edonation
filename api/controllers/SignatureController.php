<?php
/**
 * Signature Config Controller
 * API สำหรับจัดการลายเซ็นตามปีงบประมาณ
 * 
 * Endpoints:
 * GET    /signatures              - รายการทั้งหมด
 * GET    /signatures/:year        - ดูตามปีงบประมาณ
 * POST   /signatures              - เพิ่มใหม่ (Admin)
 * PUT    /signatures/:year        - แก้ไข (Admin)
 * DELETE /signatures/:year        - ลบ (Admin)
 */

class SignatureController {
    private PDO $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance();
    }
    
    public function handle(string $method, ?string $year, ?string $action): array {
        switch ($method) {
            case 'GET':
                if ($year) return $this->getByYear($year);
                return $this->index();
            case 'POST':
                AuthMiddleware::requireAdmin();
                return $this->create();
            case 'PUT':
                AuthMiddleware::requireAdmin();
                if (!$year) return Response::error('VALIDATION_ERROR', 'กรุณาระบุปีงบประมาณ');
                return $this->update($year);
            case 'DELETE':
                AuthMiddleware::requireAdmin();
                if (!$year) return Response::error('VALIDATION_ERROR', 'กรุณาระบุปีงบประมาณ');
                return $this->delete($year);
            default:
                return Response::error('METHOD_NOT_ALLOWED', 'Method not allowed', 405);
        }
    }
    
    /**
     * GET /signatures
     * รายการลายเซ็นทั้งหมด
     */
    private function index(): array {
        $sql = "SELECT * FROM signature_config ORDER BY fiscal_year DESC";
        $stmt = $this->pdo->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return Response::success($results, null, [
            'count' => count($results)
        ]);
    }
    
    /**
     * GET /signatures/:year
     * ดูลายเซ็นตามปีงบประมาณ
     */
    private function getByYear(string $year): array {
        $sql = "SELECT * FROM signature_config WHERE fiscal_year = :year LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':year' => $year]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            // ถ้าไม่พบ ให้ลอง fallback ไปปีก่อนหน้า หรือ default
            $fallbackSql = "SELECT * FROM signature_config WHERE fiscal_year <= :year AND is_active = 1 ORDER BY fiscal_year DESC LIMIT 1";
            $fallbackStmt = $this->pdo->prepare($fallbackSql);
            $fallbackStmt->execute([':year' => $year]);
            $result = $fallbackStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return Response::notFound('ไม่พบข้อมูลลายเซ็นสำหรับปี ' . $year);
            }
        }
        
        return Response::success($result);
    }
    
    /**
     * POST /signatures
     * เพิ่มลายเซ็นใหม่
     */
    private function create(): array {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        
        $v = new Validator($data);
        $v->required('fiscal_year')
          ->required('dean_signature')
          ->required('dean_name')
          ->required('collector_signature')
          ->required('collector_name');
        
        if (!$v->passes()) return Response::validation($v->errors());
        
        // ตรวจสอบว่าปีนี้มีอยู่แล้วหรือไม่
        $checkSql = "SELECT id FROM signature_config WHERE fiscal_year = :year";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([':year' => $data['fiscal_year']]);
        
        if ($checkStmt->fetch()) {
            return Response::error('DUPLICATE_ERROR', 'ปีงบประมาณ ' . $data['fiscal_year'] . ' มีข้อมูลอยู่แล้ว');
        }
        
        $sql = "INSERT INTO signature_config 
                (fiscal_year, dean_signature, dean_name, collector_signature, collector_name, is_active)
                VALUES (:fiscal_year, :dean_signature, :dean_name, :collector_signature, :collector_name, :is_active)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':fiscal_year' => $data['fiscal_year'],
            ':dean_signature' => $data['dean_signature'],
            ':dean_name' => $data['dean_name'],
            ':collector_signature' => $data['collector_signature'],
            ':collector_name' => $data['collector_name'],
            ':is_active' => $data['is_active'] ?? 1
        ]);
        
        $id = $this->pdo->lastInsertId();
        
        return Response::success([
            'id' => (int)$id,
            'fiscal_year' => (int)$data['fiscal_year']
        ], 'เพิ่มข้อมูลลายเซ็นสำเร็จ');
    }
    
    /**
     * PUT /signatures/:year
     * แก้ไขลายเซ็น
     */
    private function update(string $year): array {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        
        // ตรวจสอบว่ามีข้อมูลปีนี้หรือไม่
        $checkSql = "SELECT id FROM signature_config WHERE fiscal_year = :year";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([':year' => $year]);
        
        if (!$checkStmt->fetch()) {
            return Response::notFound('ไม่พบข้อมูลลายเซ็นสำหรับปี ' . $year);
        }
        
        // สร้าง SET clause แบบ dynamic
        $updates = [];
        $params = [':year' => $year];
        
        $allowedFields = ['dean_signature', 'dean_name', 'collector_signature', 'collector_name', 'is_active'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            return Response::error('VALIDATION_ERROR', 'ไม่มีข้อมูลที่ต้องอัปเดต');
        }
        
        $sql = "UPDATE signature_config SET " . implode(', ', $updates) . " WHERE fiscal_year = :year";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return Response::success([
            'fiscal_year' => (int)$year
        ], 'อัปเดตข้อมูลลายเซ็นสำเร็จ');
    }
    
    /**
     * DELETE /signatures/:year
     * ลบลายเซ็น
     */
    private function delete(string $year): array {
        // ตรวจสอบว่ามีข้อมูลปีนี้หรือไม่
        $checkSql = "SELECT id FROM signature_config WHERE fiscal_year = :year";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([':year' => $year]);
        
        if (!$checkStmt->fetch()) {
            return Response::notFound('ไม่พบข้อมูลลายเซ็นสำหรับปี ' . $year);
        }
        
        $sql = "DELETE FROM signature_config WHERE fiscal_year = :year";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':year' => $year]);
        
        return Response::success([
            'fiscal_year' => (int)$year
        ], 'ลบข้อมูลลายเซ็นสำเร็จ');
    }
}
