<?php

return [

    'smsList' => [
        'default' => [
            'url' => 'https://control.msg91.com/api/v5/flow',
            'authkey' => '419859AxxcWole6766aca6P1',
            'realTimeResponse' => 1,
        ],
        'registration_sucssess' => [
            // 'template_id' => '67692652d6fc056631108e24'
            'template_id' => '6965f5acdfd9d068e134ee07'
            // REGISTRATION SUCCESS MESSAGE
        ],

        'sign_up' => [
            // 'template_id' => '6766db1dd6fc05135e030683'
            'template_id' => '6965ef3af794fd1db25e17aa'
            // REGISTRATION - MOBILE NUMBER VERIFICATION
        ],

        'change_password' => [
            // 'template_id' => '6769262ad6fc05739d6cc9a2'
            'template_id' => '6965f61fac8d2b117e51ef62'
            // CHANGE PASSWORD - AFTER LOGIN
        ],

        'forgot_password' => [
            'template_id' => '6769577dd6fc0531d244d094',
            // FORGOT PASSWORD - WIHTOUT LOGIN
        ],
        'change_mobile_number' => [
            'template_id' => '67aa1c0dd6fc055bc6723ec3',
            // PROFILE - CHANGE MOBILE NUMBER
        ],
        'delete_account' => [
            'template_id' => '67ecfabdd6fc0571c8241423',
            // DELETE ACCOUNT
        ],

        'share_invoice' =>[
            // 'template_id' => '68ea4243d0f1621171522e32',
            'template_id' => '68ee5ea34069ed41a50828a3',
            // SHARE INVOICE
        ],


        // FOR OTHER COUNTRY EXCEPT INDIA
        'international_registration_sucssess' => [
            'template_id' => '67ed15e0d6fc05154a348bf2'
            // REGISTRATION SUCCESS MESSAGE
        ],

        'international_sign_up' => [
            'template_id' => '67ed159cd6fc0544c7315353'
            // REGISTRATION - MOBILE NUMBER VERIFICATION
        ],

        'international_change_password' => [
            'template_id' => '67ed15c9d6fc0515da7b5df2'
            // CHANGE PASSWORD - AFTER LOGIN
        ],

        'international_forgot_password' => [
            'template_id' => '67ed15fcd6fc0548684af304',
            // FORGOT PASSWORD - WIHTOUT LOGIN
        ],
        'international_change_mobile_number' => [
            'template_id' => '67ed161cd6fc0536a7089a12',
            // PROFILE - CHANGE MOBILE NUMBER
        ],
        'international_delete_account' => [
            'template_id' => '67ed165dd6fc05135f5e2e13',
            // DELETE ACCOUNT
        ],
        // FOR OTHER COUNTRY EXCEPT INDIA

    ],

    'phoneNumbers' => [
        0 => '1234567890',
        1 => '9876543210',
        2 => '9876543211',
        3 => '9876543212',
        4 => '9876543213',
        5 => '9876543214',
        6 => '9876543215',

        7 => '8877665544',
        8 => '8877665533',
        9 => '8877665522',
        10 => '8877665511',
        11 => '8877665500',
        12 => '8877665566',


        13 => '7766554433',
        14 => '7766554432',
        15 => '7766554431',
        16 => '7766554430',
    ],
];