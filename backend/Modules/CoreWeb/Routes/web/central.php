<?php

use Illuminate\Support\Facades\Route;


// Route::get('/home', function () {
//     return redirect(locale_route('index'));
// })->name('home');


Route::get('/dataUploadInstruction', function () {
    return view('dataUploadInstruction');
});


Route::get('/home', [Modules\CoreWeb\Http\Controllers\IndexController::class, 'index'])->name('home');
Route::get('/', [Modules\CoreWeb\Http\Controllers\IndexController::class, 'index'])->name('index');

Route::get('/contact', [Modules\CoreWeb\Http\Controllers\IndexController::class, 'contact'])->name('contact');
Route::get('/agents', [Modules\CoreWeb\Http\Controllers\IndexController::class, 'agents'])->name('agents');
Route::get('/about', [Modules\CoreWeb\Http\Controllers\IndexController::class, 'about'])->name('about');
Route::get('/privacy-policy', [Modules\CoreWeb\Http\Controllers\IndexController::class, 'privacyPolicy'])->name('privacy.policy');
Route::get('/terms-and-conditions', [Modules\CoreWeb\Http\Controllers\IndexController::class, 'termsAndConditions'])->name('terms.and.conditions');

Auth::routes();

Route::get('/login', [Modules\CoreWeb\Http\Controllers\Auth\LoginController::class, 'index'])->name('login');

// --------------------------------------------------------------------------- ROUTE FOR REGISTRATION ---------------------------------------------------------------------------

Route::get('/register', [Modules\CoreWeb\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register');

// Route::post('/register/send-otp', [Modules\CoreWeb\Http\Controllers\Auth\RegisterController::class, 'sendOTP'])->name('register.send.otp');
// Route::post('/register/verify-otp', [Modules\CoreWeb\Http\Controllers\Auth\RegisterController::class, 'verifyOTP'])->name('register.verify.otp');

// Route::post('/register/create-user', [Modules\CoreWeb\Http\Controllers\Auth\RegisterController::class, 'createUser'])->name('register.create.user');
// Route::post('/register/create-shop', [Modules\CoreWeb\Http\Controllers\Auth\RegisterController::class, 'createShop'])->name('register.create.shop');

// --------------------------------------------------------------------------- ROUTE FOR REGISTRATION ---------------------------------------------------------------------------


Route::get('/forgot-password/{mobile_number}', [Modules\CoreWeb\Http\Controllers\IndexController::class, 'forgotPassword'])->name('forgot.password');


// --------------------------------------------------------------------------- DELETE ACCOUNT ---------------------------------------------------------------------------
Route::get('/delete-account', [Modules\CoreWeb\Http\Controllers\DeleteUserController::class, 'deleteAccount']);
Route::post('/user/check-delete', [Modules\CoreWeb\Http\Controllers\DeleteUserController::class, 'checkAndDelete']);

// --------------------------------------------------------------------------- DELETE ACCOUNT ---------------------------------------------------------------------------


// --------------------------------------------------------------------------- ARTISAN COMMAND ---------------------------------------------------------------------------
// Route::get('/clear-cache', function () {
//     // Clear application cache
//     Artisan::call('cache:clear');
//     Artisan::call('config:clear');
//     Artisan::call('optimize:clear');

//     // Clear all sessions
//     session()->flush();

//     Cache::flush();

//     echo "Done";
// });


// Route::get('/cache-data', function () {

//     $memcached = Cache::getMemcached();
//     $allKeys = $memcached->getAllKeys();
//     // dd($allKeys);

//     $value = Cache::get('countries_list');
//     dd($value);
// });

// Route::get('/push-notification', function () {
//     // Artisan::call('subscription:check-expiry');
//     // Artisan::call('trigger:push-notification');
//     Artisan::call('clear:logs');
//     echo "Done";
// });


// use Illuminate\Support\Facades\Log;

// Route::get('/test-log', function () {
//     Log::info('Testing log creation');
//     return 'Log created';
// });

// --------------------------------------------------------------------------- ARTISAN COMMAND ---------------------------------------------------------------------------

use Illuminate\Support\Facades\Cache;

Route::get('/cache/all', function () {
    $cacheData = [];
    // $driver = config('cache.default'); // Should be 'memcached'
    $driver = 'memcached';

    if ($driver !== 'memcached') {
        return response()->json(['error' => 'This route only supports Memcached driver'], 400);
    }

    try {
        // Get the Memcached instance from the cache store
        $memcached = Cache::getStore()->getMemcached();

        // Get all slabs (data segments) from Memcached
        $slabs = $memcached->getStats();

        foreach ($slabs as $server => $stats) {
            if (!empty($stats)) {
                // Attempt to get all keys (requires Memcached PECL extension and appropriate permissions)
                $keys = $memcached->getAllKeys(); // Note: May not work in all setups
                if ($keys) {
                    foreach ($keys as $key) {
                        // Remove the Laravel cache prefix (e.g., 'laravel_cache:')
                        $cleanKey = str_replace(config('cache.prefix') . ':', '', $key);
                        // Retrieve the value using the Cache facade
                        $value = Cache::get($cleanKey);
                        if ($value !== null) {
                            $cacheData[$cleanKey] = $value;
                        }
                    }
                } else {
                    return response()->json(['error' => 'Unable to retrieve keys from Memcached'], 500);
                }
            }
        }
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to access Memcached: ' . $e->getMessage()], 500);
    }

    return response()->json([
        'driver' => $driver,
        'cache_data' => $cacheData,
    ]);
});


Route::get('/php-info', function () {
    phpinfo();
});



// --------------------------------------------------------------------------- CHANGE PASSWORD ---------------------------------------------------------------------------
Route::prefix('/grocery')->group(function () {
    Route::get('/change-password', [Modules\CoreWeb\Http\Controllers\HomeController::class, 'changePassword'])->name('change.password');
});
// --------------------------------------------------------------------------- CHANGE PASSWORD ---------------------------------------------------------------------------

// Route::get('/php-info', function () {
//     // phpinfo();
//     // dd(\Modules\Core\Helpers\LanguageHelpher::getAllLanguages());
//     dd(\Modules\Core\Helpers\LanguageHelpher::isValidLanguage());
// });