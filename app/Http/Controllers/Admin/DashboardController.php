<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BadmintonMatch;
use App\Models\CashTransaction;
use App\Models\Player;
use App\Models\Tournament;
use App\Services\CompletedMatchQuery;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $playerCount = Player::count();

        $regularMatchCount = BadmintonMatch::count();

        $tournamentMatchCount = CompletedMatchQuery::tournament()
            ->count();

        $matchCount = $regularMatchCount + $tournamentMatchCount;

        $tournamentCount = Tournament::count();

        $income = (int) CashTransaction::query()
            ->where('type', 'pemasukan')
            ->sum('amount');

        $expense = (int) CashTransaction::query()
            ->where('type', 'pengeluaran')
            ->sum('amount');

        $cashBalance = $income - $expense;

        return view('admin.dashboard.index', compact(
            'playerCount',
            'matchCount',
            'regularMatchCount',
            'tournamentMatchCount',
            'tournamentCount',
            'cashBalance'
        ));
    }
}
