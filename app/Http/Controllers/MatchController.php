<?php

namespace App\Http\Controllers;

use App\Models\BadmintonMatch;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MatchController extends Controller
{

    public function index(Request $request)
    {
        $data = $request->validate([
            'tanggal' => [
                'nullable',
                'date_format:Y-m-d',
            ],
        ]);

        $tanggal = $data['tanggal'] ?? null;

        $matches = BadmintonMatch::query()
            ->with([
                'participants.player',
                'creator',
            ])
            ->when($tanggal, function ($query) use ($tanggal) {
                $query->whereDate('played_on', $tanggal);
            })
            ->orderByDesc('played_on')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('matches.index', compact(
            'matches',
            'tanggal'
        ));
    }


    public function create(Request $request)
    {
        $this->ensureCanCreate($request);

        return view('matches.create', [
            'players' => $this->availablePlayers(),
            'selected' => [
                'a1' => '',
                'a2' => '',
                'b1' => '',
                'b2' => '',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureCanCreate($request);

        $data = $this->validatedMatch($request);

        DB::transaction(function () use ($data, $request) {

            $match = BadmintonMatch::create([
                'played_on' => $data['played_on'],
                'type' => $data['type'],
                'result' => $data['result'],
                'shuttlecock_count' => $data['shuttlecock_count'],
                'created_by' => $request->user()->id,
            ]);

            $match->participants()->createMany(
                $this->participantData($data)
            );
        }, 3);

        return redirect()
            ->route('matches.index')
            ->with('success', 'Pertandingan berhasil ditambahkan.');
    }

    public function edit(
        Request $request,
        BadmintonMatch $match
    ) {
        abort_unless(
            $match->editableBy($request->user()),
            403
        );

        $match->load('participants');

        $selected = [
            'a1' => '',
            'a2' => '',
            'b1' => '',
            'b2' => '',
        ];

        foreach ($match->participants as $participant) {
            $key = $participant->team . $participant->slot;

            $selected[$key] = (string) $participant->player_id;
        }

        return view('matches.edit', [
            'match' => $match,
            'players' => $this->availablePlayers($match),
            'selected' => $selected,
        ]);
    }

    public function update(
        Request $request,
        BadmintonMatch $match
    ) {
        abort_unless(
            $match->editableBy($request->user()),
            403
        );

        $data = $this->validatedMatch($request, $match);

        DB::transaction(function () use ($match, $data) {

            $match->update([
                'played_on' => $data['played_on'],
                'type' => $data['type'],
                'result' => $data['result'],
                'shuttlecock_count' => $data['shuttlecock_count'],
            ]);

            $match->participants()->delete();

            $match->participants()->createMany(
                $this->participantData($data)
            );
        }, 3);

        return redirect()
            ->route('matches.index')
            ->with('success', 'Pertandingan berhasil diperbarui.');
    }

    public function destroy(
        Request $request,
        BadmintonMatch $match
    ) {
        abort_unless(
            $request->user()->canManage(),
            403
        );

        $match->delete();

        return redirect()
            ->route('matches.index')
            ->with('success', 'Pertandingan berhasil dihapus.');
    }

    private function ensureCanCreate(Request $request): void
    {
        $user = $request->user();

        abort_unless($user && $user->is_active, 403);

        if ($user->canManage()) {
            return;
        }

        abort_unless(
            $user->role === 'pemain'
                && $user->player !== null,
            403
        );
    }

    private function validatedMatch(
        Request $request,
        ?BadmintonMatch $match = null
    ): array {
        $data = $request->validate([
            'played_on' => [
                'required',
                'date_format:Y-m-d',
            ],
            'type' => [
                'required',
                Rule::in(['tunggal', 'ganda']),
            ],
            'result' => [
                'required',
                Rule::in(['a', 'b', 'seri']),
            ],
            'shuttlecock_count' => [
                'required',
                'integer',
                'between:0,65535',
            ],
            'a1' => [
                'required',
                'integer',
                Rule::exists('players', 'id'),
            ],
            'b1' => [
                'required',
                'integer',
                Rule::exists('players', 'id'),
            ],
            'a2' => [
                Rule::requiredIf(
                    $request->input('type') === 'ganda'
                ),
                'nullable',
                'integer',
                Rule::exists('players', 'id'),
            ],
            'b2' => [
                Rule::requiredIf(
                    $request->input('type') === 'ganda'
                ),
                'nullable',
                'integer',
                Rule::exists('players', 'id'),
            ],
        ]);

        $fields = $data['type'] === 'ganda'
            ? ['a1', 'a2', 'b1', 'b2']
            : ['a1', 'b1'];

        $ids = array_map(
            fn($field) => (int) $data[$field],
            $fields
        );

        if (count(array_unique($ids)) !== count($ids)) {
            throw ValidationException::withMessages([
                'a1' => 'Pemain yang sama tidak boleh dipilih dua kali.',
            ]);
        }

        // Peserta nonaktif hanya diizinkan jika
        // memang sudah menjadi peserta pertandingan
        // yang sedang diedit.
        $existingIds = $match
            ? $match->participants()
            ->pluck('player_id')
            ->map(fn($id) => (int) $id)
            ->all()
            : [];

        $players = Player::withTrashed()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        foreach ($ids as $id) {
            $player = $players->get($id);

            if (
                !$player ||
                ($player->trashed()
                    && !in_array($id, $existingIds, true))
            ) {
                throw ValidationException::withMessages([
                    'a1' => 'Pilih pemain yang masih aktif.',
                ]);
            }
        }

        // Untuk tunggal, input pemain kedua
        // tidak dipakai meskipun dikirim dari browser.
        if ($data['type'] === 'tunggal') {
            $data['a2'] = null;
            $data['b2'] = null;
        }

        return $data;
    }

    private function participantData(array $data): array
    {
        $participants = [
            [
                'player_id' => $data['a1'],
                'team' => 'a',
                'slot' => 1,
            ],
            [
                'player_id' => $data['b1'],
                'team' => 'b',
                'slot' => 1,
            ],
        ];

        if ($data['type'] === 'ganda') {
            $participants[] = [
                'player_id' => $data['a2'],
                'team' => 'a',
                'slot' => 2,
            ];

            $participants[] = [
                'player_id' => $data['b2'],
                'team' => 'b',
                'slot' => 2,
            ];
        }

        return $participants;
    }

    private function availablePlayers(
        ?BadmintonMatch $match = null
    ) {
        $existingIds = $match
            ? $match->participants()
            ->pluck('player_id')
            ->all()
            : [];

        return Player::withTrashed()
            ->where(function ($query) use ($existingIds) {
                $query->whereNull('deleted_at');

                if (!empty($existingIds)) {
                    $query->orWhereIn('id', $existingIds);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'deleted_at']);
    }
}
