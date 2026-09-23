<?php

namespace Modules\Core\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

use Modules\Authentication\Entities\User;

use Modules\Core\Helpers\LanguageHelpher;

use Validator;
use Illuminate\Validation\Rule;
use Auth;

use Modules\Core\Helpers\ResponseHelper;

class LanguageController extends Controller
{

    public function allLanguages(){

        $allLanguages = LanguageHelpher::getAllLanguages();

        return ResponseHelper::responseFn(1, 200, 'All Languages', $allLanguages);
    }

    public function switchLanguage(Request $request)
    {

        if (Auth::guard('api')->check()) {

            $lang = $request->input('lang', 'en');



            $isValidLanguage = LanguageHelpher::checkValidLanguage($lang);

            // dd($isValidLanguage);

            if ($isValidLanguage) {
                Session::put('locale', $lang);
                App::setLocale($lang);
            } else {
                $lang = 'en';
            }

            $user = User::find(Auth::guard('api')->user()->user_id);

            $user->lang = $lang;
            $user->save();

            return response()->json([
                'status' => 'success',
            ]);

        } else {

            $lang = $request->input('lang', 'en');

            $isValidLanguage = LanguageHelpher::checkValidLanguage($lang);


            if ($isValidLanguage) {
                Session::put('locale', $lang);
                App::setLocale($lang);
                app()->setLocale($lang);
            } else {
                $lang = 'en';
            }

            return response()->json([
                'status' => 'success',
            ]);

        }

        // return response()->json([
        //     'status' => 'failed',
        //     'message' => __('validation.Unauthorized')
        // ], 401);


    }
}