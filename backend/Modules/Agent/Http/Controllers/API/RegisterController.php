<?php

namespace Modules\Agent\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


use Modules\Core\Helpers\ResponseHelper;
use Modules\Agent\Helpers\RegisterHelper;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class RegisterController extends Controller
{
    // -------------------------------------------------------------------------------------- REGISTER FORM (EMAIL INPUT) --------------------------------------------------------------------------------------
    public function registerForm(Request $request){

        $agentRegisterFrom = new RegisterHelper($request);


        $response = $agentRegisterFrom->registerForm();

        $status = $response['status'] === 200 ? 1 : 0;
        $message = ($status == 0) ? $response['message'] : __('validation.Success');

        // Prepare response data based on the response status
        $data = ($response['status'] == 200)
            ? ['data' => $response['user'] ?? []]
            : ['errors' => $response['errors'] ?? []];

        return ResponseHelper::responseFn($status, $response['status'], $message, $data);
    }
    // -------------------------------------------------------------------------------------- REGISTER FORM (EMAIL INPUT) --------------------------------------------------------------------------------------


    // -------------------------------------------------------------------------------------- AGENT DETAILS --------------------------------------------------------------------------------------
    
    public function agentDetails(Request $request)
    {

        $agentRegisterFrom = new RegisterHelper($request);


        $response = $agentRegisterFrom->agentDetails();

        $status = $response['status'] === 200 ? 1 : 0;
        $message = ($status == 0) ? $response['message'] : __('validation.Success');

        // Prepare response data based on the response status
        $data = ($response['status'] == 200)
            ? ['data' => $response['user'] ?? []]
            : ['errors' => $response['errors'] ?? []];

        return ResponseHelper::responseFn($status, $response['status'], $message, $data);
    }
    // -------------------------------------------------------------------------------------- AGENT DETAILS --------------------------------------------------------------------------------------


    // -------------------------------------------------------------------------------------- AGENT DOCUMENTS UPLOAD --------------------------------------------------------------------------------------
    
    public function agentDocumentsUpload(Request $request)
    {

        $agentRegisterFrom = new RegisterHelper($request);


        $response = $agentRegisterFrom->agentDocumentsUpload();

        $status = $response['status'] === 200 ? 1 : 0;
        $message = ($status == 0) ? $response['message'] : __('validation.Success');

        // Prepare response data based on the response status
        $data = ($response['status'] == 200)
            ? ['data' => $response['user'] ?? []]
            : ['errors' => $response['errors'] ?? []];

        return ResponseHelper::responseFn($status, $response['status'], $message, $data);
    }
    // -------------------------------------------------------------------------------------- AGENT DOCUMENTS UPLOAD --------------------------------------------------------------------------------------
    
    // -------------------------------------------------------------------------------------- AGENT ACTIVATE ACCOUNT --------------------------------------------------------------------------------------
    
    // public function activateAccount(Request $request, $token)
    // {

    //     if (!$request->hasValidSignature()) {
    //         // return response()->view('agent::errors.invalid-signature', [], 403);
    //         return view('agent::errors.invalid-signature');
    //     }

    //     $user = DB::connection('central')->table('users')
    //         ->where('email_verification_token', $token)
    //         ->first();

    //     if (!$user) {

    //         return view('agent::errors.invalid-signature');
    //         // return response()->json([
    //         //     'status' => 400,
    //         //     'message' => 'Invalid or expired token'
    //         // ]);
    //     }

    //     DB::connection('central')->table('users')
    //         ->where('user_id', $user->user_id)
    //         ->update([
    //             'isVerified' => 1,
    //             'email_verification_token' => null,
    //             'email_verified_at' => now(),
    //             'updated_at' => now()
    //         ]);


    //     return view('agent::account-activated');

    //     // return response()->json([
    //     //     'status' => 200,
    //     //     'message' => 'Account activated successfully'
    //     // ]);
    // }

    public function activateAccount(Request $request, $token)
    {
        if (!$request->hasValidSignature()) {
            return view('agent::errors.invalid-signature', [
                'message' => 'This activation link is invalid or has been tampered with.'
            ]);
        }

        $user = DB::connection('central')->table('users')
            ->where('email_verification_token', $token)
            ->first();

        if (!$user) {
            return view('agent::errors.invalid-signature', [
                'message' => 'This activation link is expired or already used.'
            ]);
        }

        DB::connection('central')->table('users')
            ->where('user_id', $user->user_id)
            ->update([
                'isVerified' => 1,
                'email_verification_token' => null,
                'email_verified_at' => now(),
                'updated_at' => now()
            ]);

        return view('agent::account-activated', [
            'message' => 'Your account has been successfully activated.'
        ]);
    }

    // public function test(){

    //     $token = Str::random(64);
    //     $activationUrl = URL::temporarySignedRoute(
    //         'agent.activate.account',
    //         now()->addHours(24), // ⏱ 24 hours validity
    //         // now()->addMinutes(1),
    //         ['token' => $token]
    //     );

    //     return view('agent::emails.verify-new-email', [
    //         'verificationUrl' => $activationUrl
    //     ]);
    // }
    // -------------------------------------------------------------------------------------- AGENT ACTIVATE ACCOUNT --------------------------------------------------------------------------------------
    
    
    // -------------------------------------------------------------------------------------- IF ACCOUNT IS PARTIALLY REGISTERED, DELETE PREVIOUS RECORD --------------------------------------------------------------------------------------
    
    // -------------------------------------------------------------------------------------- IF ACCOUNT IS PARTIALLY REGISTERED, DELETE PREVIOUS RECORD --------------------------------------------------------------------------------------


}
