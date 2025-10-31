<?php
namespace App\Models;

class Athlete extends Model {
    protected $table = 'athletes';
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'second_last_name',
        'email',
        'birthdate',
        'document_type',
        'document_number',
        'mobile_phone',
        'rep_legal_name',
        'rep_legal_email', 
        'rep_legal_phone',
        'club_id'
    ];
}
