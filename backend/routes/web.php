<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
// Auth::routes();
// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// --------------------------------------------------------------------------- ARTISAN COMMAND ---------------------------------------------------------------------------

Route::get('/clear-cache', function () {
    // Clear application cache
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('optimize:clear');

    // Clear all sessions
    session()->flush();

    Cache::flush();

    echo "Done";
    
});

// --------------------------------------------------------------------------- ARTISAN COMMAND ---------------------------------------------------------------------------

// --------------------------------------------------------------------------- ARTISAN COMMAND ---------------------------------------------------------------------------


// --------------------------------------------------------------------------- TRIGGER PUSH NOTIFICATION ---------------------------------------------------------------------------

// Route::get('/push-notification', function () {
//     // Artisan::call('subscription:check-expiry');
//     Artisan::call('trigger:push-notification');
//     // Artisan::call('clear:logs');
//     echo "Done";
// });

// --------------------------------------------------------------------------- TRIGGER PUSH NOTIFICATION ---------------------------------------------------------------------------

// --------------------------------------------------------------------------- DELETE ALL ACCOUNTS ---------------------------------------------------------------------------

// use Modules\Authentication\Http\Controllers\API\DeleteController;

// Route::controller(DeleteController::class)->group(function () {
//     Route::get('/delete-all', 'deleteAllAccounts');
// });

// --------------------------------------------------------------------------- DELETE ALL ACCOUNTS ---------------------------------------------------------------------------

// --------------------------------------------------------------------------- CLEAR ROUTES ---------------------------------------------------------------------------

use Illuminate\Support\Facades\Cache;

Route::get('/clear-all', function () {

    # CLEAR LOGS
    Artisan::call('clear:logs');

    # CLEAR TABLES 
    Artisan::call('clear:tables');

    # CLEAR CACHE DATA
    Cache::flush();

    # CLEAR SESSION DATA
    session()->flush();

    # OPTIMIZE CLEAR
    Artisan::call('optimize:clear');
    echo "Done";

});


// --------------------------------------------------------------------------- CLEAR ROUTES ---------------------------------------------------------------------------


// Route::get('/lang', function () {
//     dd(App::getLocale());
//     echo "Done";
// });


use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

// Route::get('/js/lang.js', function () {
//     $locale = App::getLocale();
//     $langPath = resource_path("lang/{$locale}");

//     $files = File::allFiles($langPath);
//     $translations = [];

//     foreach ($files as $file) {
//         $filename = pathinfo($file, PATHINFO_FILENAME);
//         $translations[$filename] = require $file;
//     }

//     $output = 'window.i18n = ' . json_encode($translations, JSON_UNESCAPED_UNICODE) . ';';
//     return Response::make($output)->header('Content-Type', 'application/javascript');
// })->name('assets.lang');



// Route::get('/api/lang/{locale}', function ($locale) {
//     if (!in_array($locale, ['en', 'hi', 'bn'])) { // add your languages
//         abort(404);
//     }

//     app()->setLocale($locale);
//     session(['locale' => $locale]);

//     $langPath = resource_path("lang/{$locale}");
//     $files = File::allFiles($langPath);
//     $translations = [];

//     foreach ($files as $file) {
//         $filename = pathinfo($file, PATHINFO_FILENAME);
//         $translations[$filename] = require $file;
//     }

//     return response()->json($translations);
// });