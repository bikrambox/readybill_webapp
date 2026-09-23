<?php

namespace Modules\Authentication\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


use Modules\Authentication\Entities\ApiKey;
use Modules\Authentication\Entities\User;

use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Exception;

use Modules\Authentication\Helpers\UserHelper;
use Auth;


class ApiKeyController extends Controller
{
    public function generateAPIKey(Request $request)
    {

        if (Auth::guard('api')->check()) {

            $user = Auth::guard('api')->user();

            // GENERATE API KEY
            $api_key = UserHelper::apiKeyGenerate($user->user_id);
            // GENERATE API KEY

            return $api_key;

        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    public function updateUserAPIKey()
    {
        if (Auth::guard('api')->check()) {
            $all_api_keys = ApiKey::where('status', 1)->get();

            foreach ($all_api_keys as $key) {
                $user = User::find($key->user_id);
                // GENERATE API KEY
                $api_key = UserHelper::apiKeyGenerate($user->user_id);
                // GENERATE API KEY

                // UPDATE API KEY
                $updateKey = ApiKey::find($key->id);
                $updateKey->key = $api_key;
                $updateKey->save();
                // UPDATE API KEY
            }
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);

    }

}
