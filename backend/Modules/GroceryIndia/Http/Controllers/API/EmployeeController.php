<?php

namespace Modules\GroceryIndia\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


use Illuminate\Support\Facades\Hash;
use Validator;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\ImageManagerStatic as Image;

use Illuminate\Validation\Rule;


use Modules\GroceryIndia\Entities\Staff;
use Modules\GroceryIndia\Entities\Shop;
use Modules\Authentication\Entities\User;


use Modules\Core\Helpers\ResponseHelper;
use Modules\Authentication\Helpers\UserHelper;
use Modules\Core\Helpers\LanguageHelpher;
use Modules\Core\Helpers\CountryHelpher;

use Modules\Core\Rules\PhoneNumber;
use Modules\Core\Rules\ValidCountryCode;

use Modules\Core\Rules\NoScriptTag;
use Modules\Core\Rules\NoSpecialCharacter;

class EmployeeController extends Controller
{
    public function addNewUser(Request $request)
    {

        if (Auth::guard('api')->check()) {

            $loggedInUser = User::find(Auth::guard('api')->user()->user_id);

            $shop_type = $loggedInUser->shop_type;

            $validate = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:250',
                    new NoScriptTag,
                    new NoScriptTag,
                    new NoSpecialCharacter('name')
                ],
                'email' => [
                    'nullable',
                    'string',
                    'max:250',
                    new NoScriptTag,
                    new NoSpecialCharacter('email'),
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value !== 'NA') {
                            $validator = Validator::make(['email' => $value], [
                                'email' => [
                                    'email:rfc,dns',
                                    function ($attribute, $value, $fail) use ($request) {
                                        $existsInStaff = DB::table('staff')
                                            ->where('email', $value)
                                            ->exists();

                                        $existsInShops = DB::table('shops')
                                            ->where('email', $value)
                                            ->exists();

                                        if ($existsInShops || $existsInStaff) {
                                            $fail(__('validation.The email has already registered.'));
                                        }
                                    }
                                ],
                            ]);

                            if ($validator->fails()) {
                                $fail($validator->errors()->first('email'));
                            }
                        }
                    },
                ],


                // 'mobile' => 'required|numeric|digits:10|unique:central.users',
                'mobile' => [
                    'required',
                    new PhoneNumber($request->country_code),
                    'unique:central.users',
                ],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    new NoScriptTag,
                ],
                'address' => ['required', 'max:500', new NoScriptTag, new NoSpecialCharacter('address')],
                'photo' => [
                    'nullable',
                    'image',
                    'mimes:jpeg,png,gif,jpg,heic',
                    'max:5120', // 5mb
                    function ($attribute, $value, $fail) use ($request) {
                        $user = User::find($request->user_id);
                        if ($user && $user->isAdmin == 0 && !$value) {
                            $fail('The ' . $attribute . ' field is required for non-admin users.');
                        }
                    },
                ],

                'isPhotoDelete' => [
                    'nullable',
                    'boolean',
                    function ($attribute, $value, $fail) use ($request) {
                        $user = User::find($request->user_id);
                        if ($user && $user->isAdmin == 0 && !$value) {
                            $fail('The ' . $attribute . ' field is required for non-admin users.');
                        }
                    },
                ],
                'country_code' => [
                    'required',
                    new ValidCountryCode()
                ],
            ], [
                // 'mobile.required' => 'You must enter your mobile number to continue.',
                // 'mobile.unique' => 'This mobile number is already registered.',


                'name.required' => __('employee.required', ['attribute' => __('employee.attributes.name')]),
                'name.string' => __('employee.string', ['attribute' => __('employee.attributes.name')]),
                'name.max' => __('employee.max', ['attribute' => __('employee.attributes.name'), 'max' => 250]),
                'email.string' => __('employee.string', ['attribute' => __('employee.attributes.email')]),
                'email.max' => __('employee.max', ['attribute' => __('employee.attributes.email'), 'max' => 250]),
                'email.email' => __('employee.email', ['attribute' => __('employee.attributes.email')]),
                'mobile.required' => __('employee.required', ['attribute' => __('employee.attributes.mobile')]),
                'mobile.unique' => __('employee.unique', ['attribute' => __('employee.attributes.mobile')]),
                'password.required' => __('employee.required', ['attribute' => __('employee.attributes.password')]),
                'password.string' => __('employee.string', ['attribute' => __('employee.attributes.password')]),
                'password.min' => __('employee.min', ['attribute' => __('employee.attributes.password'), 'min' => 8]),
                'password.confirmed' => __('employee.confirmed', ['attribute' => __('employee.attributes.password')]),
                'address.required' => __('employee.required', ['attribute' => __('employee.attributes.address')]),
                'address.max' => __('employee.max', ['attribute' => __('employee.attributes.address'), 'max' => 500]),
                'photo.image' => __('employee.image', ['attribute' => __('employee.attributes.photo')]),
                'photo.mimes' => __('employee.mimes', ['attribute' => __('employee.attributes.photo'), 'values' => 'jpeg, png, gif, jpg, heic']),
                'photo.max' => __('employee.max', ['attribute' => __('employee.attributes.photo'), 'max' => '5MB']),
                'isPhotoDelete.boolean' => __('employee.boolean', ['attribute' => __('employee.attributes.isPhotoDelete')]),
                'country_code.required' => __('employee.required', ['attribute' => __('employee.attributes.country_code')]),


            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Validation Error'),
                    'data' => $validate->errors(),
                ], 403);
            }

            // Start a transaction
            DB::beginTransaction();

            try {

                // store photo
                $photoName = 'NA';
                if ($request->hasFile('photo')) {

                    $mediaFolder = 'storage/photo';
                    if (!file_exists($mediaFolder)) {
                        mkdir($mediaFolder, 0777, true);
                    }

                    $image = $request->file('photo');
                    $photoName = time() . '_' . $image->getClientOriginalName();

                    // Store the image in the public/logo directory
                    $image->move('storage/photo', $photoName);

                    // Resize and optimize the image
                    $imagePath = 'storage/photo/' . $photoName;
                    $image = Image::make($imagePath);

                    // Get original image dimensions
                    $originalWidth = $image->width();
                    $originalHeight = $image->height();

                    // Calculate new height while preserving aspect ratio
                    $newWidth = 200;
                    $newHeight = ceil($originalHeight * ($newWidth / $originalWidth));

                    // Resize the image
                    $resizedImage = $image->resize($newWidth, $newHeight)
                        ->save('storage/photo/' . $photoName);
                } else if ($request->isPhotoDelete == 1) {
                    $photoName = 'NA';
                }
                // store photo

                // Create a new user
                $user = User::create([
                    // 'name' => $request->name,
                    'shop_type' => $shop_type,
                    // 'module_type' => $shop_type .'_'. strtolower($loggedInUser->country_details['name']),
                    'module_type' => $loggedInUser->module_type,
                    'mobile' => $request->mobile,
                    'password' => Hash::make($request->password),
                    'ip_address' => $request->ip(),
                    'last_logged_in' => now(),
                    'country_details' => json_encode(CountryHelpher::getCountryJson($request->country_code)),
                    'detected_country_code' => $loggedInUser->detected_country_code,
                    'country_code' => strtolower($request->country_code),
                ]);


                // Create the staff entry
                Staff::create([
                    'user_id' => $user->user_id,
                    'name' => $request->name,
                    'email' => $request->email ?? 'NA',
                    'address' => $request->address,
                    'addedBy' => $loggedInUser->shop->shop_id,
                    'photo' => $photoName,
                ]);

                // Commit the transaction if everything is successful
                DB::commit();

                // Delete cached data
                // Cache::forget('all_sub_users_' . $loggedInUser->mobile);
                Cache::store('memcached')->forget(env('CACHE_KEY_PREFIX') . 'sub_users_' . $loggedInUser->mobile);
                $cacheKey = env('CACHE_KEY_PREFIX') . 'sub_users_' . Auth::guard('api')->user()->mobile;
                Cache::store('memcached')->forget($cacheKey);

                return response()->json([
                    'status' => 'success',
                    'message' => 'New User Successfully Added',
                ], 200);

            } catch (\Exception $e) {
                // Rollback the transaction in case of any errors
                DB::rollBack();
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

    public function updateUserDetails(Request $request)
    {
        if (Auth::guard('api')->check()) {

            // // Fetch staff and user details
            // $staff = Staff::find($request->staff_id);
            // $user = $staff->user;

            // $loggedInUser = User::find(Auth::guard('api')->user()->user_id);
            // $shop_type = $loggedInUser->shop_type;

            // Validation
            $validate = Validator::make($request->all(), [
                'staff_id' => 'required|numeric|exists:staff,staff_id',
                'name' => [
                    'required',
                    'string',
                    'max:250',
                    new NoScriptTag,
                    new NoScriptTag,
                    new NoSpecialCharacter('name')
                ],

                // 'mobile' => [
                //     'required',
                //     new PhoneNumber($request->country_code),
                //     Rule::unique('central.users')->ignore($user->user_id, 'user_id'),
                // ],

                'mobile' => [
                    'required',
                    new PhoneNumber($request->country_code),
                    Rule::unique('central.users')->ignore(
                        Staff::where('staff_id', $request->staff_id)->value('user_id'),
                        'user_id'
                    ),
                ],

                'email' => [
                    'nullable',
                    'string',
                    'max:250',
                    new NoScriptTag,
                    new NoSpecialCharacter('email'),
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value !== 'NA') {
                            $validator = Validator::make(['email' => $value], [
                                'email' => [
                                    'email:rfc,dns',
                                    function ($attribute, $value, $fail) use ($request) {
                                        $existsInStaff = DB::table('staff')
                                            ->where('email', $value)
                                            ->where('staff_id', '!=', $request->staff_id)
                                            ->exists();

                                        $existsInShops = DB::table('shops')
                                            ->where('email', $value)
                                            ->exists();

                                        if ($existsInShops || $existsInStaff) {
                                            $fail(__('validation.The email has already registered.'));
                                        }
                                    }
                                ],
                            ]);

                            if ($validator->fails()) {
                                $fail($validator->errors()->first('email'));
                            }
                        }
                    },
                ],

                // 'password' => 'nullable|string|min:8',
                'password' => [
                    'nullable',
                    'string',
                    'min:8',
                    'confirmed',
                    new NoScriptTag,
                ],
                
                // 'password_confirmation' => 'required_with:password|same:password',

                'address' => ['required', 'max:500', new NoScriptTag, new NoSpecialCharacter('address')],
                'photo' => [
                    'nullable',
                    'image',
                    'mimes:jpeg,png,gif,jpg,heic',
                    'max:5120', // 5mb
                    function ($attribute, $value, $fail) use ($request) {
                        $user = User::find($request->user_id);
                        if ($user && $user->isAdmin == 0 && !$value) {
                            $fail('The ' . $attribute . ' field is required for non-admin users.');
                        }
                    },
                ],

                'isPhotoDelete' => [
                    'nullable',
                    'boolean',
                    function ($attribute, $value, $fail) use ($request) {
                        $user = User::find($request->user_id);
                        if ($user && $user->isAdmin == 0 && !$value) {
                            $fail('The ' . $attribute . ' field is required for non-admin users.');
                        }
                    },
                ],
                'country_code' => [
                    'required_if:type,send-otp',
                    new ValidCountryCode()
                ],

            ], [
                // 'mobile.required' => 'You must enter your mobile number to continue.',
                // 'mobile.unique' => 'This mobile number is already registered.',

                'staff_id.required' => __('employee.required', ['attribute' => __('employee.attributes.staff_id')]),
                'staff_id.numeric' => __('employee.numeric', ['attribute' => __('employee.attributes.staff_id')]),

                'staff_id.exists' => __('employee.exists', ['attribute' => __('employee.attributes.staff_id')]),
                'name.required' => __('employee.required', ['attribute' => __('employee.attributes.name')]),
                'name.string' => __('employee.string', ['attribute' => __('employee.attributes.name')]),
                'name.max' => __('employee.max', ['attribute' => __('employee.attributes.name'), 'max' => 250]),
                'mobile.required' => __('employee.required', ['attribute' => __('employee.attributes.mobile')]),
                'mobile.unique' => __('employee.unique', ['attribute' => __('employee.attributes.mobile')]),
                'email.string' => __('employee.string', ['attribute' => __('employee.attributes.email')]),
                'email.max' => __('employee.max', ['attribute' => __('employee.attributes.email'), 'max' => 250]),
                'email.email' => __('employee.email', ['attribute' => __('employee.attributes.email')]),
                'password.string' => __('employee.string', ['attribute' => __('employee.attributes.password')]),
                'password.min' => __('employee.min', ['attribute' => __('employee.attributes.password'), 'min' => 8]),
                'password_confirmation.required_with' => __('employee.required_with', ['attribute' => __('employee.attributes.password_confirmation'), 'values' => __('employee.attributes.password')]),
                'password_confirmation.same' => __('employee.same', ['attribute' => __('employee.attributes.password_confirmation'), 'other' => __('employee.attributes.password')]),
                'address.required' => __('employee.required', ['attribute' => __('employee.attributes.address')]),
                'address.string' => __('employee.string', ['attribute' => __('employee.attributes.address')]),
                'address.max' => __('employee.max', ['attribute' => __('employee.attributes.address'), 'max' => 500]),
                'photo.image' => __('employee.image', ['attribute' => __('employee.attributes.photo')]),
                'photo.mimes' => __('employee.mimes', ['attribute' => __('employee.attributes.photo'), 'values' => 'jpeg, png, gif, jpg, heic']),
                'photo.max' => __('employee.max', ['attribute' => __('employee.attributes.photo'), 'max' => '5MB']),
                'isPhotoDelete.boolean' => __('employee.boolean', ['attribute' => __('employee.attributes.isPhotoDelete')]),
                'country_code.required_if' => __('employee.required_if', ['attribute' => __('employee.attributes.country_code'), 'other' => __('employee.attributes.type'), 'value' => 'send-otp']),

            ]);

            // Handle validation errors
            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Validation Error'),
                    'data' => $validate->errors(),
                ], 403);
            }

            DB::beginTransaction();

            try {

                // Fetch staff and user details
                $staff = Staff::find($request->staff_id);
                $user = $staff->user;

                $loggedInUser = User::find(Auth::guard('api')->user()->user_id);
                $shop_type = $loggedInUser->shop_type;

                // store photo
                $photoName = 'NA';
                if ($request->hasFile('photo')) {

                    $mediaFolder = 'storage/photo';
                    if (!file_exists($mediaFolder)) {
                        mkdir($mediaFolder, 0777, true);
                    }

                    $image = $request->file('photo');
                    $photoName = time() . '_' . $image->getClientOriginalName();

                    // Store the image in the public/logo directory
                    $image->move('storage/photo', $photoName);

                    // Resize and optimize the image
                    $imagePath = 'storage/photo/' . $photoName;
                    $image = Image::make($imagePath);

                    // Get original image dimensions
                    $originalWidth = $image->width();
                    $originalHeight = $image->height();

                    // Calculate new height while preserving aspect ratio
                    $newWidth = 200;
                    $newHeight = ceil($originalHeight * ($newWidth / $originalWidth));

                    // Resize the image
                    $resizedImage = $image->resize($newWidth, $newHeight)
                        ->save('storage/photo/' . $photoName);
                } else if ($request->isPhotoDelete == 1) {
                    $photoName = 'NA';
                } else {
                    $photoName = $user->staff->photo;
                }
                // store photo

                // Hash the password if it's provided
                $password = $user->password;
                if (!empty($request->password)) {
                    $password = Hash::make($request->password);
                }

                // // Update the user details
                // $userData = [
                //     // 'name' => $request->name,
                //     'mobile' => $request->mobile,
                //     'password' => $password,
                //     'ip_address' => $request->ip(),
                // ];

                // DB::table('users')->where('user_id', $user->user_id)->update($userData);

                $staff = Staff::find($request->staff_id);

                // Find the user to update
                $user = User::find($staff->user->user_id);

                // Update the user
                $user->update([
                    // 'name' => $request->name,
                    'mobile' => $request->mobile,
                    'country_details' => json_encode(CountryHelpher::getCountryJson($request->country_code)),
                    'password' => $password,
                    'ip_address' => $request->ip(),
                    'detected_country_code' => $loggedInUser->detected_country_code,
                ]);


                // Update the staff details
                $staffData = [
                    'name' => $request->name,
                    'email' => $request->email ?? 'NA',
                    'address' => $request->address,
                    'photo' => $photoName,
                ];

                // Delete cached data
                DB::table('staff')->where('staff_id', $staff->staff_id)->update($staffData);

                // Commit the transaction
                DB::commit();


                Cache::store('memcached')->forget(env('CACHE_KEY_PREFIX') . 'sub_users_' . $request->mobile);
                $cacheKey = env('CACHE_KEY_PREFIX') . 'sub_users_' . Auth::guard('api')->user()->mobile;
                Cache::store('memcached')->forget($cacheKey);

                $response = [
                    'status' => 'success',
                    'message' => __('validation.User data is updated successfully.'),
                    'data' => $user,
                ];
                return response()->json($response, 201);
            } catch (\Exception $e) {
                // Rollback the transaction in case of any errors
                DB::rollBack();
                report($e);
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.unable_to_process'),
                ], 500);
            }
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized'),
        ], 401);
    }

    public function allSubUsers(Request $request)
    {
        if (Auth::guard('api')->check()) {
            try {
                $draw = isset($request['draw']) ? intval($request['draw']) : 0;
                $start = isset($request['start']) ? intval($request['start']) : 0;
                $length = isset($request['length']) ? intval($request['length']) : 10;
                $sortColumnIndex = isset($request['order'][0]['column']) ? intval($request['order'][0]['column']) : 0;
                $sortDirection = isset($request['order'][0]['dir']) ? $request['order'][0]['dir'] : 'asc';
                $searchValue = isset($request['search']['value']) ? $request['search']['value'] : '';
                $filter_option = isset($request['filter_option']) ? $request['filter_option'] : 'name';

                $user = Auth::guard('api')->user();

                if ($user->isAdmin === 1) {
                    $shop_id = $user->shop->shop_id;
                } else {
                    $shop = Shop::find($user->staff->addedBy);
                    $shop_id = $shop->shop_id;
                }

                $centralDbName = config('database.connections.central.database');

                // Define columns for sorting
                $columns = ["staff.name", "staff.email", "$centralDbName.users.mobile", "staff.address"];
                $sortColumn = $columns[$sortColumnIndex] ?? "staff.name";

                // Validate sort direction
                $sortDirection = strtolower($sortDirection) === 'desc' ? 'DESC' : 'ASC';

                // Base query using DB facade with raw SQL
                $selectQuery = "
                    SELECT staff.*, $centralDbName.users.*
                    FROM staff
                    INNER JOIN $centralDbName.users AS users ON staff.user_id = users.user_id
                    WHERE staff.addedBy = ?
                ";

                // Prepare the params array
                $params = [$shop_id];

                // Apply filtering
                if ($searchValue !== "") {
                    if ($filter_option == 'name') {
                        $selectQuery .= " AND staff.name LIKE ?";
                    } elseif ($filter_option == 'email') {
                        $selectQuery .= " AND staff.email LIKE ?";
                    } elseif ($filter_option == 'mobile') {
                        $selectQuery .= " AND users.mobile LIKE ?";
                    } elseif ($filter_option == 'address') {
                        $selectQuery .= " AND staff.address LIKE ?";
                    }
                    $params[] = "%$searchValue%";
                }

                // Add sorting and limiting
                $selectQuery .= " ORDER BY $sortColumn $sortDirection LIMIT ?, ?";
                $params[] = $start;
                $params[] = $length;

                // Execute the query
                $items = DB::select($selectQuery, $params);


                /* -------------------------------------------------
                | ADD PHOTO URL
                -------------------------------------------------*/

                $photoBaseUrl = env('PHOTO_URL', '');

                foreach ($items as $item) {

                    $photo = ($item->photo && $item->photo !== 'NA') ? $item->photo : '';
                    $imageUrl = $photo ? rtrim($photoBaseUrl, '/') . '/' . ltrim($photo, '/') : '';
                    $isPhoto = 0;

                    if ($imageUrl) {
                        if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                            $headers = @get_headers($imageUrl);
                            if ($headers && strpos($headers[0], '200') !== false) {
                                $isPhoto = 1;
                            }
                        } else {
                            if (file_exists(public_path($imageUrl))) {
                                $isPhoto = 1;
                            }
                        }
                    }

                    if (empty($photo)) {
                        $imageUrl = asset('assets/img/user.jpg');
                    }

                    $item->photo_url = $imageUrl;
                }



                // Count total records (using ? placeholder instead of named parameter)
                $totalRecordsQuery = "
                    SELECT COUNT(*) as count
                    FROM staff
                    WHERE addedBy = ?
                ";

                $totalRecordsResult = DB::select($totalRecordsQuery, [$shop_id]);
                $totalRecords = $totalRecordsResult[0]->count;

                // Count filtered records
                $recordsFilteredQuery = "
                    SELECT COUNT(*) as count
                    FROM staff
                    INNER JOIN $centralDbName.users AS users ON staff.user_id = users.user_id
                    WHERE staff.addedBy = ?
                ";

                $filteredParams = [$shop_id];

                if ($searchValue !== "") {
                    if ($filter_option == 'name') {
                        $recordsFilteredQuery .= " AND staff.name LIKE ?";
                    } elseif ($filter_option == 'email') {
                        $recordsFilteredQuery .= " AND staff.email LIKE ?";
                    } elseif ($filter_option == 'mobile') {
                        $recordsFilteredQuery .= " AND users.mobile LIKE ?";
                    } elseif ($filter_option == 'address') {
                        $recordsFilteredQuery .= " AND staff.address LIKE ?";
                    }
                    $filteredParams[] = "%$searchValue%";
                }

                $recordsFilteredResult = DB::select($recordsFilteredQuery, $filteredParams);
                $recordsFiltered = $recordsFilteredResult[0]->count;

                return response()->json([
                    'draw' => $draw,
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $recordsFiltered,
                    'data' => $items,
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

    public function allSubUsersWithoutPagination()
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            // $shop_id = $user->shop->shop_id;

            if ($user->isAdmin === 1) {
                $shop_id = $user->shop->shop_id;
            } else {
                $shop = Shop::find($user->staff->addedBy);
                $shop_id = $shop->shop_id;
            }

            $centralDbName = config('database.connections.central.database');

            // Check if the data is already cached
            $cacheKey = env('CACHE_KEY_PREFIX') . 'sub_users_' . $user->mobile;
            $cachedData = Cache::store('memcached')->get($cacheKey);

            // // Delete cache data
            // Cache::store('memcached')->forget($cacheKey);

            if ($cachedData) {
                return response()->json([
                    'status' => 'success',
                    'test' => 1,
                    'data' => $cachedData->original['data']
                ], 200);
            }

            // Execute the raw SQL query
            $selectQuery = "
            SELECT staff.*, $centralDbName.users.*
            FROM staff
            JOIN $centralDbName.users ON staff.user_id = $centralDbName.users.user_id
            WHERE staff.addedBy = :shop_id
            ";

            $items = DB::select($selectQuery, ['shop_id' => $shop_id]);


            // Decode country_details field if it exists
            foreach ($items as $key => $item) {
                if (!empty($item->country_details)) {
                    $items[$key]->country_details = json_decode($item->country_details, true);
                }
            }

            // Cache the response
            Cache::store('memcached')->put($cacheKey, response()->json([
                'status' => 'success',
                'data' => $items
            ], 200));

            return response()->json([
                'status' => 'success',
                'data' => $items
            ], 200);
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    public function getUserByID($id)
    {

        if (Auth::guard('api')->check()) {

            $staff = Staff::with(['user'])->find($id);

            // dd($staff);

            $staff['user']['country_details'] = json_decode($staff['user']['country_details'], true);


            $media_url = env('MEDIA_URL');
            if (isset($staff['photo']) && $staff['photo'] != 'NA') {
                $staff['photo_url'] = $media_url . '/photo/' . $staff['photo'];
            } else {
                $staff['photo_url'] = asset('assets/img/user.jpg');
            }


            return response()->json([
                'status' => 'success',
                'staff' => $staff
            ], 200);

        }
        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    public function deleteSubUser($id)
    {
        if (Auth::guard('api')->check()) {
            // Find the staff with the associated user
            $staff = Staff::with('user')->find($id);

            // Check if the staff record exists
            if (!$staff) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Staff not found'
                ], 404);
            }


            $cacheKey = env('CACHE_KEY_PREFIX') . 'sub_users_' . Auth::guard('api')->user()->mobile;
            Cache::store('memcached')->forget($cacheKey);

            // Check if the associated user exists
            if ($staff->user) {
                $staff->user->delete(); // Delete the associated user
            }

            // Delete the staff record
            $staff->delete();


            return response()->json([
                'status' => 'success',
                'message' => __('validation.Staff and associated user deleted successfully'),
                'staff' => $staff
            ], 200);
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

}
