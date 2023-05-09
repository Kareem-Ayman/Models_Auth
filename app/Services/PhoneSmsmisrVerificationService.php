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

class PhoneSmsmisrVerificationService
{
    use GeneralTrait;

    public function verify_smsmisr($phone)
    {
        try {

            DB::beginTransaction();
            $code = random_int(1000,9999);
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
                ],
            ]);

            $response = $client->post(env('SMSMISR_URL'), [
                'json' => [
                    "environment" => env('SMSMISR_ENVIRONMENT'),
                    "username" => env('SMSMISR_USERNAME'),
                    "password" => env('SMSMISR_PASSWORD'),
                    "sender" => env('SMSMISR_SENDER'),
                    "template" => env('SMSMISR_TEMPLATE'),
                    "mobile" => $phone,
                    "otp" => $code,
                ],
            ]);

            $response_content = json_decode($response->getBody()->getContents(), true);
            if($response_content['Code'] == "4901"){

                if(isset($pastVerify)){
                    $pastVerify->delete();
                }
                $user_verified = User_verify_code::create([
                    'user_id' => $user->id,
                    'code' => $code,
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


    /*
    public function verify_smsmisr_done($code, $id)
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

    */

}
