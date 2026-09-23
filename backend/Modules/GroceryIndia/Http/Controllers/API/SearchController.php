<?php

namespace Modules\GroceryIndia\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

use Modules\GroceryIndia\Helpers\TableNameHelper;

class SearchController extends Controller
{
    // ---------------------------------------------------------------------------- UPDATED SEARCH CODE WITH FULL TEXT INDEXING AND SOUNDEX ------------------------------------------------------------------------------

    // public function showProductSuggestionList(Request $request)
    // {
    //     if (!Auth::guard('api')->check()) {
    //         return response()->json([
    //             'status' => 'failed',
    //             'message' => __('validation.Unauthorized')
    //         ], 401);
    //     }

    //     $user = Auth::guard('api')->user();
    //     $table_name = TableNameHelper::getTableNameOfLoggedInUser();

    //     // Update: Now include searching by tags
    //     $items = $this->getItemsByNameOrTags($table_name, $request->item_name);

    //     if (count($items) == 0) {
    //         $allItemNames = $this->getAllItemNames($table_name);
    //         $similarItems = $this->findSimilarItems($allItemNames, strtoupper($request->item_name));

    //         return response()->json([
    //             'status' => 'success',
    //             'condition' => 'Similar word check',
    //             'count' => count($similarItems),
    //             'item_name' => $request->item_name,
    //             'data' => $similarItems,
    //         ], 201);
    //     }

    //     return response()->json([
    //         'status' => 'success',
    //         'count' => count($items),
    //         'item_name' => $request->item_name,
    //         'data' => $items,
    //     ], 201);
    // }


    // // No change needed in getAllItemNames, as it’s used for fallback search when no exact or tag match is found
    // private function getAllItemNames($table_name)
    // {
    //     return DB::table($table_name)
    //         ->select('id', 'item_name', 'short_unit', 'quantity', 'tags')  // Include 'tags' if needed
    //         ->get()
    //         ->map(function ($item) {
    //             $item->item_name = strtoupper($item->item_name);
    //             return $item;
    //         })
    //         ->toArray();
    // }


    // private function getItemsByNameOrTags($table_name, $item_name)
    // {
    //     // Prepare the search term to handle variations like "daali" -> "daal"
    //     $item_name_root = preg_replace('/i$/', '', $item_name); // E.g., "daali" to "daal"
    //     $item_name_wildcard = $item_name_root . '%';

    //     return DB::table($table_name)
    //         ->select('id', 'item_name', 'short_unit', 'quantity')
    //         ->where(function ($query) use ($item_name, $item_name_wildcard) {
    //             $query->where('item_name', 'LIKE', $item_name_wildcard) // Match by prefix
    //                 ->orWhereRaw('SOUNDEX(item_name) = SOUNDEX(?)', [$item_name]) // Phonetic match
    //                 ->orWhereRaw('MATCH(item_name, tags) AGAINST(? IN BOOLEAN MODE)', ["+$item_name*"]); // Full-text search
    //         })
    //         ->orderByRaw("item_name LIKE '{$item_name_wildcard}' DESC") // Prioritize prefix matches
    //         ->orderBy('item_name') // Alphabetical fallback
    //         ->get();
    // }


    // private function findSimilarItems($items, $searchTerm)
    // {
    //     $similarItems = [];
    //     $searchWords = explode(' ', strtolower($searchTerm));

    //     // Step 1: First pass for substring and tag match
    //     foreach ($items as $item) {
    //         $itemNameLower = strtolower($item->item_name);
    //         $tagsLower = strtolower($item->tags);

    //         $allWordsMatch = true;
    //         foreach ($searchWords as $searchWord) {
    //             if (strpos($itemNameLower, $searchWord) === false && strpos($tagsLower, $searchWord) === false) {
    //                 $allWordsMatch = false;
    //                 break;
    //             }
    //         }
    //         if ($allWordsMatch) {
    //             $similarItems[] = [
    //                 'id' => $item->id,
    //                 'item_name' => $item->item_name,
    //                 'short_unit' => $item->short_unit,
    //                 'quantity' => $item->quantity,
    //                 'match_type' => 'substring'
    //             ];
    //         }
    //     }

    //     // Step 2: Fallback to Levenshtein distance with threshold
    //     if (count($similarItems) == 0) {
    //         foreach ($items as $item) {
    //             $itemWords = explode(' ', strtolower($item->item_name));
    //             $totalDistance = 0;

    //             foreach ($searchWords as $searchWord) {
    //                 $minDistance = PHP_INT_MAX;
    //                 foreach ($itemWords as $itemWord) {
    //                     $distance = levenshtein($searchWord, $itemWord);
    //                     $minDistance = min($minDistance, $distance);
    //                 }
    //                 $totalDistance += $minDistance;
    //             }

    //             $averageDistance = $totalDistance / count($searchWords);
    //             $threshold = $this->calculateDynamicThreshold(strlen($item->item_name), strlen($searchTerm));

    //             if ($averageDistance <= $threshold) {
    //                 $similarItems[] = [
    //                     'id' => $item->id,
    //                     'item_name' => $item->item_name,
    //                     'short_unit' => $item->short_unit,
    //                     'quantity' => $item->quantity,
    //                     'average_distance' => $averageDistance,
    //                     'threshold' => $threshold
    //                 ];
    //             }
    //         }
    //     }

    //     return $similarItems;
    // }

    // // Dynamic threshold based on word lengths
    // private function calculateDynamicThreshold($itemLength, $searchLength)
    // {
    //     // Lower threshold for short words, higher for longer words
    //     if ($itemLength <= 4 || $searchLength <= 4) {
    //         return 1;
    //     } elseif ($itemLength <= 7 || $searchLength <= 7) {
    //         return 2;
    //     } else {
    //         return 3;
    //     }
    // }

    // ---------------------------------------------------------------------------- UPDATED SEARCH CODE WITH FULL TEXT INDEXING AND SOUNDEX ------------------------------------------------------------------------------
    
    
    // ---------------------------------------------------------------------------- UPDATED SEARCH CODE WITH FULL TEXT INDEXING AND SOUNDEX, SEARCH USING BARCODE ------------------------------------------------------------------------------

    public function showProductSuggestionList(Request $request)
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Unauthorized')
            ], 401);
        }

        $user = Auth::guard('api')->user();
        $table_name = TableNameHelper::getTableNameOfLoggedInUser();

        // ── Barcode search: exact match, highest priority ─────────────────────────
        if (!empty($request->barcode)) {
            $barcodeItem = DB::table($table_name)
                ->select('id', 'item_name', 'short_unit', 'quantity', 'barcode')
                ->where('barcode', $request->barcode)
                ->first();

            if ($barcodeItem) {
                return response()->json([
                    'status' => 'success',
                    'condition' => 'Barcode match',
                    'count' => 1,
                    'barcode' => $request->barcode,
                    'data' => [$barcodeItem],
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'condition' => 'Barcode not found',
                'count' => 0,
                'barcode' => $request->barcode,
                'data' => [],
            ], 200);
        }
        // ─────────────────────────────────────────────────────────────────────────

        // Existing name/tag search flow
        $items = $this->getItemsByNameOrTags($table_name, $request->item_name);

        if (count($items) == 0) {
            $allItemNames = $this->getAllItemNames($table_name);
            $similarItems = $this->findSimilarItems($allItemNames, strtoupper($request->item_name));

            return response()->json([
                'status' => 'success',
                'condition' => 'Similar word check',
                'count' => count($similarItems),
                'item_name' => $request->item_name,
                'data' => $similarItems,
            ], 201);
        }

        return response()->json([
            'status' => 'success',
            'count' => count($items),
            'item_name' => $request->item_name,
            'data' => $items,
        ], 201);
    }

    
    // No change needed in getAllItemNames, as it’s used for fallback search when no exact or tag match is found
    private function getAllItemNames($table_name)
    {
        return DB::table($table_name)
            ->select('id', 'item_name', 'short_unit', 'quantity', 'tags')  // Include 'tags' if needed
            ->get()
            ->map(function ($item) {
                $item->item_name = strtoupper($item->item_name);
                return $item;
            })
            ->toArray();
    }


    private function getItemsByNameOrTags($table_name, $item_name)
    {
        $item_name_root = preg_replace('/i$/', '', $item_name);
        $item_name_wildcard = $item_name_root . '%';

        return DB::table($table_name)
            ->select('id', 'item_name', 'short_unit', 'quantity', 'barcode')
            ->where(function ($query) use ($item_name, $item_name_wildcard) {
                $query->where('item_name', 'LIKE', $item_name_wildcard)
                    ->orWhereRaw('SOUNDEX(item_name) = SOUNDEX(?)', [$item_name])
                    ->orWhereRaw('MATCH(item_name, tags) AGAINST(? IN BOOLEAN MODE)', ["+$item_name*"])
                    ->orWhere('barcode', $item_name); // ← barcode typed into name field
            })
            ->orderByRaw("item_name LIKE '{$item_name_wildcard}' DESC")
            ->orderBy('item_name')
            ->get();
    }


    private function findSimilarItems($items, $searchTerm)
    {
        $similarItems = [];
        $searchWords = explode(' ', strtolower($searchTerm));

        // Step 1: First pass for substring and tag match
        foreach ($items as $item) {
            $itemNameLower = strtolower($item->item_name);
            $tagsLower = strtolower($item->tags);

            $allWordsMatch = true;
            foreach ($searchWords as $searchWord) {
                if (strpos($itemNameLower, $searchWord) === false && strpos($tagsLower, $searchWord) === false) {
                    $allWordsMatch = false;
                    break;
                }
            }
            if ($allWordsMatch) {
                $similarItems[] = [
                    'id' => $item->id,
                    'item_name' => $item->item_name,
                    'short_unit' => $item->short_unit,
                    'quantity' => $item->quantity,
                    'match_type' => 'substring'
                ];
            }
        }

        // Step 2: Fallback to Levenshtein distance with threshold
        if (count($similarItems) == 0) {
            foreach ($items as $item) {
                $itemWords = explode(' ', strtolower($item->item_name));
                $totalDistance = 0;

                foreach ($searchWords as $searchWord) {
                    $minDistance = PHP_INT_MAX;
                    foreach ($itemWords as $itemWord) {
                        $distance = levenshtein($searchWord, $itemWord);
                        $minDistance = min($minDistance, $distance);
                    }
                    $totalDistance += $minDistance;
                }

                $averageDistance = $totalDistance / count($searchWords);
                $threshold = $this->calculateDynamicThreshold(strlen($item->item_name), strlen($searchTerm));

                if ($averageDistance <= $threshold) {
                    $similarItems[] = [
                        'id' => $item->id,
                        'item_name' => $item->item_name,
                        'short_unit' => $item->short_unit,
                        'quantity' => $item->quantity,
                        'average_distance' => $averageDistance,
                        'threshold' => $threshold
                    ];
                }
            }
        }

        return $similarItems;
    }

    // Dynamic threshold based on word lengths
    private function calculateDynamicThreshold($itemLength, $searchLength)
    {
        // Lower threshold for short words, higher for longer words
        if ($itemLength <= 4 || $searchLength <= 4) {
            return 1;
        } elseif ($itemLength <= 7 || $searchLength <= 7) {
            return 2;
        } else {
            return 3;
        }
    }
    // ---------------------------------------------------------------------------- UPDATED SEARCH CODE WITH FULL TEXT INDEXING AND SOUNDEX, SEARCH USING BARCODE ------------------------------------------------------------------------------

}
