<?php

return [
    /**
     * Ideapay client id
     */
    'client_id' => env('IDEAPAY_CLIENT_ID', ''),
    'client_id_intl' => env('IDEAPAY_CLIENT_ID_INTERNATIONAL', ''),

    /**
     * Ideapay client secret
     */
    'client_secret' => env('IDEAPAY_CLIENT_SECRET', ''),
    'client_secret_intl' => env('IDEAPAY_CLIENT_SECRET_INTERNATIONAL', ''),

    /**
     * Live url of Ideapay
     */
    'live' => env('IDEAPAY_LIVE'),

    /**
     * Redirect urls
     */
    'success_redirect' => env('REG_URL_SUCCESS'),
    'error_redirect' => env('REG_URL_ERROR'),
];
