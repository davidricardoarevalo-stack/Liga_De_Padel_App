<?php
namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController extends Controller {

    protected function makeToken($user) {
        $now = time();
        $exp = $now + (60 * 60); // 1 hour
        $payload = [
            'sub' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'iat' => $now,
            'exp' => $exp
        ];
        $key = $_ENV['JWT_SECRET'] ?? 'secret';
        return JWT::encode($payload, $key, 'HS256');
    }

    public function login() {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->jsonResponse(['error' => 'Invalid credentials'], 401);
        }
        
        if (!password_verify($password, $user->password)) {
            $this->jsonResponse(['error' => 'Invalid credentials'], 401);
        }
        
        // Verificar que el usuario esté activo
        if ($user->status && strtolower($user->status) === 'inactivo') {
            $this->jsonResponse(['error' => 'Usuario inactivo. Contacte al administrador.'], 403);
        }
        
        $token = $this->makeToken($user);
        $this->jsonResponse(['token' => $token, 'user' => $user]);
    }

    public function register() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (User::where('email', $input['email'])->exists()) {
            $this->jsonResponse(['error' => 'Email already exists'], 400);
        }
        
        $user = new User();
        $user->name = $input['name'] ?? null;
        $user->email = $input['email'];
        $user->password = password_hash($input['password'], PASSWORD_BCRYPT);
        $user->role = $input['role'] ?? 'User';
        $user->club_id = $input['club_id'] ?? null;
        $user->birthdate = $input['birthdate'] ?? null;
        $user->save();
        
        $token = $this->makeToken($user);
        $this->jsonResponse(['token' => $token, 'user' => $user]);
    }

    public function profile() {
        // Get the token from the request header
        $headers = getallheaders();
        $token = null;
        
        if (isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];
            if (strpos($auth, 'Bearer ') === 0) {
                $token = substr($auth, 7);
            }
        }
        
        if (!$token) {
            $this->jsonResponse(['error' => 'No token provided'], 401);
        }
        
        try {
            $key = $_ENV['JWT_SECRET'] ?? 'secret';
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            
            $user = User::find($decoded->sub);
            if (!$user) {
                $this->jsonResponse(['error' => 'User not found'], 401);
            }
            
            $this->jsonResponse(['user' => $user]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Invalid token'], 401);
        }
    }
    
    public function debug() {
        // Debug method to check session state
        $this->jsonResponse([
            'session_id' => session_id(),
            'session_data' => $_SESSION,
            'user_id' => $_SESSION['auth_user_id'] ?? null,
            'user_role' => $_SESSION['auth_user_role'] ?? null,
            'headers' => getallheaders()
        ]);
    }
    
    public function healthCheck() {
        // Health check for development
        $this->jsonResponse([
            'status' => 'OK',
            'message' => 'Liga de Padel API - Development Environment',
            'time' => date('Y-m-d H:i:s'),
            'environment' => 'development',
            'endpoints' => [
                'POST /login' => 'User authentication',
                'POST /register' => 'User registration', 
                'GET /profile' => 'Get user profile (requires auth)',
                'GET /athletes' => 'List athletes (requires auth)',
                'GET /clubs' => 'List clubs (requires auth)',
                'GET /tournaments' => 'List tournaments',
                'GET /users' => 'List users (admin only)'
            ]
        ]);
    }
}
