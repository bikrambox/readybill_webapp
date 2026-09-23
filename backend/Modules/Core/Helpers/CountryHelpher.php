<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Authentication\Entities\User;

class CountryHelpher
{

    // public static function getAllCountries()
    // {
    //     $cacheKey = env('CACHE_KEY_PREFIX') . 'countries_list';

    //     return Cache::remember($cacheKey, 1440, function () {

    //         // $path = env('APP_ENV') === 'production'
    //         //     ? 'assets/js/countries/countries.json'
    //         //     : 'assets/js/countries/countries.json';

    //         $path = app()->environment('production')
    //             ? 'modules/core/js/countries/countries.json'  // published path
    //             : module_path('Core', 'Resources/assets/js/countries/countries.json'); // dev path

    //         return json_decode(file_get_contents($path), true);

    //     });
    // }


    public static function getAllCountries()
    {
        $cacheKey = env('CACHE_KEY_PREFIX') . 'countries_list';

        return Cache::remember($cacheKey, 1440, function () {
            // File paths
            $basePath = app()->environment('production')
                ? 'modules/core/js/countries/'
                : module_path('Core', 'Resources/assets/js/countries/');

            // Define files you want to merge
            $files = [
                $basePath . 'asia.json',
                $basePath . 'europe.json',
                $basePath . 'others.json',
                $basePath . 'usa.json',
            ];

            $merged = [];

            foreach ($files as $file) {
                if (file_exists($file)) {
                    $data = json_decode(file_get_contents($file), true);
                    if (is_array($data)) {
                        $merged = array_merge($merged, $data);
                    }
                }
            }

            return $merged;
        });
    }

    public static function getCountryDialCode($countryCode = '')
    {
        $countries = CountryHelpher::getAllCountries(); // Fixed typo: CountryHelpher -> CountryHelper

        $dial_code = '';

        // Check if country code is provided and not empty
        if (!empty($countryCode)) {
            // Find the country details based on the country code
            $countryDetails = collect($countries)->firstWhere('code', $countryCode);

            // If a matching country is found, set the dial code
            if ($countryDetails && isset($countryDetails['dial_code'])) {
                $dial_code = $countryDetails['dial_code'];
            }
        }

        return $dial_code;
    }

    public static function getCountryName($countryCode = '')
    {

        $countries = CountryHelpher::getAllCountries();
        $country_name = 'NA';

        // Check if country code is provided and not empty
        if (!empty($countryCode)) {
            // Find the country details based on the country code
            $countryDetails = collect($countries)->firstWhere('code', $countryCode);

            // If a matching country is found, set the dial code
            if ($countryDetails && isset($countryDetails['name'])) {
                $country_name = $countryDetails['name'];
            }
        }

        return $country_name;

    }

    public static function getRegionByCountryCode($countryCode = '')
    {

        $countries = CountryHelpher::getAllCountries();
        $region = 'asia';

        // Check if country code is provided and not empty
        if (!empty($countryCode)) {
            // Find the country details based on the country code
            $countryDetails = collect($countries)->firstWhere('code', $countryCode);

            // If a matching country is found, set the dial code
            if ($countryDetails && isset($countryDetails['name'])) {
                $region = $countryDetails['region'];
            }
        }

        return $region;

    }


    public static function getCurrency($countryCode = '')
    {
        $countries = CountryHelpher::getAllCountries();
        $currency = '$';

        // Check if country code is provided and not empty
        if (!empty($countryCode)) {
            // Find the country details based on the country code
            $countryDetails = collect($countries)->firstWhere('code', $countryCode);

            // If a matching country is found, set the dial code
            if ($countryDetails && isset($countryDetails['currency_symbol'])) {
                $currency = $countryDetails['currency_symbol'];
            }
        }

        return $currency;
    }

    public static function getCountryJson($countryCode = '')
    {
        $countries = CountryHelpher::getAllCountries(); // Fixed typo: CountryHelpher -> CountryHelper

        $country_json = [];

        // Check if country code is provided and not empty
        if (!empty($countryCode)) {
            // Find the country details based on the country code
            $countryDetails = collect($countries)->firstWhere('code', $countryCode);

            // If a matching country is found, set the dial code
            if ($countryDetails) {

                $country_json['name'] = $countryDetails['name'];
                $country_json['flag'] = $countryDetails['flag'];
                $country_json['code'] = $countryDetails['code'];
                $country_json['dial_code'] = $countryDetails['dial_code'];
                $country_json['currency'] = $countryDetails['currency'];
                $country_json['currency_symbol'] = $countryDetails['currency_symbol'];
                $country_json['decimal_separator'] = $countryDetails['decimal_separator'];
                $country_json['national_language'] = $countryDetails['national_language'];
                $country_json['language_short_code'] = $countryDetails['language_short_code'];
                $country_json['region'] = $countryDetails['region'];

            }
        }

        return $country_json;
    }

    public static function checkCountryCode($country_details, $input_country_code)
    {
        // Ensure $country_details is an array
        if (is_string($country_details)) {
            $country_details = json_decode($country_details, true);
        }

        // Check if $country_details is valid and contains 'code'
        if (!is_array($country_details) || !isset($country_details['code'])) {
            return false;
        }

        return $country_details['code'] === $input_country_code;
    }

    public static function getDialCodeFromCountryJson($country_details)
    {

        $country_details = json_decode($country_details, true);
        return $country_details['dial_code'];

    }

    public static function getCountryCodeFromCountryJson($country_details)
    {
        $country_details = json_decode($country_details, true);
        return $country_details['code'];
    }

    public static function getCountryCodeFromIp($ip)
    {
        // Handle localhost IPs
        if ($ip == '127.0.0.1' || $ip == '::1') {
            return 'in'; // Return 'in' for India as per new logic
        }

        try {
            // Make API call to ip-api.com with a timeout
            $response = Http::timeout(10)->get("http://ip-api.com/json/{$ip}");

            if ($response->failed()) {
                Log::error("ip-api.com request failed for IP {$ip}: " . $response->body());
                return 'us'; // Fallback to USA
            }

            $data = $response->json();

            if (!isset($data['status']) || $data['status'] !== 'success') {
                Log::error("Invalid ip-api.com response for IP {$ip}: " . json_encode($data));
                return 'us'; // Fallback to USA
            }

            $countryCode = $data['countryCode'] ?? '';

            // Validate country code against available countries
            $countries = self::getAllCountries(); // Assumes this method exists
            $countryExists = collect($countries)->contains('code', $countryCode);

            if (!$countryExists) {
                return 'us'; // Fallback to USA
            }

            return strtolower($countryCode);
        } catch (\Exception $e) {
            Log::error("Failed to connect to ip-api.com for IP {$ip}: " . $e->getMessage());
            return 'us'; // Fallback to USA
        }
    }

    public static function getUserCountryCode($user_id)
    {
        $user = User::find($user_id);

        if ($user->country_code == '') {
            $code = json_decode($user->country_details)->code;
            $user->country_code = strtolower($code);
            $user->save();
        }
        return $user->country_code;
    }

    public static function updateCountryAndLanguage($user_id)
    {

        $country_json = [];


        $user = User::find($user_id);

        $countries = CountryHelpher::getAllCountries();

        $countryCode = json_decode($user->country_details)->code;



        // Check if country code is provided and not empty
        if (!empty($countryCode)) {
            // Find the country details based on the country code
            $countryDetails = collect($countries)->firstWhere('code', $countryCode);

            // If a matching country is found, set the dial code
            if ($countryDetails) {

                $country_json['name'] = $countryDetails['name'];
                $country_json['flag'] = $countryDetails['flag'];
                $country_json['code'] = $countryDetails['code'];
                $country_json['dial_code'] = $countryDetails['dial_code'];
                $country_json['currency'] = $countryDetails['currency'];
                $country_json['currency_symbol'] = $countryDetails['currency_symbol'];
                $country_json['decimal_separator'] = $countryDetails['decimal_separator'];
                $country_json['national_language'] = $countryDetails['national_language'];
                $country_json['language_short_code'] = $countryDetails['language_short_code'];
                $country_json['region'] = $countryDetails['region'];
            }
        }

        $user->country_code = strtolower($country_json['code']);
        $user->lang = strtolower($country_json['language_short_code']);
        $user->country_details = json_encode($country_json);
        $user->save();
    }

    public static function isValidCountryCode($country_code = 'us')
    {

        $countries = CountryHelpher::getAllCountries();

        $isValid = 0;

        // Check if country code is provided and not empty
        if (!empty($country_code)) {
            // Find the country details based on the country code
            $countryDetails = collect($countries)->firstWhere('code', strtoupper($country_code));

            // If a matching country is found, set the dial code
            if ($countryDetails && isset($countryDetails['dial_code'])) {
                $isValid = 1;
            }
        }
        return $isValid;
    }

    public static function checkValidCountryCode($country_code = 'IN')
    {
        $countries = CountryHelpher::getAllCountries();

        // Default response
        $response = [
            'is_valid' => 0,
            'country_code' => strtolower($country_code),
            'dial_code' => null,
        ];

        // Check if country code is provided
        if (!empty($country_code)) {
            // Search in existing list
            $countryDetails = collect($countries)->firstWhere('code', strtoupper($country_code));

            if ($countryDetails && isset($countryDetails['dial_code'])) {
                $response['is_valid'] = 1;
                $response['dial_code'] = strtolower($countryDetails['dial_code']);
                return $response;
            }
        }

        // If invalid -> return default country
        $defaultCountry = collect($countries)->firstWhere('code', 'IN'); // change if needed
        if ($defaultCountry) {
            $response['country_code'] = strtolower($defaultCountry['code']);
            $response['dial_code'] = $defaultCountry['dial_code'];
        }

        return $response;
    }


    public static function getCountryRegion($country_details)
    {
        if (!is_string($country_details)) {
            return 'India'; // Default to India if input is not a string
        }

        $decoded = json_decode($country_details);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return 'India'; // Default to India if JSON decoding fails
        }

        return isset($decoded->region) ? $decoded->region : 'India'; // Default to India if region is not set
    }

}