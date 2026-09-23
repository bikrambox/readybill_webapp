<?php

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\API\ConfigDataController;
use Modules\Core\Http\Controllers\API\LanguageController;
use Modules\Core\Http\Controllers\API\QueriesController;
use Modules\Core\Http\Controllers\API\OTPController;
use Modules\Core\Http\Controllers\API\ChangePasswordController;
use Modules\Core\Http\Controllers\API\ContactController;
use Modules\Core\Http\Controllers\API\APISecretKeyController;
use Modules\Core\Http\Controllers\API\ShareInvoiceController;
use Modules\Core\Http\Controllers\API\TranslationController;
use Modules\Core\Http\Controllers\API\ScriptController;

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

// Route::middleware('auth:api')->get('/core', function (Request $request) {
//     return $request->user();
// });

Route::prefix(env('API_VERSION', 'v1'))->group(function () {
    
    // ---------------------------------------------------------------------------  CONFIG DATA ROUTE ---------------------------------------------------------------------------
    Route::get('config-data', [ConfigDataController::class, 'configData']);
    Route::get('countries-json', [ConfigDataController::class, 'getAllCountries']);
    Route::get('country-code', [ConfigDataController::class, 'getCountryCode']);

    Route::post('check-country-language-code', [ConfigDataController::class, 'checkCountryAndLanguageCode']);
    // ---------------------------------------------------------------------------  CONFIG DATA ROUTE ---------------------------------------------------------------------------


    // --------------------------------------------------------------------------- SWITCH LANGUAGE ROUTE ---------------------------------------------------------------------------
    Route::post('/switch-language', [LanguageController::class, 'switchLanguage'])->name('api.switch.language');
    // --------------------------------------------------------------------------- SWITCH LANGUAGE ROUTE ---------------------------------------------------------------------------


    // // --------------------------------------------------------- QUERIES -------------------------------------------------------
    Route::post('create-query', [QueriesController::class, 'createQuery']);
    // // --------------------------------------------------------- QUERIES -------------------------------------------------------


    // --------------------------------------------------------- OTP -------------------------------------------------------
    Route::controller(OTPController::class)->group(function () {
        Route::post('/generate-verify-otp', 'handleOtp');
        Route::post('/resend-otp', 'resendOtp');
    })->middleware('auth:api');
    // --------------------------------------------------------- OTP -------------------------------------------------------


    // --------------------------------------------------------- UPDATE PASSWORD -------------------------------------------------------
    Route::controller(ChangePasswordController::class)->group(function () {
        Route::post('/update-password', 'updatePassword');
    })->middleware('auth:api');
    // --------------------------------------------------------- UPDATE PASSWORD -------------------------------------------------------


    // --------------------------------------------------------------------------- CONTACT ROUTES ---------------------------------------------------------------------------
    Route::post('contact-submit', [ContactController::class, 'submitForm']);
    // --------------------------------------------------------------------------- CONTACT ROUTES ---------------------------------------------------------------------------



    // // --------------------------------------------------------------------------- API SECRET KEY ROUTES ---------------------------------------------------------------------------
    // Route::get('generate-api-secret', [APISecretKeyController::class, 'generateApiSecret']);
    // // --------------------------------------------------------------------------- API SECRET KEY ROUTES ---------------------------------------------------------------------------



    // --------------------------------------------------------------------------- SEND INVOICE IN SMS ---------------------------------------------------------------------------
    Route::post('send-invoice-sms', [ShareInvoiceController::class, 'sendInvoiceInSMS']);
    // --------------------------------------------------------------------------- SEND INVOICE IN SMS ---------------------------------------------------------------------------
    
    
    // --------------------------------------------------------------------------- TRANSLATION ---------------------------------------------------------------------------
    Route::get('/all-lanaguages', [LanguageController::class, 'allLanguages']);
    Route::get('/translations/{locale}', [TranslationController::class, 'getTranslations']);
    // --------------------------------------------------------------------------- TRANSLATION ---------------------------------------------------------------------------


    // --------------------------------------------------------------------------- SCRIPT ROUTES ---------------------------------------------------------------------------
    Route::get('/add-barcode', [ScriptController::class, 'addBarcode']);
    Route::get('/add-barcode-to-dataset', [ScriptController::class, 'addBarcodeToDatset']);
    Route::get('/add-purchase-price', [ScriptController::class, 'addPurchasePrice']);
    Route::get('/add-sku', [ScriptController::class, 'addSku']);
    Route::get('/generate-sku', [ScriptController::class, 'generateSKUforExistingItems']);
    // Route::get('/generate-bill', [ScriptController::class, 'generateBills']);
    // --------------------------------------------------------------------------- SCRIPT ROUTES ---------------------------------------------------------------------------


});