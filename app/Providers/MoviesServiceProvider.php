<?php

namespace App\Providers;

use App\Domain\Movies\UseCases\AnalyzeSubscriptionPlatforms\AnalyzeSubscriptionPlatformsInputBoundary;
use App\Domain\Movies\UseCases\AnalyzeSubscriptionPlatforms\AnalyzeSubscriptionPlatformsInteractor;
use App\Domain\Movies\UseCases\AnalyzeSubscriptionPlatforms\AnalyzeSubscriptionPlatformsOutputBoundary;
use App\Domain\Movies\UseCases\DataAccessInterfaces\MovieLookup;
use App\Domain\Movies\UseCases\DataAccessInterfaces\StreamingOfferLookup;
use App\Domain\Movies\UseCases\FindMovieByTitle\FindMovieByTitleInputBoundary;
use App\Domain\Movies\UseCases\FindMovieByTitle\FindMovieByTitleInteractor;
use App\Domain\Movies\UseCases\FindMovieByTitle\FindMovieByTitleOutputBoundary;
use App\Domain\Movies\UseCases\FindStreamingOffersForTitle\FindStreamingOffersForTitleInputBoundary;
use App\Domain\Movies\UseCases\FindStreamingOffersForTitle\FindStreamingOffersForTitleInteractor;
use App\Domain\Movies\UseCases\FindStreamingOffersForTitle\FindStreamingOffersForTitleOutputBoundary;
use App\Http\Presenters\Movies\AnalyzeSubscriptionPlatformsPresenter;
use App\Http\Presenters\Movies\FindMovieByTitlePresenter;
use App\Http\Presenters\Movies\FindStreamingOffersForTitlePresenter;
use App\Infrastructure\Movies\Adapters\TmdbMovieLookup;
use App\Infrastructure\Movies\Adapters\WatchmodeStreamingOfferLookup;
use Illuminate\Support\ServiceProvider;

final class MoviesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MovieLookup::class, TmdbMovieLookup::class);
        $this->app->bind(StreamingOfferLookup::class, WatchmodeStreamingOfferLookup::class);
        $this->app->bind(FindMovieByTitleInputBoundary::class, FindMovieByTitleInteractor::class);
        $this->app->scoped(FindMovieByTitlePresenter::class);
        $this->app->bind(FindMovieByTitleOutputBoundary::class, fn ($app) => $app->make(FindMovieByTitlePresenter::class));
        $this->app->bind(FindStreamingOffersForTitleInputBoundary::class, FindStreamingOffersForTitleInteractor::class);
        $this->app->scoped(FindStreamingOffersForTitlePresenter::class);
        $this->app->bind(FindStreamingOffersForTitleOutputBoundary::class, fn ($app) => $app->make(FindStreamingOffersForTitlePresenter::class));
        $this->app->bind(AnalyzeSubscriptionPlatformsInputBoundary::class, AnalyzeSubscriptionPlatformsInteractor::class);
        $this->app->scoped(AnalyzeSubscriptionPlatformsPresenter::class);
        $this->app->bind(AnalyzeSubscriptionPlatformsOutputBoundary::class, fn ($app) => $app->make(AnalyzeSubscriptionPlatformsPresenter::class));
    }
}
