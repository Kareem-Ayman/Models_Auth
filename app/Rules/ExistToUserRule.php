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

    protected $attr;

    public function __construct($attr)
    {
        $this->attr = $attr;
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
        $attr = $this->attr;
        if($user->$attr == $value){
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
