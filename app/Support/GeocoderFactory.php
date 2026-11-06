<?php

namespace App\Support;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class GeocoderFactory
{
    private const ALLOWED_DRIVERS = ['nominatim', 'google'];

    public static function forAdmin(Request $request): Geocoder
    {
        $driver = strtolower($request->input('driver', config('geocoding.driver', 'nominatim')));

        if (! in_array($driver, self::ALLOWED_DRIVERS, true)) {
            $driver = 'nominatim';
        }

        $config = config('geocoding', []);

        if ($driver === 'google') {
            $google = $config['google'] ?? [];
            $google['limit'] = 8;

            return new GoogleGeocoder(
                new Client(['timeout' => 15]),
                $google
            );
        }

        $nominatim = $config['nominatim'] ?? [];
        $nominatim['limit'] = 8;

        return new NominatimGeocoder(
            new Client(['timeout' => 15]),
            $nominatim
        );
    }
}
