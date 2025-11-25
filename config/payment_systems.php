<?php

return [
    'kapitalbank' => [
        /* Production Environment Credentials */
        'prod_api' => env('KAPITAL_BANK_PROD_API'),
        'prod_user' => env('KAPITAL_BANK_PROD_USER'),
        'prod_pass' => env('KAPITAL_BANK_PROD_PASS'),
        'prod_hpp_redirect_url' => env('KAPITAL_BANK_PROD_REDIRECT_URL'),

        /* Test Environment Credentials */
        'test_api' => env('KAPITAL_BANK_TEST_API'),
        'test_user' => env('KAPITAL_BANK_TEST_USER'),
        'test_pass' => env('KAPITAL_BANK_TEST_PASS'),
        'test_hpp_redirect_url' => env('KAPITAL_BANK_TEST_REDIRECT_URL'),

        'order' => [
            'typeRid' => [
                'Purchase' => 'Order_SMS',
                'PreAuth' => 'Order_DMS'
            ],
        ],
    ],
];
