<?php
require_once __DIR__ . '/admin/src/config/config.php';
try {
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if database exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
    if ($stmt->fetchColumn()) {
        echo "DATABASE_EXISTS";
    } else {
        echo "DATABASE_MISSING";
    }
} catch (Exception $e) {
    echo "CONNECTION_ERROR: " . $e->getMessage();
}
