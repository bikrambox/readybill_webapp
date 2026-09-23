<?php

namespace Modules\CoreWeb\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use Modules\Core\Helpers\LanguageHelpher;
use Modules\Core\Helpers\CountryHelpher;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;

class SetCountryAndLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    
    public function handle(Request $request, Closure $next)
    {
        // // Log request details for debugging
        // Log::info('SetCountryAndLanguage Middleware', [
        //     'ip' => $request->ip(),
        //     'url' => $request->fullUrl(),
        //     'segments' => $request->segments()
        // ]);

        // ✅ CASE 1: User Logged In
        if (Auth::check()) {

            // 🚨 If detected_country_code is empty -> logout & redirect to login
            if (empty(Auth::user()->detected_country_code)) {
                // Log::warning('User missing detected_country_code, forcing logout', [
                //     'user_id' => Auth::user()->id,
                // ]);

                $user = Auth::user();
                $all_tokens = $user->tokens;

                // Iterate through each token and delete expired ones
                foreach ($all_tokens as $token) {
                    if ($token->expires_at && Carbon::now()->gt($token->expires_at)) {
                        $token->delete();
                    }
                }

                // Delete the current token
                $current_token = $user->token();
                if ($current_token) {
                    $current_token->delete();
                }

                Auth::logout();
                Session::flush();

                // Remove the cache associated with the user's mobile number
                $cacheKey = env('CACHE_KEY_PREFIX') . 'user_' . $user->mobile;
                Cache::forget($cacheKey);

                return redirect(locale_route('login'))
                    ->withErrors(['empty_detected_country_code' => 'Your account is missing country information. Please contact support.']);
            }


            $countryCode = Auth::user()->country_code;
            $detected_country_code = Auth::user()->detected_country_code;
            $languageCode = Auth::user()->lang;

            $url_country_code = strtoupper($request->segment(1, ''));


            // dd($detected_country_code);

            if ($url_country_code && $url_country_code !== strtoupper($detected_country_code)) {
                $currentSegment = $request->segment(1, null);
                $path = $request->path() === '/' || $request->path() === $currentSegment ? '' : implode('/', array_slice($request->segments(), 1));
                // Log::info('Redirecting logged-in user', [
                //     'from' => $request->fullUrl(),
                //     'to' => "/{$detected_country_code}/{$path}"
                // ]);
                return redirect()->to("/{$detected_country_code}/{$path}");
            }
        }
        // ✅ CASE 2: Guest User (Before Login)
        else {
            $countryCode = CountryHelpher::getCountryCodeFromIp($request->ip());
            // Log::info('Guest user country detection', ['countryCode' => $countryCode]);

            $url_country_code = strtolower($request->segment(1, ''));

            // // Reirect to India if country code is not 'IN'
            // if (strtoupper($url_country_code) !== 'IN') {
            //     $languageCode = config('app.locale', 'en'); // Default to 'en' for India
            //     Log::info('Redirecting guest user to India', [
            //         'from' => $request->fullUrl(),
            //         'to' => "/in/{$languageCode}"
            //     ]);
            //     return redirect()->to("/in/{$languageCode}")->send();
            // }

            // Validate Country
            if (!CountryHelpher::isValidCountryCode($countryCode)) {
                // Log::info('Invalid country code, falling back', ['countryCode' => $countryCode]);
                $countryCode = config('app.default_country', 'IN');
            }

            // // Detect default language from country
            // $languageCode = LanguageHelpher::getCountryDefaultLanguage($countryCode);

            // Detect language from url
            $languageCode = strtolower($request->segment(2, ''));

            // Validate Language
            if (!LanguageHelpher::isValidLanguage($languageCode)) {
                // Log::info('Invalid language code, falling back', ['languageCode' => $languageCode]);
                $languageCode = config('app.locale', 'en');
            }

        }

        // ✅ Store in Session
        Session::put('country_code', $countryCode);
        Session::put('language_code', $languageCode);
        // Log::info('Session updated', [
        //     'country_code' => $countryCode,
        //     'language_code' => $languageCode
        // ]);

        // ✅ Apply App Locale
        App::setLocale($languageCode);
        app()->setLocale($languageCode);

        return $next($request);
    }
    
}
