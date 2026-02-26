<?php
/**
 * Auth Controller - eDonation API
 * 
 * Endpoints:
 * POST   /auth/login              - เข้าสู่ระบบด้วย email/password
 * GET    /auth/oauth/login        - รับ CMU OAuth URL สำหรับ redirect
 * POST   /auth/oauth/callback     - แลก authorization code เป็น JWT token
 * GET    /auth/oauth/redirect     - Redirect callback (สำหรับ browser flow)
 * POST   /auth/logout             - ออกจากระบบ
 * GET    /auth/me                 - ข้อมูลผู้ใช้ปัจจุบัน
 * 
 * @version 2.1
 */

class AuthController
{
    const VERSION = '2.1';
    private PDO $pdo;

    // CMU OAuth Configuration
    private string $clientId;
    private string $clientSecret;
    private string $tenantId;
    private string $scope;
    private string $authUrl;
    private string $tokenUrl;
    private string $basicInfoUrl;

    public function __construct()
    {
        $this->pdo = Database::getInstance();

        // Load OAuth config from environment (secure)
        $this->clientId = getenv('CMU_OAUTH_CLIENT_ID') ?: '9ff50902-00e4-482f-b3d0-f0d59d31c999';
        $this->clientSecret = getenv('CMU_OAUTH_CLIENT_SECRET') ?: '';
        $this->tenantId = getenv('CMU_OAUTH_TENANT_ID') ?: 'cf81f1df-de59-4c29-91da-a2dfd04aa751';
        $this->scope = 'api://cmu/Mis.Account.Read.Me.Basicinfo';
        $this->authUrl = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/authorize";
        $this->tokenUrl = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";
        $this->basicInfoUrl = 'https://api.cmu.ac.th/mis/cmuaccount/prod/v3/me/basicinfo';

        // Security check: ensure client_secret is configured
        if (empty($this->clientSecret)) {
            error_log("WARNING: CMU_OAUTH_CLIENT_SECRET not configured in environment");
        }
    }

    public function handle(string $method, ?string $id, ?string $action): array
    {
        $endpoint = $id;
        if ($id === 'oauth' && $action) {
            $endpoint = "oauth/{$action}";
        }

        switch ($endpoint) {
            case 'login':
                return $this->login();

            case 'oauth/login':
                return $this->oauthLogin();

            case 'session-token':
                return $this->getSessionToken();

            case 'oauth/callback':
                return $this->oauthCallback();

            case 'oauth/redirect':
                return $this->oauthRedirect();

            case 'logout':
                return $this->logout();

            case 'me':
                return $this->me();

            default:
                return Response::error('NOT_FOUND', 'Endpoint not found', 404);
        }
    }

    /**
     * POST /auth/login
     * Traditional email/password login
     */
    private function login(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('METHOD_NOT_ALLOWED', 'Use POST method', 405);
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $v = new Validator($data);
        $v->required('username')->required('password');

        if (!$v->passes()) {
            return Response::validation($v->errors());
        }

        // Dev account for testing
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
                "SELECT id, name, email, password_hash, role FROM edonation_admin_users 
                 WHERE email = :email AND status = 'active' LIMIT 1"
            );
            $stmt->execute([':email' => $data['username']]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($data['password'], $user['password_hash'])) {
                return Response::error('INVALID_CREDENTIALS', 'อีเมลหรือรหัสผ่านไม่ถูกต้อง', 401);
            }

            // Update last login
            $this->updateLastLogin($user['id']);

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
            error_log("Login error: " . $e->getMessage());
            return Response::error('INVALID_CREDENTIALS', 'อีเมลหรือรหัสผ่านไม่ถูกต้อง', 401);
        }
    }

    /**
     * GET /auth/oauth/login
     * Returns CMU OAuth authorization URL
     */
    private function oauthLogin(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('METHOD_NOT_ALLOWED', 'Use GET method', 405);
        }

        $redirectUri = $this->getRedirectUri();
        $state = bin2hex(random_bytes(16));

        // Store state in session for verification
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['oauth_state'] = $state;

        $params = [
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'response_mode' => 'query',
            'scope' => $this->scope,
            'state' => $state
        ];

        $authUrl = $this->authUrl . '?' . http_build_query($params);

        return Response::success([
            'auth_url' => $authUrl,
            'redirect_uri' => $redirectUri,
            'state' => $state
        ], 'Use auth_url to redirect user for login');
    }

    /**
     * POST /auth/oauth/callback
     * Exchange authorization code for JWT token
     */
    private function oauthCallback(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('METHOD_NOT_ALLOWED', 'Use POST method', 405);
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        if (empty($data['code'])) {
            return Response::error('VALIDATION_ERROR', 'กรุณาระบุ authorization code');
        }

        $code = $data['code'];
        $passedRedirectUri = $data['redirect_uri'] ?? null;

        // Use environment-based redirect URI
        $redirectUri = $this->getRedirectUri();

        // 1. Exchange code for access token with Microsoft
        $tokenResult = $this->exchangeCodeForToken($code, $redirectUri);

        if (!$tokenResult['success']) {
            error_log("Exchange Token Failure: " . $tokenResult['error']);
            return Response::error('OAUTH_ERROR', "Microsoft Error: " . $tokenResult['error'], 401);
        }

        $accessToken = $tokenResult['access_token'];

        // 2. Get user info from CMU API
        $userInfo = $this->getCmuUserInfo($accessToken);

        if (!$userInfo || !isset($userInfo['cmuitaccount'])) {
            error_log("CMU User Info Error: " . json_encode($userInfo));
            return Response::error('OAUTH_ERROR', 'ไม่สามารถดึงข้อมูลผู้ใช้จาก CMU IT Account ได้', 401);
        }

        $email = $userInfo['cmuitaccount'];

        // 3. Check if user is authorized in our database
        $authorizedUser = $this->getAdminUser($email);

        if (!$authorizedUser) {
            error_log("Access Denied: User $email not found in edonation_admin_users");
            return Response::error('UNAUTHORIZED', "บัญชี {$email} ไม่มีสิทธิ์เข้าใช้งานระบบ Admin กรุณาติดต่อผู้ดูแลระบบ", 403);
        }

        if ($authorizedUser['status'] !== 'active') {
            return Response::error('ACCOUNT_DISABLED', 'บัญชีของคุณถูกระงับการใช้งาน', 403);
        }

        // 4. Update last login
        $this->updateLastLogin($authorizedUser['id']);

        // 5. Generate JWT Token
        $tokenData = [
            'id' => $authorizedUser['id'],
            'name' => ($userInfo['firstname_TH'] ?? $authorizedUser['name']) . ' ' . ($userInfo['lastname_TH'] ?? ''),
            'email' => $email,
            'role' => $authorizedUser['role']
        ];

        $jwt = AuthMiddleware::generateToken($tokenData);

        return Response::success([
            'access_token' => $jwt,
            'token_type' => 'Bearer',
            'expires_in' => JWT_EXPIRE,
            'user' => $tokenData
        ], 'เข้าสู่ระบบสำเร็จ');
    }

    /**
     * GET /auth/oauth/redirect
     * Browser redirect callback - redirects to frontend with token
     */
    private function oauthRedirect(): array
    {
        // This endpoint handles the browser redirect from Microsoft
        // It should exchange the code and redirect to frontend with token

        if (!isset($_GET['code'])) {
            if (isset($_GET['error'])) {
                $error = htmlspecialchars($_GET['error_description'] ?? $_GET['error']);
                return Response::error('OAUTH_ERROR', $error, 401);
            }
            return Response::error('VALIDATION_ERROR', 'Missing authorization code');
        }

        $code = $_GET['code'];
        $redirectUri = $this->getRedirectUri();

        // Exchange code for access token
        $tokenResult = $this->exchangeCodeForToken($code, $redirectUri);

        if (!$tokenResult['success']) {
            // Redirect to login with error
            $errorUrl = $this->getAdminBaseUrl() . '/login.php?error=' . urlencode($tokenResult['error']);
            header("Location: $errorUrl");
            exit;
        }

        $accessToken = $tokenResult['access_token'];

        // Get user info
        $userInfo = $this->getCmuUserInfo($accessToken);

        if (!$userInfo || !isset($userInfo['cmuitaccount'])) {
            $errorUrl = $this->getAdminBaseUrl() . '/login.php?error=' . urlencode('ไม่สามารถดึงข้อมูลผู้ใช้ได้');
            header("Location: $errorUrl");
            exit;
        }

        $email = $userInfo['cmuitaccount'];
        $authorizedUser = $this->getAdminUser($email);

        if (!$authorizedUser || $authorizedUser['status'] !== 'active') {
            $errorUrl = $this->getAdminBaseUrl() . '/login.php?error=' . urlencode("คุณไม่มีสิทธิ์เข้าใช้งานระบบ ({$email})");
            header("Location: $errorUrl");
            exit;
        }

        // Update last login
        $this->updateLastLogin($authorizedUser['id']);

        // Create session for browser-based login
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['backend_user'] = [
            'id' => $authorizedUser['id'],
            'email' => $email,
            'name_th' => trim(($userInfo['firstname_TH'] ?? '') . ' ' . ($userInfo['lastname_TH'] ?? '')),
            'name_en' => trim(($userInfo['firstname_EN'] ?? '') . ' ' . ($userInfo['lastname_EN'] ?? '')),
            'organization' => $userInfo['organization_name_TH'] ?? '',
            'role' => $authorizedUser['role'],
            'logged_in' => true,
            'login_time' => date('Y-m-d H:i:s')
        ];

        // Redirect to dashboard
        $dashboardUrl = $this->getAdminBaseUrl() . '/index.php';
        header("Location: $dashboardUrl");
        exit;

        // Return success (won't reach here due to redirect)
        return Response::success(null, 'Login successful');
    }

    /**
     * POST /auth/logout
     */
    private function logout(): array
    {
        // Clear session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        session_destroy();

        return Response::success(null, 'ออกจากระบบเรียบร้อย');
    }

    /**
     * GET /auth/session-token
     * Generate JWT token from PHP session (Bridge for Admin Panel)
     */
    private function getSessionToken(): array
    {
        $user = $_SESSION['backend_user'] ?? $_SESSION['user'] ?? null;

        if (!$user || (isset($user['logged_in']) && $user['logged_in'] === false)) {
            return Response::error('UNAUTHORIZED', 'Session not found', 401);
        }

        // Bridge session data to JWT format
        $tokenData = [
            'id' => $user['id'],
            'name' => $user['name'] ?? $user['name_th'] ?? 'Admin',
            'email' => $user['email'],
            'role' => $user['role']
        ];

        $token = AuthMiddleware::generateToken($tokenData);

        return Response::success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => JWT_EXPIRE,
            'user' => $tokenData
        ], 'Token generated from session');
    }

    /**
     * GET /auth/me
     */
    private function me(): array
    {
        $user = AuthMiddleware::requireAuth();

        return Response::success([
            'id' => $user['sub'] ?? $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]);
    }

    // ========================================
    // Private Helper Methods
    // ========================================

    private function getRedirectUri(): string
    {
        // Use APP_URL if defined, otherwise fall back to dynamic detection
        if (defined('APP_URL')) {
            return APP_URL . '/admin/src/auth-callback.php';
        }

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';

        return $protocol . '://' . $host . $basePath . '/admin/src/auth-callback.php';
    }

    private function getAdminBaseUrl(): string
    {
        if (defined('ADMIN_URL')) {
            return ADMIN_URL . '/src';
        }

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';

        return $protocol . '://' . $host . $basePath . '/admin/src';
    }

    private function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init($this->tokenUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("CMU OAuth Token cURL Error: $error");
            return ['success' => false, 'error' => 'ไม่สามารถเชื่อมต่อกับ Microsoft ได้'];
        }

        $json = json_decode($response, true);

        if ($httpCode !== 200 || !isset($json['access_token'])) {
            $errorMsg = $json['error_description'] ?? $json['error'] ?? 'Token exchange failed';
            error_log("CMU OAuth Token Error: $httpCode - $errorMsg");
            return ['success' => false, 'error' => $errorMsg];
        }

        return ['success' => true, 'access_token' => $json['access_token']];
    }

    private function getCmuUserInfo(string $accessToken): ?array
    {
        $ch = curl_init($this->basicInfoUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Authorization: Bearer $accessToken"],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("CMU API cURL Error: $error");
            return null;
        }

        if ($httpCode !== 200) {
            error_log("CMU API HTTP Error: $httpCode - $response");
            return null;
        }

        return json_decode($response, true);
    }

    private function getAdminUser(string $email): ?array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT id, email, name, role, status FROM edonation_admin_users WHERE email = :email"
            );
            $stmt->execute([':email' => $email]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Get admin user error: " . $e->getMessage());
            return null;
        }
    }

    private function updateLastLogin(int $userId): void
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE edonation_admin_users SET last_login = NOW() WHERE id = :id"
            );
            $stmt->execute([':id' => $userId]);
        } catch (PDOException $e) {
            error_log("Update last login error: " . $e->getMessage());
        }
    }
}
