<?php
require_once __DIR__ . '/admin/src/config/config.php';
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);

    $stmt = $pdo->query("SHOW TABLES LIKE 'edonation_admin_users'");
    if ($stmt->fetch()) {
        echo "TABLE_EXISTS";
    } else {
        echo "TABLE_MISSING";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
