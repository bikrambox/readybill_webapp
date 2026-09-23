<?php

namespace Modules\Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class NoScriptTag implements Rule
{
    /**
     * Create a new rule instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Allow null / non-string / empty values
        if (!is_string($value) || $value === '') {
            return true;
        }

        // Normalize input
        $normalized = mb_strtolower(trim($value), 'UTF-8');
        $pathNormalized = str_replace('\\', '/', $normalized);

        switch (true) {
            // 1. Block path traversal (../../ or ..\..\)
            case preg_match('/\.\.\//', $pathNormalized):
                return false;

            // 2. Block <script> tags
            case preg_match('/<\/?\s*script\b[^>]*>/i', $normalized):
                return false;

            // 3. Block any HTML tags
            case preg_match('/<\s*\/?\s*\w+[^>]*>/i', $normalized):
                return false;

            // 4. Block javascript: protocol
            case preg_match('/javascript\s*:/i', $normalized):
                return false;

            // 5. Block inline event handlers
            case preg_match('/\bon\w+\s*=/i', $normalized):
                return false;

            // Optional extra checks
            // case preg_match('/expression\s*\(/i', $normalized):
            //     return false;

            // case preg_match('/data\s*:\s*text\/html/i', $normalized):
            //     return false;

            default:
                return true;
        }
    }

    /**
     * Get validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.invalid_script_content', [
            'reason' => 'unsafe script, HTML, or path traversal content detected',
        ]);
    }
}