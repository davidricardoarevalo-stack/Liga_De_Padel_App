<?php
namespace App\Models;

class Tournament extends Model {
    protected $table = 'tournaments';
    protected $fillable = ['name','start_date','end_date','club_id'];
    public $timestamps = false;
}
