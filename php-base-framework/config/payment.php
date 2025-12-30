<?php

return [
    'toss' => [
        'client_key' => getenv('TOSS_CLIENT_KEY') ?: '',
        'secret_key' => getenv('TOSS_SECRET_KEY') ?: '',
        'checkout_url' => 'https://pay.toss.im/web/checkout',
        'success_url' => getenv('APP_URL') . '/payment/success',
        'fail_url' => getenv('APP_URL') . '/payment/fail',
    ],

    'iamport' => [
        'api_key' => getenv('IAMPORT_API_KEY') ?: '',
        'secret_key' => getenv('IAMPORT_SECRET_KEY') ?: '',
    ],

    'stripe' => [
        'publishable_key' => getenv('STRIPE_PUBLISHABLE_KEY') ?: '',
        'secret_key' => getenv('STRIPE_SECRET_KEY') ?: '',
    ],
];
