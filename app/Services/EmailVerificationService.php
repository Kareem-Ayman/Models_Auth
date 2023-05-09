<?php

namespace App\Services;

use App\Mail\TestMail;
use App\Models\User;
use App\Models\User_verify_code;
use App\Traits\GeneralTrait;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailVerificationService
{
    use GeneralTrait;

    public function verify($user_id, $email, $type, $token)
    {

        try {

            DB::beginTransaction();
            $code = random_int(100000,999999);
            $pastVerify = User_verify_code::where('user_id', $user_id)->where('type', $type)->first();
            if(isset($pastVerify)){
                $pastVerify->delete();
            }
            $user_verified = User_verify_code::create([
                'user_id' => $user_id,
                'code' => $code,
                'verified' => 0,
                'type' => $type,
            ]);
            Mail::to($email)->send(new TestMail($user_verified, $token));
            DB::commit();
            return $user_verified;

        } catch (\Exception $e) {
            DB::rollback();
            //return $this->returnData("data", $e);
            return $this->returnError("s001", "something went wrong !");
        }
    }



}
