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
    protected const CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    // State-code-aware GSTIN pattern (01–38 etc.)
    protected const GSTIN_PATTERN = '/^(0[1-9]|[1-2][0-9]|3[0-8])[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/';

    public function passes($attribute, $value)
    {
        if (empty($value)) {
            return true; // allow nullable; remove if field must be required
        }

        $value = strtoupper(trim($value));

        // 1) Format check via regex
        if (!preg_match(self::GSTIN_PATTERN, $value)) {
            return false;
        }

        // 2) Checksum verification
        $body = substr($value, 0, -1);     // first 14 chars
        $givenChecksum = substr($value, -1); // 15th char

        return $givenChecksum === $this->calculateChecksum($body);
    }

    protected function calculateChecksum(string $gstinWithoutChecksum): string
    {
        $factor = 2;
        $total = 0;
        $chars = str_split(strrev($gstinWithoutChecksum));

        foreach ($chars as $char) {
            $codePoint = strpos(self::CHARS, $char);

            // If any character is outside 0-9A-Z, treat as invalid
            if ($codePoint === false) {
                return '';
            }

            $addend = $factor * $codePoint;
            $factor = ($factor === 2) ? 1 : 2;

            // Luhn-style base-36 sum: quotient + remainder
            $total += intdiv($addend, 36) + ($addend % 36);
        }

        $checksumIndex = (36 - ($total % 36)) % 36;

        return self::CHARS[$checksumIndex];
    }

    public function message()
    {
        return 'Please enter a valid GSTIN number.';
    }
}