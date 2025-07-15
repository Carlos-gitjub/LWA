<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\MoviesStreamingController;
use App\Http\Controllers\MovieSearchController;
use App\Http\Controllers\SubscriptionMostController;

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

Route::post('/movies-streaming/search', [MovieSearchController::class, 'search']);

Route::get('/movies-streaming/advanced', function () {
    return Inertia::render('MoviesStreaming/Advanced');
})->name('movies.streaming.advanced');

Route::get('/movies-streaming/advanced/subscription-most', [SubscriptionMostController::class, 'index'])
    ->middleware(['auth'])
    ->name('subscription.most');

Route::post('/api/movies/search-title', [MovieSearchController::class, 'searchByTitle']);

require __DIR__.'/auth.php';
