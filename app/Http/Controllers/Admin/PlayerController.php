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
        $players = $rankingService->all();

        return view('admin.modules.players', compact('players'));
    }

    public function create()
    {
        return view('players.create');
    }

    public function store(Request $request)
    {
        $this->normalizeInput($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
                Rule::unique('players', 'email'),
            ],
            'gender' => ['required', Rule::in(['L', 'P'])],
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
                    ? 'Pemain dan akun login berhasil dibuat. Password menggunakan password default.'
                    : 'Pemain berhasil ditambahkan.'
            );
    }

    public function edit(Request $request, Player $player)
    {
        $player->load('user');

        $this->ensureCanModify($request, $player);

        return view('players.edit', compact('player'));
    }

    public function update(Request $request, Player $player)
    {
        $player->load('user');

        $this->ensureCanModify($request, $player);

        $this->normalizeInput($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('players', 'email')
                    ->ignore($player->id),
                Rule::unique('users', 'email')
                    ->ignore($player->user_id),
            ],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'initial_score' => [
                'required',
                'integer',
                'between:-999999,999999',
            ],
        ]);

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
                        $user->is_active = true;
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
            ->route('admin.players.index')
            ->with(
                'success',
                $createdAccount
                    ? 'Data pemain dan akun login berhasil diperbarui. Password menggunakan password default.'
                    : 'Data pemain berhasil diperbarui.'
            );
    }

    public function destroy(Request $request, Player $player)
    {
        $player->load('user');

        $this->ensureCanModify($request, $player);

        abort_if(
            (int) $player->user_id === (int) $request->user()->id,
            403,
            'Tidak dapat menonaktifkan akun sendiri.'
        );

        DB::transaction(function () use ($player) {
            $player->delete();

            if ($player->user) {
                $player->user->update([
                    'is_active' => false,
                ]);
            }
        }, 3);

        return redirect()
            ->route('admin.players.index')
            ->with('success', 'Pemain berhasil dinonaktifkan.');
    }

    private function normalizeInput(Request $request): void
    {
        $email = Str::lower(
            trim((string) $request->input('email', ''))
        );

        $request->merge([
            'name' => trim((string) $request->input('name', '')),
            'email' => $email === '' ? null : $email,
        ]);
    }

    private function defaultPassword(): string
    {
        $password = config('pb_alfa.player_initial_password');

        if (!is_string($password) || trim($password) === '') {
            abort(500, 'Password default belum dikonfigurasi.');
        }

        return $password;
    }

    private function ensureCanModify(
        Request $request,
        Player $player
    ): void {
        abort_unless(
            $request->user()
                && $request->user()->is_active
                && $request->user()->isAdmin(),
            403
        );
    }

    private function handleDuplicateEmail(
        QueryException $exception
    ): void {
        if (
            (string) $exception->getCode() === '23000'
            && (int) ($exception->errorInfo[1] ?? 0) === 1062
        ) {
            throw ValidationException::withMessages([
                'email' => 'Email sudah digunakan.',
            ]);
        }

        throw $exception;
    }
}
