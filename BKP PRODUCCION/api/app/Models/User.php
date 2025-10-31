<?php
namespace App\Models;

class User extends Model {
    protected $table = 'users';
    protected $fillable = ['name','email','password','role','club_id','birthdate','status','first_name','middle_name','last_name','second_last_name'];
}
