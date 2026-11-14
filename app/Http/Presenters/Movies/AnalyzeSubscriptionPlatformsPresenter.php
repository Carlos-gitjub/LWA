<?php

namespace App\Http\Presenters\Movies;

use App\Domain\Movies\UseCases\AnalyzeSubscriptionPlatforms\AnalyzeSubscriptionPlatformsOutputBoundary;
use App\Domain\Movies\UseCases\AnalyzeSubscriptionPlatforms\AnalyzeSubscriptionPlatformsOutputData;

final class AnalyzeSubscriptionPlatformsPresenter implements AnalyzeSubscriptionPlatformsOutputBoundary
{
    public ?ViewModel $viewModel = null;

    public function present(AnalyzeSubscriptionPlatformsOutputData $output): void
    {
        $data = [];
        foreach ($output->platforms as $platform) {
            $data[] = [
                'id' => $platform['id'],
                'name' => $platform['name'],
                'count' => $platform['count'],
                'movies' => array_map(fn ($movie) => $movie->year === '' ? $movie->title : "{$movie->title} ({$movie->year})", $platform['movies']),
            ];
        }
        $this->viewModel = new ViewModel($data);
    }
}
