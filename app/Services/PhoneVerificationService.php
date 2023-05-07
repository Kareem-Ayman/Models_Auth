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
            return $this->returnData("dat", env('APP_URL'));

            $response = $client->post((string)env('MSEGAT_URL'), [
                'json' => [
                    "userName" => env('MSEGAT_USERNAME'),
                    "number" => $phone,
                    "apiKey" => env('MSEGAT_APIKEY'),
                    "userSender" => env('MSEGAT_USERSENDER')
                ],
            ]);

            return $this->returnData("data", json_decode($response->getBody(), true));

        } catch (\Exception $e) {
            return $this->returnData("data", dd($e));

            return $this->returnError("s001", "something went wrong !");
        } catch (\Throwable $th){
            return $this->returnError("s001", "something went wrong!");
        }
    }



}
