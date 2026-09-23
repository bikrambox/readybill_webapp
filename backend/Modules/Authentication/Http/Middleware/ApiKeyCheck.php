<?php

namespace Modules\Authentication\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use Auth;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\GroceryIndia\Entities\Shop;
use Illuminate\Support\Facades\DB;

class ApiKeyCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            if (Auth::guard('api')->check()) {

                $user = Auth::guard('api')->user();

                if($user->isAgent == 0){
                    DB::setDefaultConnection($user->module_type);
                }



                // Log user details on successful authentication
                $userDetails = [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                ];

                if($user->isAgent == 1){
                    $userAssignedApiKey = $user->apiKey->key;
                }

                else if ($user->isAdmin == 1) {
                    $userAssignedApiKey = $user->apiKey->key;
                } else if ($user->isAdmin == 0) {
                    // $shop = Shop::find($user->staff->addedBy);
                    // $user = $shop->user;
                    // $userAssignedApiKey = $user->apiKey->key;

                    
                    $staff = DB::connection($user->module_type)->table('staff')->where('user_id', $user->user_id)->first();                    
                    $shop = DB::connection($user->module_type)->table('shops')->where('shop_id', $staff->addedBy)->first();

                    
                    $userAssignedApiKey = DB::connection('central')->table('api_keys')->where('user_id', $shop->user_id)->value('key');
                    
                    // dd($userAssignedApiKey);

                }

                $requestApiKey = $request->header('auth-key');

                if ($requestApiKey) {
                    try {
                        $apiKey = Crypt::decryptString($requestApiKey);
                    } catch (\Exception $e) {

                        // Log the error for invalid API key decryption
                        $this->logError($request, 'Invalid API Key Decryption', $e->getMessage(), $userDetails);

                        return response()->json([
                            "error" => __('validation.Invalid API Key'),
                            "status" => 0,
                        ], 403);
                    }
                } else {

                    // Log the error for missing API key
                    $this->logError($request, 'API Key Not Found', 'No API key provided in the request.', $userDetails);

                    return response()->json([
                        "error" => __('validation.API Key Not Found'),
                        "status" => 0,
                    ], 403);
                }

                // dd($userAssignedApiKey);

                // Compare the decrypted API key with the key from the request header
                if ($apiKey !== $userAssignedApiKey) {

                    // Log the error for mismatching API keys
                    $this->logError($request, 'API Key Mismatch', 'Decrypted API key does not match the user\'s key.', $userDetails);

                    return response()->json([
                        "error" => __('validation.API Key is not matching'),
                        "status" => 0,
                    ], 403);
                }


            } else {

                // Log the error for unauthorized access
                $this->logError($request, 'Unauthorized Access', 'User is not authenticated.', []);

                return response()->json([
                    "error" => __('validation.Unauthorized'),
                    "status" => 0,
                ], 401);
            }

            // Create log message with a line separator between entries
            $logEntry = PHP_EOL . str_repeat('-', 80) . PHP_EOL .
                "API Request at: " . now()->toDateTimeString() . PHP_EOL .
                "API Key: " . $apiKey . PHP_EOL .
                "URL: " . $request->fullUrl() . PHP_EOL .
                "Method: " . $request->method() . PHP_EOL .
                "IP Address: " . $request->ip() . PHP_EOL .
                "User-Agent: " . $request->header('User-Agent') . PHP_EOL .
            str_repeat('-', 80) . PHP_EOL . PHP_EOL;

            // Generate date-wise log file path under 'storage/logs'
            $logFilePath = storage_path('logs/api_requests_' . now()->toDateString() . '.log');

            // Append log entry to the file
            file_put_contents($logFilePath, $logEntry, FILE_APPEND);


            // Proceed with the request if the key is valid
            return $next($request);

        } catch (\Exception $e) {
            // Log any unexpected errors
            $this->logError($request, 'Unexpected Error', $e->getMessage(), $userDetails ?? []);

            return response()->json([
                "error" => __('validation.Internal Server Error'),
                "status" => 0,
            ], 500);
        }
    }

    private function logError($request, $errorType, $errorMessage, $userDetails = [])
    {
        $logEntry = PHP_EOL . str_repeat('!', 80) . PHP_EOL .
            "Error Type: " . $errorType . PHP_EOL .
            "Error Message: " . $errorMessage . PHP_EOL .
            "Occurred At: " . now()->toDateTimeString() . PHP_EOL .
            "URL: " . $request->fullUrl() . PHP_EOL .
            "Method: " . $request->method() . PHP_EOL .
            "IP Address: " . $request->ip() . PHP_EOL .
            "User-Agent: " . $request->header('User-Agent') . PHP_EOL .
            "User Details: " . json_encode($userDetails) . PHP_EOL .
        str_repeat('!', 80) . PHP_EOL . PHP_EOL;

        // Generate date-wise log file path under 'storage/logs'
        $logFilePath = storage_path('logs/api_errors_' . now()->toDateString() . '.log');

        // Append log entry to the error log file
        file_put_contents($logFilePath, $logEntry, FILE_APPEND);
    }

}
