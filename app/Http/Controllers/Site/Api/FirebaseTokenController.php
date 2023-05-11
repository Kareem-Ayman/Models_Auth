<?php

namespace App\Http\Controllers\Site\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FirebaseRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserRegisterSocialRequest;
use App\Mail\TestMail;
use App\Models\Firebase_token;
use App\Models\User;
use App\Models\User_verify_code;
use App\Services\EmailVerificationService;
use App\Traits\GeneralTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Propaganistas\LaravelPhone\PhoneNumber;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

//use Mail;


class FirebaseTokenController extends Controller
{
    use GeneralTrait;

    public function set_firebase_token(FirebaseRequest $request)
    {
        try{

            DB::beginTransaction();
            $token = $request->header('token');
            if($token){

                $token_user = JWTAuth::parseToken()->authenticate();
                $firebase_token = Firebase_token::where('firebase_token', $request->firebase_token)->first();

                if($firebase_token){

                    $firebase_token->user_id = Auth::guard("user_api")->user()->id;
                    $firebase_token->save();
                }else{
                    $firebase_token = Firebase_token::create([
                        'user_id' => Auth::guard("user_api")->user()->id,
                        'firebase_token' => $request->firebase_token,
                    ]);
                }

                DB::commit();
                return $this->returnData("user", $firebase_token);



            }
            else{

                $firebase_token = Firebase_token::where('firebase_token', $request->firebase_token)->first();
                if($firebase_token){
                    return $this->returnErrorResponse("something went wrong !", 400);
                }else{

                    $firebase_token = Firebase_token::create([
                        'firebase_token' => $request->firebase_token,
                    ]);

                    DB::commit();
                    return $this->returnData("user", $firebase_token);
                }

            }

        } catch (Exception $e) {
            DB::rollback();
            //return $this->returnData("data", dd($e));
            return $this->returnErrorResponse("something went wrong !", 400);
        }

    }




}
