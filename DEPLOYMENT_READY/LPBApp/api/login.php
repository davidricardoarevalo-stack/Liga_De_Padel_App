<?php
/**
 * Login directo sin router - para probar redirecciones
 * Este archivo simula estar en /api/login directamente
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
    // Información de cómo llegó la petición
    $requestInfo = [
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
        'script' => $_SERVER['SCRIPT_NAME'] ?? '',
        'current_file' => __FILE__,
        'message' => 'Esta petición llegó DIRECTAMENTE a login.php (sin router)'
    ];
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'error' => 'Método no permitido. Use POST',
            'request_info' => $requestInfo,
            'note' => 'Este archivo simula /api/login pero está en /api/login.php'
        ], JSON_PRETTY_PRINT);
        exit();
    }
    
    // Cargar dependencias si existen
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
    }
    
    // Cargar .env si existe
    if (file_exists(__DIR__ . '/.env')) {
        $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }
    
    // Obtener datos POST
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Email y password requeridos',
            'request_info' => $requestInfo
        ], JSON_PRETTY_PRINT);
        exit();
    }
    
    // Login básico
    if ($email === 'app@app.com' && $password === '123') {
        $jwtSecret = $_ENV['JWT_SECRET'] ?? 'default_secret';
        $payload = [
            'sub' => 1,
            'email' => $email,
            'role' => 'Administrador',
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60)
        ];
        
        if (class_exists('Firebase\\JWT\\JWT')) {
            $token = \Firebase\JWT\JWT::encode($payload, $jwtSecret, 'HS256');
        } else {
            $token = 'jwt_not_available';
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Login exitoso desde login.php directo',
            'token' => $token,
            'user' => ['id' => 1, 'email' => $email, 'role' => 'Administrador'],
            'request_info' => $requestInfo,
            'note' => 'Esta respuesta viene de /api/login.php NO del router'
        ], JSON_PRETTY_PRINT);
    } else {
        http_response_code(401);
        echo json_encode([
            'error' => 'Credenciales inválidas',
            'request_info' => $requestInfo
        ], JSON_PRETTY_PRINT);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno',
        'message' => $e->getMessage(),
        'request_info' => $requestInfo ?? []
    ], JSON_PRETTY_PRINT);
}
?>