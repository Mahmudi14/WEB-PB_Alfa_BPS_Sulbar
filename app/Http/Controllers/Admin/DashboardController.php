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
        // PEMAIN

        $playerCount = Player::query()->count();


        // PERTANDINGAN

        $regularMatchCount = BadmintonMatch::query()
            ->count();

        $completedRegularCount = CompletedMatchQuery::regular()
            ->count();

        $pendingRegularCount = max(
            0,
            $regularMatchCount - $completedRegularCount
        );

        $tournamentMatchCount = CompletedMatchQuery::tournament()
            ->count();

        $matchCount = $regularMatchCount
            + $tournamentMatchCount;


        // TURNAMEN

        $tournamentCount = Tournament::query()->count();

        $tournamentDraftCount = Tournament::query()
            ->where('status', 'draft')
            ->count();

        $tournamentActiveCount = Tournament::query()
            ->where('status', 'active')
            ->count();

        $tournamentCompletedCount = Tournament::query()
            ->where('status', 'completed')
            ->count();


        // KEUANGAN

        $cashSummary = CashTransaction::query()
            ->selectRaw("
                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'pemasukan'
                            THEN amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS income
            ")
            ->selectRaw("
                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'pengeluaran'
                            THEN amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS expense
            ")
            ->first();

        $income = (int) ($cashSummary?->income ?? 0);

        $expense = (int) ($cashSummary?->expense ?? 0);

        $cashBalance = $income - $expense;


        // DASHBOARD

        return view('admin.dashboard.index', compact(
            'playerCount',
            'regularMatchCount',
            'completedRegularCount',
            'pendingRegularCount',
            'tournamentMatchCount',
            'matchCount',
            'tournamentCount',
            'tournamentDraftCount',
            'tournamentActiveCount',
            'tournamentCompletedCount',
            'income',
            'expense',
            'cashBalance'
        ));
    }
}
