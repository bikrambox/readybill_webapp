<?php

use Modules\Agent\Http\Controllers\API\RegisterController;
use Modules\Agent\Http\Controllers\API\AuthController;

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

// Route::prefix('agent')->group(function() {
//     Route::get('/', 'AgentController@index');
// });


Route::prefix('/authorized-agent')->group(function () {

    // --------------------------------------------------------------------------- ROUTE FOR ACTIVATE AGENT ---------------------------------------------------------------------------
    Route::controller(RegisterController::class)->group(function () {
        Route::get('/activate/{token}', 'activateAccount')->name('agent.activate.account');
        // Route::get('/test', 'test');
    });

        Route::controller(AuthController::class)->group(function () {
        Route::get('/verify/email/{token}', 'updateEmailAddress')->name('agent.email-update.verify');
        });
    // --------------------------------------------------------------------------- ROUTE FOR ACTIVATE AGENT ---------------------------------------------------------------------------

});