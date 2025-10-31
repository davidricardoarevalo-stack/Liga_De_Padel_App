<?php
/**
 * Test específico para debuggear el router principal
 * Simula exactamente lo que hace test_interface.html
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
    // Información detallada de la petición
    $debug = [
        'timestamp' => date('Y-m-d H:i:s'),
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
        'script_name' => $_SERVER['SCRIPT_NAME'] ?? '',
        'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? '',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
        'current_dir' => __DIR__,
        'current_file' => __FILE__,
        'path_info' => $_SERVER['PATH_INFO'] ?? '',
        'query_string' => $_SERVER['QUERY_STRING'] ?? ''
    ];
    
    // ¿Este archivo está siendo ejecutado directamente o a través del router?
    $isDirectAccess = strpos($_SERVER['SCRIPT_NAME'] ?? '', 'debug_router.php') !== false;
    $isRouterAccess = strpos($_SERVER['SCRIPT_NAME'] ?? '', 'public/index.php') !== false;
    
    // Información de archivos críticos
    $fileStatus = [
        'htaccess_root' => [
            'exists' => file_exists(__DIR__ . '/../.htaccess'),
            'path' => __DIR__ . '/../.htaccess'
        ],
        'htaccess_api' => [
            'exists' => file_exists(__DIR__ . '/.htaccess'),
            'path' => __DIR__ . '/.htaccess'
        ],
        'public_index' => [
            'exists' => file_exists(__DIR__ . '/public/index.php'),
            'path' => __DIR__ . '/public/index.php'
        ],
        'bootstrap' => [
            'exists' => file_exists(__DIR__ . '/bootstrap/app.php'),
            'path' => __DIR__ . '/bootstrap/app.php'
        ]
    ];
    
    // Test de redirección manual
    $redirectionTest = [
        'current_url' => 'https://' . ($_SERVER['HTTP_HOST'] ?? 'unknown') . ($_SERVER['REQUEST_URI'] ?? ''),
        'expected_login_url' => 'https://' . ($_SERVER['HTTP_HOST'] ?? 'unknown') . '/LPBApp/api/login',
        'htaccess_should_redirect_to' => __DIR__ . '/public/index.php'
    ];
    
    // Intentar cargar el bootstrap para ver si hay errores
    $bootstrapTest = [
        'can_load_bootstrap' => false,
        'bootstrap_error' => null
    ];
    
    try {
        if (file_exists(__DIR__ . '/bootstrap/app.php')) {
            ob_start();
            $app = require __DIR__ . '/bootstrap/app.php';
            ob_end_clean();
            $bootstrapTest['can_load_bootstrap'] = true;
            $bootstrapTest['app_type'] = get_class($app ?? new stdClass());
        }
    } catch (Exception $e) {
        $bootstrapTest['bootstrap_error'] = $e->getMessage();
    }
    
    $response = [
        'status' => 'debug_info',
        'message' => 'Información detallada del router',
        'access_type' => [
            'direct_access' => $isDirectAccess,
            'router_access' => $isRouterAccess,
            'access_method' => $isDirectAccess ? 'DIRECT' : ($isRouterAccess ? 'ROUTER' : 'UNKNOWN')
        ],
        'debug_info' => $debug,
        'file_status' => $fileStatus,
        'redirection_test' => $redirectionTest,
        'bootstrap_test' => $bootstrapTest,
        'recommendations' => []
    ];
    
    // Generar recomendaciones
    if (!$fileStatus['htaccess_root']['exists']) {
        $response['recommendations'][] = '❌ Falta .htaccess principal en /LPBApp/';
    }
    
    if (!$fileStatus['public_index']['exists']) {
        $response['recommendations'][] = '❌ Falta public/index.php - entrada principal de la API';
    }
    
    if ($isDirectAccess) {
        $response['recommendations'][] = '✅ Este archivo se ejecuta directamente (sin router)';
    }
    
    if (!$bootstrapTest['can_load_bootstrap']) {
        $response['recommendations'][] = '❌ No se puede cargar bootstrap/app.php: ' . ($bootstrapTest['bootstrap_error'] ?? 'Error desconocido');
    }
    
    // Test crítico: ¿La URL /api/login debería llegar hasta aquí?
    if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/login') !== false && $isDirectAccess) {
        $response['recommendations'][] = '🔍 PROBLEMA: /api/login llegó a debug_router.php en lugar de public/index.php';
        $response['recommendations'][] = '🔧 SOLUCIÓN: Verificar reglas .htaccess en /LPBApp/.htaccess';
    }
    
    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error en debug del router',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?>