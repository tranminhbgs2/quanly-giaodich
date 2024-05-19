<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MinDateRule implements Rule
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
        $time = strtotime(str_replace('/', '-', $value));
        if ($time >= strtotime(date('Y-m-d 00:00:00'))) {
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
        return ':attribute không được nhỏ hơn ngày hiện tại';
    }
}
