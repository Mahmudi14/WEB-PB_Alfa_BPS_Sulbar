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
        $filters = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'format' => ['nullable', 'in:liga,cup'],
            'status' => ['nullable', 'in:draft,active,completed'],
        ]);

        $cari = trim($filters['cari'] ?? '');
        $filterFormat = $filters['format'] ?? '';
        $filterStatus = $filters['status'] ?? '';

        $tournaments = Tournament::query()
            ->withCount(['entries', 'fixtures'])
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

        $counts = Tournament::query()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw(
                "SUM(CASE WHEN status = 'draft'
                    THEN 1 ELSE 0 END) AS drafts"
            )
            ->selectRaw(
                "SUM(CASE WHEN status = 'active'
                    THEN 1 ELSE 0 END) AS active"
            )
            ->selectRaw(
                "SUM(CASE WHEN status = 'completed'
                    THEN 1 ELSE 0 END) AS completed"
            )
            ->first();

        return view('admin.tournaments.index', compact(
            'tournaments',
            'counts',
            'cari',
            'filterFormat',
            'filterStatus'
        ));
    }

    public function create()
    {
        return view('admin.tournaments.create');
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
            ->with('success', 'Aturan turnamen berhasil disimpan.');
    }

    public function editRules(Tournament $tournament)
    {
        $this->ensureDraft($tournament);

        return view('admin.tournaments.create', compact(
            'tournament'
        ));
    }

    public function updateRules(
        StoreTournamentRequest $request,
        Tournament $tournament
    ) {
        $this->ensureDraft($tournament);

        DB::transaction(function () use ($tournament, $request) {
            $locked = Tournament::whereKey($tournament->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureDraft($locked);

            $locked->update($request->validated());
        }, 3);

        return redirect()
            ->route('admin.tournaments.entries', $tournament)
            ->with('success', 'Aturan turnamen diperbarui.');
    }

    public function entries(Tournament $tournament)
    {
        $this->ensureDraft($tournament);

        $tournament->load('entries.players');

        $players = Player::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.tournaments.entries', compact(
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
            $locked = Tournament::whereKey($tournament->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureDraft($locked);

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
            ->with('success', 'Peserta turnamen berhasil disimpan.');
    }

    public function schedule(
        Request $request,
        Tournament $tournament,
        TournamentScheduleService $scheduler
    ) {
        $this->ensureDraft($tournament);

        $tournament->load('entries.players');

        if ($tournament->entries->count() < 2) {
            return redirect()
                ->route('admin.tournaments.entries', $tournament);
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

        return view('admin.tournaments.schedule', compact(
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
            ->route('admin.tournaments.show', $tournament)
            ->with('success', 'Turnamen berhasil dimulai.');
    }

    public function show(
        Tournament $tournament,
        TournamentStandingsService $standingsService
    ) {
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

        $totalMatches = $fixtures
            ->where('is_bye', false)
            ->count();

        $completedMatches = $fixtures
            ->filter(
                fn($fixture) =>
                !$fixture->is_bye
                    && $fixture->result !== null
            )
            ->count();

        $pendingMatches = $fixtures
            ->filter(
                fn($fixture) =>
                !$fixture->is_bye
                    && $fixture->result === null
            )
            ->count();

        return view('admin.tournaments.show', compact(
            'tournament',
            'rounds',
            'standings',
            'champion',
            'totalMatches',
            'completedMatches',
            'pendingMatches'
        ));
    }

    public function destroy(
        Request $request,
        Tournament $tournament,
        ShuttlecockCashService $cash
    ) {
        DB::transaction(function () use (
            $tournament,
            $request,
            $cash
        ) {
            $locked = Tournament::whereKey($tournament->id)
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
}
