<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelPhone\PhoneNumber;

class PhoneCustomRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        try {
            //code...

            if(substr($value, 0, 1) != "+"){
                return false;
            }

            $phone = PhoneNumber::make($value, PHONE_COUNTRIES);
            $phone->formatE164();

            if (! $phone) {
                return false;
            }else{
                return true;
            }

        } catch (\Throwable $th) {
            //throw $th;
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
        return __('validation.phone');
    }
}
