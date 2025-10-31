<?php
namespace App\Http\Controllers;

use App\Models\Tournament;

class TournamentController extends Controller {
    public function index() { 
        $tournaments = Tournament::all();
        $this->jsonResponse($tournaments);
    }
    
    public function store(){
        $input = json_decode(file_get_contents('php://input'), true);
        $tournament = new Tournament();
        $tournament->name = $input['name'] ?? '';
        $tournament->start_date = $input['start_date'] ?? null;
        $tournament->end_date = $input['end_date'] ?? null;
        $tournament->club_id = $input['club_id'] ?? null;
        $tournament->save();
        $this->jsonResponse($tournament, 201);
    }
    
    public function update($id){
        $tournament = Tournament::find($id);
        if (!$tournament) $this->jsonResponse(['error'=>'Not found'], 404);
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['name'])) $tournament->name = $input['name'];
        if (isset($input['start_date'])) $tournament->start_date = $input['start_date'];
        if (isset($input['end_date'])) $tournament->end_date = $input['end_date'];
        if (isset($input['club_id'])) $tournament->club_id = $input['club_id'];
        $tournament->save();
        $this->jsonResponse($tournament);
    }
    
    public function destroy($id){
        $tournament = Tournament::find($id);
        if (!$tournament) $this->jsonResponse(['error'=>'Not found'], 404);
        $tournament->delete();
        $this->jsonResponse(['ok'=>true]);
    }
}
