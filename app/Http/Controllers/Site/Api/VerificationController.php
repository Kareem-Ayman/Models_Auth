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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;
use GuzzleHttp\Client;

class VerificationController extends Controller
{
    use GeneralTrait;

    public function emailVerify(Request $request, EmailVerificationService $emailVerificationService)
    {
        try {

            DB::beginTransaction();

            $token = $request->header('token');
            $request->headers->set('Authorization', 'Bearer '.$token, true);
            $user = JWTAuth::parseToken()->authenticate();
            if(isset($user)){
                $user_verified = $emailVerificationService->verify($user->id, $user->email, "email", $token);
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


    public function email_verify_done(Request $request, $_token, $_code)
    {

        try {

            DB::beginTransaction();
            $user = Auth::guard('user_api')->user();
            $token = JWTAuth::setToken($_token)->checkOrFail();
            try {

                $request->headers->set('Authorization', 'Bearer '.$_token, true);
                $token = JWTAuth::parseToken()->authenticate();
                $pastVerify = User_verify_code::where('code', $_code)->where('created_at', '>=', Carbon::now()->subMinutes(30))->first();
                if($token && isset($pastVerify)){
                    $pastVerify->verified = 1;
                    $pastVerify->save();
                    DB::commit();
                    return view("mails.mailVerified");
                }else{
                    return $this->returnError("s001", "something went wrong!");
                }

            } catch (TokenExpiredException  $e) {
                //$new_token = JWTAuth::refresh();
                return $this->returnError("401", "Token expired");
            }

        } catch (\Exception $e) {
            DB::rollback();
            //return $this->returnData("data", dd($e));
            return $this->returnError("s001", "something went wrong !");
        }

    }

    public function phoneVerify(VerificationRequest $request, PhoneVerificationService $phoneVerificationService)
    {
        try {

            DB::beginTransaction();
            $data = $phoneVerificationService->verify_msegat($request->phone);
            return $this->returnData("verify_code", $data);


        } catch (\Exception $e) {
            DB::rollback();
            //return $this->returnData("data", $e);
            return $this->returnError("s001", "something went wrong !");
        }
    }

    public function phone_verify_done(VerificationRequest $request, PhoneVerificationService $phoneVerificationService)
    {
        try {

            DB::beginTransaction();


        } catch (\Exception $e) {
            DB::rollback();
            //return $this->returnData("data", $e);
            return $this->returnError("s001", "something went wrong !");
        }
    }



    public function cleareverything() {

        $clearcache = Artisan::call('cache:clear');
        echo "Cache cleared<br>";

        $clearview = Artisan::call('view:clear');
        echo "View cleared<br>";

        $clearconfig = Artisan::call('config:cache');
        echo "Config cleared<br>";

        $configclear = Artisan::call('config:clear');
        echo "Config cleared<br>";

    }


}
