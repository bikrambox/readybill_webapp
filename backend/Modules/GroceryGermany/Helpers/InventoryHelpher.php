<?php

namespace Modules\GroceryGermany\Helpers;

use Illuminate\Support\Facades\DB;

use Exception;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Validator;

use Illuminate\Support\Facades\Cache;

use Modules\GroceryGermany\Helpers\TagsHelper;

use Modules\GroceryGermany\Rules\MinimumStockAlert;
// use Modules\GroceryGermany\Rules\HsnCode;
use Modules\GroceryGermany\Rules\ValidBarcode;

class InventoryHelpher
{
    // --------------------------------------------------------------------------- ADD INVENTORY ---------------------------------------------------------------------------
    public static function addInventory($request, $preferences, $table_name, $user)
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'item_name' => 'required|unique:' . $table_name . '|regex:/[a-zA-Z]/',

                    'quantity' => $preferences->preference_quantity == 1 ? 'required|numeric|gte:0' : 'nullable|numeric|gte:0',
                    'min_stock_alert' => [
                        'nullable',
                        'numeric',
                        'gte:0',
                        new MinimumStockAlert($request->input('quantity'), $preferences->preference_quantity),
                    ],

                    'mrp' => $preferences->preference_mrp == 1 ? 'required|numeric|gt:0|max:100000' : 'nullable|numeric|gt:0|max:100000',
                    'sale_price' => [
                        'required',
                        'numeric',
                        'gt:0',
                        'max:100000',
                        function ($attribute, $value, $fail) use ($request, $preferences) {
                            $mrp = floatval($request->input('mrp', 0)); // Default to 0 if not present
            
                            if ($mrp > 0 && floatval($value) > $mrp) {
                                $fail(__('inventory.sale_price_exceeds_mrp', ['mrp' => $mrp]));
                            }
                        },
                    ],

                    'full_unit' => ['required', Rule::in(array_keys(config('german_units.units')))],
                    'short_unit' => ['required', Rule::in(array_values(config('german_units.units')))],

                    'tax1' => ['required', Rule::in(array_column(config('german_tax.taxes'), 'name'))],
                    'rate1' => ['required', Rule::in(array_column(config('german_tax.taxes'), 'value'))],


                    'barcode' => [
                        'nullable',
                        new ValidBarcode($table_name, null, $user->module_type),
                    ],
                ],
                [

                    'item_name.required' => __('inventory.required', ['attribute' => __('inventory.attributes.item_name')]),
                    'item_name.unique' => __('inventory.unique', ['attribute' => __('inventory.attributes.item_name')]),
                    'item_name.regex' => __('inventory.regex_alpha', ['attribute' => __('inventory.attributes.item_name')]),
                    'quantity.required' => __('inventory.required', ['attribute' => __('inventory.attributes.quantity')]),
                    'quantity.numeric' => __('inventory.numeric', ['attribute' => __('inventory.attributes.quantity')]),
                    'quantity.gte' => __('inventory.gte', ['attribute' => __('inventory.attributes.quantity'), 'gte' => 0]),
                    'min_stock_alert.numeric' => __('inventory.numeric', ['attribute' => __('inventory.attributes.min_stock_alert')]),
                    'min_stock_alert.gte' => __('inventory.gte', ['attribute' => __('inventory.attributes.min_stock_alert'), 'gte' => 0]),
                    'mrp.required' => __('inventory.required', ['attribute' => __('inventory.attributes.mrp')]),
                    'mrp.numeric' => __('inventory.numeric', ['attribute' => __('inventory.attributes.mrp')]),
                    'mrp.gt' => __('inventory.gt', ['attribute' => __('inventory.attributes.mrp'), 'gt' => 0]),
                    'mrp.max' => __('inventory.max_numeric', ['attribute' => __('inventory.attributes.mrp'), 'max_numeric' => 100000]),
                    'sale_price.required' => __('inventory.required', ['attribute' => __('inventory.attributes.sale_price')]),
                    'sale_price.numeric' => __('inventory.numeric', ['attribute' => __('inventory.attributes.sale_price')]),
                    'sale_price.gt' => __('inventory.gt', ['attribute' => __('inventory.attributes.sale_price'), 'gt' => 0]),
                    'sale_price.max' => __('inventory.max_numeric', ['attribute' => __('inventory.attributes.sale_price'), 'max_numeric' => 100000]),
                    'full_unit.required' => __('inventory.required', ['attribute' => __('inventory.attributes.full_unit')]),
                    'full_unit.in' => __('inventory.invalid', ['attribute' => __('inventory.attributes.full_unit')]),
                    'short_unit.required' => __('inventory.required', ['attribute' => __('inventory.attributes.short_unit')]),
                    'short_unit.in' => __('inventory.invalid', ['attribute' => __('inventory.attributes.short_unit')]),

                    'tax1.required' => __('inventory.required', ['attribute' => __('inventory.attributes.tax1')]),
                    'tax1.in' => __('inventory.invalid', ['attribute' => __('inventory.attributes.tax1')]),

                    'rate1.required' => __('inventory.required', ['attribute' => __('inventory.attributes.rate1')]),
                    'rate1.in' => __('inventory.invalid', ['attribute' => __('inventory.attributes.rate1')]),

                ]
            );
            if ($validate->fails()) {
                return [
                    'status' => 403,
                    'message' => __('validation.Validation Error'),
                    'errors' => $validate->errors(),
                ];
            }

            $mrp = $request->mrp ?? '–';
            $quantity = $request->quantity ?? '–';
            $min_stock_alert = $request->min_stock_alert ?? '–';
            $barcode = $request->barcode ?? '–';

            $item_name = self::checkSpecialCharacterFromString($request->item_name);

            $data = [
                'item_name' => $item_name,
                'quantity' => $quantity,
                'min_stock_alert' => $min_stock_alert,

                'mrp' => $mrp,
                'sale_price' => $request->sale_price,

                'full_unit' => $request->full_unit,
                'short_unit' => strtoupper($request->short_unit),

                'tax1' => $request->tax1 ?? '–',
                'rate1' => $request->rate1 ?? 0,
                
                'barcode' => $barcode,

                'tags' => TagsHelper::createOrUpdateTag($item_name),
            ];

            $result = DB::table($table_name)->insert($data);

            // Delete data from cache
            Cache::forget(env('CACHE_KEY_PREFIX') . 'items_' . $user->mobile);

            // Delete data from cache
            Cache::forget(env('CACHE_KEY_PREFIX') . 'all_items_' . $user->mobile);

            return [
                'status' => 200,
                'message' => __('validation.New Item Successfully Added'),
                'data' => $result,
            ];

        } catch (Exception $e) {
            Log::error('Error adding inventory item: ' . $e->getMessage());
            report($e);
            return [
                'status' => 500,
                'message' => __('inventory.error_adding_item'),
                'errors' => __('validation.unable_to_process'),
            ];
        }
    }
    // --------------------------------------------------------------------------- ADD INVENTORY ---------------------------------------------------------------------------


    // --------------------------------------------------------------------------- EDIT INVENTORY ---------------------------------------------------------------------------
    public static function item($id, $table_name)
    {

        try {

            $item = DB::table($table_name)->find($id);

            return [
                'status' => 200,
                'message' => '',
                'data' => $item,
            ];

        } catch (Exception $e) {
            report($e);
            return [
                'status' => 500,
                'message' => __('inventory.error_adding_item'),
                'errors' => __('validation.unable_to_process'),
            ];
        }
    }
    // --------------------------------------------------------------------------- EDIT INVENTORY ---------------------------------------------------------------------------


    // --------------------------------------------------------------------------- UPDATE INVENTORY ---------------------------------------------------------------------------
    public static function updateItem($request, $preferences, $table_name, $user)
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [

                    'id' => 'required|numeric|exists:' . $table_name,
                    // 'item_name' => 'required|unique:' . $table_name . '|regex:/[a-zA-Z]/',
                    'item_name' => 'required|unique:' . $table_name . ',item_name,' . $request->id . '|regex:/[a-zA-Z]/',

                    'quantity' => $preferences->preference_quantity == 1 ? 'required|numeric|gte:0' : 'nullable|numeric|gte:0',
                    'min_stock_alert' => [
                        'nullable',
                        'numeric',
                        'gte:0',
                        new MinimumStockAlert($request->input('quantity'), $preferences->preference_quantity),
                    ],

                    'mrp' => $preferences->preference_mrp == 1 ? 'required|numeric|gt:0|max:100000' : 'nullable|numeric|gt:0|max:100000',
                    'sale_price' => [
                        'required',
                        'numeric',
                        'gt:0',
                        'max:100000',
                        function ($attribute, $value, $fail) use ($request, $preferences) {
                            $mrp = floatval($request->input('mrp', 0)); // Default to 0 if not present
            
                            if ($mrp > 0 && floatval($value) > $mrp) {
                                $fail(__('inventory.sale_price_exceeds_mrp', ['mrp' => $mrp]));
                            }
                        },
                    ],

                    'full_unit' => ['required', Rule::in(array_keys(config('german_units.units')))],
                    'short_unit' => ['required', Rule::in(array_values(config('german_units.units')))],

                    'tax1' => ['required', Rule::in(array_column(config('german_tax.taxes'), 'name'))],
                    'rate1' => ['required', Rule::in(array_column(config('german_tax.taxes'), 'value'))],

                    'barcode' => [
                        'nullable',
                        new ValidBarcode($table_name, $request->id, $user->module_type),
                    ],
                ],
                [
                    'id.required' => __('inventory.required', ['attribute' => __('inventory.attributes.id')]),
                    'id.numeric' => __('inventory.numeric', ['attribute' => __('inventory.attributes.id')]),
                    'id.exists' => __('inventory.exists', ['attribute' => __('inventory.attributes.id')]),
                    'item_name.required' => __('inventory.required', ['attribute' => __('inventory.attributes.item_name')]),
                    'item_name.unique' => __('inventory.unique', ['attribute' => __('inventory.attributes.item_name')]),
                    'item_name.regex' => __('inventory.regex_alpha', ['attribute' => __('inventory.attributes.item_name')]),
                    'quantity.required' => __('inventory.required', ['attribute' => __('inventory.attributes.quantity')]),
                    'quantity.numeric' => __('inventory.numeric', ['attribute' => __('inventory.attributes.quantity')]),
                    'quantity.gte' => __('inventory.gte', ['attribute' => __('inventory.attributes.quantity'), 'gte' => 0]),
                    'min_stock_alert.numeric' => __('inventory.numeric', ['attribute' => __('inventory.attributes.min_stock_alert')]),
                    'min_stock_alert.gte' => __('inventory.gte', ['attribute' => __('inventory.attributes.min_stock_alert'), 'gte' => 0]),
                    'mrp.required' => __('inventory.required', ['attribute' => __('inventory.attributes.mrp')]),
                    'mrp.numeric' => __('inventory.numeric', ['attribute' => __('inventory.attributes.mrp')]),
                    'mrp.gt' => __('inventory.gt', ['attribute' => __('inventory.attributes.mrp'), 'gt' => 0]),
                    'mrp.max' => __('inventory.max_numeric', ['attribute' => __('inventory.attributes.mrp'), 'max_numeric' => 100000]),
                    'sale_price.required' => __('inventory.required', ['attribute' => __('inventory.attributes.sale_price')]),
                    'sale_price.numeric' => __('inventory.numeric', ['attribute' => __('inventory.attributes.sale_price')]),
                    'sale_price.gt' => __('inventory.gt', ['attribute' => __('inventory.attributes.sale_price'), 'gt' => 0]),
                    'sale_price.max' => __('inventory.max_numeric', ['attribute' => __('inventory.attributes.sale_price'), 'max_numeric' => 100000]),
                    'full_unit.required' => __('inventory.required', ['attribute' => __('inventory.attributes.full_unit')]),
                    'full_unit.in' => __('inventory.invalid', ['attribute' => __('inventory.attributes.full_unit')]),
                    'short_unit.required' => __('inventory.required', ['attribute' => __('inventory.attributes.short_unit')]),
                    'short_unit.in' => __('inventory.invalid', ['attribute' => __('inventory.attributes.short_unit')]),

                    'tax1.required' => __('inventory.required', ['attribute' => __('inventory.attributes.tax')]),
                    'tax1.in' => __('inventory.invalid', ['attribute' => __('inventory.attributes.tax')]),

                    'rate.required' => __('inventory.required', ['attribute' => __('inventory.attributes.rate')]),
                    'rate.in' => __('inventory.invalid', ['attribute' => __('inventory.attributes.rate')]),
                ]
            );
            if ($validate->fails()) {
                return [
                    'status' => 403,
                    'message' => __('validation.Validation Error'),
                    'errors' => $validate->errors(),
                ];
            }



            $mrp = $request->mrp ?? '–';
            $quantity = $request->quantity ?? '–';
            $min_stock_alert = $request->min_stock_alert ?? '–';
            $barcode = $request->barcode ?? '–';

            $data = [
                'item_name' => $request->item_name,
                // 'stock' => $request->stock ?? 'NA',
                'quantity' => $quantity,
                'min_stock_alert' => $min_stock_alert,

                'mrp' => $mrp,
                'sale_price' => $request->sale_price,
                'full_unit' => $request->full_unit,
                'short_unit' => strtoupper($request->short_unit),

                // 'hsn' => (preg_match('/^0+$/', $request->hsn)) ? '0' : ($request->hsn ?? '–'),

                'tax1' => $request->tax1 ?? '–',
                'rate1' => $request->rate1 ?? 0,

                'barcode' => $barcode ?? '–',

                'tags' => TagsHelper::createOrUpdateTag($request->item_name),
            ];

            $result = DB::table($table_name)->where('id', $request->id)->update($data);

            // Delete data from cache
            Cache::forget(env('CACHE_KEY_PREFIX') . 'items_' . $user->mobile);

            // Delete data from cache
            Cache::forget(env('CACHE_KEY_PREFIX') . 'all_items_' . $user->mobile);

            return [
                'status' => 200,
                'message' => __('validation.Item Updated Successfully'),
                'data' => $result
            ];


        } catch (Exception $e) {
            Log::error('Error adding inventory item: ' . $e->getMessage());
            report($e);
            return [
                'status' => 500,
                'message' => __('inventory.error_updating_item'),
                'errors' => __('validation.unable_to_process'),
            ];
        }
    }
    // --------------------------------------------------------------------------- UPDATE INVENTORY ---------------------------------------------------------------------------


    // --------------------------------------------------------------------------- VIEW INVENTORY ---------------------------------------------------------------------------
    public static function allItems($user, $preferences, $table_name)
    {

        try {

            // Check if the data is already cached
            $cacheKey = env('CACHE_KEY_PREFIX') . 'all_items_' . $user->mobile;
            $cachedData = Cache::store('memcached')->get($cacheKey);

            if ($cachedData) {
                // Handle case where cached data might be a JsonResponse from previous runs
                $cachedArray = $cachedData instanceof \Illuminate\Http\JsonResponse
                    ? $cachedData->getData(true)
                    : $cachedData;


                $responseData = [
                    'items' => $cachedArray['data'],
                    'preferences' => $$cachedArray['preferences'],
                ];

                return [
                    'status' => 200,
                    'message' => '',
                    'data' => $responseData
                ];
            }

            $items = DB::table($table_name)
                ->orderByRaw("CASE 
                    WHEN COALESCE(quantity, '') REGEXP '^[0-9]*\\.?[0-9]+$' 
                    AND COALESCE(min_stock_alert, '') REGEXP '^[0-9]*\\.?[0-9]+$' 
                    AND CAST(COALESCE(quantity, '0') AS DECIMAL(10,2)) <= CAST(COALESCE(min_stock_alert, '0') AS DECIMAL(10,2)) THEN 0 
                    ELSE 1 
                    END, 
                    CASE 
                    WHEN COALESCE(quantity, '') REGEXP '^[0-9]*\\.?[0-9]+$' THEN CAST(COALESCE(quantity, '0') AS DECIMAL(10,2)) 
                    ELSE 999999 
                    END ASC, 
                    item_name ASC")
                ->get();

            $responseData = [
                'items' => $items,
                'preferences' => $preferences,
            ];

            // Store the data array in the cache
            Cache::store('memcached')->put(env('CACHE_KEY_PREFIX') . $cacheKey, $responseData);

            // return response()->json($responseData, 200);

            return [
                'status' => 200,
                'message' => '',
                'data' => $responseData
            ];

        } catch (Exception $e) {
            report($e);
            return [
                'status' => 500,
                'message' => __('inventory.error_adding_item'),
                'errors' => __('validation.unable_to_process'),
            ];
        }
    }
    // --------------------------------------------------------------------------- VIEW INVENTORY ---------------------------------------------------------------------------



    // --------------------------------------------------------------------------- OTHER ---------------------------------------------------------------------------

    private static function checkSpecialCharacterFromString($item_name)
    {

        preg_match_all('/[^a-zA-Z0-9\s\-\+]/', $item_name, $special_chars);
        $special_chars = array_unique($special_chars[0]);

        $cleaned_name = preg_replace('/[^a-zA-Z0-9\s\-\+]/', ' ', $item_name);

        foreach ($special_chars as $char) {
            $cleaned_name = str_replace($char, ' ', $cleaned_name);
        }

        // Replace multiple whitespaces with a single whitespace
        $updated_name = preg_replace('/\s+/', ' ', $cleaned_name);

        // Trim whitespace from both ends
        $updated_name = trim($updated_name);

        return $updated_name;
    }
    // --------------------------------------------------------------------------- OTHER ---------------------------------------------------------------------------

    // --------------------------------------------------------------------------- EDIT INVENTORY ---------------------------------------------------------------------------
    public static function getDataByBarcode($barcode, $table_name)
    {

        try {

            $item = DB::table($table_name)->where('barcode', $barcode)->first();

            return [
                'status' => 200,
                'message' => '',
                'data' => $item,
            ];

        } catch (Exception $e) {
            report($e);
            return [
                'status' => 500,
                'message' => __('inventory.error_adding_item'),
                'errors' => __('validation.unable_to_process'),
            ];
        }
    }
    // --------------------------------------------------------------------------- EDIT INVENTORY ---------------------------------------------------------------------------


    // --------------------------------------------------------------------------- CEHECK BARCODE ALREADY EXSITS OR NOT ---------------------------------------------------------------------------
    public static function checkBarcodeExistsOrNot($barcode, $table_name)
    {
        try {

            $item = DB::table($table_name)
                ->where('barcode', $barcode)
                ->exists();

            return [
                'status' => 200,
                'message' => '',
                'data' => $item,
            ];

        } catch (Exception $e) {
            report($e);
            return [
                'status' => 500,
                'message' => __('inventory.error_adding_item'),
                'errors' => __('validation.unable_to_process'),
            ];
        }
    }
    // --------------------------------------------------------------------------- CEHECK BARCODE ALREADY EXSITS OR NOT ---------------------------------------------------------------------------


}