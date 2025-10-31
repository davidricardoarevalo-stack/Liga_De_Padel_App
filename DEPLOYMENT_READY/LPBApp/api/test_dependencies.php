<?php
/**
 * Test completo de dependencias para producción
 * Verifica todo lo necesario para que funcione sin Docker
 */

// Configurar headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $tests = [];
    $allPassed = true;
    
    // Test 1: PHP Version
    $phpVersion = phpversion();
    $tests['php_version'] = [
        'status' => version_compare($phpVersion, '8.0', '>=') ? '✅ OK' : '❌ FAIL',
        'value' => $phpVersion,
        'required' => '>= 8.0'
    ];
    
    // Test 2: .env file
    $envExists = file_exists(__DIR__ . '/.env');
    $tests['env_file'] = [
        'status' => $envExists ? '✅ OK' : '❌ MISSING',
        'path' => __DIR__ . '/.env',
        'exists' => $envExists
    ];
    
    // Test 3: vendor/ directory
    $vendorExists = file_exists(__DIR__ . '/vendor/autoload.php');
    $tests['vendor_autoload'] = [
        'status' => $vendorExists ? '✅ OK' : '❌ MISSING',
        'path' => __DIR__ . '/vendor/autoload.php',
        'exists' => $vendorExists
    ];
    
    // Test 4: Firebase JWT
    $jwtAvailable = false;
    if ($vendorExists) {
        require_once __DIR__ . '/vendor/autoload.php';
        $jwtAvailable = class_exists('Firebase\\JWT\\JWT');
    }
    $tests['firebase_jwt'] = [
        'status' => $jwtAvailable ? '✅ OK' : '❌ MISSING',
        'class_exists' => $jwtAvailable
    ];
    
    // Test 5: Bootstrap
    $bootstrapExists = file_exists(__DIR__ . '/bootstrap/app.php');
    $tests['bootstrap'] = [
        'status' => $bootstrapExists ? '✅ OK' : '❌ MISSING',
        'path' => __DIR__ . '/bootstrap/app.php',
        'exists' => $bootstrapExists
    ];
    
    // Test 6: Variables de entorno (si .env existe)
    $envVars = [];
    if ($envExists) {
        $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
                list($key, $value) = explode('=', $line, 2);
                $envVars[trim($key)] = trim($value);
            }
        }
    }
    
    $requiredEnvVars = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'APP_KEY', 'JWT_SECRET'];
    $tests['env_variables'] = [];
    
    foreach ($requiredEnvVars as $var) {
        $exists = isset($envVars[$var]) && !empty($envVars[$var]);
        $tests['env_variables'][$var] = [
            'status' => $exists ? '✅ OK' : '❌ MISSING',
            'exists' => $exists,
            'value' => $exists ? (strlen($envVars[$var]) > 20 ? substr($envVars[$var], 0, 20) . '...' : $envVars[$var]) : null
        ];
        if (!$exists) $allPassed = false;
    }
    
    // Test 7: Controllers
    $controllerPath = __DIR__ . '/app/Http/Controllers';
    $controllersExist = is_dir($controllerPath);
    $tests['controllers'] = [
        'status' => $controllersExist ? '✅ OK' : '❌ MISSING',
        'path' => $controllerPath,
        'exists' => $controllersExist
    ];
    
    // Test 8: Routes
    $routesExist = file_exists(__DIR__ . '/routes/web.php');
    $tests['routes'] = [
        'status' => $routesExist ? '✅ OK' : '❌ MISSING',
        'path' => __DIR__ . '/routes/web.php',
        'exists' => $routesExist
    ];
    
    // Verificar si pasaron todos los tests críticos
    $criticalTests = ['php_version', 'env_file', 'vendor_autoload', 'firebase_jwt', 'bootstrap'];
    foreach ($criticalTests as $test) {
        if (strpos($tests[$test]['status'], '❌') !== false) {
            $allPassed = false;
        }
    }
    
    // Respuesta final
    $response = [
        'status' => $allPassed ? 'success' : 'warning',
        'message' => $allPassed ? 'Todas las dependencias están listas' : 'Faltan algunas dependencias',
        'ready_for_production' => $allPassed,
        'timestamp' => date('Y-m-d H:i:s'),
        'tests' => $tests,
        'summary' => [
            'total_tests' => count($tests),
            'passed' => array_sum(array_map(function($test) {
                return is_array($test) && isset($test['status']) ? 
                    (strpos($test['status'], '✅') !== false ? 1 : 0) : 0;
            }, $tests)),
            'environment' => 'production',
            'docker' => false
        ]
    ];
    
    http_response_code($allPassed ? 200 : 206); // 206 = Partial Content
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error verificando dependencias',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?>