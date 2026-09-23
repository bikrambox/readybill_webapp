<?php

namespace Modules\Authentication\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\Hash;
use Validator;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

use Modules\Authentication\Entities\Staff;
use Modules\Authentication\Entities\Shop;
use Modules\Core\Entities\OTP;
use Modules\Authentication\Entities\User;
use Modules\Core\Entities\Subscription;

use Modules\Core\Helpers\ResponseHelper;
use Modules\Authentication\Helpers\UserHelper;
use Modules\Authentication\Helpers\LoginHelper;

class LoginController extends Controller
{
    // ------------------------------------------------------------- LOGIN FUNCTIONALITY -------------------------------------------------------------
    public function login(Request $request)
    {

        $shopLogin = new LoginHelper($request);

        $response = $shopLogin->login();


        $status = (($response['code'] == 200) || ($response['code'] == 201)) ? 1 : 0;
        // $message = $status ? $response['message'] : 'Shop Login Successfully' ;
        $message = $response['message'];

        // Prepare response data based on the response status
        $data = (($response['code'] == 200) || ($response['code'] == 201))
            ? [$response['user'] ?? []]
            : ['errors' => $response['errors'] ?? []];

        if (isset($data[0]['user']['country_details'])) {
            $data[0]['user']['country_details'] = json_decode($data[0]['user']['country_details']);
        }

        // dd(json_decode($data[0]['user']['country_details']));

        return ResponseHelper::responseFn($status, $response['code'], $message, $data);

    }

    // ------------------------------------------------------------- LOGIN FUNCTIONALITY -------------------------------------------------------------
    public function loginValidation(Request $request)
    {

        $shopLogin = new LoginHelper($request);

        $validator = $shopLogin->loginValidate();


        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => __('validation.Invalid input'),
                'errors' => $validator->errors(),
                'user' => null,
            ], 400);
        }

        $response = $shopLogin->checkConditions();


        if ($response['code'] != 200) {

            return response()->json([
                'status' => $response['status'],
                'message' => $response['message'],
                'user' => $response['user'],
                'code' => $response['code'],
            ], $response['code']);
        }

        return response()->json([
            'status' => 'success',
            'message' => $response['message'],
            'user' => $response['user'],
            'code' => $response['code'],
        ], $response['code']);
    }

}
