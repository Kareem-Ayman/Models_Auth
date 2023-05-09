<?php

namespace App\Services;

use App\Mail\TestMail;
use App\Models\User;
use App\Models\User_verify_code;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class EmailVerificationService
{
    use GeneralTrait;

    public function verify()
    {

        try {

            DB::beginTransaction();
            $user = JWTAuth::parseToken()->authenticate();
            $code = random_int(100000,999999);
            $pastVerify = User_verify_code::where('user_id', $user->id)->where('type', "email")->first();
            if($pastVerify->verified == 1){
                return $this->returnSuccessMessage("You are verified !");
            }
            if(isset($pastVerify)){
                $pastVerify->delete();
            }
            $user_verified = User_verify_code::create([
                'user_id' => $user->id,
                'code' => hash('sha256', $code),
                'verified' => 0,
                'type' => "email",
            ]);

            Mail::to($user->email)->send(new TestMail($user_verified->code));
            DB::commit();
            //return $this->returnData("data", $user_verified);
            return $this->returnSuccessMessage("Email sent successfully !");

        } catch (\Exception $e) {
            DB::rollback();
            return $this->returnData("dat", dd($e));
            return $this->returnError("s001", "something went wrong !");
        }

    }

    public function verify_done($_code)
    {
        try{
            DB::beginTransaction();

            $pastVerify = User_verify_code::where('code', $_code)->where('created_at', '>=', Carbon::now()->subMinutes(5))->first();
            if($pastVerify->verified == 1){
                return view("mails.mailVerified");
            }
            if(isset($pastVerify)){
                $pastVerify->verified = 1;
                $pastVerify->save();
                DB::commit();
                return view("mails.mailVerified");
            }else{
                return view("mails.error_page");
            }
        } catch (\Exception $e) {
            DB::rollback();
            //return $this->returnData("data", dd($e));
            return view("mails.error_page");
        }
    }




}
