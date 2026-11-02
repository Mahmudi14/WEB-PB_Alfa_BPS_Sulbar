
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlayerController;
use App\Http\Controllers\Admin\PlayerAccountController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\TournamentResultController;

/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/

Route::get('/', [
    DashboardController::class,
    'index'
])->name('index');


/*
|--------------------------------------------------------------------------
| DATA PEMAIN
|--------------------------------------------------------------------------
*/

Route::get('/pemain', [
    PlayerController::class,
    'index'
])->name('players.index');


Route::get('/pemain/tambah', [
    PlayerController::class,
    'create'
])->name('players.create');


Route::post('/pemain', [
    PlayerController::class,
    'store'
])->name('players.store');


Route::get('/pemain/{player}/edit', [
    PlayerController::class,
    'edit'
])
    ->whereNumber('player')
    ->name('players.edit');


Route::get('/pemain/{player}', [
    PlayerController::class,
    'show'
])
    ->whereNumber('player')
    ->name('players.show');


Route::put('/pemain/{player}', [
    PlayerController::class,
    'update'
])
    ->whereNumber('player')
    ->name('players.update');


/*
|--------------------------------------------------------------------------
| ROLE, STATUS DAN PASSWORD
|--------------------------------------------------------------------------
*/

Route::patch('/pemain/{player}/role', [
    PlayerAccountController::class,
    'updateRole'
])
    ->whereNumber('player')
    ->name('players.role');


Route::patch('/pemain/{player}/status', [
    PlayerAccountController::class,
    'updateStatus'
])
    ->whereNumber('player')
    ->name('players.status');


Route::post('/pemain/{player}/reset-password', [
    PlayerAccountController::class,
    'resetPassword'
])
    ->whereNumber('player')
    ->middleware('throttle:10,1')
    ->name('players.reset-password');


/*
|--------------------------------------------------------------------------
| PERTANDINGAN ADMIN
|--------------------------------------------------------------------------
*/

Route::get('/pertandingan', [
    MatchController::class,
    'index'
])->name('matches.index');

Route::get('/pertandingan/tambah', [
    MatchController::class,
    'create'
])->name('matches.create');

Route::post('/pertandingan', [
    MatchController::class,
    'store'
])->name('matches.store');


/*
|--------------------------------------------------------------------------
| DETAIL DAN EDIT REGULER
|--------------------------------------------------------------------------
*/

Route::get('/pertandingan/{match}/edit', [
    MatchController::class,
    'edit'
])
    ->whereNumber('match')
    ->name('matches.edit');

Route::get('/pertandingan/{match}', [
    MatchController::class,
    'show'
])
    ->whereNumber('match')
    ->name('matches.show');

Route::patch('/pertandingan/{match}', [
    MatchController::class,
    'update'
])
    ->whereNumber('match')
    ->name('matches.update');

Route::delete('/pertandingan/{match}', [
    MatchController::class,
    'destroy'
])
    ->whereNumber('match')
    ->name('matches.destroy');


/*
|--------------------------------------------------------------------------
| DETAIL PERTANDINGAN TURNAMEN
|--------------------------------------------------------------------------
*/

Route::get('/pertandingan/turnamen/{fixture}', [
    MatchController::class,
    'showFixture'
])
    ->whereNumber('fixture')
    ->name('matches.fixtures.show');


/*
|--------------------------------------------------------------------------
| EDIT HASIL LIGA DAN CUP
|--------------------------------------------------------------------------
*/

Route::get(
    '/turnamen/{tournament}/pertandingan/{fixture}/edit',
    [
        TournamentResultController::class,
        'edit'
    ]
)
    ->whereNumber('tournament')
    ->whereNumber('fixture')
    ->name('tournaments.fixtures.edit');

Route::patch(
    '/turnamen/{tournament}/pertandingan/{fixture}',
    [
        TournamentResultController::class,
        'update'
    ]
)
    ->whereNumber('tournament')
    ->whereNumber('fixture')
    ->name('tournaments.fixtures.update');
