<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Modules\Core\Helpers\LanguageHelpher;
use Illuminate\Support\Facades\Auth;

class SetApiLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {

        // Get language from Accept-Language header, query param, or session
        $countryCode = $request->header('Accept-country_code', 'in');

        
        // Supported languages (align with LanguageHelpher if needed)
        $supportedLocales = LanguageHelpher::isValidLanguage($countryCode);
        

        // Get language from Accept-Language header, query param, or session
        // $lang = $request->header('Accept-Language', $request->query('lang', Session::get('locale', 'en')));
        $lang = $request->header('Accept-Language', 'en');

        // Normalize language (e.g., 'de-DE' to 'de')
        $lang = explode('-', $lang)[0];
        // Validate language
        // if (!in_array($lang, $supportedLocales)) {
        

        if($lang == $supportedLocales){
            $lang = 'en';
        }

        // dd($lang);

        // Set locale
        App::setLocale($lang);
        Session::put('locale', $lang);
        // Store language in request attributes for consistency
        $request->attributes->add(['lang' => $lang]);
        return $next($request);

    }
}
