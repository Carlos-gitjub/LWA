<?php

namespace Tests\Unit\Movies\Presenters;

use App\Domain\Movies\UseCases\AnalyzeSubscriptionPlatforms\AnalyzeSubscriptionPlatformsOutputData;
use App\Domain\Movies\UseCases\Data\MovieData;
use App\Http\Presenters\Movies\AnalyzeSubscriptionPlatformsPresenter;
use PHPUnit\Framework\TestCase;

final class AnalyzeSubscriptionPlatformsPresenterTest extends TestCase
{
    public function test_same_name_platforms_keep_distinct_ids_and_ranking_in_presented_results(): void
    {
        $presenter = new AnalyzeSubscriptionPlatformsPresenter;
        $presenter->present(new AnalyzeSubscriptionPlatformsOutputData([
            ['id' => '10', 'name' => 'Same name', 'count' => 2, 'movies' => [new MovieData('a', 'First', '2020'), new MovieData('b', 'Second', '')]],
            ['id' => '2', 'name' => 'Same name', 'count' => 1, 'movies' => [new MovieData('a', 'First', '2020')]],
        ]));

        self::assertSame([
            ['id' => '10', 'name' => 'Same name', 'count' => 2, 'movies' => ['First (2020)', 'Second']],
            ['id' => '2', 'name' => 'Same name', 'count' => 1, 'movies' => ['First (2020)']],
        ], $presenter->viewModel->data);
    }
}
