<?php

return [
    'required' => ':attribute is required to continue.',
    'numeric' => ':attribute must be a 10-digit number.',
    'digits' => ':attribute must be a 10-digit number.',
    'unique' => ':attribute is already registered.',
    'digits_otp' => ':attribute must be a 6-digit number.',
    'mobile_already_registered' => 'The mobile number is already registered.',
    'string' => ':attribute must be a string.',
    'min' => ':attribute must be at least :min characters.',
    'confirmed' => ':attribute confirmation does not match.',
    'invalid' => ':attribute is invalid.',
    'mobile_not_verified' => 'The mobile number is not verified. Please register again to continue.',

    'exists' => ':attribute does not exist.',
    'max' => ':attribute may not be greater than :max characters.',
    'regex_business_name' => ':attribute can only contain letters, numbers, and spaces.',
    'image' => ':attribute must be an image.',
    'mimes' => ':attribute must be a file of type: :values.',
    'required_accepted' => 'You must accept the :attribute to proceed.',
    'user_invalid' => 'Invalid user.',
    'email_already_registered' => 'The email has already been registered.',
   
    'attributes' => [
        'mobile' => 'Mobile number',
        'country_code' => 'Country code',
        'detected_country_code' => 'Detected Country code',
        'otp' => 'OTP',
        'password' => 'Password',
        'shop_type' => 'Shop type',

        'user_id' => 'User ID',
        'name' => 'Name',
        'business_name' => 'Business name',
        'email' => 'Email',
        'address' => 'Address',
        'gstin' => 'GSTIN',
        'logo' => 'Logo',
        'terms_n_conditions' => 'Terms and conditions',
    ],
];