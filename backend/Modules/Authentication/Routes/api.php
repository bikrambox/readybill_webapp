<?php

use Illuminate\Http\Request;

use Modules\Authentication\Http\Controllers\API\LoginController;
use Modules\Authentication\Http\Controllers\API\RegisterController;
use Modules\Authentication\Http\Controllers\API\AuthController;
use Modules\Authentication\Http\Controllers\API\PushNotificationController;
use Modules\Authentication\Http\Controllers\API\NotificationController;
use Modules\Authentication\Http\Controllers\API\DeleteController;

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

// Route::middleware('auth:api')->get('/authentication', function (Request $request) {
//     return $request->user();
// });

Route::prefix(env('API_VERSION', 'v1'))->group(function () {
    // --------------------------------------------------------------------------- ROUTE FOR REGISTRATION ---------------------------------------------------------------------------
    Route::controller(RegisterController::class)->group(function () {

        Route::post('/register/send-otp', 'sendOTP');
        Route::post('/register/verify-otp', 'verifyOTP');

        Route::post('/register/create-user', 'createUser');
        Route::post('/register/create-shop', 'createShop');

    });

    // --------------------------------------------------------------------------- ROUTE FOR REGISTRATION ---------------------------------------------------------------------------

    // --------------------------------------------------------------------------- ROUTE FOR LOGIN ---------------------------------------------------------------------------
    Route::controller(LoginController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/login-validation', 'loginValidation');
    });
    // --------------------------------------------------------------------------- ROUTE FOR LOGIN ---------------------------------------------------------------------------


    Route::group(['middleware' => ['ApiKeyCheck', 'checkShopSubscription',]], function () {

        // --------------------------------------------------------- AUTHENTICATION -------------------------------------------------------
        Route::controller(AuthController::class)->group(function () {
            
            Route::post('/update-profile', 'updateUserProfile');
            Route::post('/check-api-key', 'checkApiKey');
            Route::post('/update-mobile-number', 'updateMobileNumber');

        })->middleware(['auth:api', 'SwitchDatabase']);
        // --------------------------------------------------------- AUTHENTICATION -------------------------------------------------------


        // // --------------------------------------------------------- DELETE ACCOUNT -------------------------------------------------------
        Route::controller(DeleteController::class)->group(function () {
            Route::post('/delete-account', 'deleteAccount');
        })->middleware('auth:api');
        // // --------------------------------------------------------- DELETE ACCOUNT -------------------------------------------------------



        // // --------------------------------------------------------- PUSH NOTIFICATIONS -------------------------------------------------------
        Route::post('/set-device-token', [PushNotificationController::class, 'setDeviceToken']);
        // // --------------------------------------------------------- PUSH NOTIFICATIONS -------------------------------------------------------


        // // --------------------------------------------------------- NOTIFICATIONS -------------------------------------------------------
        Route::get('notifications', [NotificationController::class, 'notifications']);
        // // --------------------------------------------------------- NOTIFICATIONS -------------------------------------------------------

    });


    // SUBSCRIPTION ROUTE
    Route::group(['middleware' => ['ApiKeyCheck']], function () {

        // // --------------------------------------------------------- USER PROFILE -------------------------------------------------------
        Route::get('/user-detail', [AuthController::class, 'getUserDetails']);
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('logout');
        // // --------------------------------------------------------- USER PROFILE -------------------------------------------------------
    });
    // SUBSCRIPTION ROUTE

});



