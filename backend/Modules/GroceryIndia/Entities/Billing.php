<?php

namespace Modules\GroceryIndia\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Billing extends Model
{
    use HasFactory;

    protected $connection = 'grocery_india';
    protected $table = 'billing';
    protected $primaryKey = 'id';

    protected $casts = [
        'item_list' => 'array',
    ];

    protected $fillable = [
        'shop_id',
        'user_id',
        'customer_id',
        'invoice_number',
        'invoice_count',
        'item_list',
        'total_price',
        'payment_status'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }
    
}
