<?php

namespace Modules\GroceryIndia\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidBarcode implements Rule
{
    protected $message = '';
    protected $table;
    protected $ignoreId;
    protected $module_type;

    public function __construct($table = 'items', $ignoreId = null, $module_type = 'central')
    {
        $this->table = $table;
        $this->ignoreId = $ignoreId;
        $this->module_type = $module_type;
    }

    public function passes($attribute, $value)
    {
        /*
        |--------------------------------------------------------------------------
        | Step 1: Normalize input
        |--------------------------------------------------------------------------
        */
        $value = trim((string) $value);

        /*
        |--------------------------------------------------------------------------
        | Step 2: Ignore special allowed values
        | Example: NA, N/A, -, –
        |--------------------------------------------------------------------------
        */
        if (in_array(strtoupper($value), ['NA', 'N/A', '-', '–'])) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Step 3: Reject empty values
        |--------------------------------------------------------------------------
        */
        if ($value === '') {
            $this->message = __('validation.Barcode is required');
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Step 4: Reject all-zero values
        | Example: 00000000, 0000000000000
        |--------------------------------------------------------------------------
        */
        if (preg_match('/^0+$/', $value)) {
            $this->message = __('validation.Barcode cannot contain only zeros');
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Step 5: Validate format
        |
        | Allowed:
        | 1. GS1 / EAN / GTIN:
        |    - exactly 8, 12, 13, or 14 digits
        |
        | 2. Internal inventory barcode:
        |    - letters, numbers, hyphen, underscore
        |    - length 4 to 30
        |--------------------------------------------------------------------------
        */
        $pattern = '/^(?:\d{8}|\d{12}|\d{13}|\d{14}|[A-Za-z0-9_-]{4,30})$/';

        if (!preg_match($pattern, $value)) {
            $this->message = __(
                'validation.invalid_barcode_format'
            );
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Step 6: Check duplicate barcode in database
        |--------------------------------------------------------------------------
        */
        // $query = DB::connection($this->module_type)
        //     ->table($this->table)
        //     ->where('barcode', $value);

        // // Ignore current record while updating
        // if ($this->ignoreId) {
        //     $query->where('id', '!=', $this->ignoreId);
        // }

        // if ($query->exists()) {
        //     $this->message = __('validation.Barcode already exists');
        //     return false;
        // }

        return true;
    }

    public function message()
    {
        return $this->message;
    }
}