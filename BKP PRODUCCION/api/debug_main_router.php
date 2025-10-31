<?php

// Set UTF-8 encoding for proper character handling
header('Content-Type: application/json; charset=utf-8');
mb_internal_encoding('UTF-8');

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit();
}

try {
    // Debug information about the request
    $debugInfo = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => 'DEBUG: Router Principal - Información de petición',
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
        'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'UNKNOWN',
        'path_info' => $_SERVER['PATH_INFO'] ?? 'UNKNOWN',
        'query_string' => $_SERVER['QUERY_STRING'] ?? '',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'UNKNOWN',
        'current_dir' => __DIR__,
        'current_file' => __FILE__
    ];
    
    // Check if bootstrap exists and load it
    $bootstrapPath = __DIR__.'/../bootstrap/app.php';
    $debugInfo['bootstrap_path'] = $bootstrapPath;
    $debugInfo['bootstrap_exists'] = file_exists($bootstrapPath);
    
    if (!file_exists($bootstrapPath)) {
        throw new Exception('Bootstrap file not found: ' . $bootstrapPath);
    }
    
    // Load the bootstrap
    $app = require $bootstrapPath;
    $debugInfo['app_loaded'] = true;
    $debugInfo['app_type'] = get_class($app ?? new stdClass());
    
    // Check if router exists
    $debugInfo['router_exists'] = isset($app->router);
    $debugInfo['router_type'] = isset($app->router) ? get_class($app->router) : 'NOT_FOUND';
    
    // Parse the URL to get the path that the router should handle
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $parsedUrl = parse_url($requestUri);
    $path = $parsedUrl['path'] ?? '';
    
    // Remove /LPBApp/api from the path to get the route
    $route = str_replace('/LPBApp/api', '', $path);
    if (empty($route)) {
        $route = '/';
    }
    
    $debugInfo['url_parsing'] = [
        'full_request_uri' => $requestUri,
        'parsed_path' => $path,
        'route_for_router' => $route,
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET'
    ];
    
    // Try to manually check if the route exists in the router
    if (isset($app->router)) {
        // Get available routes (this is a hack to inspect the router)
        $reflection = new ReflectionObject($app->router);
        if ($reflection->hasProperty('routes')) {
            $routesProperty = $reflection->getProperty('routes');
            $routesProperty->setAccessible(true);
            $routes = $routesProperty->getValue($app->router);
            $debugInfo['available_routes'] = $routes;
        }
    }
    
    // Check for specific files
    $debugInfo['file_checks'] = [
        'routes_web_php' => file_exists(__DIR__ . '/../routes/web.php'),
        'auth_controller' => file_exists(__DIR__ . '/../app/Http/Controllers/AuthController.php'),
        'env_file' => file_exists(__DIR__ . '/../.env')
    ];
    
    // If this is a POST to /login, try to simulate what should happen
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === '/login') {
        $debugInfo['login_simulation'] = [
            'should_match_route' => 'POST /login -> AuthController@login',
            'input_data' => json_decode(file_get_contents('php://input'), true),
            'note' => 'Esta petición debería ser manejada por AuthController@login'
        ];
    }
    
    http_response_code(200);
    echo json_encode($debugInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error en debug del router principal',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?>