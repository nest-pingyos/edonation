<?php
/**
 * Bootstrap - Load all required files
 */

session_start();

// Load environment
require_once __DIR__ . '/env.php';

// Load database
require_once __DIR__ . '/database.php';

// Load helpers
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

// Load middleware
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
