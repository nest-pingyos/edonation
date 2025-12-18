<?php
/**
 * Donation Processing Service
 * This file is deprecated - use API instead
 * 
 * @deprecated Use /api/v1/webhooks/payment or /api/v1/donations instead
 */

// Redirect to API documentation
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'This endpoint is deprecated',
    'redirect' => '/appdev/edonation/api/v1',
    'note' => 'Use API endpoints instead of direct database access'
], JSON_UNESCAPED_UNICODE);
