<?php
/**
 * Admin Users Controller
 * 
 * API for managing admin users authorization
 */

class AdminUserController
{
    private $db;

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
            $stmt = $this->db->prepare("
                SELECT id, email, name, role, status, created_at, last_login 
                FROM edonation_admin_users 
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->jsonSuccess($users);
        } catch (Exception $e) {
            return $this->jsonError('ไม่สามารถดึงข้อมูลได้: ' . $e->getMessage());
        }
    }

    /**
     * Get single admin user by ID
     */
    public function show($id): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, email, name, role, status, created_at, last_login 
                FROM edonation_admin_users 
                WHERE id = :id
            ");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                return $this->jsonSuccess($user);
            }
            return $this->jsonError('ไม่พบผู้ใช้', 404);
        } catch (Exception $e) {
            return $this->jsonError('ไม่สามารถดึงข้อมูลได้: ' . $e->getMessage());
        }
    }

    /**
     * Check if user is authorized
     */
    public function check(): array
    {
        $email = $_GET['email'] ?? '';

        if (empty($email)) {
            return $this->jsonError('กรุณาระบุ email');
        }

        try {
            $stmt = $this->db->prepare("
                SELECT id, email, name, role, status 
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
        } catch (Exception $e) {
            return $this->jsonError('ไม่สามารถตรวจสอบได้: ' . $e->getMessage());
        }
    }

    /**
     * Create new admin user (CMU OAuth - no password needed)
     */
    public function create(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $email = $data['email'] ?? '';
        $name = $data['name'] ?? '';
        $role = $data['role'] ?? 'admin';

        if (empty($email) || empty($name)) {
            return $this->jsonError('กรุณากรอก email และชื่อ');
        }

        // Validate email format (should be CMU email)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonError('รูปแบบ email ไม่ถูกต้อง');
        }

        // Validate CMU email domain
        if (!str_ends_with($email, '@cmu.ac.th')) {
            return $this->jsonError('ต้องใช้ CMU Account email (@cmu.ac.th) เท่านั้น');
        }

        try {
            // Check if email already exists
            $checkStmt = $this->db->prepare("SELECT id FROM edonation_admin_users WHERE email = :email");
            $checkStmt->execute([':email' => $email]);
            if ($checkStmt->fetch()) {
                return $this->jsonError('Email นี้มีในระบบแล้ว');
            }

            // Insert new user (CMU OAuth - no password)
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
                'id' => $userId,
                'email' => $email,
                'name' => $name,
                'role' => $role
            ], 'เพิ่มผู้ดูแลระบบสำเร็จ');
        } catch (Exception $e) {
            return $this->jsonError('ไม่สามารถเพิ่มได้: ' . $e->getMessage());
        }
    }

    /**
     * Update admin user
     */
    public function update($id): array
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $name = $data['name'] ?? null;
        $role = $data['role'] ?? null;
        $status = $data['status'] ?? null;

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
                return $this->jsonError('ไม่มีข้อมูลที่จะอัปเดต');
            }

            $sql = "UPDATE edonation_admin_users SET " . implode(', ', $updates) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $this->jsonSuccess(null, 'อัปเดตสำเร็จ');
        } catch (Exception $e) {
            return $this->jsonError('ไม่สามารถอัปเดตได้: ' . $e->getMessage());
        }
    }

    /**
     * Delete admin user
     */
    public function delete($id): array
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM edonation_admin_users WHERE id = :id");
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() > 0) {
                return $this->jsonSuccess(null, 'ลบสำเร็จ');
            }
            return $this->jsonError('ไม่พบผู้ใช้');
        } catch (Exception $e) {
            return $this->jsonError('ไม่สามารถลบได้: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate admin user (soft delete)
     */
    public function deactivate($id): array
    {
        try {
            $stmt = $this->db->prepare("UPDATE edonation_admin_users SET status = 'inactive' WHERE id = :id");
            $stmt->execute([':id' => $id]);

            return $this->jsonSuccess(null, 'ปิดการใช้งานสำเร็จ');
        } catch (Exception $e) {
            return $this->jsonError('ไม่สามารถปิดการใช้งานได้: ' . $e->getMessage());
        }
    }

    /**
     * Activate admin user
     */
    public function activate($id): array
    {
        try {
            $stmt = $this->db->prepare("UPDATE edonation_admin_users SET status = 'active' WHERE id = :id");
            $stmt->execute([':id' => $id]);

            return $this->jsonSuccess(null, 'เปิดการใช้งานสำเร็จ');
        } catch (Exception $e) {
            return $this->jsonError('ไม่สามารถเปิดการใช้งานได้: ' . $e->getMessage());
        }
    }

    private function jsonSuccess($data, $message = null): array
    {
        return [
            'success' => true,
            'data' => $data,
            'message' => $message
        ];
    }

    private function jsonError($message, $code = 400): array
    {
        http_response_code($code);
        return [
            'success' => false,
            'error' => ['message' => $message]
        ];
    }
}
