<?php

declare(strict_types=1);

/**
 * Admin Users Controller
 *
 * API for managing admin users authorization
 *
 * @package eDonation\API\Controllers
 * @version 2.0.0 - Refactored for PSR-12, Security & Performance
 */

class AdminUserController
{
    private const ALLOWED_ROLES = ['super_admin', 'admin', 'editor', 'viewer'];
    private const ALLOWED_STATUSES = ['active', 'inactive'];
    private const CMU_EMAIL_DOMAIN = '@cmu.ac.th';

    private const SELECT_COLUMNS = [
        'id',
        'email',
        'name',
        'role',
        'status',
        'created_at',
        'last_login'
    ];

    private PDO $db;

    public function __construct()
    {
        $this->db = require_once __DIR__ . '/../../config/database.php';
    }

    /**
     * Get all admin users
     */
    public function index(): array
    {
        try {
            $columns = implode(', ', self::SELECT_COLUMNS);
            $stmt = $this->db->prepare("
                SELECT {$columns}
                FROM edonation_admin_users 
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->jsonSuccess($users);
        } catch (PDOException $e) {
            error_log("AdminUser Index DB Error: " . $e->getMessage());
            return $this->jsonError('ไม่สามารถดึงข้อมูลได้', 500);
        } catch (Exception $e) {
            error_log("AdminUser Index Error: " . $e->getMessage());
            return $this->jsonError('เกิดข้อผิดพลาดภายในระบบ', 500);
        }
    }

    /**
     * Get single admin user by ID
     */
    public function show(string $id): array
    {
        try {
            if (!$this->isValidId($id)) {
                return $this->jsonError('รูปแบบ ID ไม่ถูกต้อง', 400);
            }

            $columns = implode(', ', self::SELECT_COLUMNS);
            $stmt = $this->db->prepare("
                SELECT {$columns}
                FROM edonation_admin_users 
                WHERE id = :id
            ");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                return $this->jsonSuccess($user);
            }

            return $this->jsonError('ไม่พบผู้ใช้', 404);
        } catch (PDOException $e) {
            error_log("AdminUser Show DB Error: " . $e->getMessage());
            return $this->jsonError('ไม่สามารถดึงข้อมูลได้', 500);
        } catch (Exception $e) {
            error_log("AdminUser Show Error: " . $e->getMessage());
            return $this->jsonError('เกิดข้อผิดพลาดภายในระบบ', 500);
        }
    }

    /**
     * Check if user is authorized
     */
    public function check(): array
    {
        $email = trim($_GET['email'] ?? '');

        if (empty($email)) {
            return $this->jsonError('กรุณาระบุ email', 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonError('รูปแบบ email ไม่ถูกต้อง', 400);
        }

        try {
            $columns = 'id, email, name, role, status';
            $stmt = $this->db->prepare("
                SELECT {$columns}
                FROM edonation_admin_users 
                WHERE email = :email
            ");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                return $this->jsonSuccess([
                    'authorized' => $user['status'] === 'active',
                    'user' => $user
                ]);
            }

            return $this->jsonSuccess([
                'authorized' => false,
                'user' => null
            ]);
        } catch (PDOException $e) {
            error_log("AdminUser Check DB Error: " . $e->getMessage());
            return $this->jsonError('ไม่สามารถตรวจสอบได้', 500);
        } catch (Exception $e) {
            error_log("AdminUser Check Error: " . $e->getMessage());
            return $this->jsonError('เกิดข้อผิดพลาดภายในระบบ', 500);
        }
    }

    /**
     * Create new admin user (CMU OAuth - no password needed)
     */
    public function create(): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $email = trim($data['email'] ?? '');
        $name = trim($data['name'] ?? '');
        $role = $data['role'] ?? 'admin';

        // Validation
        $validationError = $this->validateCreateInput($email, $name, $role);
        if ($validationError !== null) {
            return $validationError;
        }

        try {
            // Check if email already exists
            $checkStmt = $this->db->prepare(
                "SELECT id FROM edonation_admin_users WHERE email = :email"
            );
            $checkStmt->execute([':email' => $email]);

            if ($checkStmt->fetch()) {
                return $this->jsonError('Email นี้มีในระบบแล้ว', 409);
            }

            // Insert new user
            $stmt = $this->db->prepare("
                INSERT INTO edonation_admin_users (email, name, role, status, created_at)
                VALUES (:email, :name, :role, 'active', NOW())
            ");
            $stmt->execute([
                ':email' => $email,
                ':name' => $name,
                ':role' => $role
            ]);

            $userId = $this->db->lastInsertId();

            return $this->jsonSuccess([
                'id' => (int) $userId,
                'email' => $email,
                'name' => $name,
                'role' => $role
            ], 'เพิ่มผู้ดูแลระบบสำเร็จ');
        } catch (PDOException $e) {
            error_log("AdminUser Create DB Error: " . $e->getMessage());
            return $this->jsonError('ไม่สามารถเพิ่มได้', 500);
        } catch (Exception $e) {
            error_log("AdminUser Create Error: " . $e->getMessage());
            return $this->jsonError('เกิดข้อผิดพลาดภายในระบบ', 500);
        }
    }

    /**
     * Update admin user
     */
    public function update(string $id): array
    {
        if (!$this->isValidId($id)) {
            return $this->jsonError('รูปแบบ ID ไม่ถูกต้อง', 400);
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $name = isset($data['name']) ? trim($data['name']) : null;
        $role = $data['role'] ?? null;
        $status = $data['status'] ?? null;

        // Validate inputs
        if ($role !== null && !in_array($role, self::ALLOWED_ROLES, true)) {
            return $this->jsonError('Role ไม่ถูกต้อง', 400);
        }

        if ($status !== null && !in_array($status, self::ALLOWED_STATUSES, true)) {
            return $this->jsonError('Status ไม่ถูกต้อง', 400);
        }

        if ($name !== null && empty($name)) {
            return $this->jsonError('ชื่อไม่สามารถเป็นค่าว่างได้', 400);
        }

        try {
            $updates = [];
            $params = [':id' => $id];

            if ($name !== null) {
                $updates[] = "name = :name";
                $params[':name'] = $name;
            }
            if ($role !== null) {
                $updates[] = "role = :role";
                $params[':role'] = $role;
            }
            if ($status !== null) {
                $updates[] = "status = :status";
                $params[':status'] = $status;
            }

            if (empty($updates)) {
                return $this->jsonError('ไม่มีข้อมูลที่จะอัปเดต', 400);
            }

            $sql = "UPDATE edonation_admin_users SET " . implode(', ', $updates) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                return $this->jsonError('ไม่พบผู้ใช้หรือข้อมูลไม่เปลี่ยนแปลง', 404);
            }

            return $this->jsonSuccess(null, 'อัปเดตสำเร็จ');
        } catch (PDOException $e) {
            error_log("AdminUser Update DB Error: " . $e->getMessage());
            return $this->jsonError('ไม่สามารถอัปเดตได้', 500);
        } catch (Exception $e) {
            error_log("AdminUser Update Error: " . $e->getMessage());
            return $this->jsonError('เกิดข้อผิดพลาดภายในระบบ', 500);
        }
    }

    /**
     * Delete admin user
     */
    public function delete(string $id): array
    {
        if (!$this->isValidId($id)) {
            return $this->jsonError('รูปแบบ ID ไม่ถูกต้อง', 400);
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM edonation_admin_users WHERE id = :id");
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() > 0) {
                return $this->jsonSuccess(null, 'ลบสำเร็จ');
            }

            return $this->jsonError('ไม่พบผู้ใช้', 404);
        } catch (PDOException $e) {
            error_log("AdminUser Delete DB Error: " . $e->getMessage());
            return $this->jsonError('ไม่สามารถลบได้', 500);
        } catch (Exception $e) {
            error_log("AdminUser Delete Error: " . $e->getMessage());
            return $this->jsonError('เกิดข้อผิดพลาดภายในระบบ', 500);
        }
    }

    /**
     * Deactivate admin user (soft delete)
     */
    public function deactivate(string $id): array
    {
        if (!$this->isValidId($id)) {
            return $this->jsonError('รูปแบบ ID ไม่ถูกต้อง', 400);
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE edonation_admin_users SET status = 'inactive' WHERE id = :id"
            );
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                return $this->jsonError('ไม่พบผู้ใช้หรือสถานะไม่เปลี่ยนแปลง', 404);
            }

            return $this->jsonSuccess(null, 'ปิดการใช้งานสำเร็จ');
        } catch (PDOException $e) {
            error_log("AdminUser Deactivate DB Error: " . $e->getMessage());
            return $this->jsonError('ไม่สามารถปิดการใช้งานได้', 500);
        } catch (Exception $e) {
            error_log("AdminUser Deactivate Error: " . $e->getMessage());
            return $this->jsonError('เกิดข้อผิดพลาดภายในระบบ', 500);
        }
    }

    /**
     * Activate admin user
     */
    public function activate(string $id): array
    {
        if (!$this->isValidId($id)) {
            return $this->jsonError('รูปแบบ ID ไม่ถูกต้อง', 400);
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE edonation_admin_users SET status = 'active' WHERE id = :id"
            );
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                return $this->jsonError('ไม่พบผู้ใช้หรือสถานะไม่เปลี่ยนแปลง', 404);
            }

            return $this->jsonSuccess(null, 'เปิดการใช้งานสำเร็จ');
        } catch (PDOException $e) {
            error_log("AdminUser Activate DB Error: " . $e->getMessage());
            return $this->jsonError('ไม่สามารถเปิดการใช้งานได้', 500);
        } catch (Exception $e) {
            error_log("AdminUser Activate Error: " . $e->getMessage());
            return $this->jsonError('เกิดข้อผิดพลาดภายในระบบ', 500);
        }
    }

    /**
     * Validate create input
     */
    private function validateCreateInput(string $email, string $name, string $role): ?array
    {
        if (empty($email) || empty($name)) {
            return $this->jsonError('กรุณากรอก email และชื่อ', 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonError('รูปแบบ email ไม่ถูกต้อง', 400);
        }

        if (!str_ends_with($email, self::CMU_EMAIL_DOMAIN)) {
            return $this->jsonError('ต้องใช้ CMU Account email (@cmu.ac.th) เท่านั้น', 400);
        }

        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            return $this->jsonError('Role ไม่ถูกต้อง', 400);
        }

        return null;
    }

    /**
     * Validate ID format
     */
    private function isValidId(string $id): bool
    {
        return ctype_digit($id) && (int) $id > 0;
    }

    /**
     * Success response helper
     */
    private function jsonSuccess(mixed $data, ?string $message = null): array
    {
        return [
            'success' => true,
            'data' => $data,
            'message' => $message
        ];
    }

    /**
     * Error response helper
     */
    private function jsonError(string $message, int $code = 400): array
    {
        http_response_code($code);
        return [
            'success' => false,
            'error' => ['message' => $message]
        ];
    }
}
