<?php

namespace Modules\Core\Rules;

use Illuminate\Contracts\Validation\Rule;
use Modules\Core\Helpers\LanguageHelpher;

class ValidLanguageCode implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

    protected $validLanuageCodes = [];

    public function __construct()
    {
        $languages = LanguageHelpher::getAllLanguages();
        $this->validLanuageCodes = array_column($languages, 'short_code');

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
        return in_array($value, $this->validLanuageCodes);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('register_validation.custom.detected_language.valid_detected_language');
    }
}
