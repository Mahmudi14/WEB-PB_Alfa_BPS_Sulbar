<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateTournamentScheduleRequest;
use App\Http\Requests\StoreTournamentRequest;
use App\Http\Requests\SyncTournamentEntriesRequest;
use App\Models\Player;
use App\Models\Tournament;
use App\Services\TournamentScheduleService;
use App\Services\TournamentStandingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TournamentController extends Controller
{
    public function index(Request $request)
    {
        $canManage = $request->user()?->is_active && $request->user()->canManage();
        $tournaments = Tournament::withCount(['entries', 'fixtures'])
            ->when(!$canManage, fn ($query) => $query->where('status', '!=', 'draft'))
            ->latest()->paginate(12);
        return view('tournaments.index', compact('tournaments', 'canManage'));
    }

    public function create()
    {
        return view('tournaments.create');
    }

    public function store(StoreTournamentRequest $request)
    {
        $tournament = Tournament::create($request->validated() + [
            'status' => 'draft', 'created_by' => $request->user()->id,
        ]);
        return redirect()->route('tournaments.entries', $tournament)
            ->with('success', 'Aturan tersimpan. Sekarang pilih peserta.');
    }

    public function editRules(Tournament $tournament)
    {
        $this->ensureDraft($tournament);
        return view('tournaments.create', compact('tournament'));
    }

    public function updateRules(StoreTournamentRequest $request, Tournament $tournament)
    {
        $this->ensureDraft($tournament);
        $tournament->update($request->validated());
        return redirect()->route('tournaments.entries', $tournament)
            ->with('success', 'Aturan turnamen diperbarui.');
    }

    public function entries(Tournament $tournament)
    {
        $this->ensureDraft($tournament);
        $tournament->load('entries.players');
        $players = Player::orderBy('name')->get(['id', 'name']);
        return view('tournaments.entries', compact('tournament', 'players'));
    }

    public function saveEntries(SyncTournamentEntriesRequest $request, Tournament $tournament)
    {
        $entries = $request->validated('entries');
        DB::transaction(function () use ($tournament, $entries) {
            $locked = Tournament::whereKey($tournament->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->status === 'draft', 403);
            $locked->entries()->delete();
            foreach (array_values($entries) as $index => $item) {
                $entry = $locked->entries()->create(['seed' => $index + 1]);
                foreach (array_values($item['players']) as $slot => $playerId) {
                    $entry->players()->attach((int) $playerId, [
                        'slot' => $slot + 1, 'tournament_id' => $locked->id,
                    ]);
                }
            }
        }, 3);
        return redirect()->route('tournaments.schedule', $tournament)
            ->with('success', 'Peserta tersimpan. Periksa bagan atau jadwal sebelum memulai.');
    }

    public function schedule(Request $request, Tournament $tournament, TournamentScheduleService $scheduler)
    {
        $this->ensureDraft($tournament);
        $tournament->load('entries.players');
        if ($tournament->entries->count() < 2) return redirect()->route('tournaments.entries', $tournament);
        $entryMap = $tournament->entries->keyBy('id');
        $defaultOrder = $tournament->entries->pluck('id')->map(fn ($id) => (int) $id)->all();
        $requested = $request->query('order');
        $order = $defaultOrder;
        if (is_array($requested) && count($requested) === count($defaultOrder)
            && collect($requested)->every(fn ($id) => is_scalar($id) && ctype_digit((string) $id))
            && count(array_unique($requested)) === count($requested)
            && !array_diff($defaultOrder, array_map('intval', $requested))) {
            $order = array_map('intval', $requested);
        }
        $plan = $scheduler->preview($tournament, $order);
        return view('tournaments.schedule', compact('tournament', 'entryMap', 'order', 'plan'));
    }

    public function start(GenerateTournamentScheduleRequest $request, Tournament $tournament, TournamentScheduleService $scheduler)
    {
        $scheduler->generate($tournament, array_map('intval', $request->validated('order')));
        return redirect()->route('tournaments.show', $tournament)
            ->with('success', 'Turnamen dimulai. Semua ronde sudah dibuat.');
    }

    public function show(Request $request, Tournament $tournament, TournamentStandingsService $standingsService)
    {
        if ($tournament->status === 'draft') {
            abort_unless($request->user()?->is_active && $request->user()->canManage(), 404);
            return redirect()->route('tournaments.schedule', $tournament);
        }
        $tournament->load(['entries.players', 'fixtures.entryA.players', 'fixtures.entryB.players', 'fixtures.winner.players', 'fixtures.sets']);
        $rounds = $tournament->fixtures->groupBy('round_number');
        $standings = $tournament->format === 'liga' ? $standingsService->forTournament($tournament) : collect();
        $canManage = $request->user()?->is_active && $request->user()->canManage();
        $champion = null;
        if ($tournament->format === 'cup' && $tournament->status === 'completed') {
            $champion = $tournament->fixtures->last()?->winner;
        }
        return view('tournaments.show', compact('tournament', 'rounds', 'standings', 'canManage', 'champion'));
    }

    private function ensureDraft(Tournament $tournament): void
    {
        abort_unless($tournament->status === 'draft', 403, 'Turnamen sudah dimulai. Aturan dan peserta terkunci.');
    }
}
