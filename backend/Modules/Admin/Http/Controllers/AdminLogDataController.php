<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class AdminLogDataController extends Controller
{
    // public function logData(Request $request)
    // {
    //     $date = $request->query('date', now()->toDateString());
    //     $logFile = storage_path('logs/custom/custom-' . $date . '.log');
    //     $issues = [];

    //     if (File::exists($logFile)) {
    //         $content = File::get($logFile);
    //         $lines = explode("\n", $content);

    //         foreach ($lines as $line) {
    //             if (empty($line)) continue;

    //             // Parse log line: [timestamp] env.LEVEL: message {"key":"value",...}
    //             if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] .+?ERROR: (.+?) (\{.*)$/', $line, $matches)) {
    //                 $timestamp = $matches[1];
    //                 $message = $matches[2];
    //                 $data = json_decode($matches[3], true);

    //                 if ($data && $message === 'Exception Occurred') {
    //                     $issues[] = [
    //                         'timestamp' => $timestamp,
    //                         'ip' => $data['ip'] ?? 'unknown',
    //                         'user_agent' => $data['user_agent'] ?? 'unknown',
    //                         'source' => $data['source'] ?? 'unknown',
    //                         'url' => $data['url'] ?? 'unknown',
    //                         'method' => $data['method'] ?? 'unknown',
    //                         'user_id' => $data['user_id'] ?? 'N/A',
    //                         'exception' => $data['exception'] ?? 'unknown',
    //                         'file' => $data['file'] ?? 'unknown',
    //                         'line' => $data['line'] ?? 'unknown',
    //                         'trace' => $data['trace'] ?? 'unknown',
    //                     ];
    //                 }
    //             }
    //         }
    //     }

    //     // Sort issues by timestamp (descending)
    //     usort($issues, function ($a, $b) {
    //         return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    //     });

    //     // dd($issues);

    //     return view('admin::log_data', [
    //         'issues' => $issues,
    //         'selectedDate' => $date,
    //     ]);
    // }


    // public function logData(Request $request)
    // {
    //     $date = $request->query('date', now()->toDateString());
    //     $logFile = storage_path('logs/custom/custom-' . $date . '.log');
    //     $issues = [];

    //     // Debug: Log the file path being checked
    //     Log::debug('Checking log file: ' . $logFile);

    //     if (!File::exists($logFile)) {
    //         Log::error('Log file does not exist: ' . $logFile);
    //     } elseif (!is_readable($logFile)) {
    //         Log::error('Log file is not readable: ' . $logFile);
    //     } else {
    //         $content = File::get($logFile);
    //         $lines = explode("\n", $content);

    //         Log::debug('Read ' . count($lines) . ' lines from log file');



    //         foreach ($lines as $line) {
    //             if (empty($line))
    //                 continue;

    //             // Parse log line: [timestamp] env.LEVEL: message {json}
    //             if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] .+?\.ERROR: (.+?) (\{.*)$/', $line, $matches)) {
    //                 $timestamp = $matches[1];
    //                 $message = $matches[2];
    //                 $jsonData = $matches[3];

    //                 Log::debug('Parsed log line: Timestamp=' . $timestamp . ', Message=' . $message);

    //                 if ($message === 'Exception Occurred') {
    //                     // Sanitize JSON data before decoding
    //                     $jsonData = preg_replace('/[\x00-\x1F\x7F]/u', '', $jsonData);
    //                     Log::debug('Raw JSON: ' . $jsonData);

    //                     $data = json_decode($jsonData, true);

    //                     if ($data === null) {
    //                         Log::error('JSON decode failed for line: ' . $line . ', Error: ' . json_last_error_msg());
    //                         continue;
    //                     }

    //                     $issues[] = [
    //                         'timestamp' => $timestamp,
    //                         'ip' => $data['ip'] ?? 'unknown',
    //                         'user_agent' => $data['user_agent'] ?? 'unknown',
    //                         'source' => $data['source'] ?? 'unknown',
    //                         'url' => $data['url'] ?? 'unknown',
    //                         'method' => $data['method'] ?? 'unknown',
    //                         'user_id' => $data['user_id'] ?? 'N/A',
    //                         'exception' => $data['exception'] ?? 'unknown',
    //                         'file' => $data['file'] ?? 'unknown',
    //                         'line' => $data['line'] ?? 'unknown',
    //                         'trace' => $data['trace'] ?? 'unknown',
    //                     ];

    //                     Log::debug('Added issue: ' . $data['exception']);
    //                 }
    //             } else {
    //                 Log::debug('Line did not match regex: ' . $line);
    //             }
    //         }
    //     }

    //     // Sort issues by timestamp (descending)
    //     usort($issues, function ($a, $b) {
    //         return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    //     });

    //     Log::debug('Found ' . count($issues) . ' issues for date ' . $date);

    //     return view('admin::log_data', [
    //         'issues' => $issues,
    //         'selectedDate' => $date,
    //     ]);
    // }


    public function logData(Request $request)
    {
        $date = $request->query('date', now()->toDateString());
        $logFile = storage_path('logs/custom/custom-' . $date . '.log');
        $items = [];

        if (!File::exists($logFile) || !is_readable($logFile)) {
            return view('admin::log_data', [
                'items' => [],
                'columns' => [],
                'selectedDate' => $date,
            ]);
        }

        $lines = explode("\n", File::get($logFile));

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // [2026-02-20 11:25:41] local.ERROR: {"message": "...", "info": {...}}
            if (
                !preg_match(
                    '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+(\S+)\.(\S+):\s+(\{.*\})$/',
                    $line,
                    $m
                )
            ) {
                continue;
            }

            $metaTime = $m[1];
            $env = $m[2];
            $level = $m[3];
            $jsonData = $m[4];

            $jsonData = preg_replace('/[\x00-\x1F\x7F]/u', '', $jsonData);
            $data = json_decode($jsonData, true);

            if (!is_array($data)) {
                continue;
            }

            // add basic meta into data so it's always there
            $data['_meta_timestamp'] = $metaTime;
            $data['_meta_env'] = $env;
            $data['_meta_level'] = $level;

            $items[] = $data;
        }

        // build dynamic columns by flattening all items
        [$columns, $flatItems] = $this->buildDynamicColumns($items);

        return view('admin::log_data', [
            'items' => $flatItems,
            'columns' => $columns,
            'selectedDate' => $date,
        ]);
    }

    /**
     * Flatten nested arrays into "dot" keys and collect all column names.
     */
    protected function buildDynamicColumns(array $items): array
    {
        $columns = [];
        $flatItems = [];

        foreach ($items as $item) {
            $flat = $this->arrayDot($item);
            $flatItems[] = $flat;
            $columns = array_unique(array_merge($columns, array_keys($flat)));
        }

        sort($columns);

        return [$columns, $flatItems];
    }

    /**
     * dot-flatten an array: ['info' => ['ip' => '...']] -> ['info.ip' => '...']
     */
    protected function arrayDot(array $array, string $prefix = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : $prefix . '.' . $key;

            if (is_array($value)) {
                $results += $this->arrayDot($value, $newKey);
            } else {
                $results[$newKey] = $value;
            }
        }

        return $results;
    }



}
