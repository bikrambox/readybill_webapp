<?php


use Illuminate\Support\Facades\Route;
use Modules\CoreWeb\Http\Controllers\GroceryIndiaController;



Route::prefix('/grocery')->group(function () {
    Route::group(['middleware' => ['ChecShopSubscripitionForWeb','auth']], function () {

        Route::group(['middleware' => ['webAdminCheck']], function () {

            Route::get('/add-item', [GroceryIndiaController::class, 'addItem'])->name('add.item');
            Route::get('/users', [GroceryIndiaController::class, 'addUser'])->name('add.user');

            Route::get('/settings', [GroceryIndiaController::class, 'settings'])->name('settings');
            Route::get('/subscription', [GroceryIndiaController::class, 'subscription'])->name('subscription');

            Route::get('/generate-report', [GroceryIndiaController::class, 'generateReport'])->name('generate.report');

        });

        Route::get('/how-to-upload', [GroceryIndiaController::class, 'dataUploadInstruction'])->name('data.upload.instruction');


        Route::get('/home', [GroceryIndiaController::class, 'index'])->name('home');

        Route::get('/sell', [GroceryIndiaController::class, 'sell'])->name('sell');
        Route::get('/refund', [GroceryIndiaController::class, 'refund'])->name('refund');
        Route::get('/items', [GroceryIndiaController::class, 'items'])->name('items');
        Route::get('/transactions', [GroceryIndiaController::class, 'transactions'])->name('transactions');
        Route::get('/all-users', [GroceryIndiaController::class, 'allUsers'])->name('all.user');

        Route::get('/profile', [GroceryIndiaController::class, 'profile'])->name('profile');

        Route::get('/support', [GroceryIndiaController::class, 'support'])->name('support');

        Route::get('/dataset', [GroceryIndiaController::class, 'dataset'])->name('dataset');
        Route::get('/upload-data', [GroceryIndiaController::class, 'uploadData'])->name('upload.data');

    });
    
});