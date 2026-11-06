<?php

namespace App\Support;

interface Geocoder
{
    /**
     * Return candidate locations for a free-text query.
     *
     * Each result is an array with:
     *  - label      : short title shown to the admin
     *  - detail     : address line (city/province/country)
     *  - lat        : decimal degrees
     *  - lon        : decimal degrees
     *  - display_name : full place string for the result (for citation)
     *  - type       : place class (address/amenity/building/street/...)
     *
     * @return array<int, array{
     *     label:string,
     *     detail:string,
     *     lat:string,
     *     lon:string,
     *     display_name:string,
     *     type:string
     * }>
     */
    public function search(string $query, int $limit = 8): array;
}
