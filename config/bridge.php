<?php

return [

    /*
     | The base URL of your Briskbase Bridge instance.
     | e.g. https://bridge.briskbase.test  or  https://bridge.yourapp.com
     */
    'url' => env('BRIDGE_URL', ''),

    /*
     | API key ID issued by Bridge (pk_sandbox_... or pk_live_...).
     */
    'key_id' => env('BRIDGE_KEY_ID', ''),

    /*
     | API secret issued by Bridge (sk_sandbox_... or sk_live_...).
     | Keep this server-side only. Never expose it in the browser or source code.
     */
    'secret' => env('BRIDGE_SECRET', ''),

    /*
     | Webhook signing secret issued by Bridge for your product's endpoint.
     | Used to verify incoming webhook payloads.
     */
    'webhook_secret' => env('BRIDGE_WEBHOOK_SECRET', ''),

    /*
     | How many seconds of clock skew are tolerated when verifying webhooks.
     */
    'webhook_tolerance' => (int) env('BRIDGE_WEBHOOK_TOLERANCE', 300),

    /*
     | Default currency sent with checkout sessions (ISO 4217).
     */
    'currency' => env('BRIDGE_CURRENCY', 'PKR'),

    /*
     | Default country sent with checkout sessions (ISO 3166-1 alpha-2).
     */
    'country' => env('BRIDGE_COUNTRY', 'PK'),

    /*
     | HTTP timeout in seconds for API calls.
     */
    'timeout' => (int) env('BRIDGE_TIMEOUT', 15),

];
