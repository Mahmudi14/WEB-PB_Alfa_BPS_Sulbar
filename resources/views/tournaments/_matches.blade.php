<section class="tr-panel tr-tournament-matches">

    <div class="tr-section-head">
        <div>
            <span class="tr-eyebrow">
                OFFICIAL MATCH RECORDS
            </span>

            <h2>Daftar Pertandingan</h2>
        </div>

        <span class="tr-board-count">
            {{ $totalPertandingan }} Pertandingan
        </span>
    </div>

    <div class="match-page tr-tournament-match-page">

        @if($totalRonde > 0)

        {{-- Navigasi Ronde --}}
        <nav class="tr-round-nav" aria-label="Navigasi ronde pertandingan">

            @if($previousRound !== null)
            <a class="tr-round-arrow" aria-label="Ronde sebelumnya" href="{{ route('tournaments.show', [
                            'tournament' => $tournament,
                            'bagian' => 'pertandingan',
                            'ronde' => $previousRound,
                        ]) }}">
                ‹
            </a>
            @else
            <span class="tr-round-arrow is-disabled" aria-hidden="true">
                ‹
            </span>
            @endif

            <h3>{{ $roundLabel }}</h3>

            @if($nextRound !== null)
            <a class="tr-round-arrow" aria-label="Ronde berikutnya" href="{{ route('tournaments.show', [
                            'tournament' => $tournament,
                            'bagian' => 'pertandingan',
                            'ronde' => $nextRound,
                        ]) }}">
                ›
            </a>
            @else
            <span class="tr-round-arrow is-disabled" aria-hidden="true">
                ›
            </span>
            @endif

        </nav>

        {{-- Daftar pertandingan --}}
        <div class="match-list">

            @forelse($currentFixtures as $fixture)

            <div class="tr-tournament-match-item" id="fixture-{{ $fixture->id }}">

                @include('tournaments._fixture', [
                'fixture' => $fixture,
                ])

            </div>

            @empty

            <div class="match-empty">
                <strong>
                    Belum ada pertandingan pada ronde ini.
                </strong>
            </div>

            @endforelse

        </div>

        @else

        <div class="match-empty">

            <strong>
                Belum ada pertandingan.
            </strong>

            @if($isDraft && $canManage)
            <a href="{{ route(
                        'tournaments.entries',
                        $tournament
                    ) }}">
                Lanjutkan Pembuatan →
            </a>
            @endif

        </div>

        @endif

    </div>

</section>