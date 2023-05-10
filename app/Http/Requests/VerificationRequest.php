<?php

namespace App\Http\Requests;

use App\Rules\ExistToUserRule;
use App\Rules\PhoneCustomRule;
use App\Traits\GeneralTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Tymon\JWTAuth\Facades\JWTAuth;

class VerificationRequest extends FormRequest
{
    use GeneralTrait;

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
            //'phone' => ['required', new PhoneCustomRule(), 'exists:users,phone', new ExistToUserRule('phone')],
        ];
    }

    public function failedValidation(Validator $validator)
    {

        throw new HttpResponseException($this->returnErrorResponse($validator->errors()->first(), 400));

    }
}
