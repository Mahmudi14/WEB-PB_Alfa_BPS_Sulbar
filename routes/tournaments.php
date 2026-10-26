<?php

use App\Http\Controllers\TournamentController;
use App\Http\Controllers\TournamentResultController;
use Illuminate\Support\Facades\Route;

Route::get('/turnamen', [TournamentController::class, 'index'])->name('tournaments.index');
Route::get('/turnamen/{tournament}', [TournamentController::class, 'show'])->whereNumber('tournament')->name('tournaments.show');

Route::middleware(['auth', 'role:admin,pengelola'])->group(function () {
    Route::get('/turnamen/buat', [TournamentController::class, 'create'])->name('tournaments.create');
    Route::post('/turnamen', [TournamentController::class, 'store'])->name('tournaments.store');
    Route::get('/turnamen/{tournament}/aturan', [TournamentController::class, 'editRules'])->whereNumber('tournament')->name('tournaments.rules.edit');
    Route::put('/turnamen/{tournament}/aturan', [TournamentController::class, 'updateRules'])->whereNumber('tournament')->name('tournaments.rules.update');
    Route::get('/turnamen/{tournament}/peserta', [TournamentController::class, 'entries'])->whereNumber('tournament')->name('tournaments.entries');
    Route::put('/turnamen/{tournament}/peserta', [TournamentController::class, 'saveEntries'])->whereNumber('tournament')->name('tournaments.entries.save');
    Route::get('/turnamen/{tournament}/bagan', [TournamentController::class, 'schedule'])->whereNumber('tournament')->name('tournaments.schedule');
    Route::post('/turnamen/{tournament}/bagan', [TournamentController::class, 'start'])->whereNumber('tournament')->name('tournaments.start');
    Route::get('/turnamen/{tournament}/pertandingan/{fixture}/edit', [TournamentResultController::class, 'edit'])->whereNumber(['tournament', 'fixture'])->name('tournaments.fixtures.edit');
    Route::patch('/turnamen/{tournament}/pertandingan/{fixture}', [TournamentResultController::class, 'update'])->whereNumber(['tournament', 'fixture'])->name('tournaments.fixtures.update');
});
