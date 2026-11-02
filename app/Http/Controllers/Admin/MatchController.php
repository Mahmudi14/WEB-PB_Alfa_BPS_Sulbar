<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BadmintonMatch;
use App\Models\Player;
use App\Models\TournamentFixture;
use App\Services\CompletedMatchQuery;
use App\Services\ShuttlecockCashService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MatchController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'sumber' => [
                'nullable',
                Rule::in(['semua', 'reguler', 'liga', 'cup']),
            ],
        ]);

        $tanggal = $data['tanggal'] ?? null;
        $sumber = $data['sumber'] ?? 'semua';

        $regular = DB::table('matches as m')
            ->whereNull('m.deleted_at')
            ->when(
                $tanggal,
                fn($q) => $q->whereDate('m.played_on', $tanggal)
            )
            ->when(
                in_array($sumber, ['liga', 'cup'], true),
                fn($q) => $q->whereRaw('1 = 0')
            )
            ->selectRaw("
                'reguler' AS sumber,
                m.id AS sumber_id,
                m.played_on AS tanggal_main,
                m.created_at AS urutan
            ");

        $tournament = CompletedMatchQuery::tournament()
            ->when(
                $tanggal,
                fn($q) => $q->whereDate('tf.played_on', $tanggal)
            )
            ->when(
                $sumber === 'reguler',
                fn($q) => $q->whereRaw('1 = 0')
            )
            ->when(
                in_array($sumber, ['liga', 'cup'], true),
                fn($q) => $q->where('t.format', $sumber)
            )
            ->selectRaw("
                'turnamen' AS sumber,
                tf.id AS sumber_id,
                tf.played_on AS tanggal_main,
                tf.updated_at AS urutan
            ");

        $matches = DB::query()
            ->fromSub(
                $regular->unionAll($tournament),
                'riwayat'
            )
            ->orderByDesc('tanggal_main')
            ->orderByDesc('urutan')
            ->orderByDesc('sumber_id')
            ->paginate(20)
            ->withQueryString();

        $page = $matches->getCollection();

        $regulars = BadmintonMatch::query()
            ->with('participants.player')
            ->whereIn(
                'id',
                $page->where('sumber', 'reguler')
                    ->pluck('sumber_id')
            )
            ->get()
            ->keyBy('id');

        $fixtures = TournamentFixture::query()
            ->with([
                'tournament',
                'entryA.players',
                'entryB.players',
                'sets',
            ])
            ->whereIn(
                'id',
                $page->where('sumber', 'turnamen')
                    ->pluck('sumber_id')
            )
            ->get()
            ->keyBy('id');

        $matches->setCollection(
            $page->map(function ($row) use (
                $regulars,
                $fixtures
            ) {
                if ($row->sumber === 'reguler') {
                    $match = $regulars->get($row->sumber_id);

                    return $match
                        ? $this->presentRegular($match)
                        : null;
                }

                $fixture = $fixtures->get($row->sumber_id);

                return $fixture && $fixture->tournament
                    ? $this->presentFixture($fixture)
                    : null;
            })->filter()->values()
        );

        return view('admin.matches.index', compact(
            'matches',
            'tanggal',
            'sumber'
        ));
    }


    public function show(BadmintonMatch $match)
    {
        $match->load('participants.player');

        $detail = $this->presentRegular($match);

        return view('admin.matches.show', compact('detail'));
    }


    public function showFixture(TournamentFixture $fixture)
    {
        abort_unless(
            CompletedMatchQuery::tournament()
                ->where('tf.id', $fixture->id)
                ->exists(),
            404
        );

        $fixture->load([
            'tournament',
            'entryA.players',
            'entryB.players',
            'sets',
        ]);

        $detail = $this->presentFixture($fixture);

        return view('admin.matches.show', compact('detail'));
    }


    public function create()
    {
        return view('admin.matches.create', [
            'players' => Player::query()
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }


    public function store(
        Request $request,
        ShuttlecockCashService $cash
    ) {
        $data = $this->validatedMatch($request);

        DB::transaction(function () use (
            $request,
            $data,
            $cash
        ) {
            $match = BadmintonMatch::create([
                'played_on' => $data['played_on'],
                'type' => $data['type'],
                'result' => $data['result'],
                'shuttlecock_count' => $data['shuttlecock_count'],
                'set1_a' => $data['set1_a'],
                'set1_b' => $data['set1_b'],
                'set2_a' => $data['set2_a'],
                'set2_b' => $data['set2_b'],
                'created_by' => $request->user()->id,
            ]);

            $match->participants()->createMany(
                $this->participantData($data)
            );

            $cash->syncPairs(
                $cash->regularPairs($match),
                (int) $request->user()->id
            );
        }, 3);

        return redirect()
            ->route('admin.matches.index')
            ->with('success', 'Pertandingan berhasil disimpan.');
    }


    public function edit(BadmintonMatch $match)
    {
        $match->load('participants.player');

        return view('admin.matches.edit', compact('match'));
    }


    public function update(
        Request $request,
        BadmintonMatch $match,
        ShuttlecockCashService $cash
    ) {
        abort_unless(
            $match->editableBy($request->user()),
            403,
            'Tidak memiliki izin mengedit pertandingan.'
        );

        $data = $this->validatedScores($request);

        DB::transaction(function () use (
            $match,
            $data,
            $cash,
            $request
        ) {
            $match->update($data);

            $cash->syncPairs(
                $cash->regularPairs($match),
                (int) $request->user()->id
            );
        }, 3);

        return redirect()
            ->route('admin.matches.show', $match)
            ->with(
                'success',
                'Skor dan jumlah kok berhasil diperbarui.'
            );
    }


    public function destroy(
        Request $request,
        BadmintonMatch $match,
        ShuttlecockCashService $cash
    ) {
        DB::transaction(function () use (
            $match,
            $cash,
            $request
        ) {
            $pairs = $cash->regularPairs($match);

            $match->delete();

            $cash->syncPairs(
                $pairs,
                (int) $request->user()->id
            );
        }, 3);

        return redirect()
            ->route('admin.matches.index')
            ->with(
                'success',
                'Pertandingan berhasil dihapus.'
            );
    }


    private function validatedMatch(Request $request): array
    {
        $data = $request->validate([
            'played_on' => [
                'required',
                'date_format:Y-m-d',
            ],
            'type' => [
                'required',
                Rule::in(['tunggal', 'ganda']),
            ],
            'a1' => ['required', 'string'],
            'b1' => ['required', 'string'],
            'a2' => [
                Rule::requiredIf(
                    $request->input('type') === 'ganda'
                ),
                'nullable',
                'string',
            ],
            'b2' => [
                Rule::requiredIf(
                    $request->input('type') === 'ganda'
                ),
                'nullable',
                'string',
            ],
        ]);

        $data = array_merge(
            $data,
            $this->validatedScores($request)
        );

        $slots = $data['type'] === 'ganda'
            ? ['a1', 'a2', 'b1', 'b2']
            : ['a1', 'b1'];

        $realIds = [];

        foreach ($slots as $slot) {
            $value = (string) $data[$slot];

            if ($value === 'guest') {
                continue;
            }

            if (!preg_match('/^[1-9][0-9]*$/D', $value)) {
                throw ValidationException::withMessages([
                    $slot => 'Pilihan pemain tidak valid.',
                ]);
            }

            $realIds[] = (int) $value;
        }

        if (
            count($realIds)
            !== count(array_unique($realIds))
        ) {
            throw ValidationException::withMessages([
                'a1' => 'Pemain yang sama tidak boleh dipilih dua kali.',
            ]);
        }

        $found = Player::query()
            ->whereIn('id', $realIds)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();

        if (count($found) !== count($realIds)) {
            throw ValidationException::withMessages([
                'a1' => 'Pilih pemain yang masih aktif.',
            ]);
        }

        if ($data['type'] === 'tunggal') {
            $data['a2'] = null;
            $data['b2'] = null;
        }

        return $data;
    }


    private function participantData(array $data): array
    {
        $slots = $data['type'] === 'ganda'
            ? ['a1', 'a2', 'b1', 'b2']
            : ['a1', 'b1'];

        $participants = [];

        foreach ($slots as $slot) {
            $value = (string) $data[$slot];
            $guest = $value === 'guest';

            $participants[] = [
                'player_id' => $guest ? null : (int) $value,
                'guest_name' => $guest ? 'Pemain Luar' : null,
                'team' => substr($slot, 0, 1),
                'slot' => (int) substr($slot, 1),
            ];
        }

        return $participants;
    }


    private function validatedScores(Request $request): array
    {
        $data = $request->validate([
            'shuttlecock_count' => [
                'required',
                'integer',
                'between:0,65535',
            ],
            'set1_a' => [
                'nullable',
                'integer',
                'between:0,99',
            ],
            'set1_b' => [
                'nullable',
                'integer',
                'between:0,99',
            ],
            'set2_a' => [
                'nullable',
                'integer',
                'between:0,99',
            ],
            'set2_b' => [
                'nullable',
                'integer',
                'between:0,99',
            ],
        ]);

        $fields = ['set1_a', 'set1_b', 'set2_a', 'set2_b'];
        $filled = 0;

        foreach ($fields as $field) {
            $value = $data[$field] ?? null;

            $data[$field] = $value === null
                ? null
                : (int) $value;

            if ($data[$field] !== null) {
                $filled++;
            }
        }

        if ($filled !== 0 && $filled !== 4) {
            throw ValidationException::withMessages([
                'set1_a' =>
                'Lengkapi semua skor atau kosongkan seluruh skor.',
            ]);
        }

        if ($filled === 0) {
            $data['result'] = null;

            return $data;
        }

        if (
            $data['set1_a'] === $data['set1_b']
            || $data['set2_a'] === $data['set2_b']
        ) {
            throw ValidationException::withMessages([
                'set1_a' =>
                'Skor dalam satu set tidak boleh seri.',
            ]);
        }

        $winsA =
            (int) ($data['set1_a'] > $data['set1_b'])
            + (int) ($data['set2_a'] > $data['set2_b']);

        $data['result'] = match ($winsA) {
            2 => 'a',
            0 => 'b',
            default => 'seri',
        };

        return $data;
    }


    private function presentRegular(
        BadmintonMatch $match
    ): array {
        $a = $match->participants
            ->where('team', 'a')
            ->sortBy('slot')
            ->pluck('display_name')
            ->values()
            ->all();

        $b = $match->participants
            ->where('team', 'b')
            ->sortBy('slot')
            ->pluck('display_name')
            ->values()
            ->all();

        return [
            'id' => $match->id,
            'source' => 'reguler',
            'source_label' => 'Reguler',
            'type_label' => $match->type === 'ganda'
                ? 'Ganda' : 'Tunggal',
            'tournament_name' => null,
            'date' => $match->played_on,
            'team_a' => $a,
            'team_b' => $b,
            'scores' => [
                [$match->set1_a, $match->set1_b],
                [$match->set2_a, $match->set2_b],
            ],
            'result' => $match->result,
            'shuttlecock_count' => $match->shuttlecock_count,
            'detail_url' => route('admin.matches.show', $match),
            'edit_url' => route('admin.matches.edit', $match),
        ];
    }


    private function presentFixture(
        TournamentFixture $fixture
    ): array {
        $sets = $fixture->sets->keyBy('set_number');

        $scores = [];

        foreach ([1, 2, 3] as $number) {
            $set = $sets->get($number);

            if ($set) {
                $scores[] = [
                    $set->a_score,
                    $set->b_score,
                ];
            }
        }

        $a = $fixture->entryA?->players
            ?->sortBy(fn($p) => $p->pivot?->slot ?? 0)
            ?->pluck('name')
            ?->values()
            ?->all() ?? [];

        $b = $fixture->entryB?->players
            ?->sortBy(fn($p) => $p->pivot?->slot ?? 0)
            ?->pluck('name')
            ?->values()
            ?->all() ?? [];

        return [
            'id' => $fixture->id,
            'source' => $fixture->tournament->format,
            'source_label' => strtoupper(
                $fixture->tournament->format
            ),
            'type_label' => $fixture->tournament->match_type
                === 'ganda' ? 'Ganda' : 'Tunggal',
            'tournament_name' => $fixture->tournament->name,
            'date' => $fixture->played_on,
            'team_a' => $a,
            'team_b' => $b,
            'scores' => $scores,
            'result' => $fixture->result === 'draw'
                ? 'seri'
                : $fixture->result,
            'shuttlecock_count' => $fixture->shuttlecock_count,
            'detail_url' => route(
                'admin.matches.fixtures.show',
                $fixture->id
            ),
            'edit_url' => route(
                'admin.tournaments.fixtures.edit',
                [
                    $fixture->tournament_id,
                    $fixture->id,
                ]
            ),
        ];
    }
}
