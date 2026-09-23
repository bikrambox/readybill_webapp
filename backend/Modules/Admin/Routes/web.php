<?php


use Modules\Admin\Http\Controllers\AdminLoginController;
use Modules\Admin\Http\Controllers\AdminDashboardController;
use Modules\Admin\Http\Controllers\AdminSubscriberController;
use Modules\Admin\Http\Controllers\AdminSubscriptionPlanController;
use Modules\Admin\Http\Controllers\AdminChangePasswordController;
use Modules\Admin\Http\Controllers\AdminLogDataController;
use Modules\Admin\Http\Controllers\AgentController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix(env('ADMIN_PREFIX'))->group(function() {

    Route::get('/', [AdminLoginController::class, 'index']);
    
    Route::get('login', [AdminLoginController::class, 'login_form'])->name('admin.login.page');
    Route::post('login', [AdminLoginController::class, 'login'])->name('admin.login');


    Route::middleware('auth:admin')->group(function () {

        Route::post('logout', [AdminLoginController::class, 'logout'])->name('admin.logout');
        Route::get('dashboard', [AdminDashboardController::class, 'dashboard'])->name('admin.dashboard');
        
        Route::get('dashboard-stats-data', [AdminDashboardController::class, 'dashboardStatsData'])->name('admin.dashboard.stats.data');

        Route::get('shop-list/{module_type?}', [AdminDashboardController::class, 'shopsList'])->name('admin.shop.list');
        Route::post('shop-list-data', [AdminDashboardController::class, 'shopListData'])->name('admin.shop.list.data');

        Route::get('staff-list/{shop_id}', [AdminDashboardController::class, 'shopStaffList'])->name('admin.staff.shop.list');
        Route::post('staff-list-data', [AdminDashboardController::class, 'shopStaffListData'])->name('admin.staff.shop.list.data');

        Route::get('item-list/{module_type}/{shop_id}', [AdminDashboardController::class, 'shopItemList'])->name('admin.item.list');
        Route::post('item-list-data', [AdminDashboardController::class, 'shopItemListData'])->name('admin.item.list.data');

        Route::get('{module_type}/shop/{shop_id}/item-tags/{item_id}', [AdminDashboardController::class, 'getItemTags'])->name('admin.get.item.tags');
        Route::post('shop/update/item-tags', [AdminDashboardController::class, 'updateItemTag'])->name('admin.update.item.tag');

        // --------------------------------------------------------------------------- TRANSACTION LIST ---------------------------------------------------------------------------
        Route::get('shop/transactions/{shop_id}', [AdminDashboardController::class, 'shopTransactions'])->name('admin.shop.transactions');
        Route::post('shop/transactions-data', [AdminDashboardController::class, 'shopTransactionData'])->name('admin.shop.transactions.data');
        Route::get('shop/transaction/{user_id}/{transaction_id}', [AdminDashboardController::class, 'shopTransaction'])->name('admin.shop.transaction.by.id');
        Route::post('shop/delete/transactions', [AdminDashboardController::class, 'deleteTransactionByDateRange'])->name('admin.shop.delete.transactions');
        // --------------------------------------------------------------------------- TRANSACTION LIST ---------------------------------------------------------------------------

        // --------------------------------------------------------------------------- SUBSCRIBERS ---------------------------------------------------------------------------
        Route::get('shop/subscribers', [AdminSubscriberController::class, 'shopSubscriberList'])->name('admin.subscriber.list');
        Route::get('shop/{module_type}/{user_id}', [AdminSubscriberController::class, 'shopDetails'])->name('admin.shop.details');
        Route::get('shop-data/{module_type}/{user_id}', [AdminSubscriberController::class, 'shopDetailsData'])->name('admin.shop.data.details');
        Route::post('shop-data-update', [AdminSubscriberController::class, 'shopDataUpdate'])->name('admin.shop.data.update');

        // --------------------------------------------------------------------------- SUBSCRIBERS ---------------------------------------------------------------------------


        // --------------------------------------------------------------------------- SUBSCRIPTION PLAN ---------------------------------------------------------------------------
        // Route::get('subscription/plans', [AdminSubscriptionPlanController::class, 'subscriptionPlans'])->name('admin.subscription.plan');
        // Route::post('subscription-plan-data', [AdminSubscriptionPlanController::class, 'subscriptionPlanData'])->name('admin.subscription.plan.data');

        // Route::get('subscription-plan/{subscription_id}', [AdminSubscriptionPlanController::class, 'getSubscriptionPlanById'])->name('admin.subscription.plan.by.id');
        // Route::post('update/subscription-plan', [AdminSubscriptionPlanController::class, 'updateSubscriptionPlan'])->name('admin.update.subscription.plan');
        // Route::post('subscription-plan/delete/{subscription_id}', [AdminSubscriptionPlanController::class, 'deleteSubscriptionPlan'])->name('admin.delete.subscription.plan');


        // Route::get('subscription-plan/create', [AdminSubscriptionPlanController::class, 'createSubscriptionPlan'])->name('admin.subscription.plan.create');
        // Route::post('subscription-plan/store', [AdminSubscriptionPlanController::class, 'storeSubscriptionPlan'])->name('admin.store.subscription.plan');
        // --------------------------------------------------------------------------- SUBSCRIPTION PLAN ---------------------------------------------------------------------------


        // --------------------------------------------------------------------------- CHANGE PASSWORD ---------------------------------------------------------------------------
        Route::get('change/password', [AdminChangePasswordController::class, 'changePassword'])->name('admin.change.password');
        Route::post('send-otp', [AdminChangePasswordController::class, 'sendOTP'])->name('admin.send.otp');
        Route::post('update-password', [AdminChangePasswordController::class, 'updatePassword'])->name('admin.update.password');
        // --------------------------------------------------------------------------- CHANGE PASSWORD ---------------------------------------------------------------------------

        // --------------------------------------------------------------------------- ACTIVATE SHOP ---------------------------------------------------------------------------
        Route::post('/admiActivate-shop', [AdminSubscriberController::class, 'activateShopOwner'])->name('admin.activate.shop');

        // --------------------------------------------------------------------------- ACTIVATE SHOP ---------------------------------------------------------------------------

        // --------------------------------------------------------------------------- DEACTIVATE SHOP ---------------------------------------------------------------------------
        Route::post('deactivate-shop', [AdminSubscriberController::class, 'deactivateShopOwner'])->name('admin.deactivate.shop');
        // --------------------------------------------------------------------------- DEACTIVATE SHOP ---------------------------------------------------------------------------


        // --------------------------------------------------------------------------- UPGRADE SUBSCRIPTION PLAN ---------------------------------------------------------------------------
        Route::get('subscription-plans', [AdminSubscriptionPlanController::class, 'allSubscriptionPlans'])->name('admin.all.subscription.plan');
        Route::post('upgrade-subscription', [AdminSubscriberController::class, 'upgradeSubscriptionPlan'])->name('admin.upgrade.subscription');
        // --------------------------------------------------------------------------- UPGRADE SUBSCRIPTION PLAN ---------------------------------------------------------------------------


        // --------------------------------------------------------------------------- ADMIN LOG ROUTES ---------------------------------------------------------------------------
        Route::get('log-data', [AdminLogDataController::class, 'logData'])->name('admin.log.data');
        // --------------------------------------------------------------------------- ADMIN LOG ROUTES ---------------------------------------------------------------------------
        
        
        // --------------------------------------------------------------------------- ADMIN AGENTS ROUTES ---------------------------------------------------------------------------
        Route::prefix(config('admin.route_prefix'))->name('admin.')->middleware(['web', 'auth'])->group(function () {

            // ── Views ────────────────────────────────────────────────────────────
            Route::get('/agents', [AgentController::class, 'agentList'])->name('agent.index');
            Route::get('/agent/{userId}', [AgentController::class, 'agentView'])->name('agent.view');
            Route::get('/agent-transactions', [AgentController::class, 'agentTransactions'])->name('agent.transactions');
            
            // ── AJAX / JSON endpoints ────────────────────────────────────────────
            Route::post('/agents/data', [AgentController::class, 'agentListData'])->name('agent.list.data');
            Route::get('/agents/{userId}/details', [AgentController::class, 'show'])->name('agent.show');
            Route::post('/agents/{userId}/toggle-status', [AgentController::class, 'toggleStatus'])->name('agent.toggle.status');
            Route::post('/agent-transactions-data', [AgentController::class, 'agentTransactionData'])->name('agent.transactions.data');
            Route::post('transactions/payment', [AgentController::class, 'storePayment'])->name('agent.transactions.payment.store');
        });
        // --------------------------------------------------------------------------- ADMIN AGENTS ROUTES ---------------------------------------------------------------------------

    });

    // Route::get('create-admin', function () {

    //     $user = \Modules\Admin\Entities\Admin::create([
    //         'name' => 'Admin',
    //         'mobile' => '9876543211',
    //         'email' => 'jay3000bc@gmail.com',
    //         'password' => Hash::make('12345678'),
    //         'ip_address' => '',
    //         'current_logged_in' => now(),
    //         'last_logged_in' => now(),
    //     ]);

    //     return response()->json([
    //         'status' => 1,
    //         'message' => 'User inserted successfully',
    //         'data' => $user
    //     ]);

    // });

});


