<?php
namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {
    public function handle($request, Closure $next)
    {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['error'=>'Unauthorized']);
            exit;
        }
        
        if (preg_match('/Bearer\s+(.*)$/i', $auth, $m)) {
            $token = $m[1];
            try {
                $key = $_ENV['JWT_SECRET'] ?? 'secret';
                $decoded = JWT::decode($token, new Key($key, 'HS256'));
                $userId = $decoded->sub ?? null;
                if (!$userId) {
                    http_response_code(401);
                    echo json_encode(['error'=>'Invalid token payload']);
                    exit;
                }
                $user = User::find($userId);
                if (!$user) {
                    http_response_code(401);
                    echo json_encode(['error'=>'User not found']);
                    exit;
                }
                // Store user in request for controllers to access
                $_REQUEST['auth_user'] = $user;
                return $next($request);
            } catch (\Firebase\JWT\ExpiredException $e) {
                http_response_code(401);
                echo json_encode(['error'=>'Token expired']);
                exit;
            } catch (\Exception $e) {
                http_response_code(401);
                echo json_encode(['error'=>'Invalid token','message'=>$e->getMessage()]);
                exit;
            }
        }
        http_response_code(401);
        echo json_encode(['error'=>'Unauthorized']);
        exit;
    }
}
