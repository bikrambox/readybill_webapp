<?php

namespace Modules\Agent\Helpers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use Modules\Authentication\Entities\User;
use Illuminate\Support\Facades\DB;

class CommonHelpher
{

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

    // // -------------------------------------------------------------------------------------- IF ACCOUNT IS PARTIALLY REGISTERED, DELETE PREVIOUS RECORD --------------------------------------------------------------------------------------
    // public static function deletePreviousUserRecord($email)
    // {
    //     $user = User::where('email', $email)->first();

    //     if ($user && !$user->agentDetail()->exists()) {
    //         $user->delete();
    //     }
    // }
    // // -------------------------------------------------------------------------------------- IF ACCOUNT IS PARTIALLY REGISTERED, DELETE PREVIOUS RECORD --------------------------------------------------------------------------------------



    public static function checkUserAgentDetails($email)
    {
        $user = User::where('email', $email)
            ->where('isAgent', 1)
            ->with('agentDetail') // eager load
            ->first();

        // User not found
        if (!$user) {
            return [
                'user_id' => 0,
                'checkAgent' => 0,
                'checkAgentDetails' => 0,
                'checkAgentDocuments' => 0,
            ];
        }

        $agentDetail = $user->agentDetail;

        // Agent detail not found
        if (!$agentDetail) {
            return [
                'user_id' => $user->user_id,
                'checkAgent' => 0,
                'checkAgentDetails' => 0,
                'checkAgentDocuments' => 0,
            ];
        }

        // Check documents
        $hasDocuments = !(
            $agentDetail->photo === 'NA' ||
            $agentDetail->aadhar_card === 'NA' ||
            $agentDetail->qr_code === 'NA'
        );

        return [
            'user_id' => $user->user_id,
            'agent_details_id' => $agentDetail->id,
            'checkAgent' => 1,
            'checkAgentDetails' => 1,
            'checkAgentDocuments' => $hasDocuments ? 1 : 0,
        ];
    }

}