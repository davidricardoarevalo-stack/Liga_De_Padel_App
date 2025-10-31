<?php
/**
 * Test básico para verificar que la API PHP funciona correctamente
 * sin depender de base de datos o autenticación
 */

// Configurar headers JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Si es una petición OPTIONS (preflight), responder y terminar
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Test básico de PHP
    $phpVersion = phpversion();
    
    // Test de autoload (vendor)
    $vendorExists = file_exists(__DIR__ . '/vendor/autoload.php');
    
    // Test de .env
    $envExists = file_exists(__DIR__ . '/.env');
    
    // Test de Firebase JWT si vendor existe
    $jwtAvailable = false;
    if ($vendorExists) {
        require_once __DIR__ . '/vendor/autoload.php';
        $jwtAvailable = class_exists('Firebase\\JWT\\JWT');
    }
    
    // Test de bootstrap
    $bootstrapExists = file_exists(__DIR__ . '/bootstrap/app.php');
    
    // Información del servidor
    $serverInfo = [
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
        'current_dir' => __DIR__
    ];
    
    // Respuesta de éxito
    $response = [
        'status' => 'success',
        'message' => 'API funcionando correctamente',
        'php_version' => $phpVersion,
        'timestamp' => date('Y-m-d H:i:s'),
        'tests' => [
            'vendor_autoload' => $vendorExists ? '✅ OK' : '❌ FALTA',
            'env_file' => $envExists ? '✅ OK' : '❌ FALTA',
            'firebase_jwt' => $jwtAvailable ? '✅ OK' : '❌ FALTA',
            'bootstrap' => $bootstrapExists ? '✅ OK' : '❌ FALTA'
        ],
        'server_info' => $serverInfo
    ];
    
    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error en test API',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?>