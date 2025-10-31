<?php
namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\User;

class ClubController extends Controller {
    public function index() { 
        // Get current user from session (set by auth middleware)
        $userId = $_SESSION['auth_user_id'] ?? null;
        $user = $userId ? User::find($userId) : null;
        
        if ($user && $user->role === 'Club') {
            // Club users can only see their own club
            if ($user->club_id) {
                $club = Club::find($user->club_id);
                $clubs = $club ? [$club] : [];
            } else {
                $clubs = [];
            }
        } else {
            // Administrador users can see all clubs
            $clubs = Club::all();
        }
        
        $this->jsonResponse($clubs);
    }
    
    public function store(){
        // Get current user from session (set by auth middleware)
        $userId = $_SESSION['auth_user_id'] ?? null;
        $user = $userId ? User::find($userId) : null;
        
        // Club users cannot create new clubs
        if ($user && $user->role === 'Club') {
            $this->jsonResponse(['error' => 'Club users cannot create new clubs'], 403);
        }
        
        // Simple input handling for now
        $input = json_decode(file_get_contents('php://input'), true);
        $club = new Club();
        $club->name = $input['name'] ?? '';
        $club->legal_representative = $input['legal_representative'] ?? null;
        $club->status = $input['status'] ?? 'activo';
        $club->address = $input['address'] ?? null;
        $club->phone = $input['phone'] ?? null;
        $club->contact_person = $input['contact_person'] ?? null;
        $club->director_tecnico = $input['director_tecnico'] ?? null;
        $club->delegado = $input['delegado'] ?? null;
        $club->fisioterapeuta = $input['fisioterapeuta'] ?? null;
        $club->asistente_tecnico = $input['asistente_tecnico'] ?? null;
        $club->save();
        $this->jsonResponse($club, 201);
    }
    
    public function update($id){
        // Get current user from session (set by auth middleware)
        $userId = $_SESSION['auth_user_id'] ?? null;
        $user = $userId ? User::find($userId) : null;
        
        $club = Club::find($id);
        if (!$club) $this->jsonResponse(['error'=>'Not found'], 404);
        
        // Club users can only edit their own club
        if ($user && $user->role === 'Club') {
            if (!$user->club_id || $user->club_id != $id) {
                $this->jsonResponse(['error' => 'You can only edit your own club'], 403);
            }
        }
        
        $rawInput = file_get_contents('php://input');
        // Convert to UTF-8 if needed
        if (!mb_check_encoding($rawInput, 'UTF-8')) {
            $rawInput = mb_convert_encoding($rawInput, 'UTF-8');
        }
        $input = json_decode($rawInput, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error: " . json_last_error_msg());
            $this->jsonResponse(['error' => 'Invalid JSON'], 400);
        }
        
        // Update all fields using the __set method by using a method call
        foreach (['name', 'legal_representative', 'status', 'address', 'phone', 'contact_person', 'director_tecnico', 'delegado', 'fisioterapeuta', 'asistente_tecnico'] as $field) {
            if (isset($input[$field])) {
                // Force using the __set method by calling it directly
                $club->__set($field, $input[$field]);
            }
        }
        
        $club->save();
        $this->jsonResponse($club);
    }
    
    public function destroy($id){
        // Get current user from session (set by auth middleware)
        $userId = $_SESSION['auth_user_id'] ?? null;
        $user = $userId ? User::find($userId) : null;
        
        // Club users cannot delete clubs
        if ($user && $user->role === 'Club') {
            $this->jsonResponse(['error' => 'Club users cannot delete clubs'], 403);
        }
        
        $club = Club::find($id);
        if (!$club) $this->jsonResponse(['error'=>'Not found'], 404);
        $club->delete();
        $this->jsonResponse(['ok'=>true]);
    }
}
