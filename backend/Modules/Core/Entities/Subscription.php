<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\GroceryIndia\Entities\ShopSubscriptions;
use Modules\GroceryIndia\Entities\Shop;

class Subscription extends Model
{
    use HasFactory;

    protected $connection = 'central';
    protected $table = 'subscriptions';

    protected $primaryKey = 'subscription_id';

    protected $fillable = [
        'plan_name',
        'months',
        'price',
        'heading',
        'subheading',
        'description',
        'active',
        'is_best_value',
    ];

    // Define the relationship with ShopSubscription
    public function shopSubscriptions()
    {
        return $this->hasMany(ShopSubscriptions::class);
    }

    public function shops()
    {
        return $this->belongsToMany(Shop::class, 'shop_subscriptions', 'subscription_id', 'shop_id')
            ->withPivot('start_date', 'end_date', 'payment_status', 'payment_reference', 'payment_mode')
            ->withTimestamps();
    }

}
