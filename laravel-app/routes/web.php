<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\MoviesStreamingController;

Route::get('/', function () {
    return Inertia::render('Home');
});

Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');

Route::get('/movies-streaming', [MoviesStreamingController::class, 'index'])->name('movies.streaming');
Route::get('/movies-streaming/advanced', fn () => Inertia::render('MoviesStreaming/Advanced'))->name('movies.streaming.advanced');
Route::get('/movies-streaming/advanced/subscription-most', [MoviesStreamingController::class, 'advancedSubscriptionMost'])
    ->middleware('auth')
    ->name('subscription.most');

Route::post('/movies-streaming/search', [MoviesStreamingController::class, 'searchStreamingPlatformsForTitle']);
Route::post('/api/movies/search-title', [MoviesStreamingController::class, 'searchTmdbMovieTitle']);
Route::post('/movies-streaming/advanced/subscription-most/analyze', [MoviesStreamingController::class, 'analyzeSubscriptionPlatformsFromMovieList'])
    ->middleware('auth')
    ->name('subscription.most.analyze');
    

require __DIR__.'/auth.php';
