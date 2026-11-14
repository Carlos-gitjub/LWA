<?php

namespace Tests\Integration\Movies\Adapters;

use App\Infrastructure\Movies\HttpClients\TmdbApiClient;
use App\Infrastructure\Movies\HttpClients\WatchmodeApiClient;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WatchmodeStreamingOfferLookupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    #[Test]
    public function the_adapter_returns_all_offer_types_for_use_case_filtering()
    {
        Http::fake([
            // TMDb
            'api.themoviedb.org/3/movie/101/external_ids*' => Http::response(['imdb_id' => 'tt101']),
            // Watchmode
            'api.watchmode.com/v1/search*' => Http::response([
                'title_results' => [['id' => 789]],
            ]),
            'api.watchmode.com/v1/title/789/sources*' => Http::response([
                ['name' => 'HBO', 'type' => 'sub'],
                ['name' => 'Amazon', 'type' => 'buy'],
            ]),
        ]);

        $service = new WatchmodeApiClient;
        $adapter = new \App\Infrastructure\Movies\Adapters\WatchmodeStreamingOfferLookup(new TmdbApiClient, $service);
        $offers = $adapter->forMovie('101', 'ES');

        $this->assertSame(['HBO', 'Amazon'], array_map(fn ($offer) => $offer->platform, $offers));
        $this->assertSame(['sub', 'buy'], array_map(fn ($offer) => $offer->type, $offers));
    }

    #[Test]
    public function it_returns_empty_array_if_no_imdb_id()
    {
        Http::fake([
            'api.themoviedb.org/3/movie/102/external_ids*' => Http::response(['imdb_id' => null]),
        ]);

        $service = new WatchmodeApiClient;
        $adapter = new \App\Infrastructure\Movies\Adapters\WatchmodeStreamingOfferLookup(new TmdbApiClient, $service);
        $platforms = $adapter->forMovie('102', 'ES');

        $this->assertEquals([], $platforms);
    }
}
