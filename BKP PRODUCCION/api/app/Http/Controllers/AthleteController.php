<?php
namespace App\Http\Controllers;

use App\Models\Athlete;
use App\Models\User;

class AthleteController extends Controller {
    public function index() { 
        // Get current user from session (set by auth middleware)
        $userId = $_SESSION['auth_user_id'] ?? null;
        $user = $userId ? User::find($userId) : null;
        
        if ($user && $user->role === 'Club') {
            // Club users can only see athletes from their own club
            if ($user->club_id) {
                $athletes = Athlete::where('club_id', $user->club_id);
            } else {
                $athletes = [];
            }
        } else {
            // Administrador users can see all athletes
            $athletes = Athlete::all();
        }
        
        $this->jsonResponse($athletes);
    }
    
    public function store(){
        // Get current user from session (set by auth middleware)
        $userId = $_SESSION['auth_user_id'] ?? null;
        $user = $userId ? User::find($userId) : null;
        
        // Simple input handling for now
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate legal representative data for minors (under 18)
        if (isset($input['birthdate'])) {
            $birthDate = new \DateTime($input['birthdate']);
            $today = new \DateTime();
            $age = $today->diff($birthDate)->y;
            
            if ($age < 18) {
                if (empty($input['rep_legal_name']) || empty($input['rep_legal_email']) || empty($input['rep_legal_phone'])) {
                    $this->jsonResponse(['error' => 'Deportista menor de 18 años requiere datos de representante legal: nombre, email y celular'], 400);
                }
            }
        }
        
        // Club users can only create athletes for their own club
        if ($user && $user->role === 'Club') {
            if (!$user->club_id) {
                $this->jsonResponse(['error' => 'Club user must be assigned to a club'], 403);
            }
            if (isset($input['club_id']) && $input['club_id'] != $user->club_id) {
                $this->jsonResponse(['error' => 'You can only create athletes for your own club'], 403);
            }
            // Force club_id to be the user's club
            $input['club_id'] = $user->club_id;
        }
        
        $athlete = new Athlete();
        
        // Handle name field - can be combined or separate
        if (isset($input['name'])) {
            $nameParts = explode(' ', $input['name'], 2);
            $athlete->__set('first_name', $nameParts[0]);
            $athlete->__set('last_name', $nameParts[1] ?? '');
        } else {
            $athlete->__set('first_name', $input['first_name'] ?? '');
            $athlete->__set('middle_name', $input['middle_name'] ?? '');
            $athlete->__set('last_name', $input['last_name'] ?? '');
            $athlete->__set('second_last_name', $input['second_last_name'] ?? '');
        }
        
        $athlete->__set('email', $input['email'] ?? null);
        $athlete->__set('club_id', $input['club_id'] ?? null);
        $athlete->__set('birthdate', $input['birthdate'] ?? null);
        $athlete->__set('document_type', $input['document_type'] ?? null);
        $athlete->__set('document_number', $input['document_number'] ?? null);
        $athlete->__set('mobile_phone', $input['mobile_phone'] ?? null);
        $athlete->__set('rep_legal_name', $input['rep_legal_name'] ?? null);
        $athlete->__set('rep_legal_email', $input['rep_legal_email'] ?? null);
        $athlete->__set('rep_legal_phone', $input['rep_legal_phone'] ?? null);
        $athlete->save();
        $this->jsonResponse($athlete, 201);
    }
    
    public function update($id){
        // Get current user from session (set by auth middleware)
        $userId = $_SESSION['auth_user_id'] ?? null;
        $user = $userId ? User::find($userId) : null;
        
        $athlete = Athlete::find($id);
        if (!$athlete) $this->jsonResponse(['error'=>'Not found'], 404);
        
        // Club users can only edit athletes from their own club
        if ($user && $user->role === 'Club') {
            if (!$user->club_id || $athlete->club_id != $user->club_id) {
                $this->jsonResponse(['error' => 'You can only edit athletes from your own club'], 403);
            }
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate legal representative data for minors (under 18)
        $birthdate = $input['birthdate'] ?? $athlete->birthdate;
        if ($birthdate) {
            $birthDate = new \DateTime($birthdate);
            $today = new \DateTime();
            $age = $today->diff($birthDate)->y;
            
            if ($age < 18) {
                $rep_name = $input['rep_legal_name'] ?? $athlete->rep_legal_name;
                $rep_email = $input['rep_legal_email'] ?? $athlete->rep_legal_email;
                $rep_phone = $input['rep_legal_phone'] ?? $athlete->rep_legal_phone;
                
                if (empty($rep_name) || empty($rep_email) || empty($rep_phone)) {
                    $this->jsonResponse(['error' => 'Deportista menor de 18 años requiere datos de representante legal: nombre, email y celular'], 400);
                }
            }
        }
        
        // Club users cannot change the club_id of an athlete
        if ($user && $user->role === 'Club') {
            if (isset($input['club_id']) && $input['club_id'] != $user->club_id) {
                $this->jsonResponse(['error' => 'You cannot transfer athletes to other clubs'], 403);
            }
            // Remove club_id from input to prevent changes
            unset($input['club_id']);
        }
        
        // Handle name field - can be combined or separate
        if (isset($input['name'])) {
            $nameParts = explode(' ', $input['name'], 2);
            $athlete->__set('first_name', $nameParts[0]);
            $athlete->__set('last_name', $nameParts[1] ?? '');
        } else {
            if (isset($input['first_name'])) $athlete->__set('first_name', $input['first_name']);
            if (isset($input['middle_name'])) $athlete->__set('middle_name', $input['middle_name']);
            if (isset($input['last_name'])) $athlete->__set('last_name', $input['last_name']);
            if (isset($input['second_last_name'])) $athlete->__set('second_last_name', $input['second_last_name']);
        }
        
        if (isset($input['email'])) $athlete->__set('email', $input['email']);
        if (isset($input['club_id'])) $athlete->__set('club_id', $input['club_id']);
        if (isset($input['birthdate'])) $athlete->__set('birthdate', $input['birthdate']);
        if (isset($input['document_type'])) $athlete->__set('document_type', $input['document_type']);
        if (isset($input['document_number'])) $athlete->__set('document_number', $input['document_number']);
        if (isset($input['mobile_phone'])) $athlete->__set('mobile_phone', $input['mobile_phone']);
        if (isset($input['rep_legal_name'])) $athlete->__set('rep_legal_name', $input['rep_legal_name']);
        if (isset($input['rep_legal_email'])) $athlete->__set('rep_legal_email', $input['rep_legal_email']);
        if (isset($input['rep_legal_phone'])) $athlete->__set('rep_legal_phone', $input['rep_legal_phone']);
        $athlete->save();
        
        // Reload the athlete from database to get updated values
        $updatedAthlete = Athlete::find($id);
        $this->jsonResponse($updatedAthlete);
    }
    
    public function destroy($id){
        // Get current user from session (set by auth middleware)
        $userId = $_SESSION['auth_user_id'] ?? null;
        $user = $userId ? User::find($userId) : null;
        
        $athlete = Athlete::find($id);
        if (!$athlete) $this->jsonResponse(['error'=>'Not found'], 404);
        
        // Club users can only delete athletes from their own club
        if ($user && $user->role === 'Club') {
            if (!$user->club_id || $athlete->club_id != $user->club_id) {
                $this->jsonResponse(['error' => 'You can only delete athletes from your own club'], 403);
            }
        }
        
        $athlete->delete();
        $this->jsonResponse(['ok'=>true]);
    }
}
