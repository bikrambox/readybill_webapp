<?php

namespace Modules\Core\Helpers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;

class CommonHelpher{


    public static function getModuleName($module_type){

        if (!$module_type) {
            return null;
        }

        // Transform module_type (e.g., 'grocery_india') to module name (e.g., 'GroceryIndia')
        return str_replace('_', '', ucwords($module_type, '_'));

    }

    public static function encryptBillId($bill_id, $user_id){
        try {
            // Additional variable (e.g., prefix, type, or user identifier)
            $prefix = $user_id; // You can make this dynamic if needed

            // Secret key from .env
            $secretKey = config('app.key');

            // Generate HMAC hash of the bill ID + prefix to make it more unique
            $hash = hash_hmac('sha256', $prefix . '|' . $bill_id, $secretKey, true);

            // Encode in URL-safe base64 and shorten to 12 chars
            $shortCode = substr(strtr(base64_encode($hash), '+/', '-_'), 0, 12);

            // Combine prefix, shortCode, and bill_id for decoding later
            // Example: B|abCdEfGh12|123
            $final = $prefix . '|' . $shortCode . '|' . $bill_id;

            // Encode final string again to keep URL clean
            $urlSafe = strtr(base64_encode($final), '+/', '-_');
            $urlSafe = rtrim($urlSafe, '=');

            $url = url('/invoice/?' . $urlSafe);

            $response = [
                'url' => $url,
                'code' => $urlSafe,
                'prefix' => $prefix,
                'status_code' => 200,
            ];

            return $response;

        } catch (\Exception $e) {
            Log::error('Encryption failed', ['error' => $e->getMessage()]);
            // return response()->json(['error' => 'Encryption failed'], 500);
            $response = [
                'status_code' => 500,
            ];
            return $response;

        }
    }


    public static function getImageUrl($file, $baseUrl)
    {
        if (!empty($file) && $file !== 'NA') {
            return rtrim($baseUrl, '/') . '/' . ltrim($file, '/');
        }

        return asset('assets/img/user.jpg'); // fallback
    }

    public static function processPhoto(Request $request, $fieldName, $folderName)
    {
        $imageName = '';

        if ($request->hasFile($fieldName)) {

            // $mediaFolder = storage_path('app/public/' . $folderName);
            $mediaFolder = 'storage/' . $folderName;

            // Create folder if not exists
            if (!file_exists($mediaFolder)) {
                mkdir($mediaFolder, 0777, true);
            }

            $file = $request->file($fieldName);

            // Clean filename (optional but recommended)
            $originalName = preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $imageName = time() . '_' . $originalName;

            // Move file
            $file->move($mediaFolder, $imageName);

            $imagePath = $mediaFolder . '/' . $imageName;

            // Resize using Intervention Image
            $image = Image::make($imagePath);

            $originalWidth = $image->width();
            $originalHeight = $image->height();

            $newWidth = 200;
            $newHeight = ceil($originalHeight * ($newWidth / $originalWidth));

            $image->resize($newWidth, $newHeight)->save($imagePath);
        }

        return $imageName;
    }

}