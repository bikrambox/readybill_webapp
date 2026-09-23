<?php
return [
    'required' => 'The :attribute field is required.',
    'email' => 'The :attribute must be a valid email address.',
    'exists' => 'The selected :attribute is not registered in ReadyBill.',
    'max' => [
        'string' => 'The :attribute may not be greater than :max characters.',
    ],
    'custom' => [
        'mobile' => [
            'phone_number' => 'The mobile number is invalid.',
            'invalid_credential' => 'Invalid credentials.',
        ],
        'country_code' => [
            'valid_country_code' => 'The country code is invalid.',
            'invalid_credential' => 'Invalid credentials.',
        ],
    ],
    'attributes' => [
        'mobile' => 'mobile number',
        'country_code' => 'country code',
        'password' => 'password',
    ],
];