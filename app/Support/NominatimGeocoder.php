<?php

namespace App\Support;

use App\Models\Konfigurasi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class NominatimGeocoder implements Geocoder
{
    private const MAX_LIMIT = 20;

    public function __construct(
        private readonly Client $http,
        private readonly array $config,
    ) {
    }

    public function search(string $query, int $limit = 8): array
    {
        $params = [
            'format' => 'jsonv2',
            'limit' => min(max((int) $limit, 1), self::MAX_LIMIT),
            'addressdetails' => 1,
            'accept-language' => 'id,en',
            'countrycodes' => $this->countryCodes(),
        ];

        try {
            $response = $this->http->get($this->endpoint(), [
                'query' => $params,
                'headers' => $this->headers(),
                'timeout' => 15,
            ]);
            $body = json_decode((string) $response->getBody(), true);
        } catch (RequestException) {
            return [];
        }

        if (! is_array($body)) {
            return [];
        }

        if (($body['status'] ?? null) === 'Timeout' || ($body['status'] ?? null) === 'Failed') {
            return [];
        }

        return $this->rank($body['results'] ?? [], $query);
    }

    private function endpoint(): string
    {
        return rtrim($this->config['endpoint'] ?? 'https://nominatim.openstreetmap.org', '/');
    }

    private function headers(): array
    {
        $ua = 'CbtLaravel/1.0 (geocoder; ' . ($this->config['email'] ?? 'admin@example.com') . ')';

        return [
            'User-Agent' => $ua,
            'Accept' => 'application/json',
        ];
    }

    private function countryCodes(): string
    {
        $codes = trim((string) Konfigurasi::get('geocode_country', 'id'));

        return trim((string) $codes) !== '' ? strtolower((string) $codes) : '';
    }

    /**
     * Rank Nominatim results so the most useful points surface first.
     *
     * Rules:
     *  1. Prefer address-level results (street + house number) over vague
     *     place names.
     *  2. Boost results inside Indonesia (the school's country).
     *  3. Otherwise sort by Nominatim's built-in importance.
     */
    private function rank(array $rawResults, string $query): array
    {
        $candidates = [];

        foreach ($rawResults as $raw) {
            $relevance = $this->score($raw);

            $address = $raw['address'] ?? [];
            $country = $address['country'] ?? '';
            if ($country === 'Indonesia') {
                $relevance += 12;
            }

            $candidates[] = [
                'raw' => $raw,
                'relevance' => $relevance,
            ];
        }

        usort($candidates, static function ($a, $b) {
            return $b['relevance'] <=> $a['relevance'];
        });

        $ranked = [];
        foreach ($candidates as $candidate) {
            $raw = $candidate['raw'];
            if (count($ranked) >= $this->limit()) {
                break;
            }
            $ranked[] = $this->normalize($raw, $query);
        }

        return $ranked;
    }

    private function score(array $raw): int
    {
        $placeClass = $raw['class'] ?? '';
        $placeType = $raw['type'] ?? '';

        if ($placeClass === 'address') {
            $address = $raw['address'] ?? [];
            if (! empty($address['road']) && ! empty($address['house_number'])) {
                return 110;
            }
            return 100;
        }

        $amenityTypes = [
            'house_number',
            'street',
            'pedestrian',
            'services',
            'commercial',
            'warehouse',
            'hospital',
            'school',
            'college',
            'university',
            'bank',
            'hotel',
            'place_of_worship',
            'theatre',
            'cinema',
            'fast_food',
            'cafe',
            'restaurant',
            'marketplace',
            'shop',
        ];

        if ($placeClass === 'amenity' && in_array($placeType, $amenityTypes, true)) {
            return 85;
        }

        if ($placeClass === 'building') {
            return 82;
        }

        if ($placeClass === 'highway') {
            return 78;
        }

        if ($placeClass === 'place') {
            return 45;
        }

        if ($placeClass === 'boundary' || $placeClass === 'protected_area') {
            return 20;
        }

        if ($placeClass === 'country') {
            return 10;
        }

        return 30;
    }

    private function limit(): int
    {
        return (int) Konfigurasi::get('geocode_limit', 8);
    }

    private function normalize(array $result, string $query): array
    {
        $address = $result['address'] ?? [];
        $detail = $this->detailLine($address);

        return [
            'label' => $result['display_name'] ?? '',
            'detail' => $detail,
            'lat' => (string) $result['lat'],
            'lon' => (string) $result['lon'],
            'display_name' => $result['display_name'] ?? '',
            'type' => $result['type'] ?? '',
        ];
    }

    private function detailLine(array $address): string
    {
        $parts = [];

        if (! empty($address['postcode'])) {
            $parts[] = $address['postcode'];
        }
        if (! empty($address['city']) || ! empty($address['town']) || ! empty($address['village'])) {
            $parts[] = implode(
                ', ',
                array_filter([
                    $address['city'] ?? '',
                    $address['town'] ?? '',
                    $address['village'] ?? '',
                ])
            );
        }
        if (! empty($address['state'])) {
            $parts[] = $address['state'];
        }
        if (! empty($address['country'])) {
            $parts[] = $address['country'];
        }

        return implode(', ', $parts) ?: '';
    }
}
