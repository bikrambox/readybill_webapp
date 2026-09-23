<?php

namespace Modules\GroceryIndia\Console;

use Illuminate\Console\Command;
use Modules\GroceryIndia\Entities\ItemsOnCart;
use Illuminate\Support\Facades\DB;
use Modules\GroceryIndia\Helpers\TableNameHelper;
use Illuminate\Support\Facades\Log;

class ClearItemsFromCart extends Command
{

    private $module_type = "grocery_india";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'groceryindia:clear-items-from-cart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear those items from cart which are not present in the inventory';


    public function handle()
    {
        $cartItems = ItemsOnCart::all()->groupBy('user_id');

        // Log::info('Cart Items', $cartItems->toArray());

        foreach ($cartItems as $userId => $items) {

            $itemIds = $items->pluck('item_id')->toArray();

            if (empty($itemIds)) {
                continue;
            }

            $table_name = TableNameHelper::getTableName($userId);

            $validIds = DB::connection($this->module_type)->table($table_name)
                ->whereIn('id', $itemIds)
                ->pluck('id')
                ->toArray();

            ItemsOnCart::where('user_id', $userId)
                ->whereNotIn('item_id', $validIds)
                ->delete();
        }

        $this->info('Invalid cart items cleared successfully.');
    }
    
}
