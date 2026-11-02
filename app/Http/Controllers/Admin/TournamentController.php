<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateTournamentScheduleRequest;
use App\Http\Requests\StoreTournamentRequest;
use App\Http\Requests\SyncTournamentEntriesRequest;
use App\Models\Player;
use App\Models\Tournament;
use App\Services\ShuttlecockCashService;
use App\Services\TournamentScheduleService;
use App\Services\TournamentStandingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TournamentController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureAdmin($request);

        $canManage = true;

        $filters = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'format' => ['nullable', 'in:liga,cup'],
            'status' => [
                'nullable',
                'in:draft,active,completed',
            ],
        ]);

        $cari = trim($filters['cari'] ?? '');
        $filterFormat = $filters['format'] ?? '';
        $filterStatus = $filters['status'] ?? '';

        $tournaments = Tournament::query()
            ->when(
                $cari !== '',
                fn($query) => $query->where(
                    'name',
                    'like',
                    '%' . $cari . '%'
                )
            )
            ->when(
                $filterFormat !== '',
                fn($query) => $query->where(
                    'format',
                    $filterFormat
                )
            )
            ->when(
                $filterStatus !== '',
                fn($query) => $query->where(
                    'status',
                    $filterStatus
                )
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('admin.modules.tournaments', compact(
            'tournaments',
            'canManage',
            'cari',
            'filterFormat',
            'filterStatus'
        ));
    }

    public function create()
    {
        return view('tournaments.create');
    }

    public function store(StoreTournamentRequest $request)
    {
        $tournament = Tournament::create(
            $request->validated() + [
                'status' => 'draft',
                'created_by' => $request->user()->id,
            ]
        );

        return redirect()
            ->route('admin.tournaments.entries', $tournament)
            ->with('success', 'Aturan turnamen tersimpan.');
    }

    public function editRules(Tournament $tournament)
    {
        $this->ensureDraft($tournament);

        return view(
            'tournaments.create',
            compact('tournament')
        );
    }

    public function updateRules(
        StoreTournamentRequest $request,
        Tournament $tournament
    ) {
        $this->ensureDraft($tournament);

        $tournament->update($request->validated());

        return redirect()
            ->route('admin.tournaments.entries', $tournament)
            ->with('success', 'Aturan turnamen diperbarui.');
    }

    public function entries(Tournament $tournament)
    {
        $this->ensureDraft($tournament);

        $tournament->load('entries.players');

        $players = Player::orderBy('name')
            ->get(['id', 'name']);

        return view('tournaments.entries', compact(
            'tournament',
            'players'
        ));
    }

    public function saveEntries(
        SyncTournamentEntriesRequest $request,
        Tournament $tournament
    ) {
        $entries = $request->validated('entries');

        DB::transaction(function () use (
            $tournament,
            $entries
        ) {
            $locked = Tournament::query()
                ->whereKey($tournament->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($locked->status === 'draft', 403);

            $locked->entries()->delete();

            foreach (array_values($entries) as $index => $item) {
                $entry = $locked->entries()->create([
                    'seed' => $index + 1,
                ]);

                foreach (
                    array_values($item['players'])
                    as $slot => $playerId
                ) {
                    $entry->players()->attach(
                        (int) $playerId,
                        [
                            'slot' => $slot + 1,
                            'tournament_id' => $locked->id,
                        ]
                    );
                }
            }
        }, 3);

        return redirect()
            ->route('admin.tournaments.schedule', $tournament)
            ->with('success', 'Peserta turnamen tersimpan.');
    }

    public function schedule(
        Request $request,
        Tournament $tournament,
        TournamentScheduleService $scheduler
    ) {
        $this->ensureDraft($tournament);

        $tournament->load('entries.players');

        if ($tournament->entries->count() < 2) {
            return redirect()->route(
                'admin.tournaments.entries',
                $tournament
            );
        }

        $entryMap = $tournament->entries->keyBy('id');

        $defaultOrder = $tournament->entries
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();

        $requested = $request->query('order');
        $order = $defaultOrder;

        if (
            is_array($requested)
            && count($requested) === count($defaultOrder)
            && collect($requested)->every(
                fn($id) =>
                is_scalar($id)
                    && ctype_digit((string) $id)
            )
            && count(array_unique($requested)) === count($requested)
            && !array_diff(
                $defaultOrder,
                array_map('intval', $requested)
            )
        ) {
            $order = array_map('intval', $requested);
        }

        $plan = $scheduler->preview($tournament, $order);

        return view('tournaments.schedule', compact(
            'tournament',
            'entryMap',
            'order',
            'plan'
        ));
    }

    public function start(
        GenerateTournamentScheduleRequest $request,
        Tournament $tournament,
        TournamentScheduleService $scheduler
    ) {
        $scheduler->generate(
            $tournament,
            array_map(
                'intval',
                $request->validated('order')
            )
        );

        return redirect()
            ->route('admin.tournaments.show', [
                'tournament' => $tournament,
                'bagian' => 'detail',
            ])
            ->with('success', 'Turnamen berhasil dimulai.');
    }

    public function show(
        Request $request,
        Tournament $tournament,
        TournamentStandingsService $standingsService
    ) {
        $this->ensureAdmin($request);

        $canManage = true;

        $tournament->load([
            'entries.players',
            'fixtures.entryA.players',
            'fixtures.entryB.players',
            'fixtures.winner.players',
            'fixtures.sets',
        ]);

        $fixtures = $tournament->fixtures
            ->sortBy([
                ['round_number', 'asc'],
                ['match_number', 'asc'],
            ])
            ->values();

        $rounds = $fixtures->groupBy('round_number');

        $standings = $tournament->format === 'liga'
            ? $standingsService->forTournament($tournament)
            : collect();

        $champion = null;

        if (
            $tournament->format === 'cup'
            && $tournament->status === 'completed'
        ) {
            $champion = $fixtures->last()?->winner;
        }

        $totalPertandingan = $fixtures
            ->where('is_bye', false)
            ->count();

        $selesai = $fixtures
            ->filter(
                fn($fixture) =>
                !$fixture->is_bye
                    && $fixture->result !== null
            )
            ->count();

        $belumSelesai = $fixtures
            ->filter(
                fn($fixture) =>
                !$fixture->is_bye
                    && $fixture->result === null
            )
            ->count();

        return view('tournaments.show', compact(
            'tournament',
            'rounds',
            'standings',
            'canManage',
            'champion',
            'totalPertandingan',
            'selesai',
            'belumSelesai'
        ));
    }

    public function destroy(
        Request $request,
        Tournament $tournament,
        ShuttlecockCashService $cash
    ) {
        $this->ensureAdmin($request);

        DB::transaction(function () use (
            $tournament,
            $cash,
            $request
        ) {
            $locked = Tournament::query()
                ->whereKey($tournament->id)
                ->lockForUpdate()
                ->firstOrFail();

            $pairs = $cash->tournamentPairs(
                (int) $locked->id
            );

            $locked->delete();

            $cash->syncPairs(
                $pairs,
                (int) $request->user()->id
            );
        }, 3);

        return redirect()
            ->route('admin.tournaments.index')
            ->with(
                'success',
                'Turnamen dihapus dan pemakaian kok disesuaikan.'
            );
    }

    private function ensureDraft(Tournament $tournament): void
    {
        abort_unless(
            $tournament->status === 'draft',
            403,
            'Turnamen sudah dimulai. Aturan dan peserta terkunci.'
        );
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless(
            $request->user()
                && $request->user()->is_active
                && $request->user()->isAdmin(),
            403
        );
    }
}
