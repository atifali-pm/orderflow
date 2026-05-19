<?php

return [
    'webhook_url_base' => env('N8N_WEBHOOK_URL_BASE', 'http://127.0.0.1:5679/webhook'),
    'hmac_secret' => env('N8N_HMAC_SECRET', ''),
    'api_token' => env('N8N_API_TOKEN', ''),
    'timeout' => (int) env('N8N_HTTP_TIMEOUT', 10),
];
