<?php

namespace Modules\Core\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class APISecretKeyController extends Controller
{
    public function generateApiSecret()
    {

        $length = 32;
        // return Str::random($length);

        $secretKey = Str::random($length);

        // $secretKey = "BVnBzajFQEEGBmjM6FCURGFUyI2vRMcwcg4TZBTGVfbfaBOOTDIkywEA6vfIfeDX";
        // dd(Str::random($length));

        $secretKey = env('API_SECRET_KEY', 'D5hlrdUecsGoQ84CIT5wvNvXLPW5mV3R');


        $encryptedKey = Crypt::encryptString($secretKey);

        $decryptedSecret = Crypt::decryptString($encryptedKey);

        $data = [
            'secret_key' => $secretKey,
            'encrypted_key' => $encryptedKey,
            'decrypted_key' => $decryptedSecret,
        ];

        // dd($data);

        return response()->json([
            $data
        ], 200);

    }
}
