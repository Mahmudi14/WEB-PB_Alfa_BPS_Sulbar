
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\CashController;

use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\InactivePlayerController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\ProfileController;

use App\Models\GalleryImage;
use App\Services\PlayerRankingService;


/*
|--------------------------------------------------------------------------
| 1. HALAMAN PUBLIK
|--------------------------------------------------------------------------
*/

// Landing Page
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


// Daftar Pemain dan Ranking
Route::get('/pemain', [
    PlayerController::class,
    'index'
])->name('players.index');


// Riwayat Pertandingan
Route::get('/pertandingan', [
    MatchController::class,
    'index'
])->name('matches.index');


// Keuangan / Kas
Route::get('/kas', [
    CashController::class,
    'index'
])->name('cash.index');


/*
|--------------------------------------------------------------------------
| 2. AUTENTIKASI
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
| 3. PENGGUNA LOGIN
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Logout semua role
    Route::post('/logout', [
        LoginController::class,
        'destroy'
    ])->name('logout');

    // Profil Saya

    Route::get('/profil', [
        ProfileController::class,
        'index',
    ])->name('profile.index');

    Route::patch('/profil/nama', [
        ProfileController::class,
        'updateName',
    ])->name('profile.name.update');

    Route::get('/profil/ganti-password', [
        ProfileController::class,
        'passwordForm',
    ])->name('profile.password.form');

    Route::put('/profil/password', [
        ProfileController::class,
        'updatePassword',
    ])
        ->middleware('throttle:5,1')
        ->name('profile.password.update');


    /*
    |--------------------------------------------------------------------------
    | 4. PERTANDINGAN
    | Admin, Pengelola, Pemain
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
    | 5. PENGELOLA DAN ADMIN
    | CRUD Pemain, Kas, dan hapus pertandingan
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


            // KAS

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
    // Informasi akun pemain baru

    Route::get('/pemain/akun-baru', [
        PlayerController::class,
        'credentials',
    ])->name('players.credentials');


    /*
    |--------------------------------------------------------------------------
    | 6. PANEL ADMIN
    | Khusus role admin
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')
        ->group(function () {

            // Dashboard
            Route::view('/admin', 'admin.index')
                ->name('admin.index');


            // KELOLA ROLE

            Route::get('/admin/pengguna', [
                RoleController::class,
                'index'
            ])->name('admin.users');

            Route::patch('/admin/pengguna/{user}/role', [
                RoleController::class,
                'update'
            ])
                ->whereNumber('user')
                ->name('admin.users.update');

            // Reset password pemain dan pengelola

            Route::post('/admin/pengguna/{user}/reset-password', [
                RoleController::class,
                'resetPassword',
            ])
                ->whereNumber('user')
                ->middleware('throttle:10,1')
                ->name('admin.users.reset-password');


            // PEMAIN NONAKTIF

            Route::get('/admin/pemain-nonaktif', [
                InactivePlayerController::class,
                'index'
            ])->name('admin.players.inactive');

            Route::patch(
                '/admin/pemain-nonaktif/{id}/restore',
                [
                    InactivePlayerController::class,
                    'restore'
                ]
            )
                ->whereNumber('id')
                ->name('admin.players.restore');


            // PENGATURAN WEBSITE

            Route::get('/admin/pengaturan', [
                SiteSettingController::class,
                'edit'
            ])->name('admin.settings.edit');

            Route::put('/admin/pengaturan', [
                SiteSettingController::class,
                'update'
            ])->name('admin.settings.update');

            Route::delete('/admin/pengaturan/logo', [
                SiteSettingController::class,
                'deleteLogo'
            ])->name('admin.settings.logo.delete');


            // GALERI

            Route::get('/admin/galeri', [
                GalleryController::class,
                'index'
            ])->name('admin.gallery.index');

            Route::post('/admin/galeri', [
                GalleryController::class,
                'store'
            ])
                ->middleware('throttle:10,1')
                ->name('admin.gallery.store');

            Route::delete('/admin/galeri/{image}', [
                GalleryController::class,
                'destroy'
            ])
                ->whereNumber('image')
                ->name('admin.gallery.destroy');
        });
});

require __DIR__ . '/tournaments.php';
