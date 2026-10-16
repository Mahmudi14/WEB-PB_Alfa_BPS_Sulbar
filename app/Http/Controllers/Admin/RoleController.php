<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
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

        return view('admin.users', compact('users'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => [
                'required',
                Rule::in(['pemain', 'pengelola']),
            ],
        ]);

        abort_unless(
            $user->is_active &&
            in_array($user->role, ['pemain', 'pengelola'], true) &&
            $user->player()
                ->whereNotNull('email')
                ->exists(),
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
}
