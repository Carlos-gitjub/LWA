<?php

namespace App\Http\Controllers\Movies;

use App\Domain\Movies\UseCases\AnalyzeSubscriptionPlatforms\AnalyzeSubscriptionPlatformsInputBoundary;
use App\Domain\Movies\UseCases\AnalyzeSubscriptionPlatforms\AnalyzeSubscriptionPlatformsInputData;
use App\Domain\Movies\UseCases\Data\MovieData;
use App\Domain\Movies\UseCases\FindMovieByTitle\FindMovieByTitleInputBoundary;
use App\Domain\Movies\UseCases\FindMovieByTitle\FindMovieByTitleInputData;
use App\Domain\Movies\UseCases\FindStreamingOffersForTitle\FindStreamingOffersForTitleInputBoundary;
use App\Domain\Movies\UseCases\FindStreamingOffersForTitle\FindStreamingOffersForTitleInputData;
use App\Domain\Movies\UseCases\ProviderUnavailable;
use App\Http\Controllers\Controller;
use App\Http\Presenters\Movies\AnalyzeSubscriptionPlatformsPresenter;
use App\Http\Presenters\Movies\FindMovieByTitlePresenter;
use App\Http\Presenters\Movies\FindStreamingOffersForTitlePresenter;
use App\Http\Requests\Movies\AnalyzeSubscriptionPlatformsRequest;
use App\Http\Requests\Movies\FindMovieRequest;
use App\Http\Requests\Movies\MovieSearchRequest;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class MoviesStreamingController extends Controller
{
    public function index()
    {
        return Inertia::render('MoviesStreaming/Index');
    }

    public function advancedSubscriptionMost()
    {
        return Inertia::render('MoviesStreaming/SubscriptionMost');
    }

    public function searchStreamingPlatformsForTitle(
        MovieSearchRequest $request,
        FindStreamingOffersForTitleInputBoundary $useCase,
        FindStreamingOffersForTitlePresenter $presenter,
    ): JsonResponse {
        $data = $request->validated();

        return $this->respond(function () use ($data, $useCase, $presenter) {
            $useCase->execute(new FindStreamingOffersForTitleInputData($data['title'], $data['region']));

            return $presenter->viewModel->data;
        });
    }

    public function searchTmdbMovieTitle(
        FindMovieRequest $request,
        FindMovieByTitleInputBoundary $useCase,
        FindMovieByTitlePresenter $presenter,
    ): JsonResponse {
        return $this->respond(function () use ($request, $useCase, $presenter) {
            $useCase->execute(new FindMovieByTitleInputData($request->validated('title')));

            return $presenter->viewModel->data;
        });
    }

    public function analyzeSubscriptionPlatformsFromMovieList(
        AnalyzeSubscriptionPlatformsRequest $request,
        AnalyzeSubscriptionPlatformsInputBoundary $useCase,
        AnalyzeSubscriptionPlatformsPresenter $presenter,
    ): JsonResponse {
        $data = $request->validated();
        $movies = array_map(fn ($movie) => new MovieData(
            (string) $movie['id'], $movie['title'], $movie['year'] ?? '',
        ), $data['movies']);

        return $this->respond(function () use ($data, $movies, $useCase, $presenter) {
            $useCase->execute(new AnalyzeSubscriptionPlatformsInputData($movies, $data['region'] ?? 'ES'));

            return $presenter->viewModel->data;
        });
    }

    private function respond(callable $operation): JsonResponse
    {
        try {
            $data = $operation();

            return $data === null
                ? JsonResponse::fromJsonString('null')
                : response()->json($data);
        } catch (ProviderUnavailable) {
            return response()->json(['message' => 'Could not complete the operation. Please try again.'], 503);
        } catch (\InvalidArgumentException $error) {
            return response()->json(['message' => $error->getMessage()], 422);
        }
    }
}
