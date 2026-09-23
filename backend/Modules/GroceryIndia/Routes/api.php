<?php

use Illuminate\Http\Request;
use Modules\GroceryIndia\Http\Controllers\API\ItemController;
use Modules\GroceryIndia\Http\Controllers\API\ItemsOnCartController;
use Modules\GroceryIndia\Http\Controllers\API\PreferenceController;
use Modules\GroceryIndia\Http\Controllers\API\BillingController;
use Modules\GroceryIndia\Http\Controllers\API\ReportController;
use Modules\GroceryIndia\Http\Controllers\API\DownloadDataController;
use Modules\GroceryIndia\Http\Controllers\API\SearchController;
use Modules\GroceryIndia\Http\Controllers\API\SubscriptionPlanController;
use Modules\GroceryIndia\Http\Controllers\API\DatasetController;
use Modules\GroceryIndia\Http\Controllers\API\UploadDataController;
use Modules\GroceryIndia\Http\Controllers\API\EmployeeController;
use Modules\GroceryIndia\Http\Controllers\API\NotificationController;
use Modules\GroceryIndia\Http\Controllers\API\CustomerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/groceryindia', function (Request $request) {
//     return $request->user();
// });

// Route::prefix(env('API_VERSION', 'v1'))->group(function () {

Route::prefix(env('API_VERSION') . '/in/grocery')->group(function () {

    Route::group(['middleware' => ['ApiKeyCheck', 'checkShopSubscription','CheckUserCountryCode']], function () {

        // --------------------------------------------------------- ITEMS -------------------------------------------------------
        Route::controller(ItemController::class)->group(function () {

            Route::post('/add-item', 'addItem')->middleware('isAdmin');
            Route::post('/items', 'items');
            Route::get('/item/{id}', 'item')->middleware('isAdmin');

            Route::get('/delete-item/{id}', 'deleteItem')->middleware('isAdmin');

            Route::get('/all-items', 'allItems');
            Route::post('/update-item', 'updateItem')->middleware('isAdmin');
            Route::post('/update-cell', 'editBasedOnCell')->middleware('isAdmin');

            Route::any('/product-suggesstions', 'showProductSuggesstions');
            Route::post('/stock-quantity', 'checkStockQuantity');

            Route::post('/related-units', 'getRelatedUnitList');

            Route::post('/multiple-delete', 'multipleDeleteOption');

            // Route::get('/item/scan/{barcode}', 'getDataByBarcode');
            Route::post('/item/scan', 'getDataByBarcode');
            Route::get('/check-barcode/{barcode}', 'checkBarcodeExistsOrNot');

            Route::post('/validate-item-or-sku', 'validateItemOrSku');

        })->middleware('auth:api');
        // --------------------------------------------------------- ITEMS -------------------------------------------------------


        // --------------------------------------------------------- ITEMS ON CART -------------------------------------------------------
        Route::controller(ItemsOnCartController::class)->group(function () {

            Route::post('/add-item-on-cart', 'addItemOnCart');
            Route::get('/items-on-cart/{location}', 'getItemsByUserId');
            Route::get('/delete-item-from-cart/{id}', 'deleteItemFromCart');
            Route::get('/delete-all-items-from-cart/{location}', 'deleteAllItemOnCart');
            Route::post('/update-item-on-cart', 'updateItemOnCart');

        })->middleware('auth:api');
        // --------------------------------------------------------- ITEMS ON CART -------------------------------------------------------


        // --------------------------------------------------------- PREFERNCES -------------------------------------------------------
        Route::controller(PreferenceController::class)->group(function () {

            Route::post('/preference', 'preference')->middleware('isAdmin');
            Route::get('/user-preferences', 'getUserPreferences');
            // ->middleware('isAdmin');

        })->middleware('auth:api');
        // --------------------------------------------------------- PREFERNCES -------------------------------------------------------


        // --------------------------------------------------------- BILLING & TRANSACTION -------------------------------------------------------
        Route::controller(BillingController::class)->group(function () {

            Route::post('/billing', 'billing');
            Route::post('/billing-n-refund', 'refund');

            Route::post('/all-transactions', 'transactions');
            Route::get('/transaction/{id}', 'getTransactionByID');
            Route::post('/update-transaction', 'updateTransaction');

            Route::get('/generate-pdf/{id}', 'billGenerate');
            
            Route::post('/generate/invoice/token', 'getInvoiceToken');
            // Route::get('/generate/bill/pdf/{id}', 'generateBillPdf');
            Route::post('/generate/bill/pdf', 'generateBillPdf');

            Route::get('/transactions/{id}/mark-paid', 'markAsPaid');

        })->middleware('auth:api');
        // --------------------------------------------------------- BILLING & TRANSACTION -------------------------------------------------------
        
        
        // --------------------------------------------------------- CUSTOMER -------------------------------------------------------
        Route::controller(CustomerController::class)->group(function () {
            Route::get('/search-customer/{mobile}', 'searchCustomer');
        })->middleware('auth:api');
        // --------------------------------------------------------- CUSTOMER -------------------------------------------------------


        // --------------------------------------------------------- REPORT GENERATION -------------------------------------------------------
        Route::controller(ReportController::class)->group(function () {

            Route::post('/transaction-report', 'transactionReport')->name('transaction.report')->middleware('isAdmin');
            Route::post('/request-report', 'requestReport')->middleware('isAdmin');
            Route::get('/re-initiate/request-report/{report_id}', 'reInititateFailedReport')->middleware('isAdmin');

            Route::post('/reports', 'getReports')->middleware('isAdmin');
            Route::get('/reports/download/{id}', 'downloadReport')->middleware('isAdmin');
            // Route::get('/reports/download-signed/{report_id}', 'downloadSigned')->name('download.signed')->middleware('signed');
        });
        // --------------------------------------------------------- REPORT GENERATION -------------------------------------------------------


        // ---------------------------------------------------------------------------  PRE DATASET IMPORT ---------------------------------------------------------------------------
        Route::controller(DatasetController::class)->group(function () {

            Route::post('/inventory-store-multiple', 'addMultipleItems');
            Route::post('/dataset', 'previewDataSet');
            Route::post('/update-cell-data', 'updateCellData');
            
            Route::post('/dataset/multiple-delete', 'multipleDeleteOption');

            Route::get('/reset-dataset', 'resetDataset');
            Route::get('/download-dataset', 'downloadDataset');

        })->middleware('auth:api');
        // ---------------------------------------------------------------------------  PRE DATASET IMPORT ---------------------------------------------------------------------------


        // ---------------------------------------------------------------------------  UPLOAD DATA  ---------------------------------------------------------------------------

        Route::controller(UploadDataController::class)->group(function () {
            Route::post('/preview/excel', 'handleExcelUpload')->middleware('isAdmin');

            Route::post('/preview/fetch/excel/data', 'fetchUploadedExcelData')->middleware('isAdmin');
            Route::post('/export-to-inventory', 'addMultipleItems');

            Route::post('/upload-data/update-cell-data', 'updateCellData');
            Route::post('/upload-data/multiple-delete', 'multipleDeleteOption');

            Route::post('/get-active-job', 'getActiveJob');

            
            Route::post('/app/preview/excel', 'app_previewUploadedExcel')->middleware('isAdmin');
            Route::post('/app/preview/fetch/excel/data', 'app_fetchExcelData')->middleware('isAdmin');
            Route::post('/app/export-to-inventory', 'app_addMultipleItems');

        })->middleware('auth:api');

        // ---------------------------------------------------------------------------  UPLOAD DATA  ---------------------------------------------------------------------------


        // ---------------------------------------------------------------------------  DOWNLOAD DATA  ---------------------------------------------------------------------------
        Route::controller(DownloadDataController::class)->group(function () {
            Route::get('/export', 'export')->middleware('isAdmin');
            Route::get('/download/sample-dataset', 'downloadPreDataset')->middleware('isAdmin');
        })->middleware('auth:api');
        // ---------------------------------------------------------------------------  DOWNLOAD DATA  ---------------------------------------------------------------------------



        // --------------------------------------------------------- SEARCHING -------------------------------------------------------
        Route::controller(SearchController::class)->group(function () {
            Route::any('/suggesstion-list', 'showProductSuggestionList');
            // Route::get('/generate-tags', 'generateTags');
        })->middleware('auth:api');
        // --------------------------------------------------------- SEARCHING -------------------------------------------------------
        
        
        // --------------------------------------------------------- EMPLOYEE OR STAFF -------------------------------------------------------
        Route::controller(EmployeeController::class)->group(function () {

            Route::post('/add-new-user', 'addNewUser')->middleware('isAdmin');
            Route::post('/update-sub-users', 'updateUserDetails')->middleware('isAdmin');
            Route::post('/all-sub-users', 'allSubUsers');
            Route::get('/all-sub-users-without-pagination', 'allSubUsersWithoutPagination');
            Route::get('/sub-users/{id}', 'getUserByID')->middleware('isAdmin');
            Route::get('/delete-sub-user/{id}', 'deleteSubUser')->middleware('isAdmin');

        })->middleware(['auth:api', 'SwitchDatabase']);
        // --------------------------------------------------------- EMPLOYEE OR STAFF -------------------------------------------------------


        // --------------------------------------------------------------------------- NOTIFICATIONS ROUTES ---------------------------------------------------------------------------
        Route::prefix('notifications')->group(function () {

            // Fetch
            Route::get('/', [NotificationController::class, 'index']);        // paginated list
            Route::get('/all', [NotificationController::class, 'all']);          // full list (bell icon)
            Route::get('/unread', [NotificationController::class, 'unread']);       // unread only
            Route::get('/unread-count', [NotificationController::class, 'unreadCount']); // badge count
            Route::get('/filter', [NotificationController::class, 'filter']);       // filter by type

            // Update
            Route::put('/mark-all-read', [NotificationController::class, 'markAllRead']); // mark all read
            Route::put('/{id}/read', [NotificationController::class, 'markRead']);    // mark one read

            // Delete
            Route::delete('/clear-all', [NotificationController::class, 'clearAll']); // delete all
            Route::delete('/{id}', [NotificationController::class, 'destroy']);   // delete one

        });
        // --------------------------------------------------------------------------- NOTIFICATIONS ROUTES ---------------------------------------------------------------------------

    });


    // SUBSCRIPTION ROUTE
    Route::group(['middleware' => ['ApiKeyCheck']], function () {

        // // --------------------------------------------------------- SUBSCRIPITION PLAN -------------------------------------------------------
        Route::controller(SubscriptionPlanController::class)->group(function () {
            Route::get('/subscription-plans', 'subscriptionPlans');
        })->middleware('auth:api');
        // // --------------------------------------------------------- SUBSCRIPITION PLAN -------------------------------------------------------

    });
    // SUBSCRIPTION ROUTE


    // --------------------------------------------------------------------------- REPORT DOWNLOAD ROUTES ---------------------------------------------------------------------------
    Route::get('/reports/download-signed/{report_id}', [ReportController::class, 'downloadSigned'])->name('india.download.signed')->middleware('signed');
    // --------------------------------------------------------------------------- REPORT DOWNLOAD ROUTES ---------------------------------------------------------------------------
    
});