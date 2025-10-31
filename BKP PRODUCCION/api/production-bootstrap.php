<?php
/*
|--------------------------------------------------------------------------
| Production Bootstrap Verification
|--------------------------------------------------------------------------
|
| This file verifies that all components needed for production are working
| correctly without Docker dependencies.
|
*/

// Check PHP version
echo "PHP Version: " . PHP_VERSION . "\n";

// Check if vendor/autoload.php exists and loads
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✅ Composer autoload loaded successfully\n";
} else {
    echo "❌ vendor/autoload.php not found\n";
    exit(1);
}

// Check Firebase JWT
try {
    $key = "test_key";
    $payload = [
        'iss' => 'test',
        'aud' => 'test',
        'iat' => time(),
        'exp' => time() + 3600
    ];
    
    $jwt = \Firebase\JWT\JWT::encode($payload, $key, 'HS256');
    $decoded = \Firebase\JWT\JWT::decode($jwt, new \Firebase\JWT\Key($key, 'HS256'));
    echo "✅ Firebase JWT library working correctly\n";
} catch (Exception $e) {
    echo "❌ Firebase JWT error: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if .env file loads
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $envVars = 0;
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
            $envVars++;
        }
    }
    echo "✅ .env file found with {$envVars} variables\n";
} else {
    echo "❌ .env file not found\n";
}

// Check bootstrap
if (file_exists(__DIR__ . '/bootstrap/app.php')) {
    echo "✅ Bootstrap file exists\n";
} else {
    echo "❌ Bootstrap file missing\n";
}

echo "\n🎯 Production readiness check complete!\n";