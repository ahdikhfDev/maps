<?php

return [
    'asset_url' => env('ASSET_URL'),
    'app_url' => env('APP_URL', 'https://maps-ruby.vercel.app'),

    'middleware_group' => 'web',

    'temporary_file_upload' => [
        'disk' => 'public',
    ],

    'legacy_model_binding' => false,
    'navigate' => true,
    'navigate_except' => ['admin/*'],
];
