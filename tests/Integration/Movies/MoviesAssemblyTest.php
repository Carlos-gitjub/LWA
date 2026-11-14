<?php

namespace Tests\Integration\Movies;

use App\Domain\Movies\UseCases\AnalyzeSubscriptionPlatforms\AnalyzeSubscriptionPlatformsInputBoundary;
use App\Domain\Movies\UseCases\AnalyzeSubscriptionPlatforms\AnalyzeSubscriptionPlatformsInputData;
use App\Domain\Movies\UseCases\Data\MovieData;
use App\Http\Presenters\Movies\AnalyzeSubscriptionPlatformsPresenter;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MoviesAssemblyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    #[Test]
    public function it_returns_subscription_platforms_for_movie_list()
    {
        Http::fake([
            // TMDb
            'api.themoviedb.org/3/movie/101/external_ids*' => Http::response(['imdb_id' => 'tt001']),
            // Watchmode
            'api.watchmode.com/v1/search*' => Http::response([
                'title_results' => [['id' => 789]],
            ]),
            'api.watchmode.com/v1/title/789/sources*' => Http::response([
                ['name' => 'Disney+', 'type' => 'sub'],
                ['name' => 'Disney+', 'type' => 'sub'], // duplicate, to test `unique()`
                ['name' => 'Amazon', 'type' => 'buy'],   // not 'sub', should be ignored
            ]),
        ]);

        $interactor = $this->app->make(AnalyzeSubscriptionPlatformsInputBoundary::class);
        $interactor->execute(new AnalyzeSubscriptionPlatformsInputData([
            new MovieData('101', 'Test', '2021'),
        ], 'ES'));
        $result = $this->app->make(AnalyzeSubscriptionPlatformsPresenter::class)->viewModel->data;

        $this->assertCount(1, $result);
        $this->assertSame('Disney+', $result[0]['name']);
        $this->assertEquals(1, $result[0]['count']);
        $this->assertContains('Test (2021)', $result[0]['movies']);
    }
}
