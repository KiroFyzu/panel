<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Billing Enabled
    |--------------------------------------------------------------------------
    |
    | When true, the root URL serves a public pricing landing page for guests.
    | Logged-in users continue to be redirected to the React dashboard.
    |
    */
    'enabled' => (bool) env('BILLING_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Pakasir Credentials
    |--------------------------------------------------------------------------
    |
    | These can be overridden at runtime via the admin Settings → Advanced page
    | (billing:pakasir_project, billing:pakasir_api_key).
    |
    */
    'pakasir_project' => env('PAKASIR_PROJECT', ''),
    'pakasir_api_key' => env('PAKASIR_API_KEY', ''),
];