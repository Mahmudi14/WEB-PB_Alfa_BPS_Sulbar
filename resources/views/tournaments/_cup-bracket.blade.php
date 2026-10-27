@php
$roundCount = count($bracketRounds);

$levels = max(0, $roundCount - 1);

$firstRound = $bracketRounds[0] ?? [];

$leafPerSide = $levels > 0
? max(1, intdiv(count($firstRound), 2))
: 1;

// Ukuran kanvas bagan dalam piksel
$columnWidth = 210;
$finalWidth = 220;
$cardWidth = 184;
$cardHeight = 88;
$stepHeight = 112;

$sideWidth = $levels * $columnWidth;

$boardWidth = ($sideWidth * 2) + $finalWidth;
$boardHeight = $leafPerSide * $stepHeight;

$finalY = $boardHeight / 2;

$cards = [];
$lines = [];

// Susun kartu dari sisi luar menuju tengah.
for ($stage = 0; $stage < $levels; $stage++) { $matches=array_values( $bracketRounds[$stage] ?? [] );
    $perSide=intdiv(count($matches), 2); foreach ([0, 1] as $side) { for ($i=0; $i < $perSide; $i++) {
    $matchIndex=$side===0 ? $i : $perSide + $i; $match=$matches[$matchIndex]; $factor=2 ** $stage; $centerY=($i + 0.5) *
    $factor * $stepHeight; if ($side===0) { $columnX=$stage * $columnWidth; } else { $columnX=$sideWidth + $finalWidth +
    ($levels - 1 - $stage) * $columnWidth; } $left=$columnX + ($columnWidth - $cardWidth) / 2; $top=$centerY -
    $cardHeight / 2; $cards[]=[ 'left'=> $left,
    'top' => $top,
    'match' => $match,
    'label' => 'RONDE ' . ($stage + 1)
    . ' · M' . ($matchIndex + 1),
    'final' => false,
    ];

    // Titik tujuan: ronde berikutnya atau final.
    if ($stage === $levels - 1) {
    $targetY = $finalY;
    } else {
    $targetY = (
    intdiv($i, 2) + 0.5
    ) * (2 ** ($stage + 1)) * $stepHeight;
    }

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

    // Kartu final di tengah.
    $finalMatch = $bracketRounds[$roundCount - 1][0] ?? null;

    if ($finalMatch) {
    $cards[] = [
    'left' => $sideWidth
    + ($finalWidth - $cardWidth) / 2,
    'top' => $finalY - $cardHeight / 2,
    'match' => $finalMatch,
    'label' => 'GRAND FINAL',
    'final' => true,
    ];
    }
    @endphp

    <div class="tr-cup-bracket">

        <p class="tr-cup-hint">
            Bagan menampilkan jalur pertandingan
            dari Grup A dan Grup B menuju Grand Final.
            Geser ke samping untuk melihat seluruh bagan.
        </p>

        <div class="tr-cup-scroll" tabindex="0" role="region"
            aria-label="Bagan Cup dari Grup A dan Grup B menuju Grand Final">

            <div class="tr-cup-map" style="width: {{ $boardWidth }}px;">

                {{-- JUDUL BAGAN --}}
                <div class="tr-cup-head" style="grid-template-columns:
                    {{ $sideWidth }}px
                    {{ $finalWidth }}px
                    {{ $sideWidth }}px;">

                    <strong>
                        @if($levels > 0)
                        GRUP A
                        @endif
                    </strong>

                    <strong class="tr-cup-head__final">
                        GRAND FINAL
                    </strong>

                    <strong>
                        @if($levels > 0)
                        GRUP B
                        @endif
                    </strong>

                </div>

                {{-- KANVAS BAGAN --}}
                <div class="tr-cup-canvas" style="
                    width: {{ $boardWidth }}px;
                    height: {{ $boardHeight }}px;
                ">

                    {{-- GARIS PENGHUBUNG --}}
                    <svg class="tr-cup-lines" xmlns="http://www.w3.org/2000/svg" width="{{ $boardWidth }}"
                        height="{{ $boardHeight }}" viewBox="0 0 {{ $boardWidth }} {{ $boardHeight }}" fill="none"
                        aria-hidden="true">

                        @foreach($lines as $line)

                        <path d="
                                M {{ $line['x1'] }} {{ $line['y1'] }}
                                H {{ $line['xm'] }}
                                V {{ $line['y2'] }}
                                H {{ $line['x2'] }}
                            " stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />

                        @endforeach

                    </svg>

                    {{-- KARTU PERTANDINGAN --}}
                    @foreach($cards as $card)

                    @php
                    $match = $card['match'];

                    $winnerId = $match['winner_id'] ?? null;

                    $aWin = $winnerId !== null
                    && $winnerId == ($match['a_id'] ?? null);

                    $bWin = $winnerId !== null
                    && $winnerId == ($match['b_id'] ?? null);

                    $aLose = $bWin;
                    $bLose = $aWin;

                    $nameA = $match['a'] ?? 'Menunggu peserta';
                    $nameB = $match['b'] ?? 'Menunggu peserta';

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

                            <span>
                                {{ $card['label'] }}
                            </span>

                            @if(!empty($match['edit_url']))

                            <a href="{{ $match['edit_url'] }}" aria-label="Edit skor pertandingan {{ $card['label'] }}"
                                title="Edit skor dan jumlah kok">
                                Edit
                            </a>

                            @endif

                        </div>

                        <div class="tr-cup-player
                            {{ $aWin ? 'is-winner' : '' }}
                            {{ $aLose ? 'is-loser' : '' }}">

                            <span title="{{ $nameA }}">
                                {{ $nameA }}
                            </span>

                            @if($aWin)
                            <b aria-label="Pemenang">✓</b>
                            @endif

                        </div>

                        <div class="tr-cup-player
                            {{ $bWin ? 'is-winner' : '' }}
                            {{ $bLose ? 'is-loser' : '' }}">

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