<?php
// Test MemberController summary
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/api/config/env.php';
require_once __DIR__ . '/api/config/database.php';
require_once __DIR__ . '/api/helpers/Response.php';
require_once __DIR__ . '/api/controllers/MemberController.php';

try {
    $controller = new MemberController();
    $result = $controller->handle('GET', '3509901156611', 'summary');
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString();
}
