@extends('layouts.admin')

@section('title', $tournament->name . ' - Admin PB Alfa')
@section('page_title', 'Detail Turnamen')

@section('content')

@php
$isDraft = $tournament->status === 'draft';
$isLiga = $tournament->format === 'liga';

$statusLabel = match ($tournament->status) {
'draft' => 'Draft',
'active' => 'Berlangsung',
'completed' => 'Selesai',
default => 'Tidak diketahui',
};

$statusColor = match ($tournament->status) {
'draft' => 'warning',
'active' => 'info',
'completed' => 'success',
default => 'info',
};

/*
* Navigasi ronde mengikuti mekanisme publik.
*/

$roundNumbers = $rounds->keys()
->map(fn ($number) => (int) $number)
->values();

$requestedRound = request()->query('ronde');

$currentRound = (
is_scalar($requestedRound)
&& ctype_digit((string) $requestedRound)
&& $roundNumbers->contains((int) $requestedRound)
)
? (int) $requestedRound
: (int) ($roundNumbers->first() ?? 1);

$roundIndex = $roundNumbers->search($currentRound);

$previousRound = $roundIndex !== false && $roundIndex > 0
? $roundNumbers[$roundIndex - 1]
: null;

$nextRound = $roundIndex !== false
&& $roundIndex < $roundNumbers->count() - 1
    ? $roundNumbers[$roundIndex + 1]
    : null;

    $currentFixtures = $rounds->get(
    $currentRound,
    collect()
    );

    $roundLabel = $isLiga
    ? 'Ronde ' . $currentRound
    : match ($currentFixtures->count()) {
    1 => 'Final',
    2 => 'Semifinal',
    4 => 'Quarterfinal',
    default => 'Ronde ' . $currentRound,
    };

    /*
    * Jumlah kolom set pada scoreboard.
    */

    $scoreSetCount = $tournament->set_format === 'best_of_three'
    ? 3
    : 2;
    @endphp

    <div class="adm-page adm-tr-page">

        {{-- HEADER --}}

        <div class="adm-page__heading">

            <div>
                <span class="adm-eyebrow">
                    TOURNAMENT MANAGEMENT / DETAIL
                </span>

                <h2>{{ $tournament->name }}</h2>

                <p>
                    {{ strtoupper($tournament->format) }}
                    · {{ $tournament->match_type === 'ganda'
                    ? 'Ganda' : 'Tunggal' }}
                    · {{ $tournament->set_format_label }}
                </p>
            </div>

            <div class="adm-player-page-actions">

                <a href="{{ route('admin.tournaments.index') }}" class="adm-btn adm-btn--secondary">
                    Kembali
                </a>

                @if($isDraft)

                <a href="{{ route('admin.tournaments.rules.edit', $tournament) }}" class="adm-btn adm-btn--primary">
                    Ubah Aturan
                </a>

                <a href="{{ route('admin.tournaments.entries', $tournament) }}" class="adm-btn adm-btn--secondary">
                    Atur Peserta
                </a>

                @endif

            </div>

        </div>


        {{-- IDENTITAS TURNAMEN --}}

        <section class="adm-card adm-tr-detail-hero">

            <div>
                <span class="adm-tr-format">
                    {{ strtoupper($tournament->format) }}
                </span>

                <h3>{{ $tournament->name }}</h3>

                <p>
                    {{ $tournament->description
                    ?: 'Kompetisi PB Alfa BPS Sulawesi Barat.' }}
                </p>
            </div>

            <span class="adm-pill adm-pill--{{ $statusColor }}">
                {{ $statusLabel }}
            </span>

        </section>


        {{-- STATISTIK --}}

        <div class="adm-tr-stats">

            @foreach([
            ['label' => 'Total Peserta', 'value' => $tournament->entries->count()],
            ['label' => 'Total Pertandingan', 'value' => $totalMatches],
            ['label' => 'Sudah Selesai', 'value' => $completedMatches],
            ['label' => 'Belum Selesai', 'value' => $pendingMatches],
            ] as $item)

            <div class="adm-card adm-tr-stat">

                <span>{{ $item['label'] }}</span>

                <strong>
                    {{ number_format($item['value'], 0, ',', '.') }}
                </strong>

            </div>

            @endforeach

        </div>


        {{-- JUARA CUP --}}

        @if($champion)

        <section class="adm-card adm-tr-champion">

            <span>JUARA CUP</span>

            <h3>{{ $champion->display_name }}</h3>

        </section>

        @endif


        {{-- INFORMASI DAN PESERTA --}}

        <div class="adm-tr-detail-grid">

            <section class="adm-card">

                <div class="adm-tr-section-title">

                    <span>01</span>

                    <div>
                        <h3>Informasi Turnamen</h3>
                        <p>Aturan kompetisi yang berlaku.</p>
                    </div>

                </div>

                <dl class="adm-tr-definition">

                    <div>
                        <dt>Format</dt>
                        <dd>{{ strtoupper($tournament->format) }}</dd>
                    </div>

                    <div>
                        <dt>Jenis Pertandingan</dt>
                        <dd>
                            {{ $tournament->match_type === 'ganda'
                            ? 'Ganda' : 'Tunggal' }}
                        </dd>
                    </div>

                    <div>
                        <dt>Sistem Set</dt>
                        <dd>{{ $tournament->set_format_label }}</dd>
                    </div>

                    <div>
                        <dt>Target Poin</dt>
                        <dd>{{ $tournament->target_points }} Poin</dd>
                    </div>

                    @if($isLiga)

                    <div>
                        <dt>Jumlah Pertemuan</dt>
                        <dd>{{ $tournament->league_legs }} Kali</dd>
                    </div>

                    <div>
                        <dt>Poin Menang / Seri / Kalah</dt>
                        <dd>
                            {{ $tournament->league_win_points }}
                            /
                            {{ $tournament->league_draw_points }}
                            /
                            {{ $tournament->league_loss_points }}
                        </dd>
                    </div>

                    @endif

                </dl>

            </section>


            {{-- DAFTAR PESERTA --}}

            <section class="adm-card">

                <div class="adm-tr-section-title">

                    <span>02</span>

                    <div>
                        <h3>Daftar Peserta</h3>
                        <p>Peserta yang terdaftar pada kompetisi.</p>
                    </div>

                </div>

                <div class="adm-tr-participants">

                    @forelse($tournament->entries as $entry)

                    <div>
                        <span>{{ $loop->iteration }}</span>
                        <strong>{{ $entry->display_name }}</strong>
                    </div>

                    @empty

                    <p class="adm-tr-empty">
                        Belum ada peserta.
                    </p>

                    @endforelse

                </div>

                @if($isDraft)

                <a href="{{ route('admin.tournaments.entries', $tournament) }}"
                    class="adm-btn adm-btn--secondary adm-tr-inline-action">
                    Kelola Peserta
                </a>

                @endif

            </section>

        </div>


        @if($isDraft)

        {{-- PERSIAPAN TURNAMEN --}}

        <section class="adm-card adm-tr-start">

            <div>
                <h3>Persiapan Turnamen</h3>

                <p>
                    Turnamen masih draft. Selesaikan pengaturan
                    peserta dan tinjau jadwal sebelum memulai.
                </p>
            </div>

            @if($tournament->entries->count() >= 2)

            <a href="{{ route('admin.tournaments.schedule', $tournament) }}" class="adm-btn adm-btn--primary">
                Pratinjau Jadwal
            </a>

            @else

            <a href="{{ route('admin.tournaments.entries', $tournament) }}" class="adm-btn adm-btn--primary">
                Atur Peserta
            </a>

            @endif

        </section>

        @else

        {{-- KLASMEN LIGA --}}

        @if($isLiga)

        <section class="adm-card adm-tr-detail-panel">

            <div class="adm-tr-section-title">

                <span>03</span>

                <div>
                    <h3>Klasemen Liga</h3>
                    <p>
                        Peringkat mengikuti hasil
                        TournamentStandingsService.
                    </p>
                </div>

            </div>

            <div class="adm-table-wrap">

                <table class="adm-table adm-tr-standings">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Peserta</th>
                            <th>Main</th>
                            <th>Menang</th>
                            <th>Seri</th>
                            <th>Kalah</th>
                            <th>Selisih Set</th>
                            <th>Poin</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($standings as $row)

                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <strong>
                                    {{ $row['entry']->display_name }}
                                </strong>
                            </td>

                            <td>{{ $row['played'] }}</td>
                            <td>{{ $row['wins'] }}</td>
                            <td>{{ $row['draws'] }}</td>
                            <td>{{ $row['losses'] }}</td>

                            <td>
                                {{ $row['sets_for'] - $row['sets_against'] }}
                            </td>

                            <td>
                                <strong>
                                    {{ $row['league_points'] }}
                                </strong>
                            </td>
                        </tr>

                        @empty

                        <tr>
                            <td colspan="8" class="adm-empty">
                                Klasemen belum tersedia.
                            </td>
                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </section>

        @endif


        {{-- JADWAL DAN HASIL PER RONDE --}}

        <section id="jadwal-pertandingan" class="adm-card adm-tr-detail-panel adm-tr-detail-schedule">

            <div class="adm-tr-section-title">

                <span>{{ $isLiga ? '04' : '03' }}</span>

                <div>
                    <h3>
                        {{ $isLiga
                        ? 'Jadwal dan Hasil Pertandingan'
                        : 'Bagan dan Hasil Cup' }}
                    </h3>

                    <p>
                        Jadwal pertandingan berdasarkan ronde.
                        Pilih Edit Skor untuk memperbarui hasil.
                    </p>
                </div>

            </div>


            @if($roundNumbers->isNotEmpty())

            {{-- NAVIGASI RONDE --}}

            <nav class="adm-tr-detail-round-nav" aria-label="Navigasi ronde pertandingan">

                @if($previousRound !== null)

                <a href="{{ route('admin.tournaments.show', [
                                'tournament' => $tournament,
                                'ronde' => $previousRound,
                            ]) }}#jadwal-pertandingan" class="adm-tr-detail-round-nav__arrow"
                    aria-label="Ronde sebelumnya">
                    &#8249;
                </a>

                @else

                <span class="adm-tr-detail-round-nav__arrow is-disabled" aria-hidden="true">
                    &#8249;
                </span>

                @endif


                <div class="adm-tr-detail-round-nav__current">

                    <strong>{{ $roundLabel }}</strong>

                    <small>
                        {{ $roundIndex + 1 }}
                        dari {{ $roundNumbers->count() }} ronde
                    </small>

                </div>


                @if($nextRound !== null)

                <a href="{{ route('admin.tournaments.show', [
                                'tournament' => $tournament,
                                'ronde' => $nextRound,
                            ]) }}#jadwal-pertandingan" class="adm-tr-detail-round-nav__arrow"
                    aria-label="Ronde berikutnya">
                    &#8250;
                </a>

                @else

                <span class="adm-tr-detail-round-nav__arrow is-disabled" aria-hidden="true">
                    &#8250;
                </span>

                @endif

            </nav>


            {{-- PROFESSIONAL SCOREBOARD --}}

            <div class="adm-tr-scoreboard {{ $scoreSetCount === 3
                    ? 'adm-tr-scoreboard--three'
                    : '' }}">

                <div class="adm-tr-scoreboard__heading">

                    <strong>
                        {{ $roundLabel }}
                    </strong>

                    <span>
                        {{ $currentFixtures->count() }}
                        pertandingan
                    </span>

                </div>


                <div class="adm-tr-scoreboard__list">

                    @forelse($currentFixtures as $fixture)

                    @php
                    $isBye = (bool) $fixture->is_bye;

                    $aName = $fixture->entryA?->display_name
                    ?? (
                    $isBye
                    ? 'BYE'
                    : 'Menunggu'
                    );

                    $bName = $fixture->entryB?->display_name
                    ?? (
                    $isBye
                    ? 'BYE'
                    : 'Menunggu'
                    );

                    $ready = !$isBye
                    && $fixture->a_entry_id !== null
                    && $fixture->b_entry_id !== null;

                    /*
                    * Skor diurutkan berdasarkan
                    * nomor set yang tersimpan.
                    */

                    $fixtureSets = $fixture->sets
                    ->keyBy('set_number');

                    $aIsWinner = $fixture->winner_entry_id !== null
                    && (int) $fixture->winner_entry_id ===
                    (int) $fixture->a_entry_id;

                    $bIsWinner = $fixture->winner_entry_id !== null
                    && (int) $fixture->winner_entry_id ===
                    (int) $fixture->b_entry_id;
                    @endphp


                    <div class="adm-tr-scoreboard__item" id="fixture-{{ $fixture->id }}">

                        {{-- NOMOR PERTANDINGAN --}}

                        <span class="adm-tr-scoreboard__number">
                            {{ str_pad(
                            (string) $loop->iteration,
                            2,
                            '0',
                            STR_PAD_LEFT
                            ) }}
                        </span>


                        {{-- TIM DAN SKOR --}}

                        <div class="adm-tr-scoreboard__board">

                            {{-- TIM A --}}

                            <div class="adm-tr-scoreboard__team-row">

                                <strong class="adm-tr-scoreboard__team {{
                                            $aIsWinner ? 'is-winner' : ''
                                        }}">
                                    {{ $aName }}
                                </strong>

                                <div class="adm-tr-scoreboard__scores" aria-label="Skor Tim A">

                                    @for($setNumber = 1; $setNumber <= $scoreSetCount; $setNumber++) @php
                                        $set=$fixtureSets->get($setNumber);

                                        $aScore = $set?->a_score;
                                        $bScore = $set?->b_score;

                                        $aWonSet = $aScore !== null
                                        && $bScore !== null
                                        && (int) $aScore > (int) $bScore;
                                        @endphp

                                        <span class="adm-tr-scoreboard__score {{
                                                    $aWonSet ? 'is-set-winner' : ''
                                                }}" title="Set {{ $setNumber }} - Tim A">

                                            {{ $aScore ?? '—' }}

                                        </span>

                                        @endfor

                                </div>

                            </div>


                            {{-- TIM B --}}

                            <div class="adm-tr-scoreboard__team-row">

                                <strong class="adm-tr-scoreboard__team {{
                                            $bIsWinner ? 'is-winner' : ''
                                        }}">
                                    {{ $bName }}
                                </strong>

                                <div class="adm-tr-scoreboard__scores" aria-label="Skor Tim B">

                                    @for($setNumber = 1; $setNumber <= $scoreSetCount; $setNumber++) @php
                                        $set=$fixtureSets->get($setNumber);

                                        $aScore = $set?->a_score;
                                        $bScore = $set?->b_score;

                                        $bWonSet = $aScore !== null
                                        && $bScore !== null
                                        && (int) $bScore > (int) $aScore;
                                        @endphp

                                        <span class="adm-tr-scoreboard__score {{
                                                    $bWonSet ? 'is-set-winner' : ''
                                                }}" title="Set {{ $setNumber }} - Tim B">

                                            {{ $bScore ?? '—' }}

                                        </span>

                                        @endfor

                                </div>

                            </div>


                            {{-- INFORMASI TAMBAHAN --}}

                            @if(
                            $fixture->jus_a !== null
                            && $fixture->jus_b !== null
                            )

                            <small class="adm-tr-scoreboard__note">
                                Jus:
                                {{ $fixture->jus_a }}
                                –
                                {{ $fixture->jus_b }}
                            </small>

                            @endif

                        </div>


                        {{-- AKSI --}}

                        <div class="adm-tr-scoreboard__action">

                            @if($ready)

                            <a href="{{ route(
                                                'admin.tournaments.fixtures.edit',
                                                [$tournament, $fixture]
                                            ) }}" class="adm-tr-scoreboard__edit"
                                aria-label="Edit skor pertandingan {{ $loop->iteration }}">

                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">

                                    <path d="M12 20h9" />

                                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L9 17l-4 1 1-4 10.5-10.5z" />

                                </svg>

                                <span>Edit Skor</span>

                            </a>

                            @elseif($isBye)

                            <span class="adm-tr-scoreboard__bye">
                                Lolos otomatis
                            </span>

                            @endif

                        </div>

                    </div>

                    @empty

                    <p class="adm-tr-empty">
                        Belum ada pertandingan pada ronde ini.
                    </p>

                    @endforelse

                </div>

            </div>

            @else

            <p class="adm-tr-empty">
                Belum ada jadwal pertandingan.
            </p>

            @endif

        </section>

        @endif


        {{-- HAPUS TURNAMEN --}}

        <section class="adm-card adm-tr-danger">

            <div>

                <h3>Hapus Turnamen</h3>

                <p>
                    Turnamen dan seluruh fixture terkait akan
                    dihapus. Pemakaian kok yang berkaitan
                    akan dihitung ulang oleh sistem.
                </p>

            </div>

            <form method="POST" action="{{ route('admin.tournaments.destroy', $tournament) }}" data-single-submit
                data-confirm="Hapus turnamen {{ $tournament->name }} beserta seluruh pertandingannya?"
                data-confirm-title="Hapus Turnamen" data-confirm-text="Ya, Hapus">

                @csrf
                @method('DELETE')

                <button type="submit" class="adm-btn adm-btn--danger">
                    Hapus Turnamen
                </button>

            </form>

        </section>

    </div>

    @endsection