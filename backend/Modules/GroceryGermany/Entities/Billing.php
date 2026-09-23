<?php

namespace Modules\GroceryGermany\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Billing extends Model
{
    use HasFactory;

    protected $connection = 'grocery_germany';
    protected $table = 'billing';
    protected $primaryKey = 'id';

    protected $casts = [
        'item_list' => 'array',
    ];

    protected $fillable = [
        'shop_id',
        'user_id',
        'invoice_number',
        'invoice_count',
        'item_list',
        'total_price',
        'payment_status'
    ];
}
