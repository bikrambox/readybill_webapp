<?php

namespace Modules\GroceryGermany\Helpers;

use Illuminate\Support\Facades\DB;

class TagsHelper
{

    public static function generateTags($table_name)
    {
        // Fetch all items
        $items = DB::table($table_name)->get();

        foreach ($items as $item) {
            // Split item_name into words to generate tags
            $tags = explode(' ', $item->item_name); // Simple split by space, adjust if necessary

            // Add the full item_name as the first tag
            array_unshift($tags, $item->item_name);

            // Update tags field in JSON format
            DB::table($table_name)
                ->where('id', $item->id)
                ->update(['tags' => json_encode($tags)]);

            // Output the operation result
            // $this->info("Tags generated for item ID {$item->id}");
        }
    }

    public static function createOrUpdateTag($item_name)
    {
        // Get the units from the config file
        $units = config('german_units.units');

        // Build a regex pattern for units
        $unitPattern = implode('|', array_map('preg_quote', $units));

        // Step 1: Remove special characters from the item_name (except spaces)
        $item_name = preg_replace('/[^a-zA-Z0-9\s]/', '', $item_name);

        // Step 2: Trim any leading or trailing spaces from the item_name
        $item_name = trim($item_name);

        // Preserve the cleaned item_name as a single tag
        $tags = [$item_name];

        // Step 3: Split the cleaned item_name into words
        preg_match('/^[^\d]*(?=\d)/', $item_name, $matches);
        if (!empty($matches)) {
            // If a match is found, use it as the first tag
            $item_name = [$matches[0]];
        } else {
            // If no number is found, use the full item_name
            $item_name = [$item_name];
        }

        // Ensure that $item_name is a string before calling explode
        $item_name = is_array($item_name) ? implode(' ', $item_name) : $item_name;

        // Now you can safely call explode
        $words = explode(' ', $item_name);

        // Initialize an array to hold filtered tags
        $filteredTags = [];

        // Step 4: Iterate through words to apply filtering rules
        foreach ($words as $index => $word) {
            // Exclude empty words or whitespace
            if (empty($word)) {
                continue;
            }

            // Exclude standalone numbers
            if (is_numeric($word)) {
                continue;
            }

            // Exclude standalone units
            if (in_array(strtoupper($word), $units)) {
                continue;
            }

            // Exclude number-unit combinations (e.g., "10 KG")
            if ($index > 0 && is_numeric($words[$index - 1]) && in_array(strtoupper($word), $units)) {
                continue;
            }

            // Exclude single words immediately following numbers (e.g., "10 BUCKET")
            if ($index > 0 && is_numeric($words[$index - 1])) {
                continue;
            }

            // Otherwise, add the word to the filtered tags
            $filteredTags[] = $word;
        }

        // Step 5: Merge the filtered tags with the initial item_name
        $tags = array_merge($tags, $filteredTags);

        // Step 6: Return the tags as a JSON encoded string
        return json_encode(array_unique($tags));
    }

}

?>