<?php

namespace Tests\Unit;

use App\Support\GeocoderFactory;
use App\Support\NominatimGeocoder;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeocoderTest extends TestCase
{
    use RefreshDatabase;
    public function test_nominatim_ranks_indonesia_address_results_first(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->nominatimBody([
                // Generic, non-Indonesia place: should be deprioritized.
                $this->nominatimResult('city', 'City', 106.9166667, -6.3166667, 'South City'),
                // Address-level result inside Indonesia: should rank first.
                $this->nominatimResult('address', 'Jl. Merdeka No. 12', 106.8166667, -6.2000000, 'Merdeka Tower'),
            ])),
        ]);

        $geocoder = new NominatimGeocoder(
            new Client(['handler' => $mock, 'timeout' => 15]),
            [
                'endpoint' => 'https://nominatim.openstreetmap.org',
                'countrycodes' => 'id',
                'limit' => 8,
                'email' => 'test@example.com',
            ]
        );

        $results = $geocoder->search('Gedung Utama');

        $this->assertNotEmpty($results);
        $this->assertSame('Merdeka Tower', $results[0]['label']);
        $this->assertSame('-6.2', $results[0]['lat']);
        $this->assertSame('106.8166667', $results[0]['lon']);
    }

    public function test_nominatim_returns_empty_for_unknown_query(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->nominatimBody([])),
        ]);

        $geocoder = new NominatimGeocoder(
            new Client(['handler' => $mock, 'timeout' => 15]),
            [
                'endpoint' => 'https://nominatim.openstreetmap.org',
                'countrycodes' => 'id',
                'limit' => 8,
            ]
        );

        $this->assertSame([], $geocoder->search('Root-ID-1994 Developer'));
    }

    public function test_factory_uses_nominatim_by_default(): void
    {
        config()->set('geocoding.driver', 'nominatim');

        $geocoder = GeocoderFactory::forAdmin(app('request'));

        $this->assertInstanceOf(NominatimGeocoder::class, $geocoder);
    }

    private function nominatimBody(array $results): string
    {
        return json_encode([
            'status' => 'OK',
            'results' => $results,
        ]);
    }

    private function nominatimResult(string $class, string $type, float $lon, float $lat, string $name): array
    {
        return [
            'place_id' => 1,
            'osm_type' => 'relation',
            'osm_id' => 2,
            'lat' => (string) $lat,
            'lon' => (string) $lon,
            'display_name' => $name,
            'class' => $class,
            'type' => $type,
            'importance' => 0.7,
            'address' => ['country' => 'Indonesia'],
        ];
    }
}
