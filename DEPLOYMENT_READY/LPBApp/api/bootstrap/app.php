<?php

// Simple bootstrap without Lumen dependency
session_start();
require_once __DIR__.'/../vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__.'/../.env')) {
    $lines = file(__DIR__.'/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// Simple router implementation
class SimpleRouter {
    private $routes = [];
    
    public function get($path, $handler) { $this->routes['GET'][$path] = $handler; }
    public function post($path, $handler) { $this->routes['POST'][$path] = $handler; }
    public function put($path, $handler) { $this->routes['PUT'][$path] = $handler; }
    public function delete($path, $handler) { $this->routes['DELETE'][$path] = $handler; }
    
    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $fullPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove /LPBApp/api prefix to get the actual route
        $path = $fullPath;
        if (strpos($path, '/LPBApp/api') === 0) {
            $path = substr($path, strlen('/LPBApp/api'));
        }
        
        // Ensure path starts with /
        if (empty($path) || $path[0] !== '/') {
            $path = '/' . $path;
        }
        
        // Debug info (remove in production)
        if (isset($_GET['debug'])) {
            echo json_encode([
                'method' => $method,
                'full_path' => $fullPath,
                'processed_path' => $path,
                'available_routes' => $this->routes,
                'timestamp' => date('Y-m-d H:i:s')
            ], JSON_PRETTY_PRINT);
            return;
        }
        
        // Handle exact matches first
        if (isset($this->routes[$method][$path])) {
            $this->handleRoute($this->routes[$method][$path]);
            return;
        }
        
        // Handle parametrized routes (e.g., /athletes/{id})
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            if (preg_match('#^' . preg_replace('/\{[^}]+\}/', '([^/]+)', $route) . '$#', $path, $matches)) {
                array_shift($matches); // Remove full match
                $this->handleRoute($handler, $matches);
                return;
            }
        }
        
        http_response_code(404);
        echo json_encode(['error' => 'Not found', 'debug' => ['method' => $method, 'path' => $path, 'full_path' => $fullPath]]);
    }
    
    private function handleRoute($handler, $params = []) {
        if (is_array($handler)) {
            list($controller, $method) = explode('@', $handler['uses']);
            $middleware = $handler['middleware'] ?? null;
            
            // Simple middleware handling
            if ($middleware) {
                $middlewares = explode(',', $middleware);
                foreach ($middlewares as $m) {
                    if (strpos($m, ':') !== false) {
                        list($name, $param) = explode(':', $m, 2);
                        $this->runMiddleware($name, $param);
                    } else {
                        $this->runMiddleware($m);
                    }
                }
            }
            
            $controllerClass = "App\\Http\\Controllers\\$controller";
            $instance = new $controllerClass();
            call_user_func_array([$instance, $method], $params);
        } else {
            list($controller, $method) = explode('@', $handler);
            $controllerClass = "App\\Http\\Controllers\\$controller";
            $instance = new $controllerClass();
            call_user_func_array([$instance, $method], $params);
        }
    }
    
    private function runMiddleware($name, $param = null) {
        $request = new StdClass();
        $request->headers = getallheaders() ?: [];
        
        if ($name === 'auth') {
            $auth = $request->headers['Authorization'] ?? $request->headers['authorization'] ?? null;
            if (!$auth || !preg_match('/Bearer\s+(.*)$/i', $auth, $m)) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            
            $token = $m[1];
            try {
                $key = $_ENV['JWT_SECRET'] ?? 'secret';
                $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($key, 'HS256'));
                
                // Store complete user info for controllers to use
                $_SESSION['auth_user_id'] = $decoded->sub;
                $_SESSION['auth_user_role'] = $decoded->role ?? 'User';
                $_SESSION['auth_user_email'] = $decoded->email ?? null;
                
                // Debug: Let's also store the full decoded token for debugging
                $_SESSION['decoded_token'] = (array)$decoded;
                
            } catch (Exception $e) {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid token']);
                exit;
            }
        } elseif ($name === 'role' && $param) {
            $userRole = $_SESSION['auth_user_role'] ?? null;
            $allowedRoles = array_map('trim', explode(',', $param));
            if (!$userRole || !in_array($userRole, $allowedRoles)) {
                http_response_code(403);
                echo json_encode(['error' => 'Forbidden - insufficient role']);
                exit;
            }
        }
    }
}

// Simple app container
$app = new StdClass();
$app->router = new SimpleRouter();

// Load routes
require __DIR__.'/../routes/web.php';

return $app;
