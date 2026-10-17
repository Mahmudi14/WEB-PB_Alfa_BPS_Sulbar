<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    // Daftar akun pemain

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
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }

    // Ubah role

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

        $user->update([
            'role' => $data['role'],
        ]);

        return back()->with(
            'success',
            'Hak akses berhasil diperbarui.'
        );
    }

    // Reset password

    public function resetPassword(Request $request, User $user)
    {
        abort_unless(
            $this->canManageUser($user)
            && $user->id !== $request->user()->id,
            404
        );

        $temporaryPassword = Str::random(20);

        $user->password = $temporaryPassword;
        $user->must_change_password = true;
        $user->password_version = $user->password_version + 1;
        $user->save();

        return redirect()
            ->route('admin.users')
            ->with('reset_credentials', [
                'name' => $user->name,
                'email' => $user->email,
                'password' => $temporaryPassword,
            ]);
    }

    // Validasi akun

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