<?php
/**
 * Auth Controller
 * 
 * Endpoints:
 * POST   /auth/login       - เข้าสู่ระบบ
 * POST   /auth/oauth/cmu   - CMU OAuth login
 * POST   /auth/logout      - ออกจากระบบ
 * GET    /auth/me          - ข้อมูลผู้ใช้ปัจจุบัน
 */

class AuthController
{
    const VERSION = '2.0';
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function handle(string $method, ?string $id, ?string $action): array
    {
        $endpoint = $id;
        if ($id === 'oauth' && $action)
            $endpoint = "oauth/{$action}";

        switch ($endpoint) {
            case 'login':
                return $this->login();
            case 'oauth/cmu':
                return $this->oauthCmu();
            case 'logout':
                return $this->logout();
            case 'me':
                return $this->me();
            default:
                return Response::error('NOT_FOUND', 'Endpoint not found', 404);
        }
    }

    // POST /auth/login
    private function login(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('METHOD_NOT_ALLOWED', 'Use POST method', 405);
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $v = new Validator($data);
        $v->required('username')->required('password');

        if (!$v->passes())
            return Response::validation($v->errors());

        // Dev account for testing (remove in production!)
        if ($data['username'] === 'admin' && $data['password'] === 'admin123') {
            $token = AuthMiddleware::generateToken([
                'id' => 1,
                'name' => 'Admin (Dev)',
                'email' => 'admin@dev.local',
                'role' => 'admin'
            ]);

            return Response::success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => JWT_EXPIRE,
                'user' => [
                    'id' => 1,
                    'name' => 'Admin (Dev)',
                    'email' => 'admin@dev.local',
                    'role' => 'admin'
                ]
            ]);
        }

        // Find user from database
        try {
            $stmt = $this->pdo->prepare(
                "SELECT id, name, email, password, role FROM users WHERE email = :email OR username = :email LIMIT 1"
            );
            $stmt->execute([':email' => $data['username']]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($data['password'], $user['password'])) {
                return Response::error('INVALID_CREDENTIALS', 'อีเมลหรือรหัสผ่านไม่ถูกต้อง', 401);
            }

            // Generate token
            $token = AuthMiddleware::generateToken([
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]);

            return Response::success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => JWT_EXPIRE,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]);
        } catch (PDOException $e) {
            // Table doesn't exist - use dev account only
            return Response::error('INVALID_CREDENTIALS', 'อีเมลหรือรหัสผ่านไม่ถูกต้อง (Dev: admin/admin123)', 401);
        }
    }

    // POST /auth/oauth/cmu
    private function oauthCmu(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('METHOD_NOT_ALLOWED', 'Use POST method', 405);
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        if (empty($data['code'])) {
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุ authorization code');
        }

        // TODO: Implement CMU OAuth exchange
        // For now, return placeholder
        return Response::error('NOT_IMPLEMENTED', 'CMU OAuth ยังไม่พร้อมใช้งาน');
    }

    // POST /auth/logout
    private function logout(): array
    {
        // JWT is stateless, client should discard token
        return Response::success(null, 'ออกจากระบบเรียบร้อย');
    }

    // GET /auth/me
    private function me(): array
    {
        $user = AuthMiddleware::requireAuth();

        return Response::success([
            'id' => $user['sub'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]);
    }
}
