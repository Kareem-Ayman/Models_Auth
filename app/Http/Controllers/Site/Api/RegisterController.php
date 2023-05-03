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

//use Mail;


class RegisterController extends Controller
{
    use GeneralTrait;
/*
    public $emailVerifServ;

    public function __construct(EmailVerificationService $emailVerificationService){
        $this->emailVerifServ = $emailVerificationService;
    }
    public function emailVerify(EmailVerificationService $emailVerificationService)
    {
        $user = Auth::guard('user_api')->user();
        $emailVerificationService->verify($user);
        return redirect('/home');
    }

    */
    public function register(UserRegisterRequest $request)
    {

        try{

            DB::beginTransaction();
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'phone' => $request->key_phone.$request->number_phone,
            ]);

            $credentials = $request->only(["email", "password"]);
            $token = Auth::guard("user_api")->attempt($credentials);
            if (!$token) {
                return $this->returnError("E001", "incorrect!");
            }
            $user = Auth::guard('user_api')->user();
            $user->token = $token;

            DB::commit();

            return $this->returnData("data", $user);


        } catch (\Exception $e) {
            DB::rollback();
            //return $this->returnData("data", $e);
            return $this->returnError("s001", "something went wrong !");
        }

    }

    public function test(Request $request)
    {
        return $this->returnData("data", $request);
    }


}
