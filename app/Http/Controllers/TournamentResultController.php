<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTournamentResultRequest;
use App\Models\Tournament;
use App\Models\TournamentFixture;
use App\Services\TournamentResultService;

class TournamentResultController extends Controller
{
    public function edit(
        Tournament $tournament,
        TournamentFixture $fixture
    ) {
        $this->ensureFixture($tournament, $fixture);

        abort_if(
            $tournament->status === 'draft'
                || $fixture->is_bye
                || !$fixture->a_entry_id
                || !$fixture->b_entry_id,
            403
        );

        $fixture->load(
            'entryA.players',
            'entryB.players',
            'sets'
        );

        return view('tournaments.edit-result', compact(
            'tournament',
            'fixture'
        ));
    }

    public function update(
        UpdateTournamentResultRequest $request,
        Tournament $tournament,
        TournamentFixture $fixture,
        TournamentResultService $service
    ) {
        $this->ensureFixture($tournament, $fixture);

        $service->save(
            $tournament,
            $fixture,
            $request->validated(),
            (int) $request->user()->id
        );

        return redirect()
            ->route('tournaments.show', [
                'tournament' => $tournament,
                'bagian' => 'pertandingan',
                'ronde' => $fixture->round_number,
            ])
            ->with(
                'success',
                'Skor dan jumlah kok berhasil disimpan.'
            );
    }

    private function ensureFixture(
        Tournament $tournament,
        TournamentFixture $fixture
    ): void {
        abort_unless(
            (int) $tournament->id
                === (int) $fixture->tournament_id,
            404
        );
    }
}
