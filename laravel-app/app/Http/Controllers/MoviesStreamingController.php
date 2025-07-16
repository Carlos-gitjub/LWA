<?php

namespace App\Http\Controllers;

use App\Http\Requests\MovieSearchRequest;
use App\Services\MoviesStreamingCoordinatorService;
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
    public function searchStreamingPlatformsForTitle(MovieSearchRequest $request, MoviesStreamingCoordinatorService $service)
    {
        $platforms = $service->getStreamingPlatformsForTitle($request->title, $request->region);
        return response()->json($platforms);
    }

    // Búsqueda por título (TMDB) para añadir a lista
    public function searchTmdbMovieTitle(Request $request, TmdbApiService $tmdb)
    {
        $title = $request->input('title');

        if (!$title) {
            return response()->json(null);
        }

        $result = $tmdb->searchMovieFirstMatchByTitle($title);

        if (!$result) {
            return response()->json(null);
        }

        return response()->json([
            'id' => $result['id'],
            'title' => $result['title'],
            'year' => substr($result['release_date'], 0, 4)
        ]);
    }

    public function analyzeSubscriptionPlatformsFromMovieList(Request $request, MoviesStreamingCoordinatorService $service)
    {
        $movies = $request->input('movies');
        $region = $request->input('region', 'ES');
    
        $result = $service->getSubscriptionPlatformsForList($movies, $region);
    
        return response()->json($result);
    }
    
}
