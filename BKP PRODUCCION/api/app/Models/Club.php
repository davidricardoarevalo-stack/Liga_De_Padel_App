<?php
namespace App\Models;

class Club extends Model {
    protected $table = 'clubs';
    protected $fillable = [
        'name',
        'legal_representative',
        'status',
        'address',
        'phone',
        'contact_person',
        'director_tecnico',
        'fisioterapeuta',
        'asistente_tecnico',
        'delegado'
    ];
}
