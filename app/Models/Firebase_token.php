<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Firebase_token extends Model
{
    protected $table = 'firebase_tokens';

    protected $fillable = ['user_id', 'firebase_token'];


    public function user() {
        return $this -> belongsTo(User::class,'user_id');
    }

}
