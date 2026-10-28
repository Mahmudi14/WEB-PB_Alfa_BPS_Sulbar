<?php

namespace App\Http\Controllers;

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
    /*
    |--------------------------------------------------------------------------
    | DAFTAR PERTANDINGAN
    |--------------------------------------------------------------------------
    |
    | Reguler: selesai dan pending tampil.
    | Liga/Cup: hanya pertandingan selesai dan valid.
    |
    | Reguler pending tetap tidak dihitung
    | untuk Ranking Global dan Kas Kok.
    |
    */

    public function index(Request $request)
    {
        $data = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $tanggal = $data['tanggal'] ?? null;

        /*
         * Riwayat Reguler:
         * Ambil seluruh pertandingan yang belum dihapus,
         * termasuk pertandingan yang belum memiliki hasil.
         *
         * CompletedMatchQuery::regular() sengaja tidak
         * digunakan di sini karena khusus hasil selesai.
         */

        $regular = DB::table('matches as m')
            ->whereNull('m.deleted_at')
            ->when(
                $tanggal,
                fn($q) => $q->whereDate('m.played_on', $tanggal)
            )
            ->selectRaw("
                'reguler' AS sumber,
                m.id AS sumber_id,
                m.played_on AS tanggal_main,
                m.created_at AS urutan
            ");

        /*
         * Liga dan Cup:
         * Hanya hasil yang sudah selesai dan valid.
         */

        $turnamen = CompletedMatchQuery::tournament()
            ->when(
                $tanggal,
                fn($q) => $q->whereDate('tf.played_on', $tanggal)
            )
            ->selectRaw("
                'turnamen' AS sumber,
                tf.id AS sumber_id,
                tf.played_on AS tanggal_main,
                tf.updated_at AS urutan
            ");

        /*
         * Gabungkan riwayat tiga sumber.
         * Pagination berlaku untuk keseluruhan data.
         */

        $matches = DB::query()
            ->fromSub(
                $regular->unionAll($turnamen),
                'riwayat'
            )
            ->orderByDesc('tanggal_main')
            ->orderByDesc('urutan')
            ->orderByDesc('sumber_id')
            ->paginate(20)
            ->withQueryString();

        $page = $matches->getCollection();

        /*
         * Ambil detail pertandingan reguler.
         */

        $regulars = BadmintonMatch::query()
            ->with('participants.player')
            ->whereIn(
                'id',
                $page->where('sumber', 'reguler')
                    ->pluck('sumber_id')
            )
            ->get()
            ->keyBy('id');

        /*
         * Ambil detail pertandingan turnamen.
         */

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

        /*
         * Siapkan data agar semua pertandingan
         * menggunakan format kartu Blade yang sama.
         */

        $matches->setCollection(
            $page->map(function ($row) use (
                $regulars,
                $fixtures
            ) {

                /*
                 * Pertandingan Reguler.
                 */

                if ($row->sumber === 'reguler') {

                    $match = $regulars->get($row->sumber_id);

                    if ($match) {
                        $match->history_source = 'reguler';
                    }

                    return $match;
                }

                /*
                 * Pertandingan Liga/Cup.
                 */

                $fixture = $fixtures->get($row->sumber_id);

                if (!$fixture || !$fixture->tournament) {
                    return null;
                }

                $participants = collect();

                foreach (
                    [
                        'a' => $fixture->entryA,
                        'b' => $fixture->entryB,
                    ] as $side => $entry
                ) {

                    foreach (
                        $entry?->players ?? collect()
                        as $index => $player
                    ) {

                        $participants->push((object) [
                            'team' => $side,

                            'slot' => $player->pivot?->slot
                                ?? ($index + 1),

                            'display_name' => $player->name,
                        ]);
                    }
                }

                $sets = $fixture->sets->keyBy('set_number');

                return (object) [

                    'history_source' =>
                    $fixture->tournament->format,

                    'tournament_name' =>
                    $fixture->tournament->name,

                    'round_number' =>
                    $fixture->round_number,

                    'tournament_url' => route(
                        'tournaments.show',
                        [
                            'tournament' =>
                            $fixture->tournament_id,

                            'bagian' => 'pertandingan',

                            'ronde' =>
                            $fixture->round_number,
                        ]
                    ),

                    'edit_url' => route(
                        'tournaments.fixtures.edit',
                        [
                            $fixture->tournament_id,
                            $fixture->id,
                        ]
                    ),

                    'played_on' =>
                    \Illuminate\Support\Carbon::parse(
                        $row->tanggal_main
                    ),

                    'participants' => $participants,

                    'result' =>
                    $fixture->result === 'draw'
                        ? 'seri'
                        : $fixture->result,

                    'set1_a' => $sets->get(1)?->a_score,
                    'set1_b' => $sets->get(1)?->b_score,

                    'set2_a' => $sets->get(2)?->a_score,
                    'set2_b' => $sets->get(2)?->b_score,

                    'set3_a' => $sets->get(3)?->a_score,
                    'set3_b' => $sets->get(3)?->b_score,

                    'jus_a' => $fixture->jus_a,
                    'jus_b' => $fixture->jus_b,

                    'shuttlecock_count' =>
                    $fixture->shuttlecock_count,

                    'created_by' => null,
                ];
            })->filter()->values()
        );

        return view(
            'matches.index',
            compact('matches', 'tanggal')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FORM TAMBAH PERTANDINGAN
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $this->ensureCanCreate($request);

        return view('matches.create', [
            'players' => Player::query()
                ->orderBy('name')
                ->get(['id', 'name']),

            'selected' => [
                'a1' => '',
                'a2' => '',
                'b1' => '',
                'b2' => '',
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | SIMPAN PERTANDINGAN REGULER
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        ShuttlecockCashService $cash
    ) {
        $this->ensureCanCreate($request);

        $data = $this->validatedMatch($request);

        DB::transaction(function () use (
            $data,
            $request,
            $cash
        ) {

            $match = BadmintonMatch::create([
                'played_on' => $data['played_on'],
                'type' => $data['type'],
                'result' => $data['result'],
                'shuttlecock_count' =>
                $data['shuttlecock_count'],

                'set1_a' => $data['set1_a'],
                'set1_b' => $data['set1_b'],
                'set2_a' => $data['set2_a'],
                'set2_b' => $data['set2_b'],

                'created_by' => $request->user()->id,
            ]);

            $match->participants()->createMany(
                $this->participantData($data)
            );

            /*
             * Sinkronisasi kok.
             * Hanya pertandingan selesai yang akan
             * memberikan kontribusi ke Kas.
             */

            $cash->syncPairs(
                $cash->regularPairs($match),
                (int) $request->user()->id
            );
        }, 3);

        return redirect()
            ->route('matches.index')
            ->with(
                'success',
                'Pertandingan berhasil disimpan.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | HAPUS PERTANDINGAN REGULER
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        BadmintonMatch $match,
        ShuttlecockCashService $cash
    ) {
        abort_unless(
            $request->user()?->canManage(),
            403
        );

        DB::transaction(function () use (
            $match,
            $request,
            $cash
        ) {

            $pairs = $cash->regularPairs($match);

            $match->delete();

            $cash->syncPairs(
                $pairs,
                (int) $request->user()->id
            );
        }, 3);

        return redirect()
            ->route('matches.index')
            ->with(
                'success',
                'Pertandingan berhasil dihapus.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | HAK MEMBUAT PERTANDINGAN
    |--------------------------------------------------------------------------
    */

    private function ensureCanCreate(Request $request): void
    {
        $user = $request->user();

        abort_unless(
            $user && $user->is_active,
            403
        );

        if ($user->canManage()) {
            return;
        }

        abort_unless(
            $user->role === 'pemain'
                && $user->player !== null,
            403
        );
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDASI DATA PERTANDINGAN
    |--------------------------------------------------------------------------
    */

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

        /*
         * Validasi skor dan tentukan hasil.
         */

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

        /*
         * Cegah pemain sama dipilih dua kali.
         */

        if (
            count($realIds)
            !== count(array_unique($realIds))
        ) {
            throw ValidationException::withMessages([
                'a1' =>
                'Pemain yang sama tidak boleh dipilih dua kali.',
            ]);
        }

        /*
         * Pastikan ID pemain valid.
         */

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


    /*
    |--------------------------------------------------------------------------
    | DATA PEMAIN PERTANDINGAN
    |--------------------------------------------------------------------------
    */

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

                'player_id' =>
                $guest ? null : (int) $value,

                'guest_name' =>
                $guest ? 'Pemain Luar' : null,

                'team' => substr($slot, 0, 1),

                'slot' => (int) substr($slot, 1),
            ];
        }

        return $participants;
    }


    /*
    |--------------------------------------------------------------------------
    | HALAMAN EDIT PERTANDINGAN
    |--------------------------------------------------------------------------
    */

    public function edit(
        Request $request,
        BadmintonMatch $match
    ) {
        $this->ensureCanViewEditor($request, $match);

        $match->load('participants.player');

        return view(
            'matches.edit',
            compact('match')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE SKOR DAN KOK
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        BadmintonMatch $match,
        ShuttlecockCashService $cash
    ) {
        abort_unless(
            $match->editableBy($request->user()),
            403,
            'Batas waktu edit pertandingan sudah berakhir.'
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
            ->route('matches.index')
            ->with(
                'success',
                'Hasil pertandingan berhasil diperbarui.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | HAK MEMBUKA EDITOR REGULER
    |--------------------------------------------------------------------------
    */

    private function ensureCanViewEditor(
        Request $request,
        BadmintonMatch $match
    ): void {
        $user = $request->user();

        abort_unless(
            $user && $user->is_active,
            403
        );

        if ($user->canManage()) {
            return;
        }

        abort_unless(
            $user->role === 'pemain'
                && $user->player !== null
                && (int) $match->created_by
                === (int) $user->id,
            403
        );
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDASI SKOR REGULER
    |--------------------------------------------------------------------------
    |
    | Skor kosong diperbolehkan sebagai Pending.
    | Hasil valid otomatis menentukan pemenang.
    |
    */

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

        $fields = [
            'set1_a',
            'set1_b',
            'set2_a',
            'set2_b',
        ];

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

        /*
         * Tidak boleh mengisi sebagian skor.
         */

        if ($filled !== 0 && $filled !== 4) {
            throw ValidationException::withMessages([
                'set1_a' =>
                'Lengkapi semua skor atau kosongkan seluruh skor.',
            ]);
        }

        /*
         * Seluruh skor kosong = Pending.
         */

        if ($filled === 0) {
            $data['result'] = null;

            return $data;
        }

        /*
         * Satu set tidak boleh berakhir seri.
         */

        if (
            $data['set1_a'] === $data['set1_b']
            || $data['set2_a'] === $data['set2_b']
        ) {
            throw ValidationException::withMessages([
                'set1_a' =>
                'Skor dalam satu set tidak boleh seri.',
            ]);
        }

        /*
         * Hitung hasil otomatis.
         */

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
}
