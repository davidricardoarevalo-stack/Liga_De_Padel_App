<?php
/**
 * Test de acceso directo a archivos
 * Verifica que este archivo se ejecute sin ser interceptado por .htaccess
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $response = [
        'status' => 'success',
        'message' => '✅ htaccess_test.php ejecutándose DIRECTAMENTE',
        'timestamp' => date('Y-m-d H:i:s'),
        'file_info' => [
            'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'current_file' => __FILE__
        ],
        'test_results' => [
            'direct_file_access' => '✅ FUNCIONANDO',
            'htaccess_allows_direct_php' => '✅ SÍ',
            'note' => 'Si ves este mensaje, el .htaccess permite acceso directo a archivos .php'
        ]
    ];
    
    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error en test htaccess',
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>