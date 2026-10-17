<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    // Daftar pengguna

    public function index()
    {
        $users = User::query()
            ->whereIn('role', ['pemain', 'pengelola'])
            ->where('is_active', true)
            ->whereHas('player', function ($query) {
                $query->whereNotNull('email');
            })
            ->orderBy('name')
            ->paginate(20);

        return response()
            ->view('admin.users', compact('users'))
            ->header(
                'Cache-Control',
                'private, no-store, max-age=0'
            );
    }

    // Ubah role pemain / pengelola

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => [
                'required',
                Rule::in(['pemain', 'pengelola']),
            ],
        ]);

        abort_unless(
            $this->canManageUser($user),
            404
        );

        $oldRole = $user->role;
        $newRole = $data['role'];

        $user->role = $newRole;

        // Baru diangkat menjadi Pengelola
        if ($oldRole !== 'pengelola' && $newRole === 'pengelola') {
            $user->must_change_password = true;
        }

        // Dikembalikan menjadi Pemain
        if ($newRole === 'pemain') {
            $user->must_change_password = false;
        }

        $user->save();

        return back()->with(
            'success',
            'Hak akses berhasil diperbarui.'
        );
    }

    // Reset ke password default

    public function resetPassword(Request $request, User $user)
    {
        abort_unless(
            $this->canManageUser($user)
                && $user->id !== $request->user()->id,
            404
        );

        $defaultPassword = config(
            'pb_alfa.player_initial_password'
        );

        if (
            !is_string($defaultPassword)
            || trim($defaultPassword) === ''
        ) {
            abort(
                500,
                'Password default belum dikonfigurasi.'
            );
        }

        $user->password = $defaultPassword;

        // Hanya Pengelola yang wajib ganti password
        $user->must_change_password =
            $user->role === 'pengelola';

        // Putuskan sesi lama setelah reset
        $user->password_version =
            $user->password_version + 1;

        $user->save();

        $message = $user->role === 'pengelola'
            ? 'Password berhasil direset ke password default. Pengelola wajib mengganti password setelah login.'
            : 'Password berhasil direset ke password default. Pemain dapat langsung login.';

        return redirect()
            ->route('admin.users')
            ->with('success', $message);
    }

    // Pastikan akun merupakan pemain terdaftar

    private function canManageUser(User $user): bool
    {
        return $user->is_active
            && in_array(
                $user->role,
                ['pemain', 'pengelola'],
                true
            )
            && $user->player()
            ->whereNotNull('email')
            ->exists();
    }
}
