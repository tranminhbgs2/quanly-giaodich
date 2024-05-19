<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CurrentDateLimitRule implements Rule
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
        if ($time <= time()) {
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
        return ':attribute không được lớn hơn ngày hiện tại';
    }
}
