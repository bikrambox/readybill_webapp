<?php

namespace Modules\Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class NoSpecialCharacter implements Rule
{
    protected string $type;
    protected string $reason = 'special characters are not allowed';

    public function __construct(string $type = 'default')
    {
        $this->type = $type;
    }

    public function passes($attribute, $value)
    {
        if ($value === null || $value === '' || !is_string($value)) {
            return true;
        }

        $value = trim($value);

        switch ($this->type) {
            case 'username':
                $this->reason = 'only letters, numbers, underscore, and dot are allowed';
                return preg_match('/^[A-Za-z0-9_.]+$/', $value);

            case 'address':
                $this->reason = 'only letters, numbers, spaces, comma, dot, slash, and hyphen are allowed';
                return preg_match('/^[\pL\pN\s,.\-\/]+$/u', $value);

            case 'description':
                $this->reason = 'only letters, numbers, spaces, comma, dot, slash, and hyphen are allowed';
                return preg_match('/^[\pL\pN\s,.\-\/]+$/u', $value);

            // case 'code':
            //     $this->reason = 'only letters, numbers, and hyphen are allowed';
            //     return preg_match('/^[A-Za-z0-9\-]+$/', $value);

            case 'email':
                $this->reason = 'Only valid email characters are allowed';
                return preg_match('/^[A-Za-z0-9@._+\-]+$/', $value);

            case 'message':
                $this->reason = 'only letters, numbers, spaces, and common punctuation are allowed';
                return preg_match('/^[\pL\pN\s,.\-\/!?():;"\'@#&]+$/u', $value);

            default:
                return preg_match('/^[\pL\pN ]+$/u', $value);
        }
    }

    public function message()
    {
        return __('validation.custom.invalid_special_character', [
            'reason' => $this->reason,
        ]);
    }
}
