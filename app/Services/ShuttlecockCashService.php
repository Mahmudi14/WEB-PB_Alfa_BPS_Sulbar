<?php

namespace App\Services;

use App\Models\BadmintonMatch;
use App\Models\CashTransaction;
use App\Models\Player;
use Illuminate\Support\Facades\DB;

class ShuttlecockCashService
{
    public function regularPairs(BadmintonMatch $match): array
    {
        if (!$match->played_on) {
            return [];
        }

        return $match->participants()
            ->whereNotNull('player_id')
            ->distinct()
            ->pluck('player_id')
            ->map(fn($id) => [
                'player_id' => (int) $id,
                'date' => $match->played_on->toDateString(),
            ])->all();
    }

    public function tournamentPairs(int $tournamentId): array
    {
        $all = [];

        foreach (['a', 'b'] as $side) {
            $rows = CompletedMatchQuery::tournament()
                ->join('tournament_entry_players as tep', function ($join) use ($side) {
                    $join->on('tep.entry_id', '=', "tf.{$side}_entry_id")
                        ->on('tep.tournament_id', '=', 'tf.tournament_id');
                })
                ->where('tf.tournament_id', $tournamentId)
                ->select('tep.player_id', 'tf.played_on')
                ->get();

            foreach ($rows as $row) {
                $all[] = [
                    'player_id' => (int) $row->player_id,
                    'date' => (string) $row->played_on,
                ];
            }
        }

        return $all;
    }

    // Panggil di dalam transaksi database penyimpanan pertandingan.
    public function syncPairs(array $pairs, int $actorId): void
    {
        $unique = collect($pairs)
            ->filter(fn($p) => !empty($p['player_id']) && !empty($p['date']))
            ->unique(fn($p) => $p['date'] . ':' . $p['player_id'])
            ->sortBy(fn($p) => $p['date'] . ':' . str_pad(
                (string) $p['player_id'],
                15,
                '0',
                STR_PAD_LEFT
            ));

        foreach ($unique as $pair) {
            $playerId = (int) $pair['player_id'];
            $date = (string) $pair['date'];

            // Mengurangi risiko transaksi ganda saat dua skor
            // milik pemain yang sama disimpan bersamaan.
            $player = Player::withTrashed()
                ->whereKey($playerId)
                ->lockForUpdate()
                ->first();

            if (!$player) {
                continue;
            }

            $regular = (int) CompletedMatchQuery::regular()
                ->whereDate('m.played_on', $date)
                ->whereExists(fn($q) => $q->selectRaw('1')
                    ->from('match_participants as mp')
                    ->whereColumn('mp.match_id', 'm.id')
                    ->where('mp.player_id', $playerId))
                ->sum('m.shuttlecock_count');

            $tournament = (int) CompletedMatchQuery::tournament()
                ->whereDate('tf.played_on', $date)
                ->where(function ($q) use ($playerId) {
                    foreach (['a', 'b'] as $side) {
                        $method = $side === 'a'
                            ? 'whereExists'
                            : 'orWhereExists';

                        $q->{$method}(fn($sub) => $sub->selectRaw('1')
                            ->from('tournament_entry_players as tep')
                            ->whereColumn(
                                'tep.entry_id',
                                "tf.{$side}_entry_id"
                            )
                            ->whereColumn(
                                'tep.tournament_id',
                                'tf.tournament_id'
                            )
                            ->where('tep.player_id', $playerId));
                    }
                })
                ->sum('tf.shuttlecock_count');

            $total = $regular + $tournament;

            $record = CashTransaction::withTrashed()
                ->where('shuttlecock_player_id', $playerId)
                ->whereDate('transaction_date', $date)
                ->lockForUpdate()
                ->first();

            if (!$record && $total === 0) {
                continue;
            }

            if ($record) {
                $wasPaid = (int) $record->amount > 0;
                $hasChanged =
                    (int) $record->shuttlecock_count !== $total;

                $record->forceFill([
                    'description' => 'Kok ' . $player->name . ' ' . $total,
                    'shuttlecock_count' => $total,
                    'shuttlecock_review_required' =>
                    (bool) $record->shuttlecock_review_required
                        || ($wasPaid && $hasChanged),
                ])->save();

                if ($total === 0 && !$wasPaid) {
                    if (!$record->trashed()) {
                        $record->delete();
                    }
                } elseif ($record->trashed()) {
                    $record->restore();
                }

                continue;
            }

            CashTransaction::create([
                'transaction_date' => $date,
                'type' => 'pemasukan',
                'amount' => 0,
                'description' => 'Kok ' . $player->name . ' ' . $total,
                'shuttlecock_player_id' => $playerId,
                'shuttlecock_count' => $total,
                'shuttlecock_review_required' => false,
                'created_by' => $actorId,
            ]);
        }
    }
}
