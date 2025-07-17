<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\MoviesStreamingController;
use App\Http\Controllers\LibraryController;

// Public routes (no auth required)
Route::get('/', function () {
    return Inertia::render('Home');
});

Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');

// Protected routes (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['verified'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

    Route::get('/movies-streaming', [MoviesStreamingController::class, 'index'])->name('movies.streaming');
    Route::get('/movies-streaming/advanced', fn () => Inertia::render('MoviesStreaming/Advanced'))->name('movies.streaming.advanced');
    Route::get('/movies-streaming/advanced/subscription-most', [MoviesStreamingController::class, 'advancedSubscriptionMost'])
        ->name('subscription.most');

    Route::post('/movies-streaming/search', [MoviesStreamingController::class, 'searchStreamingPlatformsForTitle'])->middleware('throttle:60,1');
    Route::post('/api/movies/search-title', [MoviesStreamingController::class, 'searchTmdbMovieTitle'])->middleware('throttle:500,1');
    Route::post('/movies-streaming/advanced/subscription-most/analyze', [MoviesStreamingController::class, 'analyzeSubscriptionPlatformsFromMovieList'])
        ->middleware('throttle:30,1')
        ->name('subscription.most.analyze');
        
    Route::prefix('library')->group(function () {
        Route::get('/', [LibraryController::class, 'index'])->name('library.index');
        Route::get('/search', [LibraryController::class, 'search'])->name('library.search');
        Route::post('/store', [LibraryController::class, 'store'])->name('library.store');
    });
});

require __DIR__.'/auth.php';
