<?php

namespace App\Services;

use App\Mail\TestMail;
use App\Models\User;
use App\Models\User_verify_code;
use App\Traits\GeneralTrait;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PhoneVerificationService
{
    use GeneralTrait;

    public function verify($user_id, $email, $type)
    {
        /*
        Mail::to($email)->send(new TestMail());
        $code = Str::random(6);
        $user_verified = User_verify_code::create([
            'user_id' => $user_id,
            'code' => $code,
            'type' => $type,
        ]);

        return $user_verified;
        */
    }



}
