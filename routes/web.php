
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\PlayerController as PublicPlayerController;
use App\Http\Controllers\MatchController as PublicMatchController;
use App\Http\Controllers\CashController as PublicCashController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlayerController as AdminPlayerController;
use App\Http\Controllers\Admin\MatchController as AdminMatchController;
use App\Http\Controllers\Admin\CashController as AdminCashController;
use App\Http\Controllers\Admin\TournamentController as AdminTournamentController;
use App\Http\Controllers\Admin\TournamentResultController as AdminTournamentResultController;

use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\InactivePlayerController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\GalleryController;

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
    PublicPlayerController::class,
    'index'
])->name('players.index');

Route::get('/pertandingan', [
    PublicMatchController::class,
    'index'
])->name('matches.index');

Route::get('/kas', [
    PublicCashController::class,
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
    | PERTANDINGAN PUBLIK - PENGGUNA LOGIN
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,pengelola,pemain')
        ->group(function () {

            Route::get('/pertandingan/tambah', [
                PublicMatchController::class,
                'create'
            ])->name('matches.create');

            Route::post('/pertandingan', [
                PublicMatchController::class,
                'store'
            ])->name('matches.store');

            Route::get('/pertandingan/{match}/edit', [
                PublicMatchController::class,
                'edit'
            ])
                ->whereNumber('match')
                ->name('matches.edit');

            Route::patch('/pertandingan/{match}', [
                PublicMatchController::class,
                'update'
            ])
                ->whereNumber('match')
                ->name('matches.update');
        });


    /*
    |--------------------------------------------------------------------------
    | PENGELOLA DAN ADMIN - HALAMAN PUBLIK
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,pengelola')
        ->group(function () {

            // PEMAIN

            Route::get('/pemain/tambah', [
                PublicPlayerController::class,
                'create'
            ])->name('players.create');

            Route::post('/pemain', [
                PublicPlayerController::class,
                'store'
            ])->name('players.store');

            Route::get('/pemain/{player}/edit', [
                PublicPlayerController::class,
                'edit'
            ])
                ->whereNumber('player')
                ->name('players.edit');

            Route::put('/pemain/{player}', [
                PublicPlayerController::class,
                'update'
            ])
                ->whereNumber('player')
                ->name('players.update');

            Route::delete('/pemain/{player}', [
                PublicPlayerController::class,
                'destroy'
            ])
                ->whereNumber('player')
                ->name('players.destroy');


            // PERTANDINGAN

            Route::delete('/pertandingan/{match}', [
                PublicMatchController::class,
                'destroy'
            ])
                ->whereNumber('match')
                ->name('matches.destroy');


            // KEUANGAN

            Route::get('/kas/tambah', [
                PublicCashController::class,
                'create'
            ])->name('cash.create');

            Route::post('/kas', [
                PublicCashController::class,
                'store'
            ])->name('cash.store');

            Route::get('/kas/{transaction}/edit', [
                PublicCashController::class,
                'edit'
            ])
                ->whereNumber('transaction')
                ->name('cash.edit');

            Route::put('/kas/{transaction}', [
                PublicCashController::class,
                'update'
            ])
                ->whereNumber('transaction')
                ->name('cash.update');

            Route::delete('/kas/{transaction}', [
                PublicCashController::class,
                'destroy'
            ])
                ->whereNumber('transaction')
                ->name('cash.destroy');
        });


    // INFORMASI AKUN PEMAIN

    Route::get('/pemain/akun-baru', [
        PublicPlayerController::class,
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

            // DASHBOARD

            Route::get('/', [
                DashboardController::class,
                'index'
            ])->name('index');


            // DATA PEMAIN

            Route::get('/pemain', [
                AdminPlayerController::class,
                'index'
            ])->name('players.index');

            Route::get('/pemain/tambah', [
                AdminPlayerController::class,
                'create'
            ])->name('players.create');

            Route::post('/pemain', [
                AdminPlayerController::class,
                'store'
            ])->name('players.store');

            Route::get('/pemain/{player}/edit', [
                AdminPlayerController::class,
                'edit'
            ])
                ->whereNumber('player')
                ->name('players.edit');

            Route::put('/pemain/{player}', [
                AdminPlayerController::class,
                'update'
            ])
                ->whereNumber('player')
                ->name('players.update');

            Route::delete('/pemain/{player}', [
                AdminPlayerController::class,
                'destroy'
            ])
                ->whereNumber('player')
                ->name('players.destroy');


            // PERTANDINGAN

            Route::get('/pertandingan', [
                AdminMatchController::class,
                'index'
            ])->name('matches.index');

            Route::get('/pertandingan/tambah', [
                AdminMatchController::class,
                'create'
            ])->name('matches.create');

            Route::post('/pertandingan', [
                AdminMatchController::class,
                'store'
            ])->name('matches.store');

            Route::get('/pertandingan/{match}/edit', [
                AdminMatchController::class,
                'edit'
            ])
                ->whereNumber('match')
                ->name('matches.edit');

            Route::patch('/pertandingan/{match}', [
                AdminMatchController::class,
                'update'
            ])
                ->whereNumber('match')
                ->name('matches.update');

            Route::delete('/pertandingan/{match}', [
                AdminMatchController::class,
                'destroy'
            ])
                ->whereNumber('match')
                ->name('matches.destroy');


            // TURNAMEN

            Route::get('/turnamen', [
                AdminTournamentController::class,
                'index'
            ])->name('tournaments.index');

            Route::get('/turnamen/buat', [
                AdminTournamentController::class,
                'create'
            ])->name('tournaments.create');

            Route::post('/turnamen', [
                AdminTournamentController::class,
                'store'
            ])->name('tournaments.store');

            Route::get('/turnamen/{tournament}', [
                AdminTournamentController::class,
                'show'
            ])
                ->whereNumber('tournament')
                ->name('tournaments.show');

            Route::get('/turnamen/{tournament}/aturan', [
                AdminTournamentController::class,
                'editRules'
            ])
                ->whereNumber('tournament')
                ->name('tournaments.rules.edit');

            Route::put('/turnamen/{tournament}/aturan', [
                AdminTournamentController::class,
                'updateRules'
            ])
                ->whereNumber('tournament')
                ->name('tournaments.rules.update');

            Route::get('/turnamen/{tournament}/peserta', [
                AdminTournamentController::class,
                'entries'
            ])
                ->whereNumber('tournament')
                ->name('tournaments.entries');

            Route::put('/turnamen/{tournament}/peserta', [
                AdminTournamentController::class,
                'saveEntries'
            ])
                ->whereNumber('tournament')
                ->name('tournaments.entries.save');

            Route::get('/turnamen/{tournament}/bagan', [
                AdminTournamentController::class,
                'schedule'
            ])
                ->whereNumber('tournament')
                ->name('tournaments.schedule');

            Route::post('/turnamen/{tournament}/bagan', [
                AdminTournamentController::class,
                'start'
            ])
                ->whereNumber('tournament')
                ->name('tournaments.start');

            Route::get(
                '/turnamen/{tournament}/pertandingan/{fixture}/edit',
                [
                    AdminTournamentResultController::class,
                    'edit'
                ]
            )
                ->whereNumber('tournament')
                ->whereNumber('fixture')
                ->name('tournaments.fixtures.edit');

            Route::patch(
                '/turnamen/{tournament}/pertandingan/{fixture}',
                [
                    AdminTournamentResultController::class,
                    'update'
                ]
            )
                ->whereNumber('tournament')
                ->whereNumber('fixture')
                ->name('tournaments.fixtures.update');

            Route::delete('/turnamen/{tournament}', [
                AdminTournamentController::class,
                'destroy'
            ])
                ->whereNumber('tournament')
                ->name('tournaments.destroy');


            // KEUANGAN

            Route::get('/keuangan', [
                AdminCashController::class,
                'index'
            ])->name('cash.index');

            Route::get('/keuangan/tambah', [
                AdminCashController::class,
                'create'
            ])->name('cash.create');

            Route::post('/keuangan', [
                AdminCashController::class,
                'store'
            ])->name('cash.store');

            Route::get('/keuangan/{transaction}/edit', [
                AdminCashController::class,
                'edit'
            ])
                ->whereNumber('transaction')
                ->name('cash.edit');

            Route::put('/keuangan/{transaction}', [
                AdminCashController::class,
                'update'
            ])
                ->whereNumber('transaction')
                ->name('cash.update');

            Route::delete('/keuangan/{transaction}', [
                AdminCashController::class,
                'destroy'
            ])
                ->whereNumber('transaction')
                ->name('cash.destroy');


            // PENGGUNA DAN ROLE

            Route::get('/pengguna', [
                RoleController::class,
                'index'
            ])->name('users');

            Route::patch('/pengguna/{user}/role', [
                RoleController::class,
                'update'
            ])
                ->whereNumber('user')
                ->name('users.update');

            Route::post(
                '/pengguna/{user}/reset-password',
                [
                    RoleController::class,
                    'resetPassword'
                ]
            )
                ->whereNumber('user')
                ->middleware('throttle:10,1')
                ->name('users.reset-password');


            // PEMAIN NONAKTIF

            Route::get('/pemain-nonaktif', [
                InactivePlayerController::class,
                'index'
            ])->name('players.inactive');

            Route::patch(
                '/pemain-nonaktif/{id}/restore',
                [
                    InactivePlayerController::class,
                    'restore'
                ]
            )
                ->whereNumber('id')
                ->name('players.restore');


            // PENGATURAN WEBSITE

            Route::get('/pengaturan', [
                SiteSettingController::class,
                'edit'
            ])->name('settings.edit');

            Route::put('/pengaturan', [
                SiteSettingController::class,
                'update'
            ])->name('settings.update');

            Route::delete('/pengaturan/logo', [
                SiteSettingController::class,
                'deleteLogo'
            ])->name('settings.logo.delete');


            // GALERI

            Route::get('/galeri', [
                GalleryController::class,
                'index'
            ])->name('gallery.index');

            Route::post('/galeri', [
                GalleryController::class,
                'store'
            ])
                ->middleware('throttle:10,1')
                ->name('gallery.store');

            Route::delete('/galeri/{image}', [
                GalleryController::class,
                'destroy'
            ])
                ->whereNumber('image')
                ->name('gallery.destroy');
        });
});


/*
|--------------------------------------------------------------------------
| TURNAMEN PUBLIK
|--------------------------------------------------------------------------
*/

require __DIR__ . '/tournaments.php';
