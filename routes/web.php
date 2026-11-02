<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\CashController;

use App\Models\GalleryImage;
use App\Services\PlayerRankingService;


/*
|--------------------------------------------------------------------------
| HALAMAN PUBLIK
|--------------------------------------------------------------------------
*/

Route::get('/', function (
    PlayerRankingService $rankingService
) {
    $topPlayers = $rankingService->all()
        ->filter(
            fn($player) => $player->rank_position <= 3
        );

    $galleryImages = GalleryImage::query()
        ->latest('id')
        ->limit(6)
        ->get();

    return view('pages.home', compact(
        'topPlayers',
        'galleryImages'
    ));
})->name('home');


Route::get('/pemain', [
    PlayerController::class,
    'index'
])->name('players.index');


Route::get('/pertandingan', [
    MatchController::class,
    'index'
])->name('matches.index');


Route::get('/kas', [
    CashController::class,
    'index'
])->name('cash.index');


/*
|--------------------------------------------------------------------------
| AUTENTIKASI
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [
        LoginController::class,
        'create'
    ])->name('login');

    Route::post('/login', [
        LoginController::class,
        'store'
    ])
        ->middleware('throttle:15,1')
        ->name('login.store');
});


/*
|--------------------------------------------------------------------------
| PENGGUNA LOGIN
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // LOGOUT

    Route::post('/logout', [
        LoginController::class,
        'destroy'
    ])->name('logout');


    // PROFIL

    Route::get('/profil', [
        ProfileController::class,
        'index'
    ])->name('profile.index');

    Route::patch('/profil/nama', [
        ProfileController::class,
        'updateName'
    ])->name('profile.name.update');

    Route::get('/profil/ganti-password', [
        ProfileController::class,
        'passwordForm'
    ])->name('profile.password.form');

    Route::put('/profil/password', [
        ProfileController::class,
        'updatePassword'
    ])
        ->middleware('throttle:5,1')
        ->name('profile.password.update');


    /*
    |--------------------------------------------------------------------------
    | PERTANDINGAN
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,pengelola,pemain')
        ->group(function () {

            Route::get('/pertandingan/tambah', [
                MatchController::class,
                'create'
            ])->name('matches.create');

            Route::post('/pertandingan', [
                MatchController::class,
                'store'
            ])->name('matches.store');

            Route::get('/pertandingan/{match}/edit', [
                MatchController::class,
                'edit'
            ])
                ->whereNumber('match')
                ->name('matches.edit');

            Route::patch('/pertandingan/{match}', [
                MatchController::class,
                'update'
            ])
                ->whereNumber('match')
                ->name('matches.update');
        });


    /*
    |--------------------------------------------------------------------------
    | PENGELOLA DAN ADMIN
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,pengelola')
        ->group(function () {

            // PEMAIN

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

            Route::put('/pemain/{player}', [
                PlayerController::class,
                'update'
            ])
                ->whereNumber('player')
                ->name('players.update');

            Route::delete('/pemain/{player}', [
                PlayerController::class,
                'destroy'
            ])
                ->whereNumber('player')
                ->name('players.destroy');


            // HAPUS PERTANDINGAN

            Route::delete('/pertandingan/{match}', [
                MatchController::class,
                'destroy'
            ])
                ->whereNumber('match')
                ->name('matches.destroy');


            // KEUANGAN

            Route::get('/kas/tambah', [
                CashController::class,
                'create'
            ])->name('cash.create');

            Route::post('/kas', [
                CashController::class,
                'store'
            ])->name('cash.store');

            Route::get('/kas/{transaction}/edit', [
                CashController::class,
                'edit'
            ])
                ->whereNumber('transaction')
                ->name('cash.edit');

            Route::put('/kas/{transaction}', [
                CashController::class,
                'update'
            ])
                ->whereNumber('transaction')
                ->name('cash.update');

            Route::delete('/kas/{transaction}', [
                CashController::class,
                'destroy'
            ])
                ->whereNumber('transaction')
                ->name('cash.destroy');
        });


    // INFORMASI AKUN PEMAIN

    Route::get('/pemain/akun-baru', [
        PlayerController::class,
        'credentials'
    ])->name('players.credentials');


    /*
    |--------------------------------------------------------------------------
    | PANEL ADMIN
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            require __DIR__ . '/admin.php';
        });
});


/*
|--------------------------------------------------------------------------
| TURNAMEN PUBLIK
|--------------------------------------------------------------------------
*/

require __DIR__ . '/tournaments.php';
