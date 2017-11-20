<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Current verification methods
    |--------------------------------------------------------------------------
    |
    | You can set one method (if you verification only by one method), or both
    | Available values: 'email', 'phone'
    |
    */
    'methods' => [
        'email',
        'phone',
    ],

    /*
    |--------------------------------------------------------------------------
    | E-mail and Phone verification settings
    |--------------------------------------------------------------------------
    |
    | 'field' - Name of the attribute of User model
    | 'rule' - Custom validation rule or set of rules divided by |. Needed for validation if users changes E-mail
    |     default rules are already applied (required|unique:users)
    | 'token_length' - generated token length
    | 'freeze' - min time (in seconds) required before a new validation request
    | 'attempts' - max count of verification requests per day
    | 'notification_class' - class name of your own verification class if you want to customize E-mail of Sms that user receives
    |     if you don't want customize this leave null value.
    |
    */
    'email' => [
        'field' => 'email',
        'rule' => 'email',
        'token_length' => 64,
        'freeze' => 60,
        'attempts' => 20,
        'notification_class' => null,
    ],
    'phone' => [
        'field' => 'phone',
        'rule' => 'phone',
        'token_length' => 5,
        'freeze' => 300,
        'attempts' => 5,
        'notification_class' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Username field for greetings in E-mail templates
    |--------------------------------------------------------------------------
    |
    */
    'field_username' => 'username',
];