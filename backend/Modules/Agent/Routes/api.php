<?php

use Illuminate\Http\Request;

use Modules\Agent\Http\Controllers\API\RegisterController;
use Modules\Agent\Http\Controllers\API\LoginController;
use Modules\Agent\Http\Controllers\API\AssignSubscriptionController;
use Modules\Agent\Http\Controllers\API\AuthController;
use Modules\Agent\Http\Controllers\API\ChangePasswordController;
use Modules\Agent\Http\Controllers\API\AuthorizedAgentsController;


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

// Route::middleware('auth:api')->get('/agent', function (Request $request) {
//     return $request->user();
// });


Route::prefix(env('API_VERSION', 'v1') . '/authorized-agent')->group(function () {


    // --------------------------------------------------------------------------- ROUTE FOR REGISTRATION ---------------------------------------------------------------------------
    Route::controller(RegisterController::class)->group(function () {

        Route::post('/register/register-email', 'registerForm');
        Route::post('/register/agent-details', 'agentDetails');
        Route::post('/register/agent-document-upload', 'agentDocumentsUpload');
        // Route::get('/activate/{token}', 'activateAccount')->name('agent.activate.account');
    });

    // --------------------------------------------------------------------------- ROUTE FOR REGISTRATION ---------------------------------------------------------------------------

    // --------------------------------------------------------------------------- ROUTE FOR LOGIN ---------------------------------------------------------------------------
    Route::controller(LoginController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/login-validation', 'loginValidation');
    });
    // --------------------------------------------------------------------------- ROUTE FOR LOGIN ---------------------------------------------------------------------------
    
    
    // --------------------------------------------------------------------------- ROUTE FOR AUTHORIZED AGENT LIST ---------------------------------------------------------------------------
    Route::controller(AuthorizedAgentsController::class)->group(function () {
        Route::post('/list', 'authorizedAgentsList');
    });
    // --------------------------------------------------------------------------- ROUTE FOR AUTHORIZED AGENT LIST ---------------------------------------------------------------------------



    Route::group(['middleware' => ['ApiKeyCheck']], function () {

        // --------------------------------------------------------------------------- ROUTE FOR PROFILE ---------------------------------------------------------------------------
        Route::controller(AuthController::class)->group(function () {
            Route::get('/logout', 'logout');
            Route::get('/profile-details', 'getUserDetails');
            Route::post('/update-profile', 'updateProfile');
        });
        // --------------------------------------------------------------------------- ROUTE FOR PROFILE ---------------------------------------------------------------------------
       
       
        // --------------------------------------------------------------------------- ROUTE FOR MAKE SUBSCRIPTION ---------------------------------------------------------------------------
        Route::controller(AssignSubscriptionController::class)->group(function () {
            Route::post('/shop-search', 'searchShop');
            Route::post('/assign-subscription', 'assignSubscription');
            Route::post('/earnings', 'earnings');
            Route::get('/subscription-plans', 'getPlans');
            Route::get('/earning/agent-details/{subscription_commission_id}', 'getAgentDetails');
        });
        // --------------------------------------------------------------------------- ROUTE FOR MAKE SUBSCRIPTION ---------------------------------------------------------------------------
        
        
        // --------------------------------------------------------------------------- ROUTE FOR CHANGE PASSWORD ---------------------------------------------------------------------------
        Route::controller(ChangePasswordController::class)->prefix('change-password')->group(function () {
            Route::post('/send-otp', 'sendOTP');
            Route::post('/update-password', 'updatePassword');
        });
        // --------------------------------------------------------------------------- ROUTE FOR CHANGE PASSWORD ---------------------------------------------------------------------------

    })->middleware('auth:api');





});