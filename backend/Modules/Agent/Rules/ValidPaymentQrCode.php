<?php

namespace Modules\Agent\Rules;

use Illuminate\Contracts\Validation\Rule;
use Zxing\QrReader; // ✅ correct namespace — not bare QrReader

class ValidPaymentQrCode implements Rule
{
    protected string $errorMessage = 'The :attribute must be a valid payment QR code.';

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed   $value  — UploadedFile instance
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        try {
            $qrReader = new QrReader($value->getRealPath());
            $content = $qrReader->text();

            if (empty($content)) {
                $this->errorMessage = __('validation.qr_code.invalid', [
                    'attribute' => $attribute,
                ]);
                return false;
            }

            if (!$this->isPaymentQr($content)) {
                $this->errorMessage = __('validation.qr_code.not_payment', [
                    'attribute' => $attribute,
                ]);
                return false;
            }

            return true;

        } catch (\Exception $e) {
            $this->errorMessage = __('validation.qr_code.unreadable', [
                'attribute' => $attribute,
            ]);
            return false;
        }
    }
    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return $this->errorMessage;
    }

    /**
     * Check if the decoded QR content is a payment QR.
     */
    private function isPaymentQr(string $content): bool
    {
        // UPI standard deep link
        if (preg_match('/^upi:\/\/pay\?/i', $content)) {
            return true;
        }

        // Paytm / PhonePe / Google Pay (tez) schemes
        if (preg_match('/^(paytm|phonepe|tez):\/\//i', $content)) {
            return true;
        }

        // Generic UPI — must at least contain a payee address param
        if (str_contains($content, 'pa=')) {
            return true;
        }

        return false;
    }
}