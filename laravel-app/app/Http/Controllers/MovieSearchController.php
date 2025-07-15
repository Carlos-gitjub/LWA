<?php

namespace App\Http\Controllers;

use App\Http\Requests\MovieSearchRequest;
use App\Services\MovieSearchService;
use Illuminate\Http\Request;


class MovieSearchController extends Controller
{
    public function search(MovieSearchRequest $request, MovieSearchService $service)
    {
        $platforms = $service->search($request->title, $request->region);
        return response()->json($platforms);
    }

    public function searchByTitle(Request $request)
    {
        $title = $request->input('title');

        if (!$title) {
            return response()->json(null);
        }

        $result = app(\App\Services\TmdbApiService::class)->searchMovie($title);

        if (!$result) {
            return response()->json(null);
        }

        return response()->json([
            'id' => $result['id'],
            'title' => $result['title'],
            'year' => substr($result['release_date'], 0, 4)
        ]);
    }

}
