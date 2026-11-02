<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlayerController;
use App\Http\Controllers\Admin\PlayerAccountController;
use App\Http\Controllers\Admin\MatchController;
use App\Http\Controllers\Admin\TournamentController;
use App\Http\Controllers\Admin\TournamentResultController;
use App\Http\Controllers\Admin\CashController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\SiteSettingController;

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

/*
|--------------------------------------------------------------------------
| TURNAMEN ADMIN
|--------------------------------------------------------------------------
*/

Route::get('/turnamen', [
    TournamentController::class,
    'index'
])->name('tournaments.index');

Route::get('/turnamen/buat', [
    TournamentController::class,
    'create'
])->name('tournaments.create');

Route::post('/turnamen', [
    TournamentController::class,
    'store'
])->name('tournaments.store');

Route::get('/turnamen/{tournament}', [
    TournamentController::class,
    'show'
])
    ->whereNumber('tournament')
    ->name('tournaments.show');

Route::get('/turnamen/{tournament}/aturan', [
    TournamentController::class,
    'editRules'
])
    ->whereNumber('tournament')
    ->name('tournaments.rules.edit');

Route::put('/turnamen/{tournament}/aturan', [
    TournamentController::class,
    'updateRules'
])
    ->whereNumber('tournament')
    ->name('tournaments.rules.update');

Route::get('/turnamen/{tournament}/peserta', [
    TournamentController::class,
    'entries'
])
    ->whereNumber('tournament')
    ->name('tournaments.entries');

Route::put('/turnamen/{tournament}/peserta', [
    TournamentController::class,
    'saveEntries'
])
    ->whereNumber('tournament')
    ->name('tournaments.entries.save');

Route::get('/turnamen/{tournament}/bagan', [
    TournamentController::class,
    'schedule'
])
    ->whereNumber('tournament')
    ->name('tournaments.schedule');

Route::post('/turnamen/{tournament}/bagan', [
    TournamentController::class,
    'start'
])
    ->whereNumber('tournament')
    ->name('tournaments.start');

Route::delete('/turnamen/{tournament}', [
    TournamentController::class,
    'destroy'
])
    ->whereNumber('tournament')
    ->name('tournaments.destroy');

/*
|--------------------------------------------------------------------------
| KEUANGAN ADMIN
|--------------------------------------------------------------------------
*/

Route::get('/keuangan', [
    CashController::class,
    'index'
])->name('cash.index');

Route::get('/keuangan/tambah', [
    CashController::class,
    'create'
])->name('cash.create');

Route::post('/keuangan', [
    CashController::class,
    'store'
])->name('cash.store');

Route::get('/keuangan/{transaction}/edit', [
    CashController::class,
    'edit'
])
    ->whereNumber('transaction')
    ->name('cash.edit');

Route::get('/keuangan/{transaction}', [
    CashController::class,
    'show'
])
    ->whereNumber('transaction')
    ->name('cash.show');

Route::put('/keuangan/{transaction}', [
    CashController::class,
    'update'
])
    ->whereNumber('transaction')
    ->name('cash.update');

Route::delete('/keuangan/{transaction}', [
    CashController::class,
    'destroy'
])
    ->whereNumber('transaction')
    ->name('cash.destroy');

/*
|--------------------------------------------------------------------------
| GALERI KLUB
|--------------------------------------------------------------------------
*/

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

Route::patch('/galeri/{image}/caption', [
    GalleryController::class,
    'updateCaption'
])
    ->whereNumber('image')
    ->name('gallery.caption.update');

Route::delete('/galeri/{image}', [
    GalleryController::class,
    'destroy'
])
    ->whereNumber('image')
    ->name('gallery.destroy');

/*
|--------------------------------------------------------------------------
| PENGATURAN WEBSITE
|--------------------------------------------------------------------------
*/

Route::get('/pengaturan', [
    SiteSettingController::class,
    'edit'
])->name('settings.edit');

Route::put('/pengaturan', [
    SiteSettingController::class,
    'update'
])->name('settings.update');
