<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class MoviesStreamingController extends Controller
{
    public function index()
    {
        return Inertia::render('MoviesStreaming');
    }
}