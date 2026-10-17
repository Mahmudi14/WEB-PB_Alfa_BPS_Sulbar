<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    // Profil

    public function index(Request $request)
    {
        return view('profile.index', [
            'user' => $request->user(),
            'mustChange' => false,
        ]);
    }

    // Ubah nama

    public function updateName(Request $request)
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $user = $request->user();

            $user->update([
                'name' => $data['name'],
            ]);

            $user->player()
                ->update(['name' => $data['name']]);
        }, 3);

        return back()->with(
            'success',
            'Nama profil berhasil diperbarui.'
        );
    }

    // Form wajib ganti password

    public function passwordForm(Request $request)
    {
        if (
            $request->user()->role !== 'pengelola'
            || !$request->user()->must_change_password
        ) {
            return redirect()->route('profile.index');
        }

        return response()
            ->view('profile.index', [
                'user' => $request->user(),
                'mustChange' => true,
            ])
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }

    // Perbarui password

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(12),
            ],
        ]);

        $user = $request->user();

        if (Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Password baru harus berbeda dari password sebelumnya.',
            ]);
        }

        $user->password = $data['password'];
        $user->must_change_password = false;
        $user->password_version = $user->password_version + 1;
        $user->save();

        $request->session()->regenerate();

        $request->session()->put(
            'pb_alfa_password_version',
            $user->password_version
        );

        return redirect()
            ->route('profile.index')
            ->with('success', 'Password berhasil diperbarui.');
    }
}
