<?php

namespace App\Support;

use App\Models\Konfigurasi;

use GuzzleHttp\Client;

class GoogleGeocoder implements Geocoder
{
    public function __construct(
        private readonly Client $http,
        private readonly array $config,
    ) {
    }

    public function search(string $query, int $limit = 8): array
    {
        $params = [
            'address' => $query,
            'key' => $this->apiKey(),
            'region' => $this->region(),
        ];

        try {
            $response = $this->http->get($this->endpoint(), [
                'query' => $params,
                'timeout' => 15,
            ]);
            $body = json_decode((string) $response->getBody(), true);
        } catch (\Throwable) {
            return [];
        }

        if (! is_array($body) || ($body['status'] ?? '') !== 'OK') {
            return [];
        }

        $rawResults = $body['results'] ?? [];
        $ranked = [];

        foreach (array_slice($rawResults, 0, $this->limit()) as $raw) {
            $coords = $raw['geometry']['location'] ?? [];
            $componentTypes = $raw['types'] ?? [];

            // Prioritaskan hasil yang jelas merupakan alamat Indonesia.
            $components = $raw['address_components'] ?? [];
            $isIndonesia = collect($components)->some(
                static fn ($component) => in_array(config('geocoding.google.region', 'id'), $component['types'] ?? [], true)
            );

            $ranked[] = [
                'label' => $raw['formatted_address'] ?? '',
                'detail' => $this->detailLine($components),
                'lat' => (string) ($coords['lat'] ?? ''),
                'lon' => (string) ($coords['lng'] ?? ''),
                'display_name' => $raw['formatted_address'] ?? '',
                'type' => implode(',', $componentTypes),
                'is_indonesia' => $isIndonesia,
            ];
        }

        // Urutkan: hasil Indonesia duluan, lalu berdasarkan tingkat penting (importance).
        usort($ranked, static function ($a, $b) {
            $priorityA = $a['is_indonesia'] ? 1 : 0;
            $priorityB = $b['is_indonesia'] ? 1 : 0;
            if ($priorityA !== $priorityB) {
                return $priorityB <=> $priorityA;
            }

            $coordsA = $a['geometry']['location'] ?? [];
            $coordsB = $b['geometry']['location'] ?? [];
            $importanceA = $coordsA['importance'] ?? 0;
            $importanceB = $coordsB['importance'] ?? 0;

            return $importanceB <=> $importanceA;
        });

        return $ranked;
    }

    private function apiKey(): string
    {
        $key = $this->config['api_key'] ?? '';

        return trim((string) $key);
    }

    private function region(): string
    {
        return Konfigurasi::get('geocode_country', 'id');
    }

    private function endpoint(): string
    {
        return rtrim($this->config['endpoint'] ?? 'https://maps.googleapis.com/maps/api/geocode/json', '/');
    }

    private function limit(): int
    {
        return (int) ($this->config['limit'] ?? 8);
    }

    private function detailLine(array $components): string
    {
        $parts = [];

        foreach ($components as $c) {
            $types = $c['types'] ?? [];
            if (in_array('country', $types, true) && ($c['short_name'] ?? '') === 'ID') {
                $parts[] = 'Indonesia';
            }
            if (in_array('administrative_area_level_1', $types, true)) {
                $parts[] = $c['short_name'] ?? $c['long_name'] ?? '';
            }
            if (in_array('locality', $types) && empty($parts)) {
                $parts[] = $c['long_name'] ?? '';
            }
            if (in_array('postal_code', $types) && empty($parts)) {
                $parts[] = $c['short_name'] ?? $c['long_name'] ?? '';
            }
            if (in_array('route', $types)) {
                $parts[] = $c['long_name'] ?? '';
            }
        }

        return implode(', ', array_filter($parts));
    }
}
