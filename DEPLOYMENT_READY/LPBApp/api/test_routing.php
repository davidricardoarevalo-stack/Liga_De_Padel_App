<?php
/**
 * Diagnóstico de redirecciones y routing
 * Ayuda a identificar problemas con .htaccess y rutas
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
    // Información de la petición
    $requestInfo = [
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
        'script_name' => $_SERVER['SCRIPT_NAME'] ?? '',
        'path_info' => $_SERVER['PATH_INFO'] ?? '',
        'query_string' => $_SERVER['QUERY_STRING'] ?? '',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
        'current_dir' => __DIR__,
        'current_file' => __FILE__
    ];
    
    // Información del servidor
    $serverInfo = [
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? '',
        'php_version' => phpversion(),
        'mod_rewrite' => function_exists('apache_get_modules') ? 
            (in_array('mod_rewrite', apache_get_modules()) ? 'enabled' : 'disabled') : 'unknown'
    ];
    
    // Verificar archivos importantes
    $fileChecks = [
        'htaccess_main' => file_exists(__DIR__ . '/../.htaccess'),
        'htaccess_api' => file_exists(__DIR__ . '/.htaccess'),
        'index_public' => file_exists(__DIR__ . '/public/index.php'),
        'bootstrap' => file_exists(__DIR__ . '/bootstrap/app.php'),
        'routes' => file_exists(__DIR__ . '/routes/web.php'),
        'env_file' => file_exists(__DIR__ . '/.env')
    ];
    
    // Verificar si estamos en el punto de entrada correcto
    $isCorrectEntry = strpos($_SERVER['SCRIPT_NAME'] ?? '', 'public/index.php') !== false;
    
    // Test de routing básico
    $routingTest = [
        'current_script' => basename($_SERVER['SCRIPT_NAME'] ?? ''),
        'should_be_routed' => !$isCorrectEntry && !strpos($_SERVER['REQUEST_URI'] ?? '', 'test_'),
        'router_active' => $isCorrectEntry
    ];
    
    $response = [
        'status' => 'success',
        'message' => 'Diagnóstico de routing completado',
        'timestamp' => date('Y-m-d H:i:s'),
        'request_info' => $requestInfo,
        'server_info' => $serverInfo,
        'file_checks' => $fileChecks,
        'routing_test' => $routingTest,
        'recommendations' => []
    ];
    
    // Agregar recomendaciones basadas en el diagnóstico
    if (!$fileChecks['htaccess_main']) {
        $response['recommendations'][] = 'Falta .htaccess principal en /LPBApp/';
    }
    
    if (!$fileChecks['index_public']) {
        $response['recommendations'][] = 'Falta public/index.php en la API';
    }
    
    if (!$isCorrectEntry && strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
        $response['recommendations'][] = 'Las peticiones API deberían ser redirigidas a public/index.php';
    }
    
    if (empty($response['recommendations'])) {
        $response['recommendations'][] = 'Configuración parece correcta. Si hay errores 404, revisar reglas de .htaccess';
    }
    
    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error en diagnóstico de routing',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?>