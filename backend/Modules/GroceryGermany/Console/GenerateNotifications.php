<?php

namespace Modules\GroceryGermany\Console;

use Illuminate\Console\Command;

use Modules\GroceryGermany\Helpers\NotificationHelpher;
use Modules\Authentication\Entities\User;

class GenerateNotifications extends Command
{
    private $module_type = "grocery_germany";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grocerygermany:generate-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate subscription expiry and stock alert notifications';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $users = User::where('isAdmin', 1)->get();

        foreach ($users->where('module_type', $this->module_type) as $user) {
            NotificationHelpher::checkSubscriptionExpiry($user);
            NotificationHelpher::checkStockAlerts($user);
        }

        $this->info('Notifications generated successfully.');
    }

}
