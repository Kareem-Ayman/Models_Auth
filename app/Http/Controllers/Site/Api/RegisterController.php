<?php

namespace App\Http\Controllers\Site\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegisterRequest;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    use GeneralTrait;

    public function register(UserRegisterRequest $request)
    {

        return $this->returnData("admin", $request);

        /*
        $credentials = $request->only(["email", "password"]);
        $token = Auth::guard("user_api")->attempt($credentials);
        if (!$token) {
            return $this->returnError("E001", "incorrect!");
        }
        $admin = Auth::guard('user_api')->user();
        $admin->_token = $token;
        return $this->returnData("admin", $admin);
        */
    }


}
