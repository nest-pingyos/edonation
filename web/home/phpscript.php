<?php
/**
 * Donation Processing Service
 * This file is deprecated - use API instead
 * 
 * @deprecated Use /api/v1/webhooks/payment or /api/v1/donations instead
 */

// Load config for BASE_PATH
require_once dirname(__DIR__) . '/config/env.php';
$basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';

// Redirect to API documentation
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'This endpoint is deprecated',
    'redirect' => $basePath . '/api/v1',
    'note' => 'Use API endpoints instead of direct database access'
], JSON_UNESCAPED_UNICODE);
