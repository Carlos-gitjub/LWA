<?php

namespace App\Http\Controllers;

use App\Http\Requests\MovieSearchRequest;
use App\Services\MovieSearchService;
use App\Services\WatchmodeApiService;
use App\Services\TmdbApiService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MoviesStreamingController extends Controller
{
    // Página principal
    public function index()
    {
        return Inertia::render('MoviesStreaming');
    }

    // Página avanzada: Subscription Most
    public function advancedSubscriptionMost()
    {
        return Inertia::render('SubscriptionMost');
    }

    // Búsqueda simple: plataformas por título
    public function search(MovieSearchRequest $request, MovieSearchService $service)
    {
        $platforms = $service->search($request->title, $request->region);
        return response()->json($platforms);
    }

    // Búsqueda por título (TMDB) para añadir a lista
    public function searchByTitle(Request $request, TmdbApiService $tmdb)
    {
        $title = $request->input('title');

        if (!$title) {
            return response()->json(null);
        }

        $result = $tmdb->searchMovie($title);

        if (!$result) {
            return response()->json(null);
        }

        return response()->json([
            'id' => $result['id'],
            'title' => $result['title'],
            'year' => substr($result['release_date'], 0, 4)
        ]);
    }

    // Análisis de plataformas de suscripción más frecuentes
    public function analyzeSubscription(Request $request, WatchmodeApiService $watchmode)
    {
        $movies = $request->input('movies');
        $region = $request->input('region', 'ES');
    
        $result = [];

        foreach ($movies as $movie) {
            $tmdbId = $movie['id'];
            $title = $movie['title'];
            $year = $movie['year'];

            $platforms = $watchmode->getStreamingPlatforms($tmdbId, $region);

            foreach ($platforms as $platform) {
                $result[$platform][] = "{$title} ({$year})";
            }
        }

        // Ordenar por cantidad de títulos por plataforma
        $sorted = collect($result)
            ->map(fn($movies) => ['count' => count($movies), 'movies' => $movies])
            ->sortByDesc('count')
            ->all();

        return response()->json($sorted);
    }
}
