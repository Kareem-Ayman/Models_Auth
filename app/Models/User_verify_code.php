<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User_verify_code extends Model
{
    protected $table = 'user_verify_codes';

    protected $fillable = ['user_id', 'code','type'];

    protected $hidden = [
        'password'
    ];

    public function user() {
        return $this -> belongsTo(User::class,'attribute_id');
    }

}
