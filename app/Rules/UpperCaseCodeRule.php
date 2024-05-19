<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UpperCaseCodeRule implements Rule
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
        if (preg_match('/^[A-Z0-9\-\_\.]+$/', $value)) {
            if (strtoupper($value) === $value) {
                return true;
            }
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
        return ':attribute phải viết hoa, chỉ sử dụng A->Z, 0->9, dấu gạch ngang, gạch chân và dấu chấm';
    }
}
