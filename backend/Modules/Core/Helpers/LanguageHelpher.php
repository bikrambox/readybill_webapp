<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Authentication\Entities\User;

class LanguageHelpher
{

    public static function getAllLanguages()
    {
        $cacheKey = env('CACHE_KEY_PREFIX') . 'language_list';

        return Cache::remember($cacheKey, 1440, function () {
            // $path = env('APP_ENV') === 'production'
            //     ? 'assets/js/languages/language.json'
            //     : 'assets/js/languages/language.json';

            $path = app()->environment('production')
                ? 'modules/core/js/languages/language.json'  // published path
                : module_path('Core', 'Resources/assets/js/languages/language.json'); // dev path

            return json_decode(file_get_contents($path), true);

        });
    }

    public static function isValidLanguage($country_code = 'in')
    {
        $default_short_code = 'en';

        if (empty($country_code)) {
            return $default_short_code;
        }

        $country_code = strtoupper($country_code);

        // Find the country by code (case-insensitive)
        $country = collect(CountryHelpher::getAllCountries())
            ->first(fn($c) => strtoupper($c['code']) === $country_code);

        if (!$country || empty($country['national_language'])) {
            return $default_short_code;
        }

        $national_language = strtoupper($country['national_language']);

        // Find the language by national_language (case-insensitive)
        $language = collect(LanguageHelpher::getAllLanguages())
            ->first(fn($l) => strtoupper($l['language']) === $national_language);



        return $language['short_code'] ?? $default_short_code;
    }


    public static function countryLanuages($country_code = 'in')
    {

        // $countries = CountryHelpher::getAllCountries();

        // $languages = [];

        // // Check if country code is provided and not empty
        // if (!empty($country_code)) {
        //     // Find the country details based on the country code
        //     $countryDetails = collect($countries)->firstWhere('code', strtoupper($country_code));

        //     $region = $countryDetails['region'];

        //     $allLanguages = LanguageHelpher::getAllLanguages();

        //     $languages = $allLanguages[$region];
        // }
        // return $languages;

        
        $cacheKey = env('CACHE_KEY_PREFIX') . 'language_list';

        return Cache::remember($cacheKey, 1440, function () {
            // $path = env('APP_ENV') === 'production'
            //     ? 'assets/js/languages/language.json'
            //     : 'assets/js/languages/language.json';

            $path = app()->environment('production')
                ? 'modules/core/js/languages/language.json'  // published path
                : module_path('Core', 'Resources/assets/js/languages/language.json'); // dev path

            return json_decode(file_get_contents($path), true);

        });
    }


    public static function getCountryDefaultLanguage($country_code = 'in'){

        $countries = CountryHelpher::getAllCountries();

        $country_language = 'en';

        // Check if country code is provided and not empty
        if (!empty($country_code)) {
            // Find the country details based on the country code
            // $countryDetails = collect($countries)->firstWhere('code', $country_code);


            $countryDetails = collect($countries)
                ->first(fn($l) => strtoupper($l['code']) === strtoupper($country_code));

            // If a matching country is found, set the language code
            if ($countryDetails && isset($countryDetails['language_short_code'])) {
                $country_language = $countryDetails['language_short_code'];
            }
        }

        return $country_language;

    }

    public static function checkValidLanguage($lang ='en'){

        $languages = LanguageHelpher::getAllLanguages();

        $isValid = 0;

        // Check if country code is provided and not empty
        if (!empty($lang)) {
            // Find the country details based on the country code
            $languageDetails = collect($languages)->firstWhere('short_code', $lang);

            // If a matching country is found, set the dial code
            if ($languageDetails && isset($languageDetails['short_code'])) {
                $isValid = 1;
            }
        }
        return $isValid;


    }


    public static function checkValidLanguageCode($lang = 'en')
    {
        $languages = LanguageHelpher::getAllLanguages();

        // Default response format
        $response = [
            'is_valid' => 0,
            'language_code' => strtolower($lang),
            'language_name' => null,
        ];

     

        // Validate provided language code
        if (!empty($lang)) {
            $languageDetails = collect($languages)->firstWhere('short_code', strtolower($lang));


            if ($languageDetails && isset($languageDetails['short_code'])) {
                $response['is_valid'] = 1;
                $response['language_code'] = strtolower($languageDetails['short_code']);
                $response['language_name'] = $languageDetails['name'] ?? null;
                return $response;
            }
        }

        // If invalid -> set default language fallback
        $defaultLang = collect($languages)->firstWhere('short_code', 'en');  // change if needed
        if ($defaultLang) {
            $response['language_code'] = strtolower($defaultLang['short_code']);
            $response['language_name'] = $defaultLang['name'] ?? null;
        }

        

        return $response;
    }


}