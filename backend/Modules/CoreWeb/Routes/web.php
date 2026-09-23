<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Helpers\CountryHelpher;
use Modules\Core\Helpers\LanguageHelpher;

use Modules\CoreWeb\Http\Controllers\HomeController;
use Modules\CoreWeb\Http\Controllers\IndexController;
use Modules\CoreWeb\Http\Controllers\Auth\RegisterController;
use Modules\CoreWeb\Http\Controllers\Auth\LoginController;


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

Route::get('/', function () {
    abort(403);
});

// --------------------------------------------------------------------------- SHARE INVOICE ---------------------------------------------------------------------------

// Route::get('/invoice/{encrypted_bill_id}', [IndexController::class, 'shareInvoice'])
//     ->name('share.invoice');

Route::get('/invoice', [IndexController::class, 'shareInvoice'])
    ->name('share.invoice');

// --------------------------------------------------------------------------- SHARE INVOICE ---------------------------------------------------------------------------


Route::get('/print/invoice/{bill_id}', [HomeController::class, 'printInvoice'])->name('print.invoice');
Route::get('/api/encrypt-bill-id/{bill_id}', [IndexController::class, 'encryptBillId'])->name('encrypt.bill_id');


// // Redirect root → detected country/lang
// Route::get('/', function () {
//     $country = session('country_code', config('app.default_country', 'in'));
//     // $lang = session('language_code', config('app.locale', 'en'));
//     $lang = LanguageHelpher::getCountryDefaultLanguage($country);


//     // if($country != 'in'){
//     //     return redirect("/in/en/");
//     // }
//     // else{
//     //     return redirect("/{$country}/{$lang}/");
//     // }

//     return redirect("/{$country}/{$lang}/");

// });


// // Handle country code only URL (e.g., /in/)
// Route::get('/{country_code}', function ($countryCode) {

//     $lang = LanguageHelpher::getCountryDefaultLanguage($countryCode);

//     // if ($countryCode != 'in') {
//     //     return redirect("/in/en/");
//     // } else {
//     //     return redirect("/{$countryCode}/{$lang}/");
//     // }


//     return redirect("/{$countryCode}/{$lang}/");

// });


// Route::middleware(['SetCountryAndLanguage'])->group(function () {

//     Route::group([
//         'prefix' => '{country}/{lang}',
//         'where' => [
//             'country' => '[A-Za-z]{2}',
//             'lang' => '[a-z]{2}'
//         ]
//     ], function () {


//         // Load central routes (common for all countries)
//         require __DIR__ . '/web/central.php';

//         $country_code = strtoupper(request()->segment(1));

//         if ($country_code) {

//             $countryFullName = CountryHelpher::getCountryName($country_code);

//             $routeFile = module_path('CoreWeb', "Routes/web/grocery" . ucfirst($countryFullName) . ".php");

//             if (file_exists($routeFile)) {
//                 require $routeFile;
//             } else {
//                 require module_path('CoreWeb', "Routes/web/groceryIndia.php");
//             }
//         }

//     });
// });

// Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// // --------------------------------------------------------------------------- ROUTE WITHOUT COUNTRY CODE ---------------------------------------------------------------------------
// // --------------------------------------------------------------------------- ROUTE FOR REGISTRATION ---------------------------------------------------------------------------

// Route::post('/register/send-otp', [RegisterController::class, 'sendOTP'])->name('register.send.otp');
// Route::post('/register/verify-otp', [RegisterController::class, 'verifyOTP'])->name('register.verify.otp');

// Route::post('/register/create-user', [RegisterController::class, 'createUser'])->name('register.create.user');
// Route::post('/register/create-shop', [RegisterController::class, 'createShop'])->name('register.create.shop');

// // --------------------------------------------------------------------------- ROUTE FOR REGISTRATION ---------------------------------------------------------------------------
// // --------------------------------------------------------------------------- ROUTE WITHOUT COUNTRY CODE ---------------------------------------------------------------------------

