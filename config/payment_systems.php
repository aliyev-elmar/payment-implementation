<?php

return [
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

        /* Payment Provider Order */
        'order' => [
            'typeRid' => [
                'Purchase' => 'Order_SMS',
                'PreAuth' => 'Order_DMS'
            ],
        ],
    ],
];
