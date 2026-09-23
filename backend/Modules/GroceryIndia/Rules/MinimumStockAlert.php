<?php

namespace Modules\GroceryIndia\Rules;

use Illuminate\Contracts\Validation\Rule;

class MinimumStockAlert implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

    protected $quantity;
    protected $preferenceQuantity;

    public function __construct($quantity, $preferenceQuantity)
    {
        $this->quantity = $quantity;
        $this->preferenceQuantity = $preferenceQuantity;
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
        // // Condition 1: min_stock_alert cannot be 0
        // if ($value == 0) {
        //     return false;
        // }

        // Condition 2: min_stock_alert can be greater than quantity (No restriction)

        // Condition 3: min_stock_alert cannot be set without quantity when preference_quantity is 0
        if ($this->preferenceQuantity == 0 && $value !== null && $this->quantity === null) {
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
        // if (request('min_stock_alert') == 0) {
        //     return 'The minimum stock alert cannot be 0.';
        // }

        if ($this->preferenceQuantity == 0 && request('min_stock_alert') !== null && $this->quantity === null) {
            return __('validation.The minimum stock alert cannot be accepted without specifying a stock');
        }

        return __('validation.Invalid minimum stock alert value');
    }
}
