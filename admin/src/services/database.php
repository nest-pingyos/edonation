<?php
/**
 * Database Service - MySQL Connection
 * 
 * เชื่อมต่อ MySQL database ของ eDonation
 */

require_once __DIR__ . '/../config/config.php';

class DatabaseService
{
    private static ?PDO $instance = null;

    /**
     * Get database instance (Singleton)
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
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new Exception("ไม่สามารถเชื่อมต่อฐานข้อมูลได้");
            }
        }
        return self::$instance;
    }

    /**
     * Check if user exists by email
     */
    public static function checkUser(string $email): bool
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("SELECT id FROM edonation_admin_users WHERE email = :email AND status = 'active'");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() !== false;
    }

    /**
     * Authenticate user with email and password
     */
    public static function authenticateUser(string $email, string $password): ?array
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("
            SELECT id, email, name, role, password_hash 
            FROM edonation_admin_users 
            WHERE email = :email AND status = 'active'
        ");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Update last login
            $updateStmt = $pdo->prepare("UPDATE edonation_admin_users SET last_login = NOW() WHERE id = :id");
            $updateStmt->execute([':id' => $user['id']]);

            unset($user['password_hash']);
            return $user;
        }
        return null;
    }

    /**
     * Authenticate CMU OAuth user by email
     */
    public static function authenticateCMUUser(string $email): ?array
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("
            SELECT id, email, name, role 
            FROM edonation_admin_users 
            WHERE email = :email AND status = 'active'
        ");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            // Update last login
            $updateStmt = $pdo->prepare("UPDATE edonation_admin_users SET last_login = NOW() WHERE id = :id");
            $updateStmt->execute([':id' => $user['id']]);
            return $user;
        }
        return null;
    }

    /**
     * Get user by ID
     */
    public static function getUserById(int $id): ?array
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("SELECT id, email, name, role, created_at, last_login FROM edonation_admin_users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create admin user (CMU OAuth - no password)
     */
    public static function createUser(string $email, string $name, string $role = 'admin'): bool
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare("
            INSERT INTO edonation_admin_users (email, name, role, status, created_at) 
            VALUES (:email, :name, :role, 'active', NOW())
        ");
        return $stmt->execute([
            ':email' => $email,
            ':name' => $name,
            ':role' => $role
        ]);
    }
}