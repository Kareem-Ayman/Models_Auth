<?php

namespace App\Services;

use App\Mail\TestMail;
use App\Models\User;
use App\Models\User_verify_code;
use App\Traits\GeneralTrait;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

class PhoneVerificationService
{
    use GeneralTrait;

    public function verify_msegat($phone)
    {
        try {

            DB::beginTransaction();
            $phone = Str::substr($phone, 0, 4) == '+200' ? str_replace('+200', '+20', $phone) : $phone;
            $user = JWTAuth::parseToken()->authenticate();
            $pastVerify = User_verify_code::where('user_id', $user->id)->where('type', "phone")->first();
            if($pastVerify->verified == 1){
                return $this->returnSuccessMessage("You are verified !");
            }

            $client = new Client([
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'lang'=> 'En'
                ],
            ]);

            $response = $client->post(env('MSEGAT_URL').'/sendOTPCode.php', [
                'json' => [
                    "userName" => env('MSEGAT_USERNAME'),
                    "number" => $phone,
                    "apiKey" => env('MSEGAT_APIKEY'),
                    "userSender" => env('MSEGAT_USERSENDER')
                ],
            ]);

            $response_content = json_decode($response->getBody()->getContents(), true);
            if($response_content['message'] == "Success"){

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
            return $this->returnData("verify_code", json_decode($response->getBody(), true));

            //return json_decode($response->getBody(), true);

        } catch (\Exception $e) {
            DB::rollback();
            //return $this->returnData("data", dd($e));
            return $this->returnError("s001", "something went wrong !");
        } catch (\Throwable $th){
            DB::rollback();
            //return $this->returnData("data", dd($th));
            return $this->returnError("s001", "something went wrong!");
        }
    }


    public function verify_msegat_done($code, $id)
    {
        try {

            DB::beginTransaction();
            $user = JWTAuth::parseToken()->authenticate();
            $pastVerify = User_verify_code::where('user_id', $user->id)->where('type', "phone")->first();
            if($pastVerify->verified == 1){
                return $this->returnSuccessMessage("You are verified !");
            }

            $client = new Client([
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'lang'=> 'En'
                ],
            ]);

            $response = $client->post(env('MSEGAT_URL').'/verifyOTPCode.php', [
                'json' => [
                    "userName" => env('MSEGAT_USERNAME'),
                    "id" => $id,
                    "code" => $code,
                    "apiKey" => env('MSEGAT_APIKEY'),
                    "userSender" => env('MSEGAT_USERSENDER')
                ],
            ]);

            $response_content = json_decode($response->getBody()->getContents(), true);
            if($response_content['message'] == "Success"){

                if(isset($pastVerify)){
                    $pastVerify->verified = 1;
                    $pastVerify->save();
                }

                DB::commit();
                return $this->returnSuccessMessage("Phone verified !");

            }else{
                return $this->returnError("s001", "something went wrong_!");
            }



            //return json_decode($response->getBody(), true);

        } catch (\Exception $e) {
            DB::rollback();
            //return $this->returnData("data", dd($e));
            return $this->returnError("s001", "something went wrong !");
        } catch (\Throwable $th){
            DB::rollback();
            //return $this->returnData("data", dd($th));
            return $this->returnError("s001", "something went wrong!");
        }

    }


}
