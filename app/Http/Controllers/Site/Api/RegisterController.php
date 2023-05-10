<?php

namespace App\Http\Controllers\Site\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegisterRequest;
use App\Mail\TestMail;
use App\Models\User;
use App\Services\EmailVerificationService;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Propaganistas\LaravelPhone\PhoneNumber;

//use Mail;


class RegisterController extends Controller
{
    use GeneralTrait;

    public function register(UserRegisterRequest $request)
    {

        try{

            DB::beginTransaction();
            $phone = PhoneNumber::make($request->phone, PHONE_COUNTRIES);
            $phone->formatE164();
            $user_data = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'phone' => $phone,
            ])->with('codes')->latest()->first();

            $credentials = $request->only(["email", "password"]);
            $token = Auth::guard("user_api")->attempt($credentials);
            if (!$token) {
                return $this->returnErrorResponse("incorrect!",400);
            }

            $user_data-> api_token = $token;
            $user_data-> save();
            $user = Auth::guard('user_api')->user();
            $user->token = $token;
            $user_data = User::verefied_codes($user_data);

            DB::commit();

            return $this->returnData("user", $user_data);


        } catch (\Exception $e) {
            DB::rollback();
            return $this->returnData("data", dd($e));
            return $this->returnErrorResponse("something went wrong !", 400);
        }

    }

    public function test(Request $request)
    {
        return $this->returnData("data", $request);
    }


}
