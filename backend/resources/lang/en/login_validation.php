<?php

return [
    'required' => 'The :attribute field is required.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'email' => 'The :attribute must be a valid email address.',
    'exists' => 'The selected :attribute is not registered in ReadyBill.',
    'max' => [
        'string' => 'The :attribute may not be greater than :max characters.',
    ],
    'custom' => [
        'mobile' => [
            'phone_number' => 'The mobile number is invalid.',
            'invalid_credential' => 'Invalid credentials.',
            'non_numeric' => 'The mobile number must contain only numeric digits (0-9).',

            'min_length_in' => 'The mobile number must contain at least 10 digits.',
            'max_length_in' => 'The mobile number must not exceed 10 digits.',

            'min_length_other' => 'The mobile number must contain at least 10 digits.',
            'max_length_other' => 'The mobile number must not exceed 12 digits.',

            'identical_digits' => 'The mobile number cannot contain all identical digits.',

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
        'detected_country_code' => 'Register Country Code',
        'loginFrom' => 'loginFrom'
    ],
];