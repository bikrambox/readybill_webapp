<?php

return [

    'module_type' => [
        'grocery_india' => 'Grocery India',
        'grocery_germany' => 'Grocery Germany',
    ],



    'connections' => [
        'grocery_india',
        'grocery_germany',
        // add more here in future 👈
    ],


    'shop_type' => [
        'grocery',
        'pharma',
    ],


    'payment_status' => [
        'free',
        'pending',
        'paid',
        'failed',
    ],


    'invoice_format' => [
        0 => 'A4',
        1 => '80 mm',
        2 => '50 mm'
    ],


    'invoice_prefix' => 'RB',

];