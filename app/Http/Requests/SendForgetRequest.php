<?php

namespace App\Http\Requests;

use App\Rules\ExistToUserRule;
use App\Rules\SendForgetValueRule;
use App\Traits\GeneralTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;

class SendForgetRequest extends FormRequest
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
            'type' => ['required', Rule::in([1, 0])],
            'value' => ['required', new SendForgetValueRule($request->type)],
        ];
    }

    public function failedValidation(Validator $validator)
    {

        throw new HttpResponseException($this->returnErrorResponse($validator->errors()->first(), 400));

    }

    public function messages()
    {
        return [
            'type.in' => __('validation.type_in')
        ];
    }

}
