<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\User;
use App\Services\PlayerRankingService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PlayerController extends Controller
{
    public function index(PlayerRankingService $rankingService)
    {
        $ranking = $rankingService->all()->keyBy('id');

        $players = Player::withTrashed()
            ->with('user')
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        foreach ($players as $player) {
            $stats = $ranking->get($player->id);

            $player->rank_position = $stats?->rank_position;
            $player->wins = (int) ($stats?->wins ?? 0);
            $player->draws = (int) ($stats?->draws ?? 0);
            $player->losses = (int) ($stats?->losses ?? 0);
            $player->total_score = $stats?->total_score;
        }

        $activeCount = $players
            ->filter(fn($player) => !$player->trashed())
            ->count();

        $inactiveCount = $players->count() - $activeCount;

        return response()
            ->view('admin.players.index', compact(
                'players',
                'activeCount',
                'inactiveCount'
            ))
            ->header(
                'Cache-Control',
                'private, no-store, max-age=0'
            );
    }


    public function show(
        int $player,
        PlayerRankingService $rankingService
    ) {
        $player = Player::withTrashed()
            ->with('user')
            ->findOrFail($player);

        $stats = $rankingService->all()
            ->firstWhere('id', $player->id);

        return response()
            ->view('admin.players.show', compact(
                'player',
                'stats'
            ))
            ->header(
                'Cache-Control',
                'private, no-store, max-age=0'
            );
    }


    public function create()
    {
        return view('admin.players.create');
    }


    public function store(Request $request)
    {
        $this->normalizeInput($request);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
                Rule::unique('players', 'email'),
            ],
            'gender' => [
                'required',
                Rule::in(['L', 'P']),
            ],
            'initial_score' => [
                'required',
                'integer',
                'between:-999999,999999',
            ],
        ]);

        try {
            DB::transaction(function () use ($data) {
                $userId = null;

                if (!empty($data['email'])) {
                    $user = User::create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'password' => $this->defaultPassword(),
                        'role' => 'pemain',
                        'is_active' => true,
                        'must_change_password' => false,
                    ]);

                    $userId = $user->id;
                }

                Player::create([
                    'name' => $data['name'],
                    'email' => $data['email'] ?? null,
                    'gender' => $data['gender'],
                    'initial_score' => $data['initial_score'],
                    'user_id' => $userId,
                ]);
            }, 3);
        } catch (QueryException $exception) {
            $this->handleDuplicateEmail($exception);
        }

        return redirect()
            ->route('admin.players.index')
            ->with(
                'success',
                !empty($data['email'])
                    ? 'Pemain dan akun login berhasil dibuat dengan password default.'
                    : 'Pemain berhasil ditambahkan.'
            );
    }


    public function edit(Player $player)
    {
        $player->load('user');

        return view('admin.players.edit', compact('player'));
    }


    public function update(Request $request, Player $player)
    {
        $player->load('user');

        $this->normalizeInput($request);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('players', 'email')
                    ->ignore($player->id),
                Rule::unique('users', 'email')
                    ->ignore($player->user_id),
            ],
            'gender' => [
                'required',
                Rule::in(['L', 'P']),
            ],
            'initial_score' => [
                'required',
                'integer',
                'between:-999999,999999',
            ],
        ]);

        if (
            $player->user?->role === 'admin'
            && empty($data['email'])
        ) {
            throw ValidationException::withMessages([
                'email' => 'Email akun administrator tidak boleh dikosongkan.',
            ]);
        }

        $createdAccount = false;

        try {
            DB::transaction(function () use (
                $player,
                $data,
                &$createdAccount
            ) {
                $user = $player->user;
                $email = $data['email'] ?? null;

                if ($user) {
                    $user->name = $data['name'];

                    if ($email !== null) {
                        $user->email = $email;
                    } else {
                        $user->is_active = false;
                    }

                    $user->save();
                } elseif ($email !== null) {
                    $user = User::create([
                        'name' => $data['name'],
                        'email' => $email,
                        'password' => $this->defaultPassword(),
                        'role' => 'pemain',
                        'is_active' => true,
                        'must_change_password' => false,
                    ]);

                    $createdAccount = true;
                }

                $player->update([
                    'name' => $data['name'],
                    'email' => $email,
                    'gender' => $data['gender'],
                    'initial_score' => $data['initial_score'],
                    'user_id' => $user?->id,
                ]);
            }, 3);
        } catch (QueryException $exception) {
            $this->handleDuplicateEmail($exception);
        }

        return redirect()
            ->route('admin.players.show', $player->id)
            ->with(
                'success',
                $createdAccount
                    ? 'Data pemain dan akun login baru berhasil disimpan.'
                    : 'Data pemain berhasil diperbarui.'
            );
    }


    private function normalizeInput(Request $request): void
    {
        $email = Str::lower(
            trim((string) $request->input('email', ''))
        );

        $request->merge([
            'name' => trim(
                (string) $request->input('name', '')
            ),
            'email' => $email === '' ? null : $email,
        ]);
    }


    private function defaultPassword(): string
    {
        $password = config('pb_alfa.player_initial_password');

        abort_unless(
            is_string($password) && trim($password) !== '',
            500,
            'Password default belum dikonfigurasi.'
        );

        return $password;
    }


    private function handleDuplicateEmail(
        QueryException $exception
    ): void {
        $code = (string) $exception->getCode();
        $vendor = (int) ($exception->errorInfo[1] ?? 0);

        if (
            $code === '23505'
            || ($code === '23000' && $vendor === 1062)
        ) {
            throw ValidationException::withMessages([
                'email' => 'Email sudah digunakan.',
            ]);
        }

        throw $exception;
    }
}
