<?php
return [
    'required' => 'Das Feld :attribute ist erforderlich.',
    'required_if' => ':attribute ist erforderlich, wenn :other :value ist.',
    'email' => 'Das :attribute muss eine gültige E‑Mail‑Adresse sein.',
    'exists' => 'Das ausgewählte :attribute ist nicht in ReadyBill registriert.',
    'max' => [
        'string' => 'Das :attribute darf nicht größer als :max Zeichen sein.',
    ],
    'custom' => [
        'mobile' => [
            'phone_number' => 'Die Mobilnummer ist ungültig.',
            'invalid_credential' => 'Ungültige Anmeldedaten.', 
            'non_numeric' => 'The mobile number must contain only numeric digits (0-9).', ### new entry ###
            
            
            'min_length_in' => 'The mobile number must contain at least 10 digits.',   ### new entry ###
            'max_length_in' => 'The mobile number must not exceed 10 digits.',    ### new entry ###


            'min_length_other' => 'The mobile number must contain at least 10 digits.',   ### new entry ###
            'max_length_other' => 'The mobile number must not exceed 12 digits.',   ### new entry ###


            'identical_digits' => 'The mobile number cannot contain all identical digits.',  ### new entry ###
        ],
        'country_code' => [
            'valid_country_code' => 'Der Ländercode ist ungültig.',
            'invalid_credential' => 'Ungültige Anmeldedaten.',
        ],
    ],
    'attributes' => [
        'mobile' => 'Mobilnummer',
        'country_code' => 'Ländercode',
        'password' => 'Passwort',
        'detected_country_code' => 'Ländercode registrieren',
        'loginFrom' => 'loginFrom'
    ],
];