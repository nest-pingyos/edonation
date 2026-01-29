#!/usr/bin/env php
<?php
/**
 * Security Helper Script for eDonation
 * 
 * ใช้สำหรับ generate ค่าต่างๆ ที่จำเป็นสำหรับ security
 * 
 * Usage:
 *   php scripts/generate-secrets.php
 *   php scripts/generate-secrets.php jwt
 *   php scripts/generate-secrets.php check
 * 
 * @package eDonation
 * @version 1.0.0
 */

declare(strict_types=1);

// Color output helpers
function colorOutput(string $text, string $color): string
{
    $colors = [
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'reset' => "\033[0m"
    ];

    if (php_sapi_name() !== 'cli') {
        return $text;
    }

    return ($colors[$color] ?? '') . $text . $colors['reset'];
}

function printHeader(): void
{
    echo "\n";
    echo colorOutput("╔══════════════════════════════════════════════════════════╗\n", 'blue');
    echo colorOutput("║        eDonation Security Helper Script                  ║\n", 'blue');
    echo colorOutput("║        Version 1.0.0                                     ║\n", 'blue');
    echo colorOutput("╚══════════════════════════════════════════════════════════╝\n", 'blue');
    echo "\n";
}

function generateJwtSecret(): string
{
    return bin2hex(random_bytes(32)); // 64 hex characters
}

function generateCsrfSecret(): string
{
    return bin2hex(random_bytes(16)); // 32 hex characters
}

function generateStrongPassword(int $length = 20): string
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
    $password = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, $max)];
    }
    return $password;
}

function checkEnvSecurity(): void
{
    echo colorOutput("\n📋 Checking .env Security...\n\n", 'blue');

    $envPath = dirname(__DIR__) . '/.env';

    if (!file_exists($envPath)) {
        echo colorOutput("❌ .env file not found!\n", 'red');
        echo "   Please copy .env.production to .env and configure it.\n\n";
        return;
    }

    $envContent = file_get_contents($envPath);
    $lines = explode("\n", $envContent);
    $env = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0)
            continue;
        if (strpos($line, '=') === false)
            continue;

        [$key, $value] = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }

    $issues = [];
    $warnings = [];
    $passed = [];

    // Check APP_ENV
    if (($env['APP_ENV'] ?? 'development') === 'development') {
        $issues[] = "APP_ENV is set to 'development'. Change to 'production' for deployment.";
    } else {
        $passed[] = "APP_ENV is set to 'production'";
    }

    // Check APP_DEBUG
    if (($env['APP_DEBUG'] ?? 'true') === 'true') {
        $issues[] = "APP_DEBUG is enabled. Set to 'false' for production.";
    } else {
        $passed[] = "APP_DEBUG is disabled";
    }

    // Check JWT_SECRET
    $jwtSecret = $env['JWT_SECRET'] ?? '';
    $weakSecrets = [
        'your-dev-secret-key-for-edonation-2025',
        'secret',
        'password',
        'changeme',
        'GENERATE_NEW_SECRET_WITH_COMMAND_ABOVE'
    ];

    if (empty($jwtSecret)) {
        $issues[] = "JWT_SECRET is not set!";
    } elseif (strlen($jwtSecret) < 32) {
        $issues[] = "JWT_SECRET is too short (" . strlen($jwtSecret) . " chars). Minimum 32 characters required.";
    } elseif (in_array($jwtSecret, $weakSecrets, true)) {
        $issues[] = "JWT_SECRET is using a default/weak value!";
    } else {
        $passed[] = "JWT_SECRET is properly configured (" . strlen($jwtSecret) . " chars)";
    }

    // Check HTTPS
    $appDomain = $env['APP_DOMAIN'] ?? '';
    if (strpos($appDomain, 'https://') !== 0 && strpos($appDomain, 'localhost') === false) {
        $warnings[] = "APP_DOMAIN is not using HTTPS. For production, use https://";
    } elseif (strpos($appDomain, 'https://') === 0) {
        $passed[] = "APP_DOMAIN is using HTTPS";
    }

    // Check DB_PASS
    $dbPass = $env['DB_PASS'] ?? '';
    if (empty($dbPass) && ($env['APP_ENV'] ?? '') === 'production') {
        $issues[] = "DB_PASS is empty in production!";
    } elseif (!empty($dbPass) && strlen($dbPass) < 8) {
        $warnings[] = "DB_PASS is too short. Consider using a longer password.";
    } elseif (!empty($dbPass)) {
        $passed[] = "DB_PASS is configured";
    }

    // Check OAuth credentials
    if (strpos($env['CMU_OAUTH_CLIENT_ID'] ?? '', 'CHANGE_TO') !== false) {
        $issues[] = "CMU_OAUTH_CLIENT_ID is using placeholder value!";
    }

    // Print results
    echo colorOutput("✅ Passed Checks:\n", 'green');
    foreach ($passed as $item) {
        echo "   • {$item}\n";
    }

    if (!empty($warnings)) {
        echo colorOutput("\n⚠️  Warnings:\n", 'yellow');
        foreach ($warnings as $item) {
            echo "   • {$item}\n";
        }
    }

    if (!empty($issues)) {
        echo colorOutput("\n❌ Critical Issues:\n", 'red');
        foreach ($issues as $item) {
            echo "   • {$item}\n";
        }
        echo colorOutput("\n🚫 DO NOT DEPLOY until all critical issues are resolved!\n", 'red');
    } else {
        echo colorOutput("\n✅ All security checks passed! Ready for deployment.\n", 'green');
    }

    echo "\n";
}

function printGeneratedSecrets(): void
{
    echo colorOutput("🔐 Generated Security Values:\n\n", 'green');

    $jwtSecret = generateJwtSecret();
    echo colorOutput("JWT_SECRET:\n", 'yellow');
    echo "   {$jwtSecret}\n\n";

    echo colorOutput("Copy this to your .env file:\n", 'blue');
    echo "   JWT_SECRET={$jwtSecret}\n\n";

    echo colorOutput("Strong Password (for DB_PASS):\n", 'yellow');
    echo "   " . generateStrongPassword(24) . "\n\n";
}

function printUsage(): void
{
    echo colorOutput("Usage:\n\n", 'yellow');
    echo "   php scripts/generate-secrets.php           Generate new secrets\n";
    echo "   php scripts/generate-secrets.php check     Check .env security\n";
    echo "   php scripts/generate-secrets.php jwt       Generate JWT_SECRET only\n";
    echo "   php scripts/generate-secrets.php help      Show this help\n";
    echo "\n";
}

// Main execution
printHeader();

$command = $argv[1] ?? 'generate';

switch ($command) {
    case 'check':
        checkEnvSecurity();
        break;

    case 'jwt':
        echo colorOutput("JWT_SECRET=", 'yellow');
        echo generateJwtSecret() . "\n\n";
        break;

    case 'help':
    case '--help':
    case '-h':
        printUsage();
        break;

    case 'generate':
    default:
        printGeneratedSecrets();
        checkEnvSecurity();
        break;
}
