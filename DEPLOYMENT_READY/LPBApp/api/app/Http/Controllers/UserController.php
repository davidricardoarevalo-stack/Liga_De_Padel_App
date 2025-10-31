<?php
namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller {
    public function index() { 
        $users = User::all();
        $this->jsonResponse($users);
    }
    
    public function store(){
        $input = json_decode(file_get_contents('php://input'), true);
        $user = new User();
        
        // Set all fields using __set() to ensure attributes array is updated
        $user->__set('first_name', $input['first_name'] ?? '');
        $user->__set('middle_name', $input['middle_name'] ?? '');
        $user->__set('last_name', $input['last_name'] ?? '');
        $user->__set('second_last_name', $input['second_last_name'] ?? '');
        $user->__set('email', $input['email'] ?? '');
        $user->__set('password', password_hash($input['password'] ?? '', PASSWORD_BCRYPT));
        $user->__set('role', $input['role'] ?? 'Deportista');
        $user->__set('club_id', $input['club_id'] ?? null);
        $user->__set('birthdate', $input['birthdate'] ?? null);
        $user->__set('status', $input['status'] ?? 'Activo');
        
        // Keep legacy name field for compatibility
        $nameParts = array_filter([
            $user->first_name,
            $user->middle_name,
            $user->last_name,
            $user->second_last_name
        ]);
        $user->__set('name', implode(' ', $nameParts));
        
        $user->save();
        $this->jsonResponse($user, 201);
    }
    
    public function update($id){
        $user = User::find($id);
        if (!$user) $this->jsonResponse(['error'=>'Not found'], 404);
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Update fields using __set() to ensure attributes array is updated
        if (isset($input['first_name'])) $user->__set('first_name', $input['first_name']);
        if (isset($input['middle_name'])) $user->__set('middle_name', $input['middle_name']);
        if (isset($input['last_name'])) $user->__set('last_name', $input['last_name']);
        if (isset($input['second_last_name'])) $user->__set('second_last_name', $input['second_last_name']);
        if (isset($input['email'])) $user->__set('email', $input['email']);
        if (isset($input['password']) && !empty($input['password'])) {
            $user->__set('password', password_hash($input['password'], PASSWORD_BCRYPT));
        }
        if (isset($input['role'])) $user->__set('role', $input['role']);
        if (isset($input['club_id'])) $user->__set('club_id', $input['club_id']);
        if (isset($input['birthdate'])) $user->__set('birthdate', $input['birthdate']);
        if (isset($input['status'])) $user->__set('status', $input['status']);
        
        // Update legacy name field for compatibility
        $nameParts = array_filter([
            $user->first_name,
            $user->middle_name,
            $user->last_name,
            $user->second_last_name
        ]);
        if (!empty($nameParts)) {
            $user->__set('name', implode(' ', $nameParts));
        }
        
        $user->save();
        $this->jsonResponse($user);
    }
    
    public function destroy($id){
        $user = User::find($id);
        if (!$user) $this->jsonResponse(['error'=>'Not found'], 404);
        $user->delete();
        $this->jsonResponse(['ok'=>true]);
    }
}
