<?php

namespace App\Http\Requests;


use App\Traits\GeneralTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;

class EmailVerifyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            "code" => "required|exists:user_verify_codes,code",
            "token" => "required|exists:users,api_token"
        ];
    }


    public function failedValidation(Validator $validator)
    {

        throw new HttpResponseException($this->returnError("001", $validator->errors()->first()));

    }
}
