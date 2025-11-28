<?php

return [
    'default_driver' => env('PAYMENT_DEFAULT_DRIVER', 'kapitalbank'),

    'drivers' => [
        /* Kapital Bank */
        'kapitalbank' => [
            /* Production Environment Credentials */
            'prod' => [
                'api' => env('KAPITAL_BANK_PROD_API'),
                'user' => env('KAPITAL_BANK_PROD_USER'),
                'pass' => env('KAPITAL_BANK_PROD_PASS'),
                'hpp_redirect_url' => env('KAPITAL_BANK_PROD_REDIRECT_URL'),
            ],

            /* Test Environment Credentials */
            'test' => [
                'api' => env('KAPITAL_BANK_TEST_API'),
                'user' => env('KAPITAL_BANK_TEST_USER'),
                'pass' => env('KAPITAL_BANK_TEST_PASS'),
                'hpp_redirect_url' => env('KAPITAL_BANK_TEST_REDIRECT_URL'),
            ],
        ],

        /* The Others */
    ],

    'map' => [
        'kapitalbank' => \App\Repositories\Payment\KapitalBankRepository::class,
    ],
];
