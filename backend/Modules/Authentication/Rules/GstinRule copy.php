<?php

namespace Modules\Authentication\Rules;

use Illuminate\Contracts\Validation\Rule;

class GstinRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (empty($value)) {
            return true; // allow nullable, remove if required
        }

        $value = strtoupper(trim($value));

        return preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Please enter a valid GSTIN number.';
    }
}