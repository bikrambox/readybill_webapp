<?php

namespace Modules\Authentication\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;


// use Modules\GroceryIndia\Entities\Shop;
// use Modules\GroceryIndia\Entities\Staff;
use Modules\GroceryIndia\Entities\Preference;
use Modules\GroceryIndia\Entities\ItemsOnCart;

use Modules\Agent\Entities\AgentDetails;

use Modules\Core\Helpers\CommonHelpher;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $connection = 'central';
    protected $table = 'users';
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'mobile',
        'password',
        'ip_address',
        'last_logged_in',
        'isAdmin',
        'isAgent',
        'addedBy',
        'token',
        'active',
        'isVerified',
        'shop_type',
        'module_type',
        'country_details',
        'country_code',
        'detected_country_code',
        'lang',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function apiKey()
    {
        return $this->hasOne(ApiKey::class, 'user_id');
    }

    // public function shop()
    // {
    //     return $this->hasOne(Shop::class, 'user_id');
    // }

    // public function staff()
    // {
    //     return $this->hasOne(Staff::class, 'user_id');
    // }

    public function shop()
    {
        // Ensure module_type is available and not empty
        if (!$this->module_type) {
            return $this->emptyRelation();
        }

        // \Log::info('module_type',$this->module_type);

        $module = CommonHelpher::getModuleName($this->module_type);

        // Construct the namespace dynamically based on module name
        $shopClass = "Modules\\{$module}\\Entities\\Shop";

        // Check if the class exists to avoid errors
        if (!class_exists($shopClass)) {
            return $this->emptyRelation();
        }

        // Return the hasOne relationship with the dynamic Staff class
        return $this->hasOne($shopClass, 'user_id');
    }

    public function staff()
    {
        // Ensure module_type is available and not empty
        if (!$this->module_type) {
            return $this->emptyRelation();
        }

      
        $module = CommonHelpher::getModuleName($this->module_type);

        // Construct the namespace dynamically based on module name
        $staffClass = "Modules\\{$module}\\Entities\\Staff";

        // Check if the class exists to avoid errors
        if (!class_exists($staffClass)) {
            return $this->emptyRelation();
        }

        // Return the hasOne relationship with the dynamic Staff class
        return $this->hasOne($staffClass, 'user_id');
    }

    public function preference()
    {

        // Ensure module_type is available and not empty
        if (!$this->module_type) {
            return $this->emptyRelation();
        }

        $module = CommonHelpher::getModuleName($this->module_type);

        // Construct the namespace dynamically based on module name
        $preferenceClass = "Modules\\{$module}\\Entities\\Preference";

        // Check if the class exists to avoid errors
        if (!class_exists($preferenceClass)) {
            return $this->emptyRelation();
        }

        // Return the hasOne relationship with the dynamic Preference class
        return $this->hasOne($preferenceClass, 'user_id');

        // return $this->hasOne(preference::class, 'user_id');
    }

    public function cartItems()
    {

        // Ensure module_type is available and not empty
        if (!$this->module_type) {
            return $this->emptyRelation();
        }

        $module = CommonHelpher::getModuleName($this->module_type);

        // Construct the namespace dynamically based on module name
        $ItemsOnCartClass = "Modules\\{$module}\\Entities\\ItemsOnCart";

        // Check if the class exists to avoid errors
        if (!class_exists($ItemsOnCartClass)) {
            return $this->emptyRelation();
        }

        // Return the hasOne relationship with the dynamic ItemsOnCart class
        return $this->hasOne($ItemsOnCartClass, 'user_id');

        // return $this->hasMany(ItemsOnCart::class, 'user_id');
    }


    public function agentDetail()
    {
        return $this->hasOne(AgentDetails::class,'user_id');
    }


    public function assignedSubscripitons()
    {
        // Ensure module_type is available and not empty
        if (!$this->module_type) {
            return $this->emptyRelation();
        }


        $module = CommonHelpher::getModuleName($this->module_type);

        // Construct the namespace dynamically based on module name
        $shopSubscriptionClass = "Modules\\{$module}\\Entities\\ShopSubscriptions";

        // Check if the class exists to avoid errors
        if (!class_exists($shopSubscriptionClass)) {
            return $this->emptyRelation();
        }

        // Return the hasOne relationship with the dynamic Staff class
        return $this->morphMany($shopSubscriptionClass, 'assigned_by');
    }


    /**
     * Returns a HasOne that always resolves to null safely.
     * Used instead of returning null from relationship methods.
     */
    private function emptyRelation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(self::class, 'user_id')->whereRaw('1 = 0');
    }


}
