<?php

namespace App\Http\Controllers\Site\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PhoneVerifyRequest;
use App\Http\Requests\ReceiveForgetRequest;
use App\Http\Requests\SendForgetRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\VerificationRequest;
use App\Mail\ForgetMail;
use App\Mail\TestMail;
use App\Models\User;
use App\Models\User_verify_code;
use App\Services\EmailVerificationService;
use App\Services\PhoneVerificationService;
use App\Services\SendEmailService;
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
use Propaganistas\LaravelPhone\PhoneNumber;

class ForgetPasswordController extends Controller
{
    use GeneralTrait;


    public function send_forget(SendForgetRequest $request, SendEmailService $sendEmailService, PhoneVerificationService $phoneVerificationService)
    {
        try{
            DB::beginTransaction();
            if($request->type == 1){
                $code = random_int(1000,9999);
                $cur_user = User::where('email', $request->value)->first();
                if(! $cur_user){
                    return $this->returnSuccessMessage("Email not found !");
                }
                $verify = User_verify_code::updateOrCreate(
                    ['user_id' => $cur_user->id, 'type' => "forget_email"],
                    ['code' => $code, 'verified' => 0]
                );
                $sendEmailService->send($request->value, new ForgetMail($code));
                DB::commit();
                return $this->returnSuccessMessage("Email sent successfully !");
            }else{

                $response_content = $phoneVerificationService->verify_msegat($request->value);

                if($response_content['code'] == 1){
                    $cur_user = User::where('phone', $request->value)->first();
                    if(! $cur_user){
                        return $this->returnSuccessMessage("Phone not found !");
                    }
                    $verify = User_verify_code::updateOrCreate(
                        ['user_id' => $cur_user->id, 'type' => "forget_phone"],
                        ['code' => $response_content['id'], 'verified' => 0]
                    );
                }

                DB::commit();
                return $this->returnData("verify_code", $response_content);

            }

        } catch (\Exception $e) {
            DB::rollback();
            //return $this->returnData("data", dd($e));
            return $this->returnError("s001", "something went wrong !");
        }
    }


    public function receive_forget(ReceiveForgetRequest $request, PhoneVerificationService $phoneVerificationService)
    {
        try{
            DB::beginTransaction();
            if($request->type == 1){
                $cur_user = User::where('email', $request->value)->first();
                if(! $cur_user){
                    return $this->returnSuccessMessage("Email not found !");
                }
                $pastVerify = User_verify_code::where('code', $request->code)->where('type', 'forget_email')->where('user_id', $cur_user->id)->first();
                if(isset($pastVerify)){
                    $pastVerify->verified = 1;
                    $pastVerify->save();
                    DB::commit();
                    return $this->returnSuccessMessage("You are verified !");
                }else{
                    return $this->returnSuccessMessage("Not found !");
                }

            }else{

                $cur_user = User::where('phone', $request->value)->first();
                if(! $cur_user){
                    return $this->returnSuccessMessage("Email not found !");
                }
                $pastVerify = User_verify_code::where('type', 'forget_phone')->where('user_id', $cur_user->id)->first();
                if(isset($pastVerify)){
                    if($pastVerify->verified == 1){
                        return $this->returnSuccessMessage("You are verified !");
                    }
                    $response_content = $phoneVerificationService->verify_msegat_done($request->code, $pastVerify->code);
                    if($response_content['code'] == 1){
                        $pastVerify->verified = 1;
                        $pastVerify->save();
                        DB::commit();
                        return $this->returnSuccessMessage("You are verified !");
                    }else{
                        return $this->returnSuccessMessage("something went wrong !");
                    }


                }else{
                    return $this->returnSuccessMessage("Not found !");
                }

            }

        } catch (\Exception $e) {
            DB::rollback();
            //return $this->returnData("data", dd($e));
            return $this->returnError("s001", "something went wrong !");
        }
    }



}
