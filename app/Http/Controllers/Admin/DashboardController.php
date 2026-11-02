<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BadmintonMatch;
use App\Models\CashTransaction;
use App\Models\GalleryImage;
use App\Models\Player;
use App\Models\Tournament;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $playerCount = Player::count();

        // Hanya pertandingan reguler yang belum dihapus.
        $regularMatchCount = BadmintonMatch::count();

        $tournamentCount = Tournament::count();

        $galleryCount = GalleryImage::count();

        // Mengikuti rumus saldo pada CashController.
        // Soft-deleted transactions otomatis dikecualikan.
        $income = (int) CashTransaction::query()
            ->where('type', 'pemasukan')
            ->sum('amount');

        $expense = (int) CashTransaction::query()
            ->where('type', 'pengeluaran')
            ->sum('amount');

        $cashBalance = $income - $expense;

        return view('admin.index', compact(
            'playerCount',
            'regularMatchCount',
            'tournamentCount',
            'galleryCount',
            'cashBalance'
        ));
    }
}
