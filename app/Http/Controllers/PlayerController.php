<?php

namespace App\Http\Controllers;

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
    // Daftar pemain

    public function index(PlayerRankingService $rankingService)
    {
        $players = $rankingService->all();

        return view('players.index', compact('players'));
    }

    // Form tambah

    public function create()
    {
        return view('players.create');
    }

    // Simpan pemain

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

        $credentials = null;

        try {
            DB::transaction(function () use ($data, &$credentials) {
                $userId = null;

                // Akun hanya dibuat jika email diisi

                if (!empty($data['email'])) {
                    $temporaryPassword = Str::random(20);

                    $user = User::create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'password' => $temporaryPassword,
                        'role' => 'pemain',
                        'is_active' => true,
                        'must_change_password' => true,
                    ]);

                    $userId = $user->id;

                    $credentials = [
                        'name' => $user->name,
                        'email' => $user->email,
                        'password' => $temporaryPassword,
                    ];
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

        if ($credentials) {
            return redirect()
                ->route('players.credentials')
                ->with('new_player_credentials', $credentials);
        }

        return redirect()
            ->route('players.index')
            ->with('success', 'Pemain berhasil ditambahkan.');
    }

    // Form edit

    public function edit(Request $request, Player $player)
    {
        $player->load('user');

        $this->ensureCanModify($request, $player);

        return view('players.edit', compact('player'));
    }

    // Update pemain

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

        $credentials = null;

        try {
            DB::transaction(function () use (
                $player,
                $data,
                &$credentials
            ) {
                $user = $player->user;
                $email = $data['email'] ?? null;

                if ($user) {

                    // Sinkron nama akun

                    $user->name = $data['name'];

                    if ($email !== null) {
                        $user->email = $email;
                        $user->is_active = true;
                    } else {
                        $user->is_active = false;
                    }

                    $user->save();

                } elseif ($email !== null) {

                    // Buat akun pemain baru

                    $temporaryPassword = Str::random(20);

                    $user = User::create([
                        'name' => $data['name'],
                        'email' => $email,
                        'password' => $temporaryPassword,
                        'role' => 'pemain',
                        'is_active' => true,
                        'must_change_password' => true,
                    ]);

                    $credentials = [
                        'name' => $user->name,
                        'email' => $user->email,
                        'password' => $temporaryPassword,
                    ];
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

        if ($credentials) {
            return redirect()
                ->route('players.credentials')
                ->with('new_player_credentials', $credentials);
        }

        return redirect()
            ->route('players.index')
            ->with('success', 'Data pemain berhasil diperbarui.');
    }

    // Tampilkan password baru sekali

    public function credentials(Request $request)
    {
        $credentials = $request->session()
            ->pull('new_player_credentials');

        if (!$credentials) {
            return redirect()->route('players.index');
        }

        return response()
            ->view('players.credentials', compact('credentials'))
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }

    // Nonaktifkan pemain

    public function destroy(Request $request, Player $player)
    {
        $player->load('user');

        $this->ensureCanModify($request, $player);

        abort_if(
            $player->user_id === $request->user()->id,
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
            ->route('players.index')
            ->with('success', 'Pemain berhasil dinonaktifkan.');
    }

    // Normalisasi input

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

    // Hak akses edit

    private function ensureCanModify(
        Request $request,
        Player $player
    ): void {
        abort_unless($request->user()->canManage(), 403);

        if (!$request->user()->isAdmin()) {
            abort_if(
                in_array(
                    $player->user?->role,
                    ['admin', 'pengelola'],
                    true
                ),
                403
            );
        }
    }

    // Email duplikat

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