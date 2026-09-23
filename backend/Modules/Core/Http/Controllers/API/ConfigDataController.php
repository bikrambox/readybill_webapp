<?php

namespace Modules\Core\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\Log;

use Modules\Core\Helpers\CountryHelpher;
use Modules\Core\Helpers\LanguageHelpher;
use Modules\Core\Helpers\ResponseHelper;
use Validator;

class ConfigDataController extends Controller
{
    public function configData()
    {

        $data = [
            'india_units' => config('india_units.units'),
            'german_units' => config('german_units.units'),
            'india_tax' => config('india_tax.taxes'),
            'german_tax' => config('german_tax.taxes'),
            'resend_countdown' => env('COUNTDOWN'),
            'indian_states' => config('states.states'),
        ];

        return ResponseHelper::responseFn(1, 200, 'Data', $data);
    }

    public function getAllCountries()
    {

        $countries = CountryHelpher::getAllCountries();
        return response()->json($countries);
    }

    public function getCountryCode()
    {
        $ip = request()->ip();

        // Handle localhost IPs
        if ($ip == '127.0.0.1' || $ip == '::1') {

            $all_languages = LanguageHelpher::countryLanuages('IN');

            return response()->json([
                'country' => 'India',
                'countryCode' => 'IN',
                'region' => 'Assam',
                'city' => 'Guwahati',
                'timezone' => 'Asia/Kolkata',
                'dialCode' => '+91',
                'all_languages' => $all_languages,
                'national_language' => 'en',
                'flag' => '🇮🇳',
            ]);
        }

        try {
            // Make API call to ip-api.com with a timeout
            $response = Http::timeout(10)->get("http://ip-api.com/json/{$ip}");

            if ($response->failed()) {
                Log::error("ip-api.com request failed for IP {$ip}: " . $response->body());
                // Fallback to USA
                $countries = CountryHelpher::getAllCountries();

                $all_languages = LanguageHelpher::countryLanuages('US');

                return response()->json([
                    'country' => 'USA',
                    'countryCode' => 'US',
                    'region' => '',
                    'city' => '',
                    'timezone' => 'America/New_York',
                    'dialCode' => collect($countries)->firstWhere('code', 'US')['dial_code'] ?? '+1',
                    'all_languages' => $all_languages,
                    'national_language' => 'en',
                    'flag' => '🇺🇸',
                ]);
            }

            $data = $response->json();

            if (!isset($data['status']) || $data['status'] !== 'success') {
                Log::error("Invalid ip-api.com response for IP {$ip}: " . json_encode($data));
                // Fallback to USA
                $countries = CountryHelpher::getAllCountries();

                $all_languages = LanguageHelpher::countryLanuages('US');

                return response()->json([
                    'country' => 'USA',
                    'countryCode' => 'US',
                    'region' => '',
                    'city' => '',
                    'timezone' => 'America/New_York',
                    'dialCode' => collect($countries)->firstWhere('code', 'US')['dial_code'] ?? '+1',
                    'all_languages' => $all_languages,
                    'national_language' => 'en',
                    'flag' => '🇺🇸',
                ]);
            }

            $countries = CountryHelpher::getAllCountries();
            $countryCode = $data['countryCode'] ?? '';

            $countryExists = collect($countries)->contains('code', $countryCode);

            if (!$countryExists) {

                $all_languages = LanguageHelpher::countryLanuages('US');

                return response()->json([
                    'country' => 'USA',
                    'countryCode' => 'US',
                    'region' => $data['regionName'] ?? '',
                    'city' => $data['city'] ?? '',
                    'timezone' => $data['timezone'] ?? '',
                    'dialCode' => collect($countries)->firstWhere('code', 'US')['dial_code'] ?? '+1',
                    'all_languages' => $all_languages,
                    'national_language' => 'en',
                    'flag' => '🇺🇸',
                ]);
            }

            $countryDetails = collect($countries)->firstWhere('code', $countryCode);

            $all_languages = LanguageHelpher::countryLanuages($countryCode);

            return response()->json([
                'country' => $data['country'] ?? 'Unknown',
                'countryCode' => $countryCode,
                'region' => $data['regionName'] ?? '',
                'city' => $data['city'] ?? '',
                'timezone' => $data['timezone'] ?? '',
                'dialCode' => $countryDetails['dial_code'] ?? '',
                'all_languages' => $all_languages ?? [],
                'national_language' => $countryDetails['language_short_code'] ?? '',
                'flag' => $countryDetails['flag'] ?? '',
                
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to connect to ip-api.com for IP {$ip}: " . $e->getMessage());
            // Fallback to USA
            $countries = CountryHelpher::getAllCountries();

            $all_languages = LanguageHelpher::countryLanuages('US');

            return response()->json([
                'country' => 'USA',
                'countryCode' => 'US',
                'region' => '',
                'city' => '',
                'timezone' => 'America/New_York',
                'dialCode' => collect($countries)->firstWhere('code', 'US')['dial_code'] ?? '+1',
                'all_languages' => $all_languages,
                'national_language' => 'en',
                'flag' => '🇺🇸',
            ]);
        }
    }

    public function checkCountryAndLanguageCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_code' => 'required|string|max:2',
            'language_code' => 'required|string|max:2',
        ], [
            'country_code.required' => __('validation.required', ['attribute' => __('validation.attributes.country_code')]),
            'country_code.string' => __('validation.string', ['attribute' => __('validation.attributes.country_code')]),
            'country_code.max' => __('validation.max.string', ['attribute' => __('validation.attributes.country_code'), 'max' => 2]),

            'language_code.required' => __('validation.required', ['attribute' => __('validation.attributes.language_code')]),
            'language_code.string' => __('validation.string', ['attribute' => __('validation.attributes.language_code')]),
            'language_code.max' => __('validation.max.string', ['attribute' => __('validation.attributes.language_code'), 'max' => 2]),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Validate and get response structures
        $country = CountryHelpher::checkValidCountryCode($request->country_code);
        $language = LanguageHelpher::checkValidLanguageCode($request->language_code);

        return response()->json([
            'status' => 'success',
            'country' => $country,
            'language' => $language,
        ]);
    }



}
