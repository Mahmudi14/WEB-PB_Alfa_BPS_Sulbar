<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        return response()
            ->view('admin.profile.index', [
                'user' => $request->user(),
            ])
            ->header(
                'Cache-Control',
                'private, no-store, max-age=0'
            );
    }

    public function updateName(Request $request)
    {
        $request->merge([
            'name' => trim(
                (string) $request->input('name', '')
            ),
        ]);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        DB::transaction(function () use ($request, $data) {
            $user = $request->user();

            // Hanya memperbarui nama akun.
            $user->update([
                'name' => $data['name'],
            ]);

            // Samakan nama profil pemain jika terhubung.
            $user->player()->update([
                'name' => $data['name'],
            ]);
        }, 3);

        return redirect()
            ->route('admin.profile.index')
            ->with(
                'success',
                'Nama profil administrator berhasil diperbarui.'
            );
    }
}
