<?php

namespace App\Http\Controllers\Site\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PhoneVerifyRequest;
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
use Illuminate\Support\Str;
use GuzzleHttp\Client;

class VerificationController extends Controller
{
    use GeneralTrait;

    public function emailVerify(Request $request, EmailVerificationService $emailVerificationService)
    {
        try {

            return $emailVerificationService->verify();

        } catch (\Exception $e) {
            DB::rollback();
            //return $this->returnData("data", $e);
            return $this->returnErrorResponse("something went wrong !",400);
        }
    }


    public function email_verify_done(Request $request, $_code, EmailVerificationService $emailVerificationService)
    {

        try {

            return $emailVerificationService->verify_done($_code);

        } catch (\Exception $e) {
            //return $this->returnData("data", dd($e));
            return view("mails.error_page");
        }

    }

    public function phoneVerify(VerificationRequest $request, PhoneVerificationService $phoneVerificationService)
    {
        try {

            DB::beginTransaction();
            $user = JWTAuth::parseToken()->authenticate();
            $pastVerify = User_verify_code::where('user_id', $user->id)->where('type', "phone")->first();
            if(isset($pastVerify) && $pastVerify->verified == 1){
                return $this->returnSuccessMessage("You are verified !");
            }

            $response_content = $phoneVerificationService->verify_msegat($user->phone);

            if($response_content['code'] == 1){

                if(isset($pastVerify)){
                    $pastVerify->delete();
                }
                $user_verified = User_verify_code::create([
                    'user_id' => $user->id,
                    'code' => $response_content['id'],
                    'verified' => 0,
                    'type' => 'phone',
                ]);

            }

            DB::commit();
            return $this->returnData("verify_code", $response_content);

        } catch (\Exception $e) {
            //return $this->returnData("data", dd($e));
            return $this->returnErrorResponse("something went wrong !",400);
        }
    }

    public function phone_verify_done(PhoneVerifyRequest $request, PhoneVerificationService $phoneVerificationService)
    {
        try {

            DB::beginTransaction();
            $user = JWTAuth::parseToken()->authenticate();
            $pastVerify = User_verify_code::where('user_id', $user->id)->where('type', "phone")->first();
            if($pastVerify->verified == 1){
                return $this->returnSuccessMessage("You are verified !");
            }

            $response_content = $phoneVerificationService->verify_msegat_done($request->code, $request->id);

            if($response_content['message'] == "Success"){

                if(isset($pastVerify)){
                    $pastVerify->verified = 1;
                    $pastVerify->save();
                }

                DB::commit();
                return $this->returnSuccessMessage("Phone verified !");

            }else{
                return $this->returnErrorResponse("something went wrong_!",400);
            }


        } catch (\Exception $e) {
            //return $this->returnData("data", $e);
            return $this->returnErrorResponse("something went wrong !",400);
        }
    }


    public function cleareverything() {

        $clearcache = Artisan::call('cache:clear');
        echo "Cache cleared<br>";

        $clearview = Artisan::call('view:clear');
        echo "View cleared<br>";

        $clearconfig = Artisan::call('config:cache');
        echo "Config cached<br>";

        $configclear = Artisan::call('config:clear');
        echo "Config cleared<br>";

        $configclear = Artisan::call('route:cache');
        echo "route cleared<br>";


    }


}
