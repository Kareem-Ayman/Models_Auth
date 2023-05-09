<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use libphonenumber\PhoneNumberUtil;
use Propaganistas\LaravelPhone\PhoneNumber;
use Tymon\JWTAuth\Facades\JWTAuth;

class SendForgetValueRule implements Rule
{
    private $type;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($type)
    {
        $this->type = $type;
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
        if($this->type == 1){

            return preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $value);

        }elseif($this->type == 0){

            if(substr($value, 0, 1) != "+"){
                return false;
            }

            $validator = Validator::make([$attribute => $value], [
                'value' => 'phone:SA,EG',
            ]);

            if ($validator->fails()) {
                return false;
            }else{
                return true;
            }


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
        return __('validation.send_forget_value_rule');
    }

    public function after($validator)
    {
        //$phone = PhoneNumber::make($value, ['SA', 'EG']);
        //$phone->formatE164();
        //$validator->setAttribute('value', 'admin@admin.com');
        //$validator->setRules(["phone"]);
    }


}
