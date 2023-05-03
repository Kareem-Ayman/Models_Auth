<?php

namespace App\Http\Controllers\Site\Api;

use App\Http\Controllers\Controller;
use App\Traits\GeneralTrait;
use Validator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    use GeneralTrait;

    public function checkLogin(Request $request)
    {
        return $this->returnData('data', Auth::guard("user_api")->user());
    }

    public function login(Request $request)
    {


        try {

            $request->headers->set('Authorization', 'Bearer '.$request->header('_token'), true);
            $token = JWTAuth::parseToken()->authenticate();

        } catch (TokenExpiredException  $e) {
            $new_token = JWTAuth::refresh();
            return $this->returnData("admin", $new_token);
        }


        try {
            $rules = [
                "email" => "required|exists:admin,email",
                "password" => "required"
            ];
            /*$validator = Validator::make($request->all(), $rules);
            if($validator->fails()){
                $code = $this->returnCodeAccordingToInput($validator);
                return $this->returnValidationError($code, $validator);
            }*/

            $credentials = $request->only(["email", "password"]);
            $token = Auth::guard("admin_api")->attempt($credentials);
            if(!$token){
                return $this->returnError("E001", "incorrect!");
            }

            $admin = Auth::guard('admin_api')->user();
            $admin->api_token = $token;
            return $this->returnData("admin", $admin);

        } catch (Exception $e) {
            return $this->returnError($e->getCode(), $e->getMessage());
        }
    }
}
