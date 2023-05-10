<?php

namespace App\Http\Requests;

use App\Rules\PhoneCustomRule;
use App\Traits\GeneralTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * Summary of UserRegisterRequest
 */
class UserRegisterRequest extends FormRequest
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
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|string|min:8',
            'key_phone' => 'required|string',
            'phone' => ['required', new PhoneCustomRule(), 'unique:users'],
        ];
    }


    public function failedValidation(Validator $validator)
    {

        throw new HttpResponseException($this->returnErrorResponse($validator->errors()->first(), 400));

    }






}
