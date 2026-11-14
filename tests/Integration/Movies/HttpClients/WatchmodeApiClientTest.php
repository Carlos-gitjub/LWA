<?php

namespace Tests\Integration\Movies\HttpClients;

use App\Infrastructure\Movies\HttpClients\WatchmodeApiClient;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WatchmodeApiClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    protected function makeService(): WatchmodeApiClient
    {
        return new WatchmodeApiClient;
    }

    #[Test]
    public function it_returns_first_match_by_imdb_id()
    {
        Http::fake([
            'api.watchmode.com/v1/search*' => Http::response([
                'title_results' => [['id' => 456, 'title' => 'Test']],
            ]),
        ]);

        $service = $this->makeService();
        $result = $service->searchTitleFirstMatchByImdbId('tt123');

        $this->assertIsArray($result);
        $this->assertEquals(456, $result['id']);
    }

    #[Test]
    public function it_returns_null_if_no_watchmode_result()
    {
        Http::fake([
            'api.watchmode.com/v1/search*' => Http::response([
                'title_results' => [],
            ]),
        ]);

        $service = $this->makeService();
        $result = $service->searchTitleFirstMatchByImdbId('tt0000000');

        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_sources_by_watchmode_id()
    {
        Http::fake([
            'api.watchmode.com/v1/title/456/sources*' => Http::response([
                ['name' => 'Netflix', 'type' => 'sub'],
            ]),
        ]);

        $service = $this->makeService();
        $sources = $service->getStreamingSourcesByWatchmodeId(456, 'ES');

        $this->assertIsArray($sources);
        $this->assertEquals('Netflix', $sources[0]['name']);
    }

}
