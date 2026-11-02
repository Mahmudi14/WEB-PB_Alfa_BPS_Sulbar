<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PlayerAccountController extends Controller
{
    public function updateRole(
        Request $request,
        Player $player
    ) {
        $data = $request->validate([
            'role' => [
                'required',
                Rule::in(['pemain', 'pengelola']),
            ],
        ]);

        DB::transaction(function () use (
            $player,
            $data,
            $request
        ) {
            $locked = Player::query()
                ->whereKey($player->id)
                ->lockForUpdate()
                ->firstOrFail();

            $user = $locked->user;

            abort_unless(
                $user
                    && in_array(
                        $user->role,
                        ['pemain', 'pengelola'],
                        true
                    ),
                403
            );

            abort_if(
                (int) $user->id ===
                    (int) $request->user()->id,
                403
            );

            $oldRole = $user->role;
            $newRole = $data['role'];

            if ($oldRole !== $newRole) {
                $user->role = $newRole;

                $user->must_change_password =
                    $newRole === 'pengelola';

                $user->password_version =
                    (int) $user->password_version + 1;

                $user->save();
            }
        }, 3);

        return back()->with(
            'success',
            'Role pemain berhasil diperbarui.'
        );
    }

    public function updateStatus(
        Request $request,
        int $player
    ) {
        $data = $request->validate([
            'status' => [
                'required',
                Rule::in(['aktif', 'nonaktif']),
            ],
        ]);

        DB::transaction(function () use (
            $player,
            $data,
            $request
        ) {
            $locked = Player::withTrashed()
                ->whereKey($player)
                ->lockForUpdate()
                ->firstOrFail();

            $user = $locked->user;

            abort_if(
                $user?->role === 'admin',
                403,
                'Akun admin dilindungi.'
            );

            if ($data['status'] === 'nonaktif') {
                abort_if(
                    (int) $locked->user_id ===
                        (int) $request->user()->id,
                    403
                );

                if (!$locked->trashed()) {
                    $locked->delete();
                }

                if ($user) {
                    $user->is_active = false;
                    $user->save();
                }
            } else {
                if ($locked->trashed()) {
                    $locked->restore();
                }

                if ($user) {
                    $user->is_active = !empty($locked->email);
                    $user->save();
                }
            }
        }, 3);

        return back()->with(
            'success',
            $data['status'] === 'aktif'
                ? 'Pemain berhasil diaktifkan kembali.'
                : 'Pemain berhasil dinonaktifkan.'
        );
    }

    public function resetPassword(
        Request $request,
        Player $player
    ) {
        DB::transaction(function () use (
            $player,
            $request
        ) {
            $locked = Player::query()
                ->whereKey($player->id)
                ->lockForUpdate()
                ->firstOrFail();

            $user = $locked->user;

            abort_unless(
                $user
                    && in_array(
                        $user->role,
                        ['pemain', 'pengelola'],
                        true
                    ),
                403
            );

            abort_if(
                (int) $user->id ===
                    (int) $request->user()->id,
                403
            );

            $password = config(
                'pb_alfa.player_initial_password'
            );

            abort_unless(
                is_string($password)
                    && trim($password) !== '',
                500,
                'Password default belum dikonfigurasi.'
            );

            $user->password = $password;

            $user->must_change_password =
                $user->role === 'pengelola';

            $user->password_version =
                (int) $user->password_version + 1;

            $user->save();
        }, 3);

        return back()->with(
            'success',
            'Password direset ke password default. Sesi login lama akan terputus.'
        );
    }
}
