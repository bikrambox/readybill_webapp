<?php

namespace Modules\GroceryGermany\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidBarcode implements Rule
{
    protected $message = '';
    protected $table;
    protected $ignoreId;

    public function __construct($table = 'items', $ignoreId = null, $module_type = 'central')
    {
        $this->table = $table;
        $this->ignoreId = $ignoreId;
        $this->module_type = $module_type;
    }

    public function passes($attribute, $value)
    {

        // Ignore validation if value is NA or –
        if (in_array(trim(strtoupper($value)), ['NA', 'N/A', '–', '-'])) {
            return true;
        }

        // Must be string
        if (!is_string($value)) {
            $this->message = __('validation.Barcode must be a valid string.');
            return false;
        }

        // Format validation
        if (!preg_match('/^[A-Za-z0-9]{1,50}$/', $value)) {
            $this->message = __('validation.Barcode must be alphanumeric and not exceed 50 characters.');
            return false;
        }

        $query = DB::connection($this->module_type)->table($this->table)
            ->where('barcode', $value);

        // Ignore current record during update
        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $this->message = __('validation.Barcode already exists') . ".";
            return false;
        }

        return true;
    }

    public function message()
    {
        return $this->message;
    }
}