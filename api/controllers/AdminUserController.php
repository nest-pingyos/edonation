<?php

declare(strict_types=1);

/**
 * Admin Users Controller - Secure RBAC Implementation
 *
 * API for managing admin users authorization with enhanced security
 *
 * @package eDonation\API\Controllers
 * @version 3.0.0 - Enhanced Security: Privilege Escalation Prevention, Self-Protection, Transactions
 * 
 * Security Features:
 * - Privilege Escalation Prevention: Users cannot modify accounts with equal/higher roles
 * - Self-Deletion Protection: Users cannot delete their own account
 * - Self-Demotion Protection: Users cannot lower their own role or deactivate themselves
 * - Transaction Support: Atomic operations for permission updates
 * - Input Sanitization: Strict validation for all user inputs
 * - Role Hierarchy Enforcement: Role-based access control with hierarchy
 */

class AdminUserController
{
    // Role hierarchy: higher number = more privileges
    private const ROLE_HIERARCHY = [
        'super_admin' => 100,
        'admin' => 50,
        'editor' => 25,
        'viewer' => 10
    ];

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
    private ?array $currentUser = null;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->initCurrentUser();
    }

    /**
     * Handle incoming requests
     */
    public function handle(string $method, ?string $id, ?string $action): array
    {
        // Require at least 'admin' role to manage users
        AuthMiddleware::requireAdmin();

        return match ($method) {
            'GET' => $id === 'check' ? $this->check() : ($id ? $this->show($id) : $this->index()),
            'POST' => $this->create(),
            'PUT' => $this->update((string) $id),
            'DELETE' => $this->delete((string) $id),
            'PATCH' => match ($action) {
                    'activate' => $this->activate((string) $id),
                    'deactivate' => $this->deactivate((string) $id),
                    default => $this->jsonError('Action not found', 404)
                },
            default => $this->jsonError('Method not allowed', 405)
        };
    }

    /**
     * Initialize current authenticated user from session/JWT
     */
    private function initCurrentUser(): void
    {
        // Get from AuthMiddleware or Session
        if (defined('APP_ENV') && APP_ENV === 'development') {
            $this->currentUser = [
                'id' => 1,
                'email' => 'dev@edonation.internal',
                'role' => 'super_admin'
            ];
            return;
        }

        // Attempt to get from JWT (API requests)
        $user = AuthMiddleware::authenticate();
        if ($user) {
            $this->currentUser = $user;
            return;
        }

        // Fallback to session (admin panel requests)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['backend_user']) && $_SESSION['backend_user']['logged_in'] === true) {
            $this->currentUser = [
                'id' => $_SESSION['backend_user']['id'] ?? 0,
                'email' => $_SESSION['backend_user']['email'],
                'role' => $_SESSION['backend_user']['role']
            ];
        } elseif (isset($_SESSION['user']) && isset($_SESSION['user']['id'])) {
            $this->currentUser = $_SESSION['user'];
        }
    }

    /**
     * Get current user's role level
     */
    private function getCurrentUserRoleLevel(): int
    {
        if (!$this->currentUser) {
            return 0;
        }
        return self::ROLE_HIERARCHY[$this->currentUser['role']] ?? 0;
    }

    /**
     * Check if current user can manage target user (based on role hierarchy)
     * Rule: Can only manage users with LOWER role level
     */
    private function canManageUser(array $targetUser): bool
    {
        $currentLevel = $this->getCurrentUserRoleLevel();
        $targetLevel = self::ROLE_HIERARCHY[$targetUser['role']] ?? 0;

        // Must have higher role to manage
        return $currentLevel > $targetLevel;
    }

    /**
     * Check if current user can assign a specific role
     * Rule: Can only assign roles LOWER than own role
     */
    private function canAssignRole(string $role): bool
    {
        $currentLevel = $this->getCurrentUserRoleLevel();
        $targetRoleLevel = self::ROLE_HIERARCHY[$role] ?? 0;

        // Must have higher role to assign
        return $currentLevel > $targetRoleLevel;
    }

    /**
     * Check if target is self (same user ID)
     */
    private function isSelf(int $targetId): bool
    {
        return $this->currentUser && (int) $this->currentUser['id'] === $targetId;
    }

    /**
     * Get target user by ID with role info
     */
    private function getTargetUser(string $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, email, name, role, status FROM edonation_admin_users WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // ========== CRUD Operations ==========

    /**
     * GET /admin-users - Get all admin users
     */
    public function index(): array
    {
        try {
            $columns = implode(', ', self::SELECT_COLUMNS);
            $stmt = $this->db->prepare("
                SELECT {$columns}
                FROM edonation_admin_users 
                ORDER BY 
                    FIELD(role, 'super_admin', 'admin', 'editor', 'viewer'),
                    created_at DESC
            ");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Add management flags for UI
            $currentLevel = $this->getCurrentUserRoleLevel();
            foreach ($users as &$user) {
                $userLevel = self::ROLE_HIERARCHY[$user['role']] ?? 0;
                $user['can_edit'] = $currentLevel > $userLevel;
                $user['can_delete'] = $currentLevel > $userLevel && !$this->isSelf((int) $user['id']);
            }

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
     * GET /admin-users/:id - Get single admin user
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
     * GET /admin-users/check?email=xxx - Check if user is authorized
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
     * POST /admin-users - Create new admin user
     * 
     * Security: Cannot create user with role equal to or higher than own
     */
    public function create(): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $email = trim($data['email'] ?? '');
        $name = trim($data['name'] ?? '');
        $role = $data['role'] ?? 'viewer';

        // Validation
        $validationError = $this->validateCreateInput($email, $name, $role);
        if ($validationError !== null) {
            return $validationError;
        }

        // Security: Check if current user can assign this role
        if (!$this->canAssignRole($role)) {
            error_log("Privilege escalation attempt: User {$this->currentUser['email']} tried to create user with role {$role}");
            return $this->jsonError('ไม่สามารถกำหนดสิทธิ์นี้ได้ (สิทธิ์เกินขอบเขต)', 403);
        }

        try {
            $this->db->beginTransaction();

            // Check if email already exists
            $checkStmt = $this->db->prepare(
                "SELECT id FROM edonation_admin_users WHERE email = :email"
            );
            $checkStmt->execute([':email' => $email]);

            if ($checkStmt->fetch()) {
                $this->db->rollBack();
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

            $this->db->commit();

            // Audit log
            error_log("AUDIT: User created - ID: {$userId}, Email: {$email}, Role: {$role}, By: {$this->currentUser['email']}");

            return $this->jsonSuccess([
                'id' => (int) $userId,
                'email' => $email,
                'name' => $name,
                'role' => $role
            ], 'เพิ่มผู้ดูแลระบบสำเร็จ');

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("AdminUser Create DB Error: " . $e->getMessage());
            return $this->jsonError('ไม่สามารถเพิ่มได้', 500);
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("AdminUser Create Error: " . $e->getMessage());
            return $this->jsonError('เกิดข้อผิดพลาดภายในระบบ', 500);
        }
    }

    /**
     * PUT /admin-users/:id - Update admin user
     * 
     * Security:
     * - Cannot update user with equal or higher role
     * - Cannot demote self
     * - Cannot assign role equal to or higher than own
     */
    public function update(string $id): array
    {
        if (!$this->isValidId($id)) {
            return $this->jsonError('รูปแบบ ID ไม่ถูกต้อง', 400);
        }

        // Get target user first
        $targetUser = $this->getTargetUser($id);
        if (!$targetUser) {
            return $this->jsonError('ไม่พบผู้ใช้', 404);
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $name = isset($data['name']) ? trim($data['name']) : null;
        $role = $data['role'] ?? null;
        $status = $data['status'] ?? null;

        // Security: Self-modification restrictions
        if ($this->isSelf((int) $id)) {
            // Cannot change own role (prevent self-escalation or self-demotion)
            if ($role !== null && $role !== $targetUser['role']) {
                error_log("Self-role-change attempt: User {$this->currentUser['email']} tried to change own role to {$role}");
                return $this->jsonError('ไม่สามารถเปลี่ยนสิทธิ์ของตัวเองได้', 403);
            }

            // Cannot deactivate self
            if ($status === 'inactive') {
                error_log("Self-deactivation attempt: User {$this->currentUser['email']}");
                return $this->jsonError('ไม่สามารถปิดการใช้งานตัวเองได้', 403);
            }
        }

        // Security: Cannot modify user with equal or higher role
        if (!$this->isSelf((int) $id) && !$this->canManageUser($targetUser)) {
            error_log("Unauthorized modification attempt: User {$this->currentUser['email']} tried to modify user with role {$targetUser['role']}");
            return $this->jsonError('ไม่มีสิทธิ์แก้ไขผู้ใช้รายนี้', 403);
        }

        // Validate inputs
        if ($role !== null && !in_array($role, self::ALLOWED_ROLES, true)) {
            return $this->jsonError('Role ไม่ถูกต้อง', 400);
        }

        // Security: Cannot assign role equal to or higher than own
        if ($role !== null && !$this->canAssignRole($role)) {
            error_log("Privilege escalation in update: User {$this->currentUser['email']} tried to set role {$role}");
            return $this->jsonError('ไม่สามารถกำหนดสิทธิ์นี้ได้', 403);
        }

        if ($status !== null && !in_array($status, self::ALLOWED_STATUSES, true)) {
            return $this->jsonError('Status ไม่ถูกต้อง', 400);
        }

        if ($name !== null && empty($name)) {
            return $this->jsonError('ชื่อไม่สามารถเป็นค่าว่างได้', 400);
        }

        try {
            $this->db->beginTransaction();

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
                $this->db->rollBack();
                return $this->jsonError('ไม่มีข้อมูลที่จะอัปเดต', 400);
            }

            $sql = "UPDATE edonation_admin_users SET " . implode(', ', $updates) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $this->db->commit();

            // Audit log
            error_log("AUDIT: User updated - ID: {$id}, Changes: " . json_encode($data) . ", By: {$this->currentUser['email']}");

            return $this->jsonSuccess(null, 'อัปเดตสำเร็จ');

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("AdminUser Update DB Error: " . $e->getMessage());
            return $this->jsonError('ไม่สามารถอัปเดตได้', 500);
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("AdminUser Update Error: " . $e->getMessage());
            return $this->jsonError('เกิดข้อผิดพลาดภายในระบบ', 500);
        }
    }

    /**
     * DELETE /admin-users/:id - Delete admin user
     * 
     * Security:
     * - Cannot delete self
     * - Cannot delete user with equal or higher role
     */
    public function delete(string $id): array
    {
        if (!$this->isValidId($id)) {
            return $this->jsonError('รูปแบบ ID ไม่ถูกต้อง', 400);
        }

        // Security: Cannot delete self
        if ($this->isSelf((int) $id)) {
            error_log("Self-deletion attempt: User {$this->currentUser['email']}");
            return $this->jsonError('ไม่สามารถลบบัญชีของตัวเองได้', 403);
        }

        // Get target user first
        $targetUser = $this->getTargetUser($id);
        if (!$targetUser) {
            return $this->jsonError('ไม่พบผู้ใช้', 404);
        }

        // Security: Cannot delete user with equal or higher role
        if (!$this->canManageUser($targetUser)) {
            error_log("Unauthorized deletion attempt: User {$this->currentUser['email']} tried to delete user with role {$targetUser['role']}");
            return $this->jsonError('ไม่มีสิทธิ์ลบผู้ใช้รายนี้', 403);
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("DELETE FROM edonation_admin_users WHERE id = :id");
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() > 0) {
                $this->db->commit();

                // Audit log
                error_log("AUDIT: User deleted - ID: {$id}, Email: {$targetUser['email']}, By: {$this->currentUser['email']}");

                return $this->jsonSuccess(null, 'ลบสำเร็จ');
            }

            $this->db->rollBack();
            return $this->jsonError('ไม่พบผู้ใช้', 404);

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("AdminUser Delete DB Error: " . $e->getMessage());
            return $this->jsonError('ไม่สามารถลบได้', 500);
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("AdminUser Delete Error: " . $e->getMessage());
            return $this->jsonError('เกิดข้อผิดพลาดภายในระบบ', 500);
        }
    }

    /**
     * PATCH /admin-users/:id/deactivate - Soft delete (deactivate) admin user
     * 
     * Security: Same as delete
     */
    public function deactivate(string $id): array
    {
        if (!$this->isValidId($id)) {
            return $this->jsonError('รูปแบบ ID ไม่ถูกต้อง', 400);
        }

        // Security: Cannot deactivate self
        if ($this->isSelf((int) $id)) {
            error_log("Self-deactivation attempt: User {$this->currentUser['email']}");
            return $this->jsonError('ไม่สามารถปิดการใช้งานบัญชีของตัวเองได้', 403);
        }

        // Get target user first
        $targetUser = $this->getTargetUser($id);
        if (!$targetUser) {
            return $this->jsonError('ไม่พบผู้ใช้', 404);
        }

        // Security: Cannot deactivate user with equal or higher role
        if (!$this->canManageUser($targetUser)) {
            error_log("Unauthorized deactivation attempt: User {$this->currentUser['email']} tried to deactivate user with role {$targetUser['role']}");
            return $this->jsonError('ไม่มีสิทธิ์ปิดการใช้งานผู้ใช้รายนี้', 403);
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE edonation_admin_users SET status = 'inactive' WHERE id = :id"
            );
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                return $this->jsonError('ไม่พบผู้ใช้หรือสถานะไม่เปลี่ยนแปลง', 404);
            }

            // Audit log
            error_log("AUDIT: User deactivated - ID: {$id}, By: {$this->currentUser['email']}");

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
     * PATCH /admin-users/:id/activate - Activate admin user
     */
    public function activate(string $id): array
    {
        if (!$this->isValidId($id)) {
            return $this->jsonError('รูปแบบ ID ไม่ถูกต้อง', 400);
        }

        // Get target user first
        $targetUser = $this->getTargetUser($id);
        if (!$targetUser) {
            return $this->jsonError('ไม่พบผู้ใช้', 404);
        }

        // Security: Cannot activate user with equal or higher role
        if (!$this->canManageUser($targetUser)) {
            return $this->jsonError('ไม่มีสิทธิ์เปิดการใช้งานผู้ใช้รายนี้', 403);
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE edonation_admin_users SET status = 'active' WHERE id = :id"
            );
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                return $this->jsonError('ไม่พบผู้ใช้หรือสถานะไม่เปลี่ยนแปลง', 404);
            }

            // Audit log
            error_log("AUDIT: User activated - ID: {$id}, By: {$this->currentUser['email']}");

            return $this->jsonSuccess(null, 'เปิดการใช้งานสำเร็จ');
        } catch (PDOException $e) {
            error_log("AdminUser Activate DB Error: " . $e->getMessage());
            return $this->jsonError('ไม่สามารถเปิดการใช้งานได้', 500);
        } catch (Exception $e) {
            error_log("AdminUser Activate Error: " . $e->getMessage());
            return $this->jsonError('เกิดข้อผิดพลาดภายในระบบ', 500);
        }
    }

    // ========== Batch Permission Updates (Many-to-Many) ==========

    /**
     * PUT /admin-users/:id/permissions - Update user permissions atomically
     * Uses Transaction to ensure data integrity
     * 
     * Example: Updating page access rights for a user
     * Input: { "page_ids": [1, 2, 5, 7] }
     */
    public function updatePermissions(string $id): array
    {
        if (!$this->isValidId($id)) {
            return $this->jsonError('รูปแบบ ID ไม่ถูกต้อง', 400);
        }

        // Get target user
        $targetUser = $this->getTargetUser($id);
        if (!$targetUser) {
            return $this->jsonError('ไม่พบผู้ใช้', 404);
        }

        // Security check
        if (!$this->canManageUser($targetUser)) {
            return $this->jsonError('ไม่มีสิทธิ์จัดการ permission ของผู้ใช้รายนี้', 403);
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $pageIds = $data['page_ids'] ?? [];

        // Validate page_ids are integers
        if (!is_array($pageIds)) {
            return $this->jsonError('page_ids ต้องเป็น array', 400);
        }

        $validPageIds = [];
        foreach ($pageIds as $pageId) {
            if (!is_int($pageId) && !ctype_digit((string) $pageId)) {
                return $this->jsonError('page_ids ทุกตัวต้องเป็นตัวเลข', 400);
            }
            $validPageIds[] = (int) $pageId;
        }

        try {
            // Use transaction for atomic permission update
            $this->db->beginTransaction();

            // Step 1: Delete all existing permissions for this user
            $deleteStmt = $this->db->prepare(
                "DELETE FROM edonation_user_page_permissions WHERE user_id = :user_id"
            );
            $deleteStmt->execute([':user_id' => $id]);

            // Step 2: Batch insert new permissions (if any)
            if (!empty($validPageIds)) {
                // Verify page IDs exist (optional but recommended)
                $placeholders = implode(',', array_fill(0, count($validPageIds), '?'));
                $verifyStmt = $this->db->prepare(
                    "SELECT id FROM edonation_pages WHERE id IN ({$placeholders})"
                );
                $verifyStmt->execute($validPageIds);
                $existingPages = $verifyStmt->fetchAll(PDO::FETCH_COLUMN);

                // Filter to only valid page IDs
                $validPageIds = array_intersect($validPageIds, $existingPages);

                // Batch insert
                if (!empty($validPageIds)) {
                    $insertSql = "INSERT INTO edonation_user_page_permissions (user_id, page_id, created_at) VALUES ";
                    $insertValues = [];
                    $insertParams = [];

                    foreach ($validPageIds as $index => $pageId) {
                        $insertValues[] = "(:user_id_{$index}, :page_id_{$index}, NOW())";
                        $insertParams[":user_id_{$index}"] = $id;
                        $insertParams[":page_id_{$index}"] = $pageId;
                    }

                    $insertSql .= implode(', ', $insertValues);
                    $insertStmt = $this->db->prepare($insertSql);
                    $insertStmt->execute($insertParams);
                }
            }

            $this->db->commit();

            // Audit log
            error_log("AUDIT: Permissions updated - User ID: {$id}, Pages: " . implode(',', $validPageIds) . ", By: {$this->currentUser['email']}");

            return $this->jsonSuccess([
                'user_id' => (int) $id,
                'page_ids' => $validPageIds,
                'count' => count($validPageIds)
            ], 'อัปเดต permission สำเร็จ');

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("AdminUser UpdatePermissions DB Error: " . $e->getMessage());
            return $this->jsonError('ไม่สามารถอัปเดต permission ได้', 500);
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("AdminUser UpdatePermissions Error: " . $e->getMessage());
            return $this->jsonError('เกิดข้อผิดพลาดภายในระบบ', 500);
        }
    }

    // ========== Validation Helpers ==========

    /**
     * Validate create input
     */
    private function validateCreateInput(string $email, string $name, string $role): ?array
    {
        if (empty($email) || empty($name)) {
            return $this->jsonError('กรุณากรอก email และชื่อ', 400);
        }

        // Sanitize and validate email
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonError('รูปแบบ email ไม่ถูกต้อง', 400);
        }

        if (!str_ends_with($email, self::CMU_EMAIL_DOMAIN)) {
            return $this->jsonError('ต้องใช้ CMU Account email (@cmu.ac.th) เท่านั้น', 400);
        }

        // Sanitize name (allow letters, spaces, dots)
        $name = preg_replace('/[^\p{L}\s.\-]/u', '', $name);
        if (strlen($name) < 2) {
            return $this->jsonError('ชื่อต้องมีอย่างน้อย 2 ตัวอักษร', 400);
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

    // ========== Response Helpers ==========

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
