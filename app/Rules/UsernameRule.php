<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UsernameRule implements Rule
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
        $pattern = '/^[a-zA-Z]{1,}[a-zA-Z0-9_.]{5,31}$/';
        if (preg_match($pattern, $value)) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attribute không chứa khoảng trắng, bắt đầu là ký tự, độ dài từ 6-32 ký tự không dấu';
    }
}
