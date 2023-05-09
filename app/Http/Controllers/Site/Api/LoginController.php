<?php

namespace App\Http\Controllers\Site\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Models\User;
use App\Traits\GeneralTrait;
use Validator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    use GeneralTrait;

    public function checkLogin(Request $request)
    {
        return $this->returnData('data', Auth::guard("user_api")->user());
    }

    public function refreshToken(Request $request)
    {

        $token = $request->header('token');
        $request->headers->set('token', (string) $token, true);
        $request->headers->set('Authorization', 'Bearer '.$token, true);
        try {

            if (Auth::guard("user_api")->user()) {
                $token = JWTAuth::parseToken()->authenticate();
                if($token){
                    return $this->returnData("new_token", $token);
                }
            }else{
                return $this->returnError("s001", "You are not user!");
            }

        } catch (TokenExpiredException  $e) {
            $new_token = JWTAuth::refresh();
            return $this->returnData("new_token", $new_token);
        }
        catch (Exception  $e) {
            //return $this->returnData("admin", dd($e));
            return $this->returnError("s001", "something went wrong !");
        }
    }

    public function login(UserLoginRequest $request)
    {

        $attempts = 0;
        while ($attempts < 2) {
            try {
                if ($token = auth()->guard('user_api')->attempt(['email' => $request->input("email"), 'password' => $request->input("password")])) {
                    $user = User::where('email', $request->input("email"))->with('codes')->first();
                    $old_token = $user->api_token;
                    $user->api_token = $token;
                    $user->save();
                    // set token without setting headers
                    JWTAuth::setToken($old_token);
                    if($old_token && Auth::guard("user_api")->user()){
                        // destroy token if you want forever invalidate(true)
                        JWTAuth::invalidate(JWTAuth::getToken());
                    }
                    $user = User::verefied_codes($user);
                    $attempts = 2;
                    return $this->returnData("user", $user);
                }
                return $this->returnError("E001", "email or password is incorrect!");

            } catch (TokenExpiredException $e) {
                $attempts++;
            } catch (Exception $e) {
                return $this->returnError($e->getCode(), dd($e));
            }
        }
    }

    public function logout(Request $request){
        JWTAuth::invalidate(JWTAuth::getToken());
        $user = User::where('api_token', $request->header("token"))->first();
        $user->api_token = null;
        $user->save();
        return $this->returnError("", "You are logout");

    }

}
