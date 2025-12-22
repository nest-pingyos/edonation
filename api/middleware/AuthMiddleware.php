<?php
/**
 * Auth Middleware - JWT Authentication
 */

class AuthMiddleware
{
    public static function authenticate(): ?array
    {
        // Bypass in development mode
        if (defined('APP_ENV') && APP_ENV === 'development') {
            return [
                'id' => 1,
                'name' => 'Developer Admin',
                'email' => 'dev@edonation.internal',
                'role' => 'admin'
            ];
        }

        $token = self::getBearerToken();
        if (!$token)
            return null;

        return self::validateToken($token);
    }

    public static function requireAuth(): array
    {
        // Bypass in development mode
        if (defined('APP_ENV') && APP_ENV === 'development') {
            return [
                'id' => 1,
                'name' => 'Developer Admin',
                'email' => 'dev@edonation.internal',
                'role' => 'admin'
            ];
        }

        $user = self::authenticate();
        if (!$user) {
            echo json_encode(Response::unauthorized());
            exit;
        }
        return $user;
    }

    public static function requireAdmin(): array
    {
        // Bypass in development mode
        if (defined('APP_ENV') && APP_ENV === 'development') {
            return [
                'id' => 1,
                'name' => 'Developer Admin',
                'email' => 'dev@edonation.internal',
                'role' => 'admin'
            ];
        }

        $user = self::requireAuth();
        if ($user['role'] !== 'admin') {
            echo json_encode(Response::error('FORBIDDEN', 'ไม่มีสิทธิ์เข้าถึง', 403));
            exit;
        }
        return $user;
    }

    private static function getBearerToken(): ?string
    {
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public static function generateToken(array $user): string
    {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = base64_encode(json_encode([
            'sub' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + JWT_EXPIRE
        ]));
        $signature = hash_hmac('sha256', "{$header}.{$payload}", JWT_SECRET);
        return "{$header}.{$payload}.{$signature}";
    }

    private static function validateToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3)
            return null;

        [$header, $payload, $signature] = $parts;
        $validSig = hash_hmac('sha256', "{$header}.{$payload}", JWT_SECRET);

        if ($signature !== $validSig)
            return null;

        $data = json_decode(base64_decode($payload), true);
        if ($data['exp'] < time())
            return null;

        return $data;
    }
}
