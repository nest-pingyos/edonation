<?php

declare(strict_types=1);

/**
 * Auth Middleware - JWT Authentication with Enhanced Security
 * 
 * Security Features:
 * - Timing-safe signature comparison
 * - Token expiration validation
 * - Role hierarchy enforcement
 * - Development mode bypass (controlled)
 * 
 * @package eDonation\API\Middleware
 * @version 2.0.0
 */

class AuthMiddleware
{
    private const ROLE_HIERARCHY = [
        'super_admin' => 100,
        'finance_admin' => 80,
        'hr_admin' => 60,
        'news_admin' => 40
    ];

    /**
     * Authenticate user from Bearer token
     * 
     * @return array|null User data if authenticated, null otherwise
     */
    public static function authenticate(): ?array
    {
        // 1. Try real token first
        $token = self::getBearerToken();
        if ($token) {
            $user = self::validateToken($token);
            if ($user) {
                return $user;
            }
        }

        // 2. Bypass in development mode ONLY if no valid token
        if (defined('APP_ENV') && APP_ENV === 'development') {
            return self::getDevUser();
        }

        return null;
    }

    /**
     * Require authentication - exit with 401 if not authenticated
     * 
     * @return array User data
     */
    public static function requireAuth(): array
    {
        $user = self::authenticate();
        if (!$user) {
            self::sendUnauthorizedResponse();
        }
        return $user;
    }

    /**
     * Require admin role - exit with 403 if not admin
     * 
     * @return array User data
     */
    public static function requireAdmin(): array
    {
        $user = self::requireAuth();

        if (!self::hasMinimumRole($user, 'admin')) {
            self::sendForbiddenResponse();
        }

        return $user;
    }

    /**
     * Require super_admin role
     * 
     * @return array User data
     */
    public static function requireSuperAdmin(): array
    {
        $user = self::requireAuth();

        if (!self::hasMinimumRole($user, 'super_admin')) {
            self::sendForbiddenResponse();
        }

        return $user;
    }

    /**
     * Require specific minimum role
     * 
     * @param string $role Minimum required role
     * @return array User data
     */
    public static function requireRole(string $role): array
    {
        $user = self::requireAuth();

        if (!self::hasMinimumRole($user, $role)) {
            self::sendForbiddenResponse();
        }

        return $user;
    }

    /**
     * Check if user has minimum role level
     * 
     * @param array $user User data with role
     * @param string $minimumRole Required minimum role
     * @return bool
     */
    public static function hasMinimumRole(array $user, string $minimumRole): bool
    {
        $userLevel = self::ROLE_HIERARCHY[$user['role'] ?? ''] ?? 0;
        $requiredLevel = self::ROLE_HIERARCHY[$minimumRole] ?? 0;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Get Bearer token from headers
     * 
     * @return string|null Token if present, null otherwise
     */
    private static function getBearerToken(): ?string
    {
        $auth = null;

        // Try various ways to get Authorization header (Apache/Nginx/FastCGI)
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $auth = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['Authorization'])) {
            $auth = $_SERVER['Authorization'];
        } elseif (function_exists('getallheaders')) {
            $headers = getallheaders();
            // Case-insensitive search for Authorization header
            foreach ($headers as $name => $value) {
                if (strtolower($name) === 'authorization') {
                    $auth = $value;
                    break;
                }
            }
        }

        if ($auth && preg_match('/Bearer\s+(.+)$/i', $auth, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Generate JWT token for user
     * 
     * @param array $user User data (must include id, name, email, role)
     * @return string JWT token
     */
    public static function generateToken(array $user): string
    {
        $header = self::base64UrlEncode(json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]));

        $issuedAt = time();
        $expiration = $issuedAt + (defined('JWT_EXPIRE') ? JWT_EXPIRE : 28800); // 8 hours default

        $payload = self::base64UrlEncode(json_encode([
            'sub' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'iat' => $issuedAt,
            'exp' => $expiration,
            // Add jti for token revocation support (optional)
            'jti' => bin2hex(random_bytes(16))
        ]));

        $signature = self::createSignature("{$header}.{$payload}");

        return "{$header}.{$payload}.{$signature}";
    }

    /**
     * Validate JWT token
     * 
     * @param string $token JWT token
     * @return array|null User data if valid, null otherwise
     */
    private static function validateToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;

        // Verify signature using timing-safe comparison
        $expectedSignature = self::createSignature("{$header}.{$payload}");
        if (!hash_equals($expectedSignature, $signature)) {
            error_log("JWT: Invalid signature detected");
            return null;
        }

        // Decode payload
        $data = json_decode(self::base64UrlDecode($payload), true);
        if (!$data) {
            return null;
        }

        // Check expiration
        if (!isset($data['exp']) || $data['exp'] < time()) {
            $userEmail = $data['email'] ?? 'unknown';
            error_log("JWT: Token expired for user {$userEmail}");
            return null;
        }

        // Check issued at (prevent tokens from the future)
        if (isset($data['iat']) && $data['iat'] > time() + 60) {
            error_log("JWT: Token issued in the future, possible clock skew attack");
            return null;
        }

        return [
            'id' => isset($data['sub']) ? $data['sub'] : 0,
            'name' => isset($data['name']) ? $data['name'] : '',
            'email' => isset($data['email']) ? $data['email'] : '',
            'role' => isset($data['role']) ? $data['role'] : 'viewer'
        ];
    }

    /**
     * Create HMAC-SHA256 signature
     * 
     * @param string $data Data to sign
     * @return string URL-safe base64 encoded signature
     */
    private static function createSignature(string $data): string
    {
        $secret = defined('JWT_SECRET') ? JWT_SECRET : 'default-secret-change-me';

        // Use raw binary output and encode as base64url
        $signature = hash_hmac('sha256', $data, $secret, true);
        return self::base64UrlEncode($signature);
    }

    /**
     * Base64 URL-safe encode
     * 
     * @param string $data Data to encode
     * @return string URL-safe base64 encoded string
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL-safe decode
     * 
     * @param string $data URL-safe base64 encoded string
     * @return string Decoded data
     */
    private static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Get development user (for dev mode only)
     * 
     * @return array Dev user data
     */
    private static function getDevUser(): array
    {
        return [
            'id' => 1,
            'name' => 'Developer Admin',
            'email' => 'dev@edonation.internal',
            'role' => 'super_admin'
        ];
    }

    /**
     * Send 401 Unauthorized response and exit
     */
    private static function sendUnauthorizedResponse(): void
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'UNAUTHORIZED',
                'message' => 'กรุณาเข้าสู่ระบบ'
            ]
        ]);
        exit;
    }

    /**
     * Send 403 Forbidden response and exit
     */
    private static function sendForbiddenResponse(): void
    {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'FORBIDDEN',
                'message' => 'ไม่มีสิทธิ์เข้าถึง'
            ]
        ]);
        exit;
    }

    /**
     * Refresh token (issue new token with extended expiration)
     * 
     * @param string $token Current valid token
     * @return string|null New token if valid, null if expired
     */
    public static function refreshToken(string $token): ?string
    {
        $user = self::validateToken($token);
        if (!$user) {
            return null;
        }

        return self::generateToken($user);
    }
}
