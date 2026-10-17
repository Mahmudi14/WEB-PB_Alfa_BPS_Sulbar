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
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $tanggal = $data['tanggal'] ?? null;

        $matches = BadmintonMatch::query()
            ->with(['participants.player', 'creator'])
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
                'set1_a' => $data['set1_a'],
                'set1_b' => $data['set1_b'],
                'set2_a' => $data['set2_a'],
                'set2_b' => $data['set2_b'],
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

    public function destroy(Request $request, BadmintonMatch $match)
    {
        abort_unless(
            $request->user()?->canManage(),
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
            'shuttlecock_count' => [
                'required',
                'integer',
                'between:0,65535',
            ],
            'set1_a' => ['required', 'integer', 'between:0,99'],
            'set1_b' => ['required', 'integer', 'between:0,99'],
            'set2_a' => ['required', 'integer', 'between:0,99'],
            'set2_b' => ['required', 'integer', 'between:0,99'],
            'a1' => ['required', 'string'],
            'b1' => ['required', 'string'],
            'a2' => [
                Rule::requiredIf($request->input('type') === 'ganda'),
                'nullable',
                'string',
            ],
            'b2' => [
                Rule::requiredIf($request->input('type') === 'ganda'),
                'nullable',
                'string',
            ],
        ]);

        // Validasi skor
        if (
            $data['set1_a'] === $data['set1_b']
            || $data['set2_a'] === $data['set2_b']
        ) {
            throw ValidationException::withMessages([
                'set1_a' => 'Skor dalam satu set tidak boleh seri.',
            ]);
        }

        // Hasil otomatis
        $winsA = 0;

        if ($data['set1_a'] > $data['set1_b']) {
            $winsA++;
        }

        if ($data['set2_a'] > $data['set2_b']) {
            $winsA++;
        }

        $data['result'] = match ($winsA) {
            2 => 'a',
            0 => 'b',
            default => 'seri',
        };

        // Peserta
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

        if (count($realIds) !== count(array_unique($realIds))) {
            throw ValidationException::withMessages([
                'a1' => 'Pemain yang sama tidak boleh dipilih dua kali.',
            ]);
        }

        $found = Player::query()
            ->whereIn('id', $realIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
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
}