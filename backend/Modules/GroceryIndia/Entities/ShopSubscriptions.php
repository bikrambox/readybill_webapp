<?php

namespace Modules\GroceryIndia\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Entities\Subscription;

class ShopSubscriptions extends Model
{
    use HasFactory;

    protected $connection = 'grocery_india';
    protected $table = 'shop_subscriptions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'shop_id',
        // 'subscription_id',
        'subscription_data',
        'start_date',
        'end_date',
        'payment_status',
        'payment_mode',
        'payment_reference',
        'renewed_by_type',
        'assigned_by_id',
        'assigned_by_type',
        'note',
        'created_at',
        'updated_at',
    ];

    // Define the relationship with Shop
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    // Define the relationship with Subscription
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function assignedBy()
    {
        return $this->morphTo();
    }
    
}
