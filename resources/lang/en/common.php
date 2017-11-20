<?php

return [
    'verify' => [
        'ok' => 'Your account has been successfully activated.',
        'need' => 'Account is not activated',
        'email' => [
            'ok' => 'Your E-mail address has been successfully verified.',
            'send' => 'We have sent you a new activation code, please check your email.',
            'freeze' => 'You can make a new activation request by E-mail only after :seconds seconds.',
            'locked' => 'Your request cannot be performed cause you exceed max count E-mail activation attempts per day (max. :attempts).',
        ],
        'phone' => [
            'ok' => 'Your mobile number has been successfully verified.',
            'send' => 'We have send you a new activation code, please check your phone.',
            'freeze' => 'You can make a new activation request by sms only after :seconds seconds.',
            'locked' => 'Your request cannot be performed cause you exceed max count sms activation attempts per day (max. :attempts).',
            'sms' => 'Activation code - :token',
        ],
    ],

    'change' => [
        'email' => [
            'ok' => 'Your E-mail address was successfully changed.',
        ],
        'phone' => [
            'ok' => 'You mobile number was successfully changed.',
        ],
    ],

    'resend' => [
        'email' => [
            'info' => 'If you are not yet received email with activation instructions, you can',
            'link' => 'Resend Again',
        ],

        'phone' => [
            'info' => 'If you are not yet received an sms with activation code, you can',
            'link' => 'Resend Again',
            'otherwise' => 'If already received',
            'placeholder' => 'Input code...',
            'ok' => 'Ok',
        ],
    ],
];