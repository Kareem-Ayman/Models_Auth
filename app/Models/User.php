<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail as AuthMustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use Notifiable, AuthMustVerifyEmail;

    protected $fillable = [
        'name', 'email', 'password','phone','api_token'
    ];

    protected $hidden = [
        'password'
    ];

    public function codes() {
        return $this -> hasMany(User_verify_code::class,'user_id');
    }

    public static function verefied_codes($user)
    {
        if(count($user->codes) < 2){
            $user->email_verified = 0;
            $user->phone_verified = 0;
        }

        foreach ($user->codes as $key) {
            if($key->type == "email"){
                $user->email_verified = $key->verified;
            }elseif($key->type == "phone"){
                $user->phone_verified = $key->verified;
            }
        }

        unset($user->codes);
        return $user;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
