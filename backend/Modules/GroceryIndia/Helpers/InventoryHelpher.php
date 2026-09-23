<?php

namespace Modules\GroceryIndia\Helpers;

use Illuminate\Support\Facades\DB;

use Exception;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Validator;

use Illuminate\Support\Facades\Cache;

use Modules\GroceryIndia\Helpers\TagsHelper;

use Modules\GroceryIndia\Rules\MinimumStockAlert;
use Modules\GroceryIndia\Rules\HsnCode;
use Modules\GroceryIndia\Rules\ValidBarcode;
use Illuminate\Support\Str;
use Modules\Core\Rules\NoScriptTag;

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

                    'sku' => [
                        $preferences->preference_sku == 1 ? 'required' : 'nullable',
                        'unique:' . $table_name . ',sku',
                        'string',
                        'max:50',
                        new NoScriptTag
                    ],

                    'category_id' => [
                        $preferences->preference_sku == 1 ? 'required' : 'nullable',
                        'integer',
                        'exists:categories,id'
                    ],

                    'quantity' => $preferences->preference_quantity == 1 ? 'required|integer|gte:0|max:100000' : 'nullable|integer|gte:0|max:100000',
                    'min_stock_alert' => [
                        'nullable',
                        'integer',
                        'gte:0',
                        'max:100000',
                        new MinimumStockAlert($request->input('quantity'), $preferences->preference_quantity),
                    ],
                    'purchase_price' => $preferences->preference_purchase_price == 1 ? 'required|numeric|gte:0|max:100000' : 'nullable|numeric|gte:0|max:100000',
                    'mrp' => $preferences->preference_mrp == 1 ? 'required|numeric|gt:0|max:100000' : 'nullable|numeric|gte:0|max:100000',
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

                    'full_unit' => ['required', Rule::in(array_keys(config('india_units.units')))],
                    'short_unit' => ['required', Rule::in(array_values(config('india_units.units')))],

                    'tax1' => [
                        'required',
                        'required_with:rate1',
                        function ($attribute, $value, $fail) use ($request) {
                            $tax2 = $request->input('tax2');

                            if ($tax2 !== null && $value === $tax2) {
                                $fail(__('inventory.tax_duplicate'));
                            }
                        },
                    ],

                    // 'rate1' => 'required|required_with:tax1|gte:0|between:0,100',
                    'rate1' => 'required|required_with:tax1|numeric|gte:0|lte:100',
                    'tax2' => [
                        'nullable',
                        'required_with:rate2',
                        function ($attribute, $value, $fail) use ($request) {
                            $tax1 = $request->input('tax1');

                            if ($tax1 !== null && $value === $tax1) {
                                $fail(__('inventory.tax_duplicate'));
                            }
                        },
                    ],

                    // 'rate2' => 'nullable|required_with:tax2|gte:0|between:0,100',
                    'rate2' => 'required|required_with:tax2|numeric|gte:0|lte:100',
                    'hsn' => [
                        $preferences->preference_hsn == 1 ? 'required' : 'nullable',
                        new HsnCode($preferences->preference_hsn == 1),
                    ],

                    'barcode' => [
                        'nullable',
                        new NoScriptTag,
                        new ValidBarcode($table_name, null, $user->module_type),
                    ],

                ],
                [

                    'sku.required' => __('inventory.required', ['attribute' => __('inventory.attributes.sku')]),
                    'sku.unique' => __('inventory.unique', ['attribute' => __('inventory.attributes.sku')]),
                    'sku.string' => __('inventory.string', ['attribute' => __('inventory.attributes.sku')]),
                    'sku.max' => __('inventory.max', ['attribute' => __('inventory.attributes.sku')]),
                    
                    'category_id.required' => __('inventory.required', ['attribute' => __('inventory.attributes.category_id')]),
                    'category_id.integer' => __('inventory.integer', ['attribute' => __('inventory.attributes.category_id')]),
                    'category_id.exists' => __('inventory.exists', ['attribute' => __('inventory.attributes.category_id')]),


                    'item_name.required' => __('inventory.required', ['attribute' => __('inventory.attributes.item_name')]),
                    'item_name.unique' => __('inventory.unique', ['attribute' => __('inventory.attributes.item_name')]),
                    'item_name.regex' => __('inventory.regex_alpha', ['attribute' => __('inventory.attributes.item_name')]),
                    'quantity.required' => __('inventory.required', ['attribute' => __('inventory.attributes.quantity')]),
                    'quantity.numeric' => __('inventory.numeric', ['attribute' => __('inventory.attributes.quantity')]),
                    'quantity.gte' => __('inventory.gte', ['attribute' => __('inventory.attributes.quantity'), 'gte' => 0]),
                    'quantity.max' => __('inventory.max', ['attribute' => __('inventory.attributes.quantity'), 'max' => 100000]),
                    'min_stock_alert.numeric' => __('inventory.numeric', ['attribute' => __('inventory.attributes.min_stock_alert')]),
                    'min_stock_alert.gte' => __('inventory.gte', ['attribute' => __('inventory.attributes.min_stock_alert'), 'gte' => 0]),

                    'purchase_price.required' => __('inventory.required', ['attribute' => __('inventory.attributes.purchase_price')]),
                    'purchase_price' => __('inventory.numeric', ['attribute' => __('inventory.attributes.purchase_price')]),
                    'purchase_price.gt' => __('inventory.gt', ['attribute' => __('inventory.attributes.purchase_price'), 'gt' => 0]),
                    'purchase_price.max' => __('inventory.max_numeric', ['attribute' => __('inventory.attributes.purchase_price'), 'max_numeric' => 100000]),

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
                    'tax1.required_with' => __('inventory.required_with', ['attribute' => __('inventory.attributes.tax1'), 'values' => __('inventory.attributes.rate1')]),
                    'rate1.required' => __('inventory.required_tax_rate', ['attribute' => __('inventory.attributes.rate1'), 'tax' => 'tax1']),
                    'rate1.required_with' => __('inventory.required_with', ['attribute' => __('inventory.attributes.rate1'), 'values' => __('inventory.attributes.tax1')]),
                    'rate1.gte' => __('inventory.gte', ['attribute' => __('inventory.attributes.rate1'), 'gte' => 0]),
                    'rate1.between' => __('inventory.between_tax_rate', ['attribute' => __('inventory.attributes.rate1'), 'tax' => 'tax1']),
                    'tax2.required_with' => __('inventory.required_with', ['attribute' => __('inventory.attributes.tax2'), 'values' => __('inventory.attributes.rate2')]),
                    'rate2.required_with' => __('inventory.required_tax_rate', ['attribute' => __('inventory.attributes.rate2'), 'tax' => 'tax2']),
                    'rate2.gte' => __('inventory.gte', ['attribute' => __('inventory.attributes.rate2'), 'gte' => 0]),
                    'rate2.between' => __('inventory.between_tax_rate', ['attribute' => __('inventory.attributes.rate2'), 'tax' => 'tax2']),
                    'hsn.required' => __('inventory.required', ['attribute' => __('inventory.attributes.hsn')]),
                ]
            );
            if ($validate->fails()) {
                return [
                    'status' => 403,
                    'message' => __('validation.Validation Error'),
                    'errors' => $validate->errors(),
                ];
            }

            $sku = $request->sku ?? NULL;
            $category_id = $request->category_id ?? NULL;
            $purchase_price = $request->purchase_price ?? 0;
            $mrp = $request->mrp ?? 0;
            $quantity = $request->quantity ?? 0;
            $min_stock_alert = $request->min_stock_alert ?? 0;
            $barcode = $request->barcode ?? '–';

            $item_name = self::checkSpecialCharacterFromString($request->item_name);


            $data = [
                'sku' => $sku,
                'category_id' => $category_id,
                'item_name' => $item_name,
                'quantity' => $quantity,
                'min_stock_alert' => $min_stock_alert,

                'purchase_price' => $purchase_price,
                'mrp' => $mrp,
                'sale_price' => $request->sale_price,

                'full_unit' => $request->full_unit,
                'short_unit' => strtoupper($request->short_unit),

                'hsn' => (preg_match('/^0+$/', $request->hsn)) ? '0' : ($request->hsn ?? '–'),

                'tax1' => $request->tax1 ?? '–',
                'rate1' => $request->rate1 ?? 0,

                'tax2' => $request->tax2 ?? '–',
                'rate2' => $request->rate2 ?? 0,

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
            // Log::error('Error adding inventory item: ' . $e->getMessage());
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

                    'sku' => [
                        $preferences->preference_sku == 1 ? 'required' : 'nullable',
                        'unique:' . $table_name . ',sku,' . $request->id,
                        'string',
                        'max:50',
                        new NoScriptTag
                    ],

                    'category_id' => [
                        $preferences->preference_sku == 1 ? 'required' : 'nullable',
                        'integer',
                        'exists:categories,id'
                    ],

                    'quantity' => $preferences->preference_quantity == 1 ? 'required|integer|gte:0|max:100000' : 'nullable|integer|gte:0|max:100000',
                    'min_stock_alert' => [
                        'nullable',
                        'integer',
                        'gte:0',
                        'max:100000',
                        new MinimumStockAlert($request->input('quantity'), $preferences->preference_quantity),
                    ],
                    'purchase_price' => $preferences->preference_purchase_price == 1 ? 'required|numeric|gte:0|max:100000' : 'nullable|numeric|gte:0|max:100000',
                    'mrp' => $preferences->preference_mrp == 1 ? 'required|numeric|gt:0|max:100000' : 'nullable|numeric|gt:0|max:100000',
                    'sale_price' => [
                        'required',
                        'numeric',
                        'gt:0',
                        'max:100000',
                        function ($attribute, $value, $fail) use ($request, $preferences) {
                            $mrp = floatval($request->input('mrp', 0)); // Default to 0 if not present
            
                            if ($mrp > 0 && floatval($value) > $mrp) {
                                $fail("Sale price cannot be greater than MRP ($mrp).");
                            }
                        },
                    ],

                    'full_unit' => ['required', Rule::in(array_keys(config('india_units.units')))],
                    'short_unit' => ['required', Rule::in(array_values(config('india_units.units')))],

                    'tax1' => [
                        'required',
                        'required_with:rate1',
                        function ($attribute, $value, $fail) use ($request) {
                            $tax2 = $request->input('tax2');

                            if ($tax2 !== null && $value === $tax2) {
                                $fail(__('inventory.tax_duplicate'));
                            }
                        },
                    ],

                    'rate1' => 'required|required_with:tax1|gte:0|between:0,100',
                    'tax2' => [
                        'nullable',
                        'required_with:rate2',
                        function ($attribute, $value, $fail) use ($request) {
                            $tax1 = $request->input('tax1');

                            if ($tax1 !== null && $value === $tax1) {
                                $fail(__('inventory.tax_duplicate'));
                            }
                        },
                    ],

                    'rate2' => 'nullable|required_with:tax2|gte:0|between:0,100',
                    'hsn' => [
                        $preferences->preference_hsn == 1 ? 'required' : 'nullable',
                        new HsnCode($preferences->preference_hsn == 1),
                    ],

                    'barcode' => [
                        'nullable',
                        new NoScriptTag,
                        new ValidBarcode($table_name, $request->id, $user->module_type),
                    ],
                ],
                [

                    'sku.required' => __('inventory.required', ['attribute' => __('inventory.attributes.sku')]),
                    'sku.unique' => __('inventory.unique', ['attribute' => __('inventory.attributes.sku')]),
                    'sku.string' => __('inventory.string', ['attribute' => __('inventory.attributes.sku')]),
                    'sku.max' => __('inventory.max', ['attribute' => __('inventory.attributes.sku')]),

                    'category_id.required' => __('inventory.required', ['attribute' => __('inventory.attributes.category_id')]),
                    'category_id.integer' => __('inventory.integer', ['attribute' => __('inventory.attributes.category_id')]),
                    'category_id.exists' => __('inventory.exists', ['attribute' => __('inventory.attributes.category_id')]),


                    'id.required' => __('inventory.required', ['attribute' => __('inventory.attributes.id')]),
                    'id.numeric' => __('inventory.numeric', ['attribute' => __('inventory.attributes.id')]),
                    'id.exists' => __('inventory.exists', ['attribute' => __('inventory.attributes.id')]),
                    'item_name.required' => __('inventory.required', ['attribute' => __('inventory.attributes.item_name')]),
                    'item_name.unique' => __('inventory.unique', ['attribute' => __('inventory.attributes.item_name')]),
                    'item_name.regex' => __('inventory.regex_alpha', ['attribute' => __('inventory.attributes.item_name')]),
                    'quantity.required' => __('inventory.required', ['attribute' => __('inventory.attributes.quantity')]),
                    'quantity.numeric' => __('inventory.numeric', ['attribute' => __('inventory.attributes.quantity')]),
                    'quantity.gte' => __('inventory.gte', ['attribute' => __('inventory.attributes.quantity'), 'gte' => 0]),
                    'quantity.max' => __('inventory.max', ['attribute' => __('inventory.attributes.quantity'), 'max' => 100000]),
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
                    'tax1.required_with' => __('inventory.required_with', ['attribute' => __('inventory.attributes.tax1'), 'values' => __('inventory.attributes.rate1')]),
                    'rate1.required' => __('inventory.required_tax_rate', ['attribute' => __('inventory.attributes.rate1'), 'tax' => 'tax1']),
                    'rate1.required_with' => __('inventory.required_with', ['attribute' => __('inventory.attributes.rate1'), 'values' => __('inventory.attributes.tax1')]),
                    'rate1.gte' => __('inventory.gte', ['attribute' => __('inventory.attributes.rate1'), 'gte' => 0]),
                    'rate1.between' => __('inventory.between_tax_rate', ['attribute' => __('inventory.attributes.rate1'), 'tax' => 'tax1']),
                    'tax2.required_with' => __('inventory.required_with', ['attribute' => __('inventory.attributes.tax2'), 'values' => __('inventory.attributes.rate2')]),
                    'rate2.required_with' => __('inventory.required_tax_rate', ['attribute' => __('inventory.attributes.rate2'), 'tax' => 'tax2']),
                    'rate2.gte' => __('inventory.gte', ['attribute' => __('inventory.attributes.rate2'), 'gte' => 0]),
                    'rate2.between' => __('inventory.between_tax_rate', ['attribute' => __('inventory.attributes.rate2'), 'tax' => 'tax2']),
                    'hsn.required' => __('inventory.required', ['attribute' => __('inventory.attributes.hsn')]),

                ]
            );
            if ($validate->fails()) {
                return [
                    'status' => 403,
                    'message' => __('validation.Validation Error'),
                    'errors' => $validate->errors(),
                ];
            }


            $sku = $request->sku ?? NULL;
            $category_id = $request->category_id ?? NULL;
            $purchase_price = $request->purchase_price ?? 0;
            $mrp = $request->mrp ?? 0;
            $quantity = $request->quantity ?? 0;
            $min_stock_alert = $request->min_stock_alert ?? 0;
            $barcode = $request->barcode ?? '–';

            $data = [
                'sku' => $sku,
                'category_id' => $category_id,
                
                'item_name' => $request->item_name,
                // 'stock' => $request->stock ?? 'NA',
                'quantity' => $quantity,
                'min_stock_alert' => $min_stock_alert,

                'purchase_price' => $purchase_price,
                'mrp' => $mrp,
                'sale_price' => $request->sale_price,
                'full_unit' => $request->full_unit,
                'short_unit' => strtoupper($request->short_unit),

                'hsn' => (preg_match('/^0+$/', $request->hsn)) ? '0' : ($request->hsn ?? '–'),

                'tax1' => $request->tax1 ?? '–',
                'rate1' => $request->rate1 ?? 0,

                'tax2' => $request->tax2 ?? '–',
                'rate2' => $request->rate2 ?? 0,

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
            // Log::error('Error adding inventory item: ' . $e->getMessage());

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

    // --------------------------------------------------------------------------- SKU ---------------------------------------------------------------------------

    public static function generateSkuFromSequence($item, $category='None')
    {
        $item = collect(explode(' ', $item))
            ->map(fn($word) => strtoupper(substr(preg_replace('/\W/', '', $word), 0, 3)))
            ->implode('-');

        $category = strtoupper(substr(preg_replace('/\W/', '', $category), 0, 3));

        // clean 3-digit safe numeric code (no dot issue)
        $timePart = substr((string) ((int) (microtime(true) * 1000)), -2);
        $randomPart = random_int(0, 9);

        $uniqueCode = $timePart . $randomPart;

        return "{$item}-{$category}-{$uniqueCode}";
    }

    // public static function generateSkuFromSequence($item, $category = 'None')
    // {
    //     $item = collect(explode(' ', $item))
    //         ->map(fn($word) => strtoupper(substr(preg_replace('/\W/', '', $word), 0, 3)))
    //         ->implode('-');

    //     $category = strtoupper(substr(preg_replace('/\W/', '', $category), 0, 3));

    //     $uniqueCode = strtoupper(Str::random(8));

    //     return "{$item}-{$category}-{$uniqueCode}";
    // }

    // --------------------------------------------------------------------------- SKU ---------------------------------------------------------------------------

}