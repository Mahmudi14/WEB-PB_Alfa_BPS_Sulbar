@php
$bracketRounds = array_values($bracketRounds);

$roundCount = count($bracketRounds);
$levels = max(0, $roundCount - 1);

$firstRound = $bracketRounds[0] ?? [];

$leafPerSide = $levels > 0
? max(1, intdiv(count($firstRound), 2))
: 1;

$columnWidth = 210;
$finalWidth = 220;
$cardWidth = 184;
$cardHeight = 88;
$stepHeight = 112;

$sideWidth = $levels * $columnWidth;

$boardWidth = $sideWidth * 2 + $finalWidth;
$boardHeight = $leafPerSide * $stepHeight;
$finalY = $boardHeight / 2;

$cards = [];
$lines = [];
$headers = [];
$roundNames = [];

// Penamaan babak otomatis.
foreach ($bracketRounds as $r => $matches) {
$roundNames[$r] = match (count($matches)) {
1 => 'FINAL',
2 => 'SEMIFINAL',
4 => 'QUARTERFINAL',
default => 'RONDE ' . ($r + 1),
};
}

// Susun bagan kiri dan kanan menuju tengah.
for ($stage = 0; $stage < $levels; $stage++) { $matches=array_values( $bracketRounds[$stage] ?? [] );
    $perSide=intdiv(count($matches), 2); $factor=2 ** $stage; foreach ([0, 1] as $side) { $columnX=$side===0 ? $stage *
    $columnWidth : $sideWidth + $finalWidth + ($levels - 1 - $stage) * $columnWidth; $headers[]=[ 'left'=> $columnX,
    'width' => $columnWidth,
    'label' => $roundNames[$stage],
    'final' => false,
    ];

    for ($i = 0; $i < $perSide; $i++) { $matchIndex=$side===0 ? $i : $perSide + $i; $match=$matches[$matchIndex];
        $centerY=($i + 0.5) * $factor * $stepHeight; $left=$columnX + ($columnWidth - $cardWidth) / 2; $top=$centerY -
        $cardHeight / 2; $cards[]=[ 'left'=> $left,
        'top' => $top,
        'match' => $match,
        'label' => $roundNames[$stage]
        . ' · M' . ($matchIndex + 1),
        'final' => false,
        ];

        $targetY = $stage === $levels - 1
        ? $finalY
        : (intdiv($i, 2) + 0.5)
        * (2 ** ($stage + 1))
        * $stepHeight;

        if ($side === 0) {
        $x1 = $left + $cardWidth;

        $x2 = $stage === $levels - 1
        ? $sideWidth
        + ($finalWidth - $cardWidth) / 2
        : ($stage + 1) * $columnWidth
        + ($columnWidth - $cardWidth) / 2;
        } else {
        $x1 = $left;

        $x2 = $stage === $levels - 1
        ? $sideWidth
        + ($finalWidth + $cardWidth) / 2
        : $sideWidth
        + $finalWidth
        + ($levels - 2 - $stage) * $columnWidth
        + ($columnWidth + $cardWidth) / 2;
        }

        $lines[] = [
        'x1' => $x1,
        'x2' => $x2,
        'xm' => ($x1 + $x2) / 2,
        'y1' => $centerY,
        'y2' => $targetY,
        ];
        }
        }
        }

        // Final di tengah.
        $headers[] = [
        'left' => $sideWidth,
        'width' => $finalWidth,
        'label' => 'FINAL',
        'final' => true,
        ];

        $finalMatch = $bracketRounds[$roundCount - 1][0]
        ?? null;

        if ($finalMatch) {
        $cards[] = [
        'left' => $sideWidth
        + ($finalWidth - $cardWidth) / 2,
        'top' => $finalY - $cardHeight / 2,
        'match' => $finalMatch,
        'label' => 'FINAL',
        'final' => true,
        ];
        }
        @endphp

        <div class="tr-cup-bracket">

            <div class="tr-cup-scroll tr-bracket-viewport" data-bracket-viewport tabindex="0" role="region"
                aria-label="Bagan pertandingan Cup">

                <div class="tr-bracket-stage" data-bracket-stage>

                    <div class="tr-bracket-content" data-bracket-content>

                        <div class="tr-cup-map" style="width: {{ $boardWidth }}px;">

                            {{-- JUDUL BABAK --}}
                            <div class="tr-cup-round-titles" style="width: {{ $boardWidth }}px;">

                                @foreach($headers as $header)

                                <div class="tr-cup-round-title {{ $header['final'] ? 'is-final' : '' }}" style="
                                    left: {{ $header['left'] }}px;
                                    width: {{ $header['width'] }}px;
                                ">

                                    {{ $header['label'] }}

                                </div>

                                @endforeach

                            </div>

                            {{-- KANVAS --}}
                            <div class="tr-cup-canvas" style="
                            width: {{ $boardWidth }}px;
                            height: {{ $boardHeight }}px;
                        ">

                                <svg class="tr-cup-lines" xmlns="http://www.w3.org/2000/svg" width="{{ $boardWidth }}"
                                    height="{{ $boardHeight }}" viewBox="0 0 {{ $boardWidth }} {{ $boardHeight }}"
                                    fill="none" aria-hidden="true">

                                    @foreach($lines as $line)

                                    <path d="
                                        M {{ $line['x1'] }} {{ $line['y1'] }}
                                        H {{ $line['xm'] }}
                                        V {{ $line['y2'] }}
                                        H {{ $line['x2'] }}
                                    " stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />

                                    @endforeach

                                </svg>


                                {{-- KARTU PERTANDINGAN --}}
                                @foreach($cards as $card)

                                @php
                                $match = $card['match'];

                                $winnerId = $match['winner_id']
                                ?? null;

                                $aId = $match['a_id'] ?? null;
                                $bId = $match['b_id'] ?? null;

                                $aWin = $winnerId !== null
                                && $aId !== null
                                && (int) $winnerId === (int) $aId;

                                $bWin = $winnerId !== null
                                && $bId !== null
                                && (int) $winnerId === (int) $bId;

                                $rawA = trim((string) (
                                $match['a'] ?? ''
                                ));

                                $rawB = trim((string) (
                                $match['b'] ?? ''
                                ));

                                // Semua placeholder menjadi "Menunggu".
                                $nameA = $rawA === ''
                                || preg_match('/^menunggu\b/i', $rawA)
                                ? 'Menunggu'
                                : $rawA;

                                $nameB = $rawB === ''
                                || preg_match('/^menunggu\b/i', $rawB)
                                ? 'Menunggu'
                                : $rawB;

                                $scoreText = $match['score'] ?? '';

                                $statusText = $match['status']
                                ?? 'Belum dimainkan';
                                @endphp

                                <article class="tr-cup-game {{ $card['final'] ? 'tr-cup-game--final' : '' }}" style="
                                    left: {{ $card['left'] }}px;
                                    top: {{ $card['top'] }}px;
                                    width: {{ $cardWidth }}px;
                                    height: {{ $cardHeight }}px;
                                ">

                                    <div class="tr-cup-game__top">

                                        <span>{{ $card['label'] }}</span>

                                        @if(!empty($match['edit_url']))
                                        <a href="{{ $match['edit_url'] }}" title="Isi atau edit skor">
                                            Edit
                                        </a>
                                        @endif

                                    </div>

                                    <div class="tr-cup-player
                                    {{ $aWin ? 'is-winner' : '' }}
                                    {{ $bWin ? 'is-loser' : '' }}">

                                        <span title="{{ $nameA }}">
                                            {{ $nameA }}
                                        </span>

                                        @if($aWin)
                                        <b aria-label="Pemenang">✓</b>
                                        @endif

                                    </div>

                                    <div class="tr-cup-player
                                    {{ $bWin ? 'is-winner' : '' }}
                                    {{ $aWin ? 'is-loser' : '' }}">

                                        <span title="{{ $nameB }}">
                                            {{ $nameB }}
                                        </span>

                                        @if($bWin)
                                        <b aria-label="Pemenang">✓</b>
                                        @endif

                                    </div>

                                    <div class="tr-cup-game__bottom" title="{{ $scoreText ?: $statusText }}">

                                        {{ $scoreText ?: $statusText }}

                                    </div>

                                </article>

                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Aktif untuk halaman Detail dan pratinjau Cup --}}
        @once
        @push('scripts')
        <script src="{{ asset('assets/js/tournament-bracket-zoom.js') }}?v=13" defer>
        </script>
        @endpush
        @endonce