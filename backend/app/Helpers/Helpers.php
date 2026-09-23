<?php

if (!function_exists('locale_route')) {
    function locale_route($name, $parameters = []) {
        $country = session('country_code', config('app.default_country', 'IN'));
        $lang = session('language_code', config('app.locale', 'en'));

        return route($name, array_merge([$country, $lang], $parameters));
    }
}
