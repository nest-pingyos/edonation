<?php
/**
 * SCB Payment Service (API Wrapper)
 * 
 * ไฟล์นี้เป็น wrapper ที่โหลด shared SCBPaymentService
 * เพื่อให้ backwards compatible กับโค้ดที่มีอยู่
 */

require_once __DIR__ . '/../config/scb.php';
require_once dirname(__DIR__, 2) . '/shared/services/SCBPaymentService.php';
