<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\WatchmodeApiService;

class SubscriptionMostController extends Controller
{
    public function index()
    {
        return Inertia::render('SubscriptionMost');
    }

    public function analyze(Request $request, WatchmodeApiService $watchmode)
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
    
        // Ordenar por cantidad de películas por plataforma (descendente)
        $sorted = collect($result)
            ->map(fn($movies) => ['count' => count($movies), 'movies' => $movies])
            ->sortByDesc('count')
            ->all();
    
        return response()->json($sorted);
    }


}
