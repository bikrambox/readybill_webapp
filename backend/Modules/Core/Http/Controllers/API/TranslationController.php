<?php

namespace Modules\Core\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class TranslationController extends Controller
{
    public function getTranslations($locale)
    {
        $translations = [];
        $langPath = resource_path("lang/{$locale}");

        if (!File::exists($langPath)) {
            return response()->json(['error' => 'Language not found'], 404);
        }

        // Load all PHP files in the language directory
        $files = File::files($langPath);

        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $translations[$filename] = include $file;
        }

        return response()->json($translations);
    }
}