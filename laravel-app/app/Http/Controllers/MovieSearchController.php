<?php

namespace App\Http\Controllers;

use App\Http\Requests\MovieSearchRequest;
use App\Services\MovieSearchService;

class MovieSearchController extends Controller
{
    public function search(MovieSearchRequest $request, MovieSearchService $service)
    {
        $platforms = $service->search($request->title, $request->region);
        return response()->json($platforms);
    }
}
