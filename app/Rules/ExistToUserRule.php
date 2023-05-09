<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;

class ExistToUserRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if($user->phone == $value){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.phone_not_exist_for_user');
    }
}
