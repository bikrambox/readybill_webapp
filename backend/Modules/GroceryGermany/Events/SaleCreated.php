<?php

namespace Modules\GroceryGermany\Events;

use Illuminate\Queue\SerializesModels;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;


use Modules\GroceryGermany\Entities\Billing;
use Modules\Authentication\Entities\User;
use Modules\GroceryGermany\Entities\Shop;

class SaleCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $billing;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(Billing $billing)
    {
        $this->billing = $billing;
        $this->user = User::find($this->billing->user_id);

        // \Log::info('SaleCreated event constructed with billing ID: ' . $this->billing->shop_id);
        // \Log::info('User isAdmin status: ' . ($this->user ? $this->user->isAdmin : 'User not found'));
    }

    // public function broadcastOn()
    // {
    //     \Log::info('Billing data On broadcast: ' . json_encode($this->billing->toArray()));
    //     return new Channel('transactions.' . $this->billing->shop_id);
    // }

    public function broadcastOn()
    {
        $channels = [];

        try {
            if ($this->user->isAdmin == 0) {
                $channels[] = new Channel('staff.transactions.' . $this->billing->user_id);
                // \Log::info('Broadcasting SaleCreated to staff channel: staff.transactions.' . $this->billing->user_id);

                $channels[] = new Channel('admin.transactions.' . $this->billing->shop_id);
                // \Log::info('Broadcasting SaleCreated to admin channel: admin.transactions.' . $this->billing->shop_id);
            } else {
                $channels[] = new Channel('admin.transactions.' . $this->billing->shop_id);
                // \Log::info('Broadcasting SaleCreated to admin channel: admin.transactions.' . $this->billing->shop_id);
            }
        } catch (\Exception $e) {
            \Log::error('Error in SaleCreated broadcastOn: ' . $e->getMessage());
        }

        return $channels;
    }

    public function broadcastWith()
    {
        // \Log::info('broadcase with');
        // \Log::info('Billing data to broadcast: ' . json_encode($this->billing->toArray()));

        $user_name = 'NA';
        if ($this->user->isAdmin == 0) {
            $user_name = $this->user->staff ? $this->user->staff->name : 'No Staff Associated';
        } else if ($this->user->isAdmin == 1) {
            $user_name = $this->user->shop ? $this->user->shop->name : 'No Shop Associated';
        }

        $billingData = $this->billing->toArray();
        $billingData['user_name'] = $user_name;

        return [
            // 'billing' => $this->billing,
            // 'user_name' => $user_name
            'billing' => $billingData
        ];
    }


    public function broadcastAs()
    {
        return 'SaleCreated';
    }
}
