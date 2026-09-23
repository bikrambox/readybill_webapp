<?php

return [
    
    'free_subscription_id' => env('FREE_SUBSCRIPTION_ID', 1),
    
    'free_plan_access' => env('FREE_PLAN_ACCESS', 'restricted'),
    
    /*
    |--------------------------------------------------------------------------
    | Permanently Restricted Routes for Free Plan
    |--------------------------------------------------------------------------
    | These routes are completely blocked for free users (require premium)
    */
    
    'restricted_routes' => [

        // Employee section - ALWAYS blocked for free users
        'api/' . env('API_VERSION') . '/in/grocery/add-new-user',
        'api/' . env('API_VERSION') . '/in/grocery/update-sub-users',
        'api/' . env('API_VERSION') . '/in/grocery/all-sub-users',
        'api/' . env('API_VERSION') . '/in/grocery/all-sub-users-without-pagination',
        'api/' . env('API_VERSION') . '/in/grocery/sub-users',
        'api/' . env('API_VERSION') . '/in/grocery/delete-sub-user',
        
        // // Transaction section - ALWAYS blocked for free users
        // 'api/v2/in/grocery/transactions',
        // 'api/v2/in/grocery/transaction-history',
        // 'api/v2/in/grocery/create-transaction',
        // 'api/v2/in/grocery/update-transaction',
        // 'api/v2/in/grocery/delete-transaction',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Report Data Access Duration (in years)
    |--------------------------------------------------------------------------
    | How many years of historical data can be accessed for reports
    */
    
    'free_plan_report_data_years' => env('FREE_PLAN_REPORT_DATA_YEARS', 1),
    'premium_plan_report_data_years' => env('PREMIUM_PLAN_REPORT_DATA_YEARS', 3),
    
    /*
    |--------------------------------------------------------------------------
    | Always Accessible Routes
    |--------------------------------------------------------------------------
    */
    
    'always_accessible_routes' => [
        'api/update-profile',
        'api/generate-verify-otp',
        'api/update-mobile-number',
        'api/delete-account',
        'api/update-password',
        'api/create-query',
        'api/inventory-store-multiple',
        'api/dataset',
        'api/update-cell-data',
        'api/dataset/multiple-delete',
        'api/reset-dataset',
        'api/user-preferences',
        'api/v2/notifications',
        'api/set-device-token',
    ],
    
];
