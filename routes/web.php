<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\GameController as AdminGameController;
use App\Http\Controllers\Admin\SportController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Facilitator\DashboardController as FacilitatorDashboard;
use App\Http\Controllers\Facilitator\MatchController;
use App\Http\Controllers\Facilitator\MedalController;
use App\Http\Controllers\Public\GameController;
use App\Http\Controllers\Public\TallyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC — no login required
|--------------------------------------------------------------------------
*/
Route::get('/', [TallyController::class, 'index'])->name('public.tally');
Route::get('/sports/{sport}', [GameController::class, 'index'])->name('public.sport');
Route::get('/sports/{sport}/games/{game}', [GameController::class, 'show'])->name('public.game');

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::get('/divmeetsanjose', [LoginController::class, 'create'])->name('login')->middleware('guest');
Route::post('/divmeetsanjose', [LoginController::class, 'store'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| SPORT FACILITATOR
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:facilitator'])->prefix('facilitator')->name('facilitator.')->group(function () {
    Route::get('/', [FacilitatorDashboard::class, 'index'])->name('dashboard');

    Route::get('/games/{game}/matches', [MatchController::class, 'index'])->name('matches.index');
    Route::post('/games/{game}/matches', [MatchController::class, 'store'])->name('matches.store');
    Route::put('/games/{game}/bracket', [MatchController::class, 'updateBracket'])->name('bracket.update');
    Route::put('/games/{game}/matches/{match}', [MatchController::class, 'update'])->name('matches.update');
    Route::delete('/games/{game}/matches/{match}', [MatchController::class, 'destroy'])->name('matches.destroy');

    Route::get('/games/{game}/medals', [MedalController::class, 'index'])->name('medals.index');
    Route::post('/games/{game}/medals', [MedalController::class, 'store'])->name('medals.store');
});

/*
|--------------------------------------------------------------------------
| SUPER ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboard::class, 'index'])->name('dashboard');
    Route::put('/event-settings', [AdminDashboard::class, 'updateEventSettings'])->name('event-settings.update');

    Route::get('/sports', [SportController::class, 'index'])->name('sports.index');
    Route::post('/sports', [SportController::class, 'store'])->name('sports.store');
    Route::put('/sports/{sport}', [SportController::class, 'update'])->name('sports.update');
    Route::delete('/sports/{sport}', [SportController::class, 'destroy'])->name('sports.destroy');

    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');

    Route::get('/games', [AdminGameController::class, 'index'])->name('games.index');
    Route::post('/games', [AdminGameController::class, 'store'])->name('games.store');
    Route::put('/games/{game}', [AdminGameController::class, 'update'])->name('games.update');
    Route::delete('/games/{game}', [AdminGameController::class, 'destroy'])->name('games.destroy');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Super admin can also manage brackets/medals for ANY sport (reuse facilitator views/logic
    // via the same controllers, but without the sport_id restriction). Simplest: log in as
    // facilitator-scoped admin is out of scope — admins manage setup; facilitators run the games.
});
