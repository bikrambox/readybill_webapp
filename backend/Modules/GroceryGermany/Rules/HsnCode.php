<?php

namespace Modules\GroceryGermany\Rules;

use Illuminate\Contracts\Validation\Rule;

class HsnCode implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $isRequired;

    public function __construct($isRequired)
    {
        $this->isRequired = $isRequired;
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
        // If required and empty, return false
        if ($this->isRequired && empty($value)) {
            return false;
        }

        // Restrict values consisting only of zeros ("0", "00", "0000", etc.)
        if (preg_match('/^0+$/', $value)) {
            return false;
        }

        // Allow empty value 
        // if ( !$this->isRequired && ($value === '-' || $value === '-')) {
        if (!$this->isRequired && ($value === '')) {
            return true;
        }

        // If provided (not null), it must be numeric with 2-16 digits
        if (!empty($value) && !preg_match('/^\d{2,16}$/', $value)) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->isRequired) {
            return __('validation.The HSN code must be a numeric value between 2 and 16 digits and cannot be all zeros');
        }

        return __('validation.The HSN code must be a numeric value between 2 and 16 digits or empty and cannot be all zeros');
    }
}
