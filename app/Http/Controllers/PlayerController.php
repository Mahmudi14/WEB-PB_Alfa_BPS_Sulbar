<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Services\PlayerRankingService;
use Illuminate\Pagination\LengthAwarePaginator;

class PlayerController extends Controller
{
    // DAFTAR PEMAIN PUBLIK



    public function index(PlayerRankingService $rankingService)
    {
        // Semua data ranking dimuat sekali.
        // Pencarian dan pagination dilakukan di browser.
        $players = $rankingService->all();

        return view('players.index', compact('players'));
    }



    // FORM TAMBAH

    public function create()
    {
        return view('players.create');
    }

    // SIMPAN PEMAIN

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

        try {
            DB::transaction(function () use ($data) {
                $userId = null;

                // Buat akun HANYA jika email diisi.
                if (!empty($data['email'])) {
                    $password = $this->initialPassword();

                    $user = User::create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'password' => Hash::make($password),
                        'role' => 'pemain',
                        'is_active' => true,
                    ]);

                    $userId = $user->id;
                }

                // Data pemain selalu dibuat.
                Player::create([
                    'name' => $data['name'],
                    'email' => $data['email'] ?? null,
                    'gender' => $data['gender'],
                    'initial_score' => $data['initial_score'],
                    'user_id' => $userId,
                ]);
            }, 3);
        } catch (QueryException $e) {
            $this->handleDuplicateEmail($e);
        }

        return redirect()
            ->route('players.index')
            ->with('success', 'Pemain berhasil ditambahkan.');
    }

    // FORM EDIT

    public function edit(
        Request $request,
        Player $player
    ) {
        $player->load('user');

        $this->ensureCanModify($request, $player);

        return view('players.edit', compact('player'));
    }

    // UPDATE PEMAIN

    public function update(
        Request $request,
        Player $player
    ) {
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

        try {
            DB::transaction(function () use ($player, $data) {
                $user = $player->user;
                $email = $data['email'] ?? null;

                if ($user) {

                    // Pemain sudah pernah memiliki akun.
                    // Update nama akun agar sinkron.
                    $user->name = $data['name'];

                    if ($email !== null) {
                        $user->email = $email;
                        $user->is_active = true;
                    } else {
                        // Email dihapus, akun tidak
                        // dapat dipakai login lagi.
                        $user->is_active = false;
                    }

                    $user->save();
                } elseif ($email !== null) {

                    // Pemain sebelumnya tanpa akun.
                    // Email baru diisi: buat akun.
                    $user = User::create([
                        'name' => $data['name'],
                        'email' => $email,
                        'password' => Hash::make(
                            $this->initialPassword()
                        ),
                        'role' => 'pemain',
                        'is_active' => true,
                    ]);
                }

                $player->update([
                    'name' => $data['name'],
                    'email' => $email,
                    'gender' => $data['gender'],
                    'initial_score' => $data['initial_score'],
                    'user_id' => $user?->id,
                ]);
            }, 3);
        } catch (QueryException $e) {
            $this->handleDuplicateEmail($e);
        }

        return redirect()
            ->route('players.index')
            ->with('success', 'Data pemain berhasil diperbarui.');
    }

    // SOFT DELETE

    public function destroy(
        Request $request,
        Player $player
    ) {
        $player->load('user');

        $this->ensureCanModify($request, $player);

        abort_if(
            $player->user_id === $request->user()->id,
            403,
            'Tidak dapat menonaktifkan akun sendiri.'
        );

        DB::transaction(function () use ($player) {
            $player->delete();

            // Pemain tanpa akun tetap bisa dihapus.
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

    // FUNGSI PENDUKUNG

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

    private function initialPassword(): string
    {
        $password = config('pb_alfa.player_initial_password');

        if (!is_string($password) || $password === '') {
            abort(500, 'Password awal belum dikonfigurasi.');
        }

        return $password;
    }

    private function ensureCanModify(
        Request $request,
        Player $player
    ): void {
        abort_unless($request->user()->canManage(), 403);

        // Pengelola tidak boleh mengubah akun
        // Admin atau Pengelola lainnya.
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

    private function handleDuplicateEmail(
        QueryException $exception
    ): void {
        if (
            (string) $exception->getCode() === '23000' &&
            (int) ($exception->errorInfo[1] ?? 0) === 1062
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Email sudah digunakan.',
            ]);
        }

        throw $exception;
    }
}
