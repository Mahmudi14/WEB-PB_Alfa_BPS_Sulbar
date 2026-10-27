
<?php

use App\Http\Controllers\TournamentController;
use App\Http\Controllers\TournamentResultController;
use Illuminate\Support\Facades\Route;

// Halaman publik.
Route::get('/turnamen', [
    TournamentController::class,
    'index',
])->name('tournaments.index');

Route::get('/turnamen/{tournament}', [
    TournamentController::class,
    'show',
])
    ->whereNumber('tournament')
    ->name('tournaments.show');


// Khusus admin dan pengelola.
Route::middleware([
    'auth',
    'role:admin,pengelola',
])->group(function () {

    // Aturan turnamen.
    Route::get('/turnamen/buat', [
        TournamentController::class,
        'create',
    ])->name('tournaments.create');

    Route::post('/turnamen', [
        TournamentController::class,
        'store',
    ])->name('tournaments.store');

    Route::get('/turnamen/{tournament}/aturan', [
        TournamentController::class,
        'editRules',
    ])->name('tournaments.rules.edit');

    Route::put('/turnamen/{tournament}/aturan', [
        TournamentController::class,
        'updateRules',
    ])->name('tournaments.rules.update');

    // Pemilihan peserta.
    Route::get('/turnamen/{tournament}/peserta', [
        TournamentController::class,
        'entries',
    ])->name('tournaments.entries');

    Route::put('/turnamen/{tournament}/peserta', [
        TournamentController::class,
        'saveEntries',
    ])->name('tournaments.entries.save');

    // Generate bagan atau jadwal.
    Route::get('/turnamen/{tournament}/bagan', [
        TournamentController::class,
        'schedule',
    ])->name('tournaments.schedule');

    Route::post('/turnamen/{tournament}/bagan', [
        TournamentController::class,
        'start',
    ])->name('tournaments.start');

    // Hasil pertandingan.
    Route::get(
        '/turnamen/{tournament}/pertandingan/{fixture}/edit',
        [TournamentResultController::class, 'edit']
    )->name('tournaments.fixtures.edit');

    Route::patch(
        '/turnamen/{tournament}/pertandingan/{fixture}',
        [TournamentResultController::class, 'update']
    )->name('tournaments.fixtures.update');

    // Hapus turnamen.
    Route::delete('/turnamen/{tournament}', [
        TournamentController::class,
        'destroy',
    ])->name('tournaments.destroy');
});
