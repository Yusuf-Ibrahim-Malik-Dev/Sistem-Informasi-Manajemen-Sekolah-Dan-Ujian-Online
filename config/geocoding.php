<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Geocoding driver
    |--------------------------------------------------------------------------
    | Set this to "google" and supply a Google Maps API key in .env
    | (GOOGLE_MAPS_API_KEY). Google resolves arbitrary business names
    | (e.g. "Root-ID-1994 Developer") far more reliably than Nominatim.
    | When the key is missing or the driver is "nominatim", the app falls
    | back to OpenStreetMap Nominatim (a place-name gazetteer).
    |
    | Nominatim is free, but it cannot reliably geocode business names and
    | returns results for ANY country. For Indonesian schools the provider
    | restricts results to Indonesia below.
    |
    | Available drivers: nominatim, google
    */
    'driver' => env('GEOCODER_DRIVER', 'nominatim'),

    'nominatim' => [
        'endpoint' => env('NOMINATIM_ENDPOINT', 'https://nominatim.openstreetmap.org'),
        /*
         * Contact email appended to the User-Agent so Nominatim operators can
         * reach you. Change this to a working address (or your domain) if you
         * run many searches.
         */
        'email' => env('NOMINATIM_EMAIL', 'admin@example.com'),
        'limit' => 8,
        /*
         * Restrict results to these ISO country codes. Set to empty/omit to
         * allow everything (useful when you add more countries later).
         */
        'countrycodes' => env('NOMINATIM_COUNTRY_CODES', 'id'),
    ],

    'google' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
        'endpoint' => env('GOOGLE_MAPS_ENDPOINT', 'https://maps.googleapis.com/maps/api/geocode/json'),
        'region' => env('GOOGLE_REGIONAL_CODE', 'id'),
    ],

];
