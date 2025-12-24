<?php
echo "Hello from E2E Test\n";
require_once "web/config/env.php";
echo "Loaded env.php\n";
echo "BASE_PATH: " . (defined('BASE_PATH') ? BASE_PATH : 'NOT DEFINED') . "\n";
require_once "web/config/database.php";
echo "Loaded database.php\n";
if (isset($pdo))
    echo "PDO is set\n";
?>