<?php

namespace Modules\Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class PhoneNumber implements Rule
{
    protected string $errorType = '';
    protected $countryCode;

    protected int $inMin;
    protected int $inMax;
    protected int $otherMin;
    protected int $otherMax;

    public function __construct($countryCode = null)
    {
        $this->countryCode = strtoupper($countryCode);

        // Load rules from ENV
        $this->inMin = (int) env('IN_PHONE_MIN_LENGTH', 10);
        $this->inMax = (int) env('IN_PHONE_MAX_LENGTH', 10);

        $this->otherMin = (int) env('OTHER_PHONE_MIN_LENGTH', 10);
        $this->otherMax = (int) env('OTHER_PHONE_MAX_LENGTH', 12);
    }

    public function passes($attribute, $value)
    {
        $value = trim((string) $value);

        // Digits only
        if (!ctype_digit($value)) {
            $this->errorType = 'non_numeric';
            return false;
        }

        $length = strlen($value);

        // India validation
        if ($this->countryCode === 'IN') {

            if ($length < $this->inMin) {
                $this->errorType = 'min_length_in';
                return false;
            }

            if ($length > $this->inMax) {
                $this->errorType = 'max_length_in';
                return false;
            }

        } else {
            // Other countries
            if ($length < $this->otherMin) {
                $this->errorType = 'min_length_other';
                return false;
            }

            if ($length > $this->otherMax) {
                $this->errorType = 'max_length_other';
                return false;
            }
        }

        // Reject identical digits
        if (preg_match('/^(\d)\1+$/', $value)) {
            $this->errorType = 'identical_digits';
            return false;
        }

        return true;
    }

    public function message()
    {
        switch ($this->errorType) {

            case 'non_numeric':
                return __('login_validation.custom.mobile.non_numeric');

            case 'min_length_in':
                return __('login_validation.custom.mobile.min_length_in');

            case 'max_length_in':
                return __('login_validation.custom.mobile.max_length_in');

            case 'min_length_other':
                return __('login_validation.custom.mobile.min_length_other');

            case 'max_length_other':
                return __('login_validation.custom.mobile.max_length_other');

            case 'identical_digits':
                return __('login_validation.custom.mobile.identical_digits');

            default:
                return __('login_validation.custom.mobile.phone_number');
        }
    }
}
