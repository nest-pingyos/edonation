<?php
/**
 * Admin Directory Redirect
 * Redirects /admin/ to /admin/src/
 */

// Get the current base path
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

// Redirect to src/ directory
header('Location: ' . $basePath . '/src/');
exit;
