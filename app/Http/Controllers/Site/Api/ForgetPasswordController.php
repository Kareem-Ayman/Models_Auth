<?php

namespace App\Http\Controllers\Site\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PhoneVerifyRequest;
use App\Http\Requests\ReceiveForgetRequest;
use App\Http\Requests\ResetPassForgetRequest;
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
                if(! isset($cur_user) || ($cur_user->register_type == "google" || $cur_user->register_type == "apple")){
                    return $this->returnErrorResponse("Not for Social account !",400);
                }
                $pastVerify = User_verify_code::where('user_id', $cur_user->id)->where('type', "email")->first();
                if(! isset($pastVerify) || $pastVerify->verified != 1){
                    return $this->returnErrorResponse("You are not verified !",400);
                }
                if(! $cur_user){
                    return $this->returnErrorResponse("Email not found !",400);
                }
                $verify = User_verify_code::updateOrCreate(
                    ['user_id' => $cur_user->id, 'type' => "forget_email"],
                    ['code' => $code, 'verified' => 0]
                );
                $sendEmailService->send($request->value, new ForgetMail($code));
                DB::commit();
                return $this->returnSuccessMessage("Email sent successfully !");
            }else{

                $phone = PhoneNumber::make($request->value, PHONE_COUNTRIES);
                $phone->formatE164();
                $cur_user = User::where('phone', $phone)->first();
                if(! $cur_user){
                    return $this->returnErrorResponse("Phone not found !",400);
                }
                $pastVerify = User_verify_code::where('user_id', $cur_user->id)->where('type', "phone")->first();
                if(! isset($pastVerify) || $pastVerify->verified != 1){
                    return $this->returnErrorResponse("You are not verified !",400);
                }

                $response_content = $phoneVerificationService->verify_msegat($request->value);
                if($response_content['code'] == 1){
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
            return $this->returnErrorResponse("something went wrong !", 400);
        }
    }


    public function receive_forget(ReceiveForgetRequest $request, PhoneVerificationService $phoneVerificationService)
    {
        try{
            DB::beginTransaction();
            if($request->type == 1){
                $cur_user = User::where('email', $request->value)->first();
                if(! $cur_user){
                    return $this->returnErrorResponse("Email not found !",400);
                }
                $pastVerify = User_verify_code::where('code', $request->code)->where('type', 'forget_email')->where('user_id', $cur_user->id)->first();
                if(isset($pastVerify)){
                    $pastVerify->verified = 1;
                    $pastVerify->save();
                    DB::commit();
                    return $this->returnSuccessMessage("You are verified !");
                }else{
                    return $this->returnErrorResponse("Not found !",400);
                }

            }else{

                $cur_user = User::where('phone', $request->value)->first();
                if(! $cur_user){
                    return $this->returnErrorResponse("Phone not found !",400);
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
                        return $this->returnErrorResponse("something went wrong !",400);
                    }


                }else{
                    return $this->returnErrorResponse("Not found !",400);
                }

            }

        } catch (\Exception $e) {
            DB::rollback();
            //return $this->returnData("data", dd($e));
            return $this->returnErrorResponse("something went wrong !", 400);
        }
    }


    public function reset_pass_forget(ResetPassForgetRequest $request){
        try{
            DB::beginTransaction();
            if($request->type == 1){
                $cur_user = User::where('email', $request->value)->first();
                if(! $cur_user){
                    return $this->returnErrorResponse("Email not found !",400);
                }
                $pastVerify = User_verify_code::where('type', 'forget_email')->where('user_id', $cur_user->id)->where('updated_at', '>=', Carbon::now()->subMinutes(3))->first();
                if(isset($pastVerify)){
                    $cur_user->password = bcrypt($request->password);
                    $cur_user->save();
                    DB::commit();
                    return $this->returnSuccessMessage("Password Reset Successfully !");
                }else{
                    return $this->returnErrorResponse("Session Expired !",400);
                }

            }else{

                $cur_user = User::where('phone', $request->value)->first();
                if(! $cur_user){
                    return $this->returnErrorResponse("Phone not found !",400);
                }
                $pastVerify = User_verify_code::where('type', 'forget_phone')->where('user_id', $cur_user->id)->where('updated_at', '>=', Carbon::now()->subMinutes(3))->first();
                if(isset($pastVerify)){
                    $cur_user->password = bcrypt($request->password);
                    $cur_user->save();
                    DB::commit();
                    return $this->returnSuccessMessage("Password Reset Successfully !");
                }else{
                    return $this->returnErrorResponse("Session Expired !",400);
                }

            }

        } catch (\Exception $e) {
            DB::rollback();
            //return $this->returnData("data", dd($e));
            return $this->returnErrorResponse("something went wrong !",400);
        }
    }



}
