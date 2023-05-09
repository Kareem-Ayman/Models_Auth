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
            return $this->returnError("s001", "something went wrong !");
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

            return $phoneVerificationService->verify_msegat($request->phone);

        } catch (\Exception $e) {
            //return $this->returnData("data", $e);
            return $this->returnError("s001", "something went wrong !");
        }
    }

    public function phone_verify_done(PhoneVerifyRequest $request, PhoneVerificationService $phoneVerificationService)
    {
        try {


            return $phoneVerificationService->verify_msegat_done($request->code, $request->id);


        } catch (\Exception $e) {
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
        
        $configclear = Artisan::call('route:cache');
        echo "Config cleared<br>";


    }


}
