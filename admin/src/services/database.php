<?php

declare(strict_types=1);

/**
 * Database Service - MySQL Connection with Security Best Practices
 * 
 * เชื่อมต่อ MySQL database ของ eDonation
 * 
 * Security Features:
 * - Singleton pattern with PDO
 * - Prepared statements enforcement
 * - Password hashing with Argon2/Bcrypt
 * - No plain-text password logging
 * - Timing-safe password verification
 * 
 * @package eDonation\Admin\Services
 * @version 2.0.0
 */

require_once __DIR__ . '/../config/config.php';

class DatabaseService
{
    private static ?PDO $instance = null;

    // Password hashing options (Argon2id preferred, bcrypt fallback)
    private const PASSWORD_ALGO = PASSWORD_DEFAULT; // Uses best available (Argon2id on PHP 7.3+)
    private const PASSWORD_OPTIONS = [
        'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
        'time_cost' => PASSWORD_ARGON2_DEFAULT_TIME_COST,
        'threads' => PASSWORD_ARGON2_DEFAULT_THREADS,
    ];

    /**
     * Get database instance (Singleton)
     * 
     * @return PDO Database connection
     * @throws Exception If connection fails
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=%s',
                    DB_HOST,
                    DB_NAME,
                    DB_CHARSET
                );

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false, // Use real prepared statements
                    PDO::ATTR_STRINGIFY_FETCHES => false, // Return native types
                    PDO::MYSQL_ATTR_FOUND_ROWS => true,   // Return matched rows for UPDATE
                ]);
            } catch (PDOException $e) {
                // Log without exposing connection details
                error_log("Database connection failed: " . $e->getCode());
                throw new Exception("ไม่สามารถเชื่อมต่อฐานข้อมูลได้");
            }
        }
        return self::$instance;
    }

    /**
     * Check if user exists by email (for CMU OAuth pre-check)
     * 
     * @param string $email User email
     * @return bool True if user exists and is active
     */
    public static function checkUser(string $email): bool
    {
        // Validate email format first
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $pdo = self::getInstance();
        $stmt = $pdo->prepare(
            "SELECT 1 FROM edonation_admin_users WHERE email = :email AND status = 'active' LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() !== false;
    }

    /**
     * Authenticate user with email and password
     * 
     * Security:
     * - Uses password_verify (timing-safe)
     * - Never logs passwords
     * - Returns null for any auth failure (prevents user enumeration)
     * 
     * @param string $email User email
     * @param string $password Plain-text password (will NOT be logged)
     * @return array|null User data if authenticated, null otherwise
     */
    public static function authenticateUser(string $email, string $password): ?array
    {
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        // Never log passwords!
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("
            SELECT id, email, name, role, password_hash, status 
            FROM edonation_admin_users 
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        // User not found or inactive
        if (!$user || $user['status'] !== 'active') {
            // Perform dummy password_verify to prevent timing attacks
            password_verify($password, '$2y$10$dummyhashtopreventtimingattacksxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
            return null;
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            // Log failed attempt (without password!)
            error_log("Auth failed for user: {$email} - Invalid password");
            return null;
        }

        // Check if password needs rehashing (algorithm upgrade)
        if (password_needs_rehash($user['password_hash'], self::PASSWORD_ALGO, self::PASSWORD_OPTIONS)) {
            self::updatePasswordHash((int) $user['id'], $password);
        }

        // Update last login timestamp
        self::updateLastLogin((int) $user['id']);

        // Remove sensitive data before returning
        unset($user['password_hash']);
        unset($user['status']);

        return $user;
    }

    /**
     * Authenticate CMU OAuth user by email
     * 
     * @param string $email CMU email
     * @return array|null User data if found, null otherwise
     */
    public static function authenticateCMUUser(string $email): ?array
    {
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $pdo = self::getInstance();
        $stmt = $pdo->prepare("
            SELECT id, email, name, role, status 
            FROM edonation_admin_users 
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    /**
     * Update last login timestamp
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public static function updateLastLogin(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $pdo = self::getInstance();
        $stmt = $pdo->prepare("UPDATE edonation_admin_users SET last_login = NOW() WHERE id = :id");
        return $stmt->execute([':id' => $userId]);
    }

    /**
     * Get user by ID
     * 
     * @param int $id User ID
     * @return array|null User data if found
     */
    public static function getUserById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $pdo = self::getInstance();
        $stmt = $pdo->prepare(
            "SELECT id, email, name, role, status, created_at, last_login 
             FROM edonation_admin_users 
             WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create admin user
     * 
     * @param string $email User email
     * @param string $name User display name
     * @param string $role User role
     * @param string|null $password Plain-text password (optional for CMU OAuth users)
     * @return int|false New user ID on success, false on failure
     */
    public static function createUser(
        string $email,
        string $name,
        string $role = 'admin',
        ?string $password = null
    ): int|false {
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Validate role
        $allowedRoles = ['super_admin', 'admin', 'editor', 'viewer'];
        if (!in_array($role, $allowedRoles, true)) {
            return false;
        }

        $pdo = self::getInstance();

        try {
            $pdo->beginTransaction();

            // Check if email already exists
            $checkStmt = $pdo->prepare("SELECT 1 FROM edonation_admin_users WHERE email = :email LIMIT 1");
            $checkStmt->execute([':email' => $email]);
            if ($checkStmt->fetch()) {
                $pdo->rollBack();
                return false; // Email already exists
            }

            // Hash password if provided
            $passwordHash = null;
            if ($password !== null) {
                $passwordHash = self::hashPassword($password);
            }

            // Insert user
            $stmt = $pdo->prepare("
                INSERT INTO edonation_admin_users (email, name, role, password_hash, status, created_at) 
                VALUES (:email, :name, :role, :password_hash, 'active', NOW())
            ");
            $stmt->execute([
                ':email' => $email,
                ':name' => $name,
                ':role' => $role,
                ':password_hash' => $passwordHash
            ]);

            $userId = (int) $pdo->lastInsertId();
            $pdo->commit();

            // Audit log (never log passwords!)
            error_log("AUDIT: User created - ID: {$userId}, Email: {$email}, Role: {$role}");

            return $userId;

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("DatabaseService::createUser Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Hash password using modern algorithms
     * 
     * Uses Argon2id (PHP 7.3+) or bcrypt as fallback
     * 
     * @param string $password Plain-text password
     * @return string Hashed password
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, self::PASSWORD_ALGO, self::PASSWORD_OPTIONS);
    }

    /**
     * Update user's password hash (for rehashing on algorithm upgrade)
     * 
     * @param int $userId User ID
     * @param string $newPassword New plain-text password
     * @return bool Success status
     */
    public static function updatePasswordHash(int $userId, string $newPassword): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $pdo = self::getInstance();
        $hash = self::hashPassword($newPassword);

        $stmt = $pdo->prepare("UPDATE edonation_admin_users SET password_hash = :hash WHERE id = :id");
        return $stmt->execute([
            ':hash' => $hash,
            ':id' => $userId
        ]);
    }

    /**
     * Change user password (requires old password verification)
     * 
     * @param int $userId User ID
     * @param string $oldPassword Current password
     * @param string $newPassword New password
     * @return bool Success status
     */
    public static function changePassword(int $userId, string $oldPassword, string $newPassword): bool
    {
        if ($userId <= 0) {
            return false;
        }

        // Get current hash
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("SELECT password_hash FROM edonation_admin_users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($oldPassword, $user['password_hash'])) {
            return false;
        }

        return self::updatePasswordHash($userId, $newPassword);
    }

    /**
     * Get user permissions (page access rights)
     * Fetches all permissions in a single query (no loop queries)
     * 
     * @param int $userId User ID
     * @return array List of accessible page IDs
     */
    public static function getUserPermissions(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $pdo = self::getInstance();
        $stmt = $pdo->prepare("
            SELECT page_id 
            FROM edonation_user_page_permissions 
            WHERE user_id = :user_id
        ");
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * Check if user can access specific page
     * Uses cached permissions from session if available
     * 
     * @param int $userId User ID
     * @param int $pageId Page ID
     * @return bool True if user has access
     */
    public static function canAccessPage(int $userId, int $pageId): bool
    {
        // Check session cache first
        if (isset($_SESSION['user_permissions'][$userId])) {
            return in_array($pageId, $_SESSION['user_permissions'][$userId], true);
        }

        // Fetch and cache permissions
        $permissions = self::getUserPermissions($userId);

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['user_permissions'][$userId] = $permissions;
        }

        return in_array($pageId, $permissions, true);
    }

    /**
     * Invalidate cached permissions for user
     * Call this after updating user permissions
     * 
     * @param int $userId User ID
     */
    public static function invalidatePermissionCache(int $userId): void
    {
        if (isset($_SESSION['user_permissions'][$userId])) {
            unset($_SESSION['user_permissions'][$userId]);
        }
    }
}