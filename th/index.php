<?php
// Thai language - Default
// Redirect to home page with Thai language
require_once dirname(__DIR__) . '/config/env.php';
$basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';
header('Location: ' . $basePath . '/home/');
exit;
