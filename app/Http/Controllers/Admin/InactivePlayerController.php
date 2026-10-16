<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Support\Facades\DB;

class InactivePlayerController extends Controller
{
    public function index()
    {
        $players = Player::onlyTrashed()
            ->with('user')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.inactive-players', compact('players'));
    }

    public function restore(int $id)
    {
        $player = Player::withTrashed()
            ->with('user')
            ->findOrFail($id);

        DB::transaction(function () use ($player) {
            $player->restore();

            if ($player->user) {
                $player->user->update([
                    'is_active' => !empty($player->email),
                ]);
            }
        }, 3);

        return redirect()
            ->route('admin.players.inactive')
            ->with('success', 'Pemain berhasil diaktifkan kembali.');
    }
}
