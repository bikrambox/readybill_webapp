<?php

namespace Modules\Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class NoScriptTag implements Rule
{
    /**
     * Create a new rule instance.
     *
     * This rule is intended for plain-text fields where you do NOT
     * expect any HTML or JavaScript, and you want to aggressively
     * block common XSS payload patterns.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute  The field name being validated.
     * @param  mixed   $value      The submitted value for the field.
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // 1) Allow null / non-string values to pass (adjust if needed)
        //    If you want to be stricter, you can cast to string instead.
        if (!is_string($value) || $value === '') {
            return true;
        }

        // 2) Normalize value for consistent checks
        //    Lowercasing makes case-insensitive pattern checks easier.
        $normalized = mb_strtolower($value, 'UTF-8');

        // 3) Block any <script> or </script> tag (with arbitrary attributes/spaces)
        //    This catches classic script tag injections.
        if (preg_match('/<\/?\s*script\b[^>]*>/i', $normalized)) {
            return false;
        }

        // 4) Block any HTML tags at all: <tag ...> or </tag ...>
        //    If this field is supposed to be plain text, any tag is suspicious.
        if (preg_match('/<\s*\/?\s*\w+[^>]*>/i', $normalized)) {
            return false;
        }

        // 5) Block javascript: protocol in links or URLs
        //    Covers payloads like "javascript:alert(1)" in href/src.
        if (preg_match('/javascript\s*:/i', $normalized)) {
            return false;
        }

        // 6) Block common inline event handler attributes
        //    e.g. onclick=, onload=, onerror=, etc.
        if (preg_match('/\bon\w+\s*=/i', $normalized)) {
            return false;
        }

        // 7) Optionally block some other typical XSS patterns
        //    e.g. "expression(" (old CSS-based XSS), "data:text/html" URLs, etc.
        //    Uncomment any of these if your use case requires more strictness.

        // if (preg_match('/expression\s*\(/i', $normalized)) {
        //     return false;
        // }

        // if (preg_match('/data\s*:\s*text\/html/i', $normalized)) {
        //     return false;
        // }

        // 8) If none of the patterns matched, treat input as safe for this rule.
        //    Remember: Blade {{ }} still escapes on output.
        return true;
    }

    /**
     * Get the validation error message.
     *
     * This message should clearly indicate that unsafe script-like
     * content is not allowed in this field.
     *
     * @return string
     */
    public function message()
    {
        // You can localize this as needed.
        return __('validation.invalid_script_content', [
            'reason' => 'unsafe script or HTML content detected',
        ]);
    }
}