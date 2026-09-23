<?php

namespace Modules\Agent\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_id',
        'module_type',
        'commission_id',
        'transaction_id',
        'shop_subs_type',
        'shop_subs_id',
        'company_percentage',
        'agent_percentage',
        'company_amount',
        'agent_amount',
        'payment_status',
        'payment_mode',
        'agent_payment_date',
        'note',
    ];




}
