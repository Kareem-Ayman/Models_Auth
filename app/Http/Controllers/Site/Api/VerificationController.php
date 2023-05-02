<?php

namespace App\Http\Controllers\Site\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\VerificationRequest;
use App\Mail\TestMail;
use App\Models\User;
use App\Models\User_verify_code;
use App\Services\EmailVerificationService;
use App\Services\PhoneVerificationService;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

class VerificationController extends Controller
{
    use GeneralTrait;

    public function emailVerify(VerificationRequest $request, EmailVerificationService $emailVerificationService)
    {
        try {

            DB::beginTransaction();

            $user = Auth::guard('user_api')->user();
            if(isset($user)){
                $user_verified = $emailVerificationService->verify($user->id, $user->email, "email");
                $user->codes = $user_verified;
                DB::commit();
                return $this->returnData("data", $user);
            }else{
                return $this->returnError("s001", "You are not login !");
            }

        } catch (\Exception $e) {
            DB::rollback();
            //return $this->returnData("data", $e);
            return $this->returnError("s001", "something went wrong !");
        }
    }

    public function email_verify_done($_token, $_code)
    {
        //return $this->returnData("data", Auth::guard('user_api')->user());

        try {

            DB::beginTransaction();
            $user = Auth::guard('user_api')->user();
            $token = JWTAuth::setToken($_token)->checkOrFail();
            $pastVerify = User_verify_code::where('code', $_code)->where('created_at', '>=', Carbon::now()->subMinutes(30))->first();
            if($token && isset($pastVerify)){
                $pastVerify->verified = 1;
                $pastVerify->save();
                DB::commit();
                return view("mails.mailVerified");
            }else{
                return $this->returnError("s001", "something went wrong!");
            }

        } catch (\Exception $e) {
            DB::rollback();
            //return $this->returnData("data", $e);
            return $this->returnError("s001", "something went wrong !");
        }

    }

    public function phoneVerify(VerificationRequest $request, PhoneVerificationService $phoneVerificationService)
    {
        try {

            DB::beginTransaction();


        } catch (\Exception $e) {
            DB::rollback();
            //return $this->returnData("data", $e);
            return $this->returnError("s001", "something went wrong !");
        }
    }
}
