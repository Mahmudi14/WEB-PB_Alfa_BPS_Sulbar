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
@endphp

<div class="adm-page adm-tr-page">

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

    @if($champion)
    <section class="adm-card adm-tr-champion">
        <span>JUARA CUP</span>
        <h3>{{ $champion->display_name }}</h3>
    </section>
    @endif

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
                    <dd>
                        {{ $tournament->league_legs }} Kali
                    </dd>
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

    {{-- Klasemen Liga --}}

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


    {{-- Jadwal dan Bagan --}}

    <section class="adm-card adm-tr-detail-panel">

        <div class="adm-tr-section-title">
            <span>{{ $isLiga ? '04' : '03' }}</span>

            <div>
                <h3>
                    {{ $isLiga
                    ? 'Jadwal dan Hasil Pertandingan'
                    : 'Bagan dan Hasil Cup' }}
                </h3>

                <p>
                    Pilih Edit Skor untuk memperbarui
                    pertandingan yang memiliki dua peserta.
                </p>
            </div>
        </div>

        <div class="adm-tr-rounds">

            @forelse($rounds as $roundNumber => $fixtures)

            <div class="adm-tr-round">

                <div class="adm-tr-round__head">
                    <strong>
                        Ronde {{ $roundNumber }}
                    </strong>

                    <span>
                        {{ $fixtures->count() }} fixture
                    </span>
                </div>

                <div class="adm-tr-round__matches">

                    @foreach($fixtures as $fixture)

                    @php
                    $aName = $fixture->entryA?->display_name
                    ?? (
                    $fixture->is_bye
                    ? 'BYE'
                    : 'Menunggu'
                    );

                    $bName = $fixture->entryB?->display_name
                    ?? (
                    $fixture->is_bye
                    ? 'BYE'
                    : 'Menunggu'
                    );

                    $ready = !$fixture->is_bye
                    && $fixture->a_entry_id !== null
                    && $fixture->b_entry_id !== null;

                    $scoreText = $fixture->sets
                    ->map(
                    fn ($set) =>
                    $set->a_score .
                    ' : ' .
                    $set->b_score
                    )
                    ->implode(' · ');
                    @endphp

                    <div class="adm-tr-fixture adm-tr-fixture--detail">

                        <div class="adm-tr-fixture__team">
                            {{ $aName }}
                        </div>

                        <div class="adm-tr-fixture__vs">
                            VS
                        </div>

                        <div class="adm-tr-fixture__team">
                            {{ $bName }}
                        </div>

                        <div class="adm-tr-fixture__foot">

                            <span class="adm-pill {{
                                            $fixture->is_bye
                                                ? 'adm-pill--warning'
                                                : (
                                                    $fixture->result !== null
                                                        ? 'adm-pill--success'
                                                        : 'adm-pill--info'
                                                )
                                        }}">
                                {{ $fixture->is_bye
                                ? 'BYE'
                                : (
                                $fixture->result !== null
                                ? 'Selesai'
                                : 'Pending'
                                ) }}
                            </span>

                            @if($scoreText !== '')
                            <strong>
                                {{ $scoreText }}
                            </strong>
                            @endif

                            @if($fixture->jus_a !== null && $fixture->jus_b !== null)
                            <small>
                                Jus:
                                {{ $fixture->jus_a }}
                                :
                                {{ $fixture->jus_b }}
                            </small>
                            @endif

                            @if($ready)
                            <a href="{{ route(
                                                    'admin.tournaments.fixtures.edit',
                                                    [$tournament, $fixture]
                                                ) }}" class="adm-player-action adm-player-action--edit">
                                Edit Skor
                            </a>
                            @endif

                        </div>

                    </div>

                    @endforeach

                </div>

            </div>

            @empty

            <p class="adm-tr-empty">
                Belum ada jadwal pertandingan.
            </p>

            @endforelse

        </div>

    </section>

    @endif


    {{-- Penghapusan turnamen --}}

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