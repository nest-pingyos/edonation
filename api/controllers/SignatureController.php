<?php

declare(strict_types=1);

/**
 * Signature Config Controller
 *
 * API สำหรับจัดการลายเซ็นตามปีงบประมาณ
 *
 * Endpoints:
 * GET    /signatures              - รายการทั้งหมด
 * GET    /signatures/:year        - ดูตามปีงบประมาณ
 * POST   /signatures              - เพิ่มใหม่ (Admin)
 * PUT    /signatures/:year        - แก้ไข (Admin)
 * DELETE /signatures/:year        - ลบ (Admin)
 *
 * @package eDonation\API\Controllers
 * @version 3.0.0 - Refactored for PSR-12, Security & Performance
 */

class SignatureController
{
    public const VERSION = '3.0';

    private const SELECT_COLUMNS = [
        'id',
        'fiscal_year',
        'dean_signature',
        'dean_name',
        'collector_signature',
        'collector_name',
        'is_active',
        'created_at',
        'updated_at'
    ];

    private const UPDATABLE_FIELDS = [
        'dean_signature',
        'dean_name',
        'collector_signature',
        'collector_name',
        'is_active'
    ];

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function handle(string $method, ?string $year, ?string $action): array
    {
        try {
            return match ($method) {
                'GET' => $year ? $this->getByYear($year) : $this->index(),
                'POST' => $this->requireAdminAndExecute(fn() => $this->create()),
                'PUT' => $this->requireAdminAndExecute(
                    fn() => $year
                    ? $this->update($year)
                    : Response::error('VALIDATION_ERROR', 'กรุณาระบุปีงบประมาณ')
                ),
                'DELETE' => $this->requireAdminAndExecute(
                    fn() => $year
                    ? $this->delete($year)
                    : Response::error('VALIDATION_ERROR', 'กรุณาระบุปีงบประมาณ')
                ),
                default => Response::error('METHOD_NOT_ALLOWED', 'Method not allowed', 405)
            };
        } catch (PDOException $e) {
            error_log("Signature Controller DB Error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล', 500);
        } catch (Exception $e) {
            error_log("Signature Controller Error: " . $e->getMessage());
            return Response::error('SERVER_ERROR', 'เกิดข้อผิดพลาดภายในระบบ', 500);
        }
    }

    /**
     * GET /signatures
     * รายการลายเซ็นทั้งหมด
     */
    private function index(): array
    {
        $columns = implode(', ', self::SELECT_COLUMNS);
        $sql = "SELECT {$columns} FROM signature_config ORDER BY fiscal_year DESC";

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
    private function getByYear(string $year): array
    {
        if (!$this->isValidFiscalYear($year)) {
            return Response::error('VALIDATION_ERROR', 'รูปแบบปีงบประมาณไม่ถูกต้อง');
        }

        $columns = implode(', ', self::SELECT_COLUMNS);
        $sql = "SELECT {$columns} FROM signature_config WHERE fiscal_year = :year LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':year' => $year]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            // Fallback: หาปีที่ใกล้ที่สุดและ active
            $result = $this->getFallbackSignature($year);

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
    private function create(): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        // Validate required fields
        $validator = new Validator($data);
        $validator->required('fiscal_year')
            ->required('dean_signature')
            ->required('dean_name')
            ->required('collector_signature')
            ->required('collector_name');

        if (!$validator->passes()) {
            return Response::validation($validator->errors());
        }

        // Additional fiscal year validation
        if (!$this->isValidFiscalYear((string) $data['fiscal_year'])) {
            return Response::error('VALIDATION_ERROR', 'รูปแบบปีงบประมาณไม่ถูกต้อง');
        }

        // Check for duplicate
        if ($this->fiscalYearExists((string) $data['fiscal_year'])) {
            return Response::error(
                'DUPLICATE_ERROR',
                'ปีงบประมาณ ' . $data['fiscal_year'] . ' มีข้อมูลอยู่แล้ว',
                409
            );
        }

        // Insert
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
            'id' => (int) $id,
            'fiscal_year' => (int) $data['fiscal_year']
        ], 'เพิ่มข้อมูลลายเซ็นสำเร็จ');
    }

    /**
     * PUT /signatures/:year
     * แก้ไขลายเซ็น
     */
    private function update(string $year): array
    {
        if (!$this->isValidFiscalYear($year)) {
            return Response::error('VALIDATION_ERROR', 'รูปแบบปีงบประมาณไม่ถูกต้อง');
        }

        if (!$this->fiscalYearExists($year)) {
            return Response::notFound('ไม่พบข้อมูลลายเซ็นสำหรับปี ' . $year);
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        // Build dynamic UPDATE statement
        $updates = [];
        $params = [':year' => $year];

        foreach (self::UPDATABLE_FIELDS as $field) {
            if (array_key_exists($field, $data)) {
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
            'fiscal_year' => (int) $year
        ], 'อัปเดตข้อมูลลายเซ็นสำเร็จ');
    }

    /**
     * DELETE /signatures/:year
     * ลบลายเซ็น
     */
    private function delete(string $year): array
    {
        if (!$this->isValidFiscalYear($year)) {
            return Response::error('VALIDATION_ERROR', 'รูปแบบปีงบประมาณไม่ถูกต้อง');
        }

        if (!$this->fiscalYearExists($year)) {
            return Response::notFound('ไม่พบข้อมูลลายเซ็นสำหรับปี ' . $year);
        }

        $sql = "DELETE FROM signature_config WHERE fiscal_year = :year";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':year' => $year]);

        return Response::success([
            'fiscal_year' => (int) $year
        ], 'ลบข้อมูลลายเซ็นสำเร็จ');
    }

    /**
     * Get fallback signature for year
     */
    private function getFallbackSignature(string $year): array|false
    {
        $columns = implode(', ', self::SELECT_COLUMNS);
        $sql = "SELECT {$columns}
                FROM signature_config 
                WHERE fiscal_year <= :year AND is_active = 1 
                ORDER BY fiscal_year DESC 
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':year' => $year]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check if fiscal year exists
     */
    private function fiscalYearExists(string $year): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM signature_config WHERE fiscal_year = :year LIMIT 1"
        );
        $stmt->execute([':year' => $year]);

        return $stmt->fetch() !== false;
    }

    /**
     * Validate fiscal year format (Buddhist year, 4 digits, 2500-2700)
     */
    private function isValidFiscalYear(string $year): bool
    {
        return ctype_digit($year)
            && strlen($year) === 4
            && (int) $year >= 2500
            && (int) $year <= 2700;
    }

    /**
     * Require admin privileges and execute callback
     */
    private function requireAdminAndExecute(callable $callback): array
    {
        AuthMiddleware::requireAdmin();
        return $callback();
    }
}
