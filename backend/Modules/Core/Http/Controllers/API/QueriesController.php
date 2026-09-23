<?php

namespace Modules\Core\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


use Validator;
use Auth;
use Intervention\Image\ImageManagerStatic as Image;

use Modules\Core\Emails\QuerySubmittedMail;

use Illuminate\Support\Facades\Mail;

use Modules\Core\Entities\Queries;
use Modules\Authentication\Entities\User;
use Modules\GroceryIndia\Entities\Shop;
use Illuminate\Support\Facades\DB;

use Modules\Core\Rules\NoSpecialCharacter;
use Modules\Core\Rules\NoScriptTag;

class QueriesController extends Controller
{
    public function createQuery(Request $request)
    {

        if (Auth::guard('api')->check()) {
            try {

                $validate = Validator::make($request->all(), [
                    'title' => [
                        'required',
                        'string',
                        'max:250',
                        new NoScriptTag,
                        new NoSpecialCharacter('name')
                    ],
                    'description' => [
                        'required',
                        'string',
                        'max:500',
                        new NoScriptTag,
                        new NoSpecialCharacter('description')
                    ],
                    'attachment' => [
                        'nullable',
                        'file',
                        'mimes:jpeg,jpg,gif,bmp,png,txt,rtf,doc,docx,pdf',
                        'max:20480',
                        function ($attribute, $value, $fail) {
                            if (strtolower($value->getClientOriginalExtension()) === 'sql') {
                                $fail('SQL files are not allowed.');
                            }
                        },
                    ],

                ], [
                    'title.required' => __('queries.required', ['attribute' => __('queries.attributes.title')]),
                    'title.string' => __('queries.string', ['attribute' => __('queries.attributes.title')]),
                    'title.max' => __('queries.max', ['attribute' => __('queries.attributes.title'), 'max' => 250]),
                    'description.required' => __('queries.required', ['attribute' => __('queries.attributes.description')]),
                    'description.string' => __('queries.string', ['attribute' => __('queries.attributes.description')]),
                    'description.max' => __('queries.max', ['attribute' => __('queries.attributes.description'), 'max' => 500]),
                    'attachment.file' => __('queries.file', ['attribute' => __('queries.attributes.attachment')]),
                    'attachment.mimes' => __('queries.mimes', ['attribute' => __('queries.attributes.attachment'), 'values' => 'jpeg, jpg, gif, bmp, png, txt, rtf, doc, docx, pdf']),
                    'attachment.max' => __('queries.max', ['attribute' => __('queries.attributes.attachment'), 'max' => '20MB']),
                ]);

                if ($validate->fails()) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => __('validation.Validation Error'),
                        'data' => $validate->errors(),
                    ], 403);
                }

                $fileName = 'NA';

                // Check if a file is attached
                if ($request->hasFile('attachment')) {
                    $file = $request->file('attachment'); // Fix input name
                    $extension = strtolower($file->getClientOriginalExtension());

                    // Define supported extensions
                    $imageExtensions = ['jpeg', 'jpg', 'gif', 'bmp', 'png'];
                    $documentExtensions = ['txt', 'rtf', 'doc', 'docx', 'pdf'];

                    // Generate a unique file name
                    $fileName = time() . '_' . $file->getClientOriginalName();

                    // Define storage paths
                    $mediaFolder = 'storage/attachment';

                    // Ensure the directory exists
                    if (!file_exists($mediaFolder)) {
                        mkdir($mediaFolder, 0777, true);
                    }

                    // Full file path
                    $filePath = $mediaFolder . '/' . $fileName;

                    // Move the file to the designated folder
                    $file->move($mediaFolder, $fileName);

                    // Optimize images if applicable
                    if (in_array($extension, $imageExtensions)) {
                        $imageInstance = Image::make($filePath);

                        // Optional optimization (e.g., compress without resizing)
                        $imageInstance->save($filePath, 90); // Save with 90% quality
                    }

                }

                // Store the query details in the database
                $queryData = [
                    'user_id' => Auth::guard('api')->user()->user_id,
                    'title' => $request->title,
                    'description' => $request->description,
                    'attachment' => $fileName,
                ];

                Queries::create($queryData);

                $user = User::where('user_id', Auth::guard('api')->user()->user_id)->first();

                $entity_id = '';
                $userName = 'N/A';
                if ($user->isAdmin == 1) {
                    $entity_id = (new \DateTime($user->shop->created_at))->format('dmY') . $user->shop->shop_id;

                    $business_name = $user->shop->business_name;
                    $email = $user->shop->email;
                    $userName = $user->shop->name;

                } else if (($user->isAdmin == 0) && ($user->staff->email)) {

                    // $shop = Shop::find($user->staff->addedBy);
                    $shop = DB::connection($user->module_type)->table('shops')->where('shop_id', $user->staff->addedBy)->first();

                    $entity_id = (new \DateTime($user->created_at))->format('dmY') . $shop->shop_id;

                    $business_name = $shop->business_name;
                    $email = $user->staff->email;
                    $userName = $user->staff->name;
                }


                $queryData['entity_id'] = $entity_id;
                $queryData['userName'] = $userName;
                $queryData['shopBusinessName'] = $business_name;
                $queryData['shopEmail'] = $email ?? "NA";
                $queryData['shopMobileNumber'] = $user->mobile;


                // Send the email with the attachment
                Mail::to(env('ROOT_ADMIN_EMAIL_ADDRESS'))->send(new QuerySubmittedMail($queryData, $fileName));

                return response()->json([
                    'status' => 'success',
                    'message' => __('queries.Queries Successfully submitted'),
                ], 200);

            } catch (\Exception $e) {

                report($e);

                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.unable_to_process'),
                ], 500);
            }
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }
}
