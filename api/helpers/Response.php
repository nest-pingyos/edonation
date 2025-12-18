<?php
/**
 * Response Helper
 */

class Response {
    public static function json($data, int $statusCode = 200): array {
        http_response_code($statusCode);
        return $data;
    }
    
    public static function success($data = null, string $message = null): array {
        $response = ['success' => true];
        if ($message) $response['message'] = $message;
        if ($data !== null) $response['data'] = $data;
        return $response;
    }
    
    public static function error(string $code, string $message, int $statusCode = 400): array {
        http_response_code($statusCode);
        return [
            'success' => false,
            'error' => ['code' => $code, 'message' => $message]
        ];
    }
    
    public static function notFound(string $message = 'ไม่พบข้อมูล'): array {
        return self::error('NOT_FOUND', $message, 404);
    }
    
    public static function unauthorized(string $message = 'กรุณาเข้าสู่ระบบ'): array {
        return self::error('UNAUTHORIZED', $message, 401);
    }
    
    public static function validation(array $errors): array {
        return self::error('VALIDATION_ERROR', 'ข้อมูลไม่ถูกต้อง', 400);
    }
}
