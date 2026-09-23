<?php

namespace Modules\Core\Rules;

use Illuminate\Contracts\Validation\Rule;


use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Modules\Core\Helpers\CountryHelpher;

class ValidCountryCode implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $validCountryCodes = [];
    public function __construct()
    {
        $countries = CountryHelpher::getAllCountries();
        $this->validCountryCodes = array_column($countries, 'code'); // Extract country codes (e.g., "+1", "+91")
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
        return in_array($value, $this->validCountryCodes);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('login_validation.custom.country_code.valid_country_code');
    }
}
