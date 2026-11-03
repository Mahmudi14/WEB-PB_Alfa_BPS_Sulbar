@extends('layouts.admin')

@section('title', 'Pertandingan - Admin PB Alfa')
@section('page_title', 'Pertandingan')

@section('content')

<div class="adm-page adm-match-page">

    {{-- HEADER --}}

    <div class="adm-page__heading">

        <div>
            <span class="adm-eyebrow">
                MATCH MANAGEMENT
            </span>

            <h2>Data Pertandingan</h2>

            <p>
                Pertandingan reguler termasuk pending,
                serta hasil Liga dan Cup yang valid.
            </p>
        </div>

        <a href="{{ route('admin.matches.create') }}" class="adm-btn adm-btn--primary">
            + Tambah Pertandingan
        </a>

    </div>


    {{-- RIWAYAT PERTANDINGAN --}}

    <section class="adm-card adm-match-panel">

        <div class="adm-match-panel__header">

            <div>
                <h3>Riwayat Pertandingan</h3>

                <p>
                    {{ number_format($matches->total(), 0, ',', '.') }}
                    pertandingan ditemukan
                </p>
            </div>

        </div>


        {{-- FILTER --}}

        <form method="GET" action="{{ route('admin.matches.index') }}" class="adm-match-filters">

            <div class="adm-field">

                <label for="match-filter-date">
                    Tanggal Bermain
                </label>

                <input id="match-filter-date" class="adm-input" type="date" name="tanggal" value="{{ $tanggal }}">

            </div>


            <div class="adm-field">

                <label for="match-filter-source">
                    Sumber Pertandingan
                </label>

                <div class="adm-select-shell">

                    <select id="match-filter-source" name="sumber" class="adm-select adm-premium-select"
                        data-admin-premium-select>

                        <option value="semua" @selected($sumber==='semua' )>
                            Semua Pertandingan
                        </option>

                        <option value="reguler" @selected($sumber==='reguler' )>
                            Reguler
                        </option>

                        <option value="liga" @selected($sumber==='liga' )>
                            Liga
                        </option>

                        <option value="cup" @selected($sumber==='cup' )>
                            Cup
                        </option>

                    </select>

                </div>

            </div>


            <button class="adm-btn adm-btn--primary" type="submit">
                Terapkan
            </button>

            <a href="{{ route('admin.matches.index') }}" class="adm-btn adm-btn--secondary">
                Reset
            </a>

        </form>


        {{-- TABEL PERTANDINGAN --}}

        <div class="adm-table-wrap adm-match-table-wrap">

            <table class="adm-table adm-match-table">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Tim A</th>
                        <th>Tim B</th>
                        <th>Skor</th>
                        <th>Kok</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($matches as $match)

                    @php
                    $pending = $match['result'] === null;

                    /*
                    * FORMAT SKOR HORIZONTAL
                    *
                    * Reguler / Liga:
                    * 21–18 / 19–21
                    *
                    * Best of Three:
                    * 21–18 / 19–21 / 21–16
                    *
                    * Hanya menampilkan set dengan
                    * skor Tim A dan Tim B lengkap.
                    */

                    $scoreText = collect(
                    $match['scores'] ?? []
                    )
                    ->filter(
                    fn ($set) =>
                    is_array($set)
                    && array_key_exists(0, $set)
                    && array_key_exists(1, $set)
                    && $set[0] !== null
                    && $set[1] !== null
                    )
                    ->map(
                    fn ($set) =>
                    $set[0] . '–' . $set[1]
                    )
                    ->implode(' / ');
                    @endphp

                    <tr>

                        {{-- NOMOR --}}

                        <td>
                            {{ $matches->firstItem() + $loop->index }}
                        </td>


                        {{-- TANGGAL --}}

                        <td class="adm-match-nowrap">
                            {{ $match['date']?->format('d/m/Y') ?? '—' }}
                        </td>


                        {{-- JENIS --}}

                        <td>

                            <span class="adm-match-source adm-match-source--{{ $match['source'] }}">
                                {{ $match['source_label'] }}
                            </span>

                            @if($match['tournament_name'])

                            <small class="adm-match-subline">
                                {{ $match['tournament_name'] }}
                            </small>

                            @else

                            <small class="adm-match-subline">
                                {{ $match['type_label'] }}
                            </small>

                            @endif

                        </td>


                        {{-- TIM A --}}

                        <td>

                            <strong class="adm-match-team-text">
                                {{ implode(' / ', $match['team_a']) ?: '—' }}
                            </strong>

                        </td>


                        {{-- TIM B --}}

                        <td>

                            <strong class="adm-match-team-text">
                                {{ implode(' / ', $match['team_b']) ?: '—' }}
                            </strong>

                        </td>


                        {{-- SKOR HORIZONTAL MINIMALIS --}}

                        <td class="adm-match-score-cell">

                            @if($scoreText !== '')

                            <span class="adm-match-score-inline">
                                {{ $scoreText }}
                            </span>

                            @else

                            <span class="adm-match-score-pending">
                                Belum ada skor
                            </span>

                            @endif

                        </td>


                        {{-- JUMLAH KOK --}}

                        <td class="adm-match-nowrap">

                            {{ number_format(
                            $match['shuttlecock_count'] ?? 0,
                            0, ',', '.'
                            ) }}

                        </td>


                        {{-- STATUS --}}

                        <td>

                            <span class="adm-pill {{
                                        $pending
                                            ? 'adm-pill--warning'
                                            : 'adm-pill--success'
                                    }}">

                                {{ $pending ? 'Pending' : 'Selesai' }}

                            </span>

                        </td>


                        {{-- AKSI --}}

                        <td>

                            <div class="adm-match-actions">

                                <a href="{{ $match['detail_url'] }}"
                                    class="adm-player-action adm-player-action--detail">
                                    Detail
                                </a>

                                <a href="{{ $match['edit_url'] }}" class="adm-player-action adm-player-action--edit">
                                    Edit
                                </a>

                            </div>

                        </td>

                    </tr>

                    @empty

                    <tr>
                        <td colspan="9" class="adm-empty">
                            Tidak ada pertandingan untuk filter ini.
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- PAGINATION --}}

        @if($matches->hasPages())

        <nav class="adm-match-pagination" aria-label="Halaman pertandingan">

            <span>
                Halaman {{ $matches->currentPage() }}
                dari {{ $matches->lastPage() }}
            </span>

            <div>

                @if($matches->onFirstPage())

                <span class="adm-btn adm-btn--secondary" aria-disabled="true">
                    Sebelumnya
                </span>

                @else

                <a href="{{ $matches->previousPageUrl() }}" class="adm-btn adm-btn--secondary">
                    Sebelumnya
                </a>

                @endif


                @if($matches->hasMorePages())

                <a href="{{ $matches->nextPageUrl() }}" class="adm-btn adm-btn--secondary">
                    Berikutnya
                </a>

                @else

                <span class="adm-btn adm-btn--secondary" aria-disabled="true">
                    Berikutnya
                </span>

                @endif

            </div>

        </nav>

        @endif

    </section>

</div>

@endsection