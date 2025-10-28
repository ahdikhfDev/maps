<?php

return [
    'paths' => ['api/*', 'admin/*', 'livewire/*', 'filament/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['https://maps-ruby.vercel.app'],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];
