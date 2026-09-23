<?php


use Illuminate\Support\Facades\Route;
use Modules\CoreWeb\Http\Controllers\GroceryGermanyController;


// Route::prefix('/grocery')->group(function () {
//     Route::group(['middleware' => ['ChecShopSubscripitionForWeb']], function () {

//         Route::group(['middleware' => ['webAdminCheck']], function () {

//             Route::get('/sell', [GroceryGermanyController::class, 'sell'])->name('sell');
            
//         });

//        // dd('Grocery Germany Under Constructions');

//     });
// });



Route::prefix('/grocery')->group(function () {
    Route::group(['middleware' => ['ChecShopSubscripitionForWeb','auth']], function () {

        Route::group(['middleware' => ['webAdminCheck']], function () {

            Route::get('/add-item', [GroceryGermanyController::class, 'addItem'])->name('add.item');
            Route::get('/users', [GroceryGermanyController::class, 'addUser'])->name('add.user');

            Route::get('/settings', [GroceryGermanyController::class, 'settings'])->name('settings');
            Route::get('/subscription', [GroceryGermanyController::class, 'subscription'])->name('subscription');

            Route::get('/generate-report', [GroceryGermanyController::class, 'generateReport'])->name('generate.report');

        });

        Route::get('/how-to-upload', [GroceryGermanyController::class, 'dataUploadInstruction'])->name('data.upload.instruction');

        Route::get('/home', [GroceryGermanyController::class, 'index'])->name('home');

        Route::get('/sell', [GroceryGermanyController::class, 'sell'])->name('sell');
        Route::get('/refund', [GroceryGermanyController::class, 'refund'])->name('refund');
        Route::get('/items', [GroceryGermanyController::class, 'items'])->name('items');
        Route::get('/transactions', [GroceryGermanyController::class, 'transactions'])->name('transactions');
        Route::get('/all-users', [GroceryGermanyController::class, 'allUsers'])->name('all.user');

        Route::get('/profile', [GroceryGermanyController::class, 'profile'])->name('profile');

        Route::get('/support', [GroceryGermanyController::class, 'support'])->name('support');

        Route::get('/dataset', [GroceryGermanyController::class, 'dataset'])->name('dataset');
        Route::get('/upload-data', [GroceryGermanyController::class, 'uploadData'])->name('upload.data');
        
        Route::get('/invoice/{bill_id}', [GroceryGermanyController::class, 'viewInvoice'])->name('view.invoice');

    });

});