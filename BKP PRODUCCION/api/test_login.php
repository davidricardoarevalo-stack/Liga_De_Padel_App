<?php
/**
 * Test directo de login para verificar autenticación
 * Permite probar el login sin pasar por el router completo
 */

// Configurar headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Solo aceptar POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido. Use POST']);
        exit();
    }
    
    // Cargar dependencias
    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        throw new Exception('vendor/autoload.php no encontrado');
    }
    require_once __DIR__ . '/vendor/autoload.php';
    
    // Cargar variables de entorno
    if (file_exists(__DIR__ . '/.env')) {
        $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
                putenv(trim($key) . '=' . trim($value));
            }
        }
    }
    
    // Obtener datos del POST
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email y password son requeridos']);
        exit();
    }
    
    // Simulación de login básico (para testing)
    // En producción real, esto verificaría contra la base de datos
    $testUsers = [
        'app@app.com' => [
            'password' => '123',
            'role' => 'Administrador',
            'id' => 1,
            'name' => 'Admin User'
        ],
        'test@test.com' => [
            'password' => 'test',
            'role' => 'User',
            'id' => 2,
            'name' => 'Test User'
        ]
    ];
    
    // Verificar credenciales
    if (!isset($testUsers[$email]) || $testUsers[$email]['password'] !== $password) {
        http_response_code(401);
        echo json_encode(['error' => 'Credenciales inválidas']);
        exit();
    }
    
    $user = $testUsers[$email];
    
    // Generar JWT
    $jwtSecret = $_ENV['JWT_SECRET'] ?? 'default_secret';
    $payload = [
        'sub' => $user['id'],
        'email' => $email,
        'role' => $user['role'],
        'name' => $user['name'],
        'iat' => time(),
        'exp' => time() + (24 * 60 * 60) // 24 horas
    ];
    
    $token = \Firebase\JWT\JWT::encode($payload, $jwtSecret, 'HS256');
    
    // Respuesta exitosa
    $response = [
        'success' => true,
        'message' => 'Login exitoso',
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'email' => $email,
            'role' => $user['role'],
            'name' => $user['name']
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?>