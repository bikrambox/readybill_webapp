<?php

namespace Modules\GroceryGermany\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Authentication\Entities\User;
use Modules\Core\Entities\Subscription;

class Shop extends Model
{
    use HasFactory;

    protected $connection = 'grocery_germany';
    protected $table = 'shops';
    protected $primaryKey = 'shop_id';

    protected $fillable = [
        'shop_id',
        'user_id',
        'name',
        'email',
        'business_name',
        'address',
        'gstin',
        'logo',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Define the relationship with ShopSubscription
    public function shopSubscriptions()
    {
        return $this->hasMany(ShopSubscriptions::class);
    }

    public function subscriptions()
    {
        return $this->belongsToMany(Subscription::class, 'shop_subscriptions', 'shop_id', 'subscription_id')
            ->withPivot('start_date', 'end_date', 'payment_status', 'payment_reference', 'payment_mode')
            ->withTimestamps();
    }

}