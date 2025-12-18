<?php
/**
 * SCB Open Banking API Configuration
 * 
 * ⚠️ สำหรับ Production ควรย้ายไปเก็บใน Environment Variables
 */

// SCB API Credentials
define('SCB_API_KEY', 'l78c80bdfcdcea46d2a5cce5fd43eb01e6');
define('SCB_API_SECRET', 'c99c50331e454450924db56c3348bfa6');
define('SCB_BILLER_ID', '099400258783792');

// SCB API Endpoints (Production)
define('SCB_API_BASE_URL', 'https://api.scb.co.th/partners');
define('SCB_OAUTH_URL', SCB_API_BASE_URL . '/v1/oauth/token');
define('SCB_QR_CREATE_URL', SCB_API_BASE_URL . '/v2/payment/qrcode/create');

// QR Settings
define('SCB_QR_TYPE', 'PPCS');           // PromptPay Credit Transfer
define('SCB_PP_TYPE', 'BILLERID');       // Biller ID Type
define('SCB_QR_EXPIRY_MINUTES', 30);     // QR หมดอายุใน 30 นาที
define('SCB_QR_NUMBER_OF_TIMES', 1);     // Single-use QR

// Reference Settings
define('SCB_REF3', 'NUR');               // รหัสหน่วยงาน (คณะพยาบาล)
