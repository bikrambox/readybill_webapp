<?php

namespace Modules\GroceryIndia\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $connection = 'grocery_india';
    protected $table = 'customers';
    protected $primaryKey = 'customer_id';
    protected $fillable = [
        'dial_code',
        'mobile',
        'name',
        'address',
        'state',
        'state_code',
        'gstin',
    ];

    public function bills()
    {
        return $this->hasMany(Billing::class, 'customer_id', 'customer_id');
        // 2nd param = FK in billing table
        // 3rd param = PK in customers table
    }

    
}
