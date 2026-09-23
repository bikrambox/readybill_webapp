<?php

namespace Modules\Authentication\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

use Modules\Authentication\Entities\PushNotificationToken;

class PushNotificationController extends Controller
{
    public function setDeviceToken(Request $request)
    {
        // Check authentication
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Unauthorized')
            ], 401);
        }

        // Validate request data
        $validator = Validator::make($request->all(), [
            'device_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Validation Error'),
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::guard('api')->user();
        $deviceToken = $request->device_token;

        // Use DB query builder with 'central' connection
        $exists = DB::connection('central')
            ->table('push_notification_tokens')
            ->where('device_token', $deviceToken)
            ->exists();

        if (!$exists) {
            DB::connection('central')
                ->table('push_notification_tokens')
                ->insert([
                    'user_id' => $user->user_id,
                    'device_token' => $deviceToken,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => __('validation.Device Token Successfully Stored')
        ], 200);
    }

}
