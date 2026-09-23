<?php

namespace Modules\GroceryIndia\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Authentication\Entities\User;

class ItemsOnCart extends Model
{
    use HasFactory;


    protected $connection = 'grocery_india';
    protected $table = 'items_on_carts';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'item_id',
        'item_name',
        'sale_price',
        'quantity',
        'item_unit',
        'location',
        'isRefund',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


}
