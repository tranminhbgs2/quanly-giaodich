<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UpperCaseRule implements Rule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // TODO: Implement passes() method.
        if (strtoupper($value) === $value) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        // TODO: Implement message() method.
        return ':attribute phải viết hoa';
    }
}
