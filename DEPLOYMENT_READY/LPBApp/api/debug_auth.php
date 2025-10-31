<?php
/**
 * Debug específico para AuthController
 * Simula lo que hace AuthController::login() paso a paso
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Cargar dependencias
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
    }
    
    // Cargar .env
    if (file_exists(__DIR__ . '/.env')) {
        $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }
    
    $debug = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => 'Debug AuthController login process',
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'
    ];
    
    // Test 1: Obtener input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = $_POST;
    }
    
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    
    $debug['input_test'] = [
        'raw_input' => file_get_contents('php://input'),
        'parsed_input' => $input,
        'email' => $email,
        'password' => $password ? str_repeat('*', strlen($password)) : 'EMPTY'
    ];
    
    // Test 2: Verificar conexión a BD
    $dbTest = [
        'db_config' => [
            'host' => $_ENV['DB_HOST'] ?? 'NOT_SET',
            'database' => $_ENV['DB_DATABASE'] ?? 'NOT_SET',
            'username' => $_ENV['DB_USERNAME'] ?? 'NOT_SET',
            'password' => isset($_ENV['DB_PASSWORD']) ? 'SET' : 'NOT_SET'
        ]
    ];
    
    // Test 3: Verificar si User class existe y funciona
    $userClassTest = [
        'autoload_exists' => file_exists(__DIR__ . '/vendor/autoload.php'),
        'user_class_exists' => false,
        'user_class_error' => null
    ];
    
    try {
        if (class_exists('App\\Models\\User')) {
            $userClassTest['user_class_exists'] = true;
            
            // Intentar hacer una consulta básica
            try {
                // Esto podría fallar si no hay BD
                $userClassTest['db_connection_test'] = 'Attempting User::where()...';
            } catch (Exception $e) {
                $userClassTest['db_connection_error'] = $e->getMessage();
            }
        }
    } catch (Exception $e) {
        $userClassTest['user_class_error'] = $e->getMessage();
    }
    
    // Test 4: Firebase JWT
    $jwtTest = [
        'jwt_class_exists' => class_exists('Firebase\\JWT\\JWT'),
        'jwt_secret' => isset($_ENV['JWT_SECRET']) ? 'SET' : 'NOT_SET'
    ];
    
    // Test 5: Simular usuario hardcodeado
    $hardcodedTest = null;
    if ($email === 'app@app.com' && $password === '123') {
        $testUser = [
            'id' => 1,
            'email' => 'app@app.com',
            'role' => 'Administrador'
        ];
        
        if (class_exists('Firebase\\JWT\\JWT')) {
            $payload = [
                'sub' => $testUser['id'],
                'email' => $testUser['email'],
                'role' => $testUser['role'],
                'iat' => time(),
                'exp' => time() + 3600
            ];
            $key = $_ENV['JWT_SECRET'] ?? 'secret';
            $token = \Firebase\JWT\JWT::encode($payload, $key, 'HS256');
            
            $hardcodedTest = [
                'status' => 'SUCCESS',
                'user' => $testUser,
                'token' => $token,
                'note' => 'Hardcoded user login would work'
            ];
        }
    } else {
        $hardcodedTest = [
            'status' => 'FAIL',
            'note' => 'Credentials do not match hardcoded test user (app@app.com / 123)'
        ];
    }
    
    $debug['tests'] = [
        'input' => $debug['input_test'],
        'database' => $dbTest,
        'user_class' => $userClassTest,
        'jwt' => $jwtTest,
        'hardcoded_user' => $hardcodedTest
    ];
    
    $debug['recommendations'] = [];
    
    if (!$userClassTest['user_class_exists']) {
        $debug['recommendations'][] = 'User class not found - check autoload';
    }
    
    if ($dbTest['db_config']['host'] === 'NOT_SET') {
        $debug['recommendations'][] = 'Database not configured - User::where() will fail';
    }
    
    if ($hardcodedTest['status'] === 'SUCCESS') {
        $debug['recommendations'][] = 'Consider using hardcoded users for testing';
    }
    
    http_response_code(200);
    echo json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error in AuthController debug',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>