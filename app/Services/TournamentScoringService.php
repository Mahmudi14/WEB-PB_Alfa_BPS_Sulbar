<?php

namespace App\Services;

use App\Models\Tournament;
use Illuminate\Validation\ValidationException;

class TournamentScoringService
{
    /**
     * Return ['result' => a|b|draw|null, 'winner_side' => a|b|null, 'sets' => [...], 'jus_a' => ?int, 'jus_b' => ?int].
     * Empty scores intentionally clear a recorded result. Deuce in a set requires a two-point margin.
     */
    public function evaluate(Tournament $tournament, array $rawSets, ?int $jusA, ?int $jusB): array
    {
        $sets = [];
        foreach ($rawSets as $index => $scores) {
            $a = $scores['a'] ?? null;
            $b = $scores['b'] ?? null;
            if (($a === null || $a === '') && ($b === null || $b === '')) {
                continue;
            }
            if ($a === null || $a === '' || $b === null || $b === '') {
                $this->fail('Isi kedua skor untuk setiap set yang dimainkan.');
            }
            if (!is_numeric($a) || !is_numeric($b) || (int) $a != $a || (int) $b != $b || $a < 0 || $b < 0 || $a > 999 || $b > 999) {
                $this->fail('Skor set harus berupa bilangan bulat antara 0 dan 999.');
            }
            $sets[] = ['a' => (int) $a, 'b' => (int) $b];
        }

        if ($sets === []) {
            if ($jusA !== null || $jusB !== null) {
                $this->fail('Jus hanya dapat diisi setelah dua set selesai.');
            }
            return ['result' => null, 'winner_side' => null, 'sets' => [], 'jus_a' => null, 'jus_b' => null];
        }

        $target = $tournament->target_points;
        foreach ($sets as $set) {
            if (max($set['a'], $set['b']) < $target || abs($set['a'] - $set['b']) !== 2 && max($set['a'], $set['b']) > $target ||
                (max($set['a'], $set['b']) === $target && abs($set['a'] - $set['b']) < 2)) {
                $this->fail("Set harus mencapai {$target} poin dengan selisih minimal 2; saat jus, selisih akhir tepat 2.");
            }
        }

        $format = $tournament->set_format;
        $maxSets = $format === 'best_of_three' ? 3 : 2;
        if (count($sets) > $maxSets) {
            $this->fail('Jumlah set melebihi aturan turnamen.');
        }
        // Missing middle set is never accepted (e.g., scores[1] and scores[3]).
        $filledKeys = array_keys(array_filter($rawSets, fn ($s) => ($s['a'] ?? null) !== null && ($s['a'] ?? '') !== '' || ($s['b'] ?? null) !== null && ($s['b'] ?? '') !== ''));
        if ($filledKeys !== range(0, count($sets) - 1) && $filledKeys !== range(1, count($sets))) {
            $this->fail('Urutan set tidak boleh dilompati.');
        }

        if ($format === 'best_of_three') {
            if ($jusA !== null || $jusB !== null) {
                $this->fail('Jus akumulasi hanya untuk format 2 Set Akumulasi.');
            }
            if (count($sets) < 2) {
                $this->fail('Masukkan minimal dua set, atau kosongkan semua skor.');
            }
            $winsA = count(array_filter($sets, fn ($s) => $s['a'] > $s['b']));
            $winsB = count($sets) - $winsA;
            $firstTwoSplit = ($sets[0]['a'] > $sets[0]['b']) !== ($sets[1]['a'] > $sets[1]['b']);
            if ((count($sets) === 2 && max($winsA, $winsB) === 2)
                || (count($sets) === 3 && $firstTwoSplit && max($winsA, $winsB) === 2)) {
                $side = $winsA > $winsB ? 'a' : 'b';
                return ['result' => $side, 'winner_side' => $side, 'sets' => $sets, 'jus_a' => null, 'jus_b' => null];
            }
            $this->fail('Jika dua set pertama imbang 1–1, set ketiga wajib diisi. Jika sudah 2–0, set ketiga tidak dimainkan.');
        }

        if (count($sets) !== 2) {
            $this->fail('Format ini harus memainkan tepat dua set.');
        }

        if ($format === 'two_draw') {
            if ($jusA !== null || $jusB !== null) {
                $this->fail('Format 2 Set Boleh Seri tidak memakai jus akumulasi.');
            }
            $winsA = (int) ($sets[0]['a'] > $sets[0]['b']) + (int) ($sets[1]['a'] > $sets[1]['b']);
            $side = $winsA === 2 ? 'a' : ($winsA === 0 ? 'b' : null);
            return ['result' => $side ?? 'draw', 'winner_side' => $side, 'sets' => $sets, 'jus_a' => null, 'jus_b' => null];
        }

        $totalA = $sets[0]['a'] + $sets[1]['a'];
        $totalB = $sets[0]['b'] + $sets[1]['b'];
        if ($totalA === $totalB) {
            if ($jusA === null || $jusB === null || $jusA < 0 || $jusB < 0 || max($jusA, $jusB) < 2 || abs($jusA - $jusB) !== 2) {
                $this->fail('Total poin sama: isi jus hingga salah satu tim unggul tepat 2 poin.');
            }
            $side = $jusA > $jusB ? 'a' : 'b';
            return ['result' => $side, 'winner_side' => $side, 'sets' => $sets, 'jus_a' => $jusA, 'jus_b' => $jusB];
        }
        if ($jusA !== null || $jusB !== null) {
            $this->fail('Jus tidak diperlukan jika akumulasi poin sudah berbeda.');
        }
        $side = $totalA > $totalB ? 'a' : 'b';
        return ['result' => $side, 'winner_side' => $side, 'sets' => $sets, 'jus_a' => null, 'jus_b' => null];
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['scores' => $message]);
    }
}
