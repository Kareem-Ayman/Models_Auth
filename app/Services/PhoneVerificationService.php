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

            return $response_content = json_decode($response->getBody()->getContents(), true);

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

            return json_decode($response->getBody()->getContents(), true);

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
