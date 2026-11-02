@extends('layouts.admin')

@section('title', 'Keuangan - Admin PB Alfa')
@section('page_title', 'Keuangan')

@section('content')

<div class="adm-page adm-ca-page">

    <div class="adm-page__heading">
        <div>
            <span class="adm-eyebrow">
                FINANCIAL MANAGEMENT
            </span>

            <h2>Keuangan Klub</h2>

            <p>
                Kelola seluruh pemasukan, pengeluaran,
                dan pencatatan kok PB Alfa.
            </p>
        </div>

        <a href="{{ route('admin.cash.create') }}" class="adm-btn adm-btn--primary">
            + Tambah Transaksi
        </a>
    </div>


    {{-- RINGKASAN KEUANGAN --}}

    <section class="adm-ca-summary">

        <div class="adm-ca-stat adm-ca-stat--balance">

            <div class="adm-ca-stat__heading">
                <span>SALDO KAS</span>
                <span class="adm-ca-stat__symbol">Rp</span>
            </div>

            <strong>
                Rp {{ number_format($balance, 0, ',', '.') }}
            </strong>

            <small>
                Total seluruh transaksi aktif
            </small>

        </div>

        <div class="adm-ca-stat">

            <div class="adm-ca-stat__heading">
                <span>TOTAL PEMASUKAN</span>
                <span class="adm-ca-stat__indicator adm-ca-stat__indicator--income">
                    +
                </span>
            </div>

            <strong class="adm-ca-positive">
                Rp {{ number_format($income, 0, ',', '.') }}
            </strong>

            <small>
                Termasuk pencatatan kok otomatis
            </small>

        </div>

        <div class="adm-ca-stat">

            <div class="adm-ca-stat__heading">
                <span>TOTAL PENGELUARAN</span>
                <span class="adm-ca-stat__indicator adm-ca-stat__indicator--expense">
                    −
                </span>
            </div>

            <strong class="adm-ca-negative">
                Rp {{ number_format($expense, 0, ',', '.') }}
            </strong>

            <small>
                Seluruh pengeluaran tercatat
            </small>

        </div>

    </section>


    @if($reviewCount > 0)

    <div class="adm-ca-review-alert">

        <div>
            <strong>
                {{ $reviewCount }} catatan kok perlu ditinjau
            </strong>

            <p>
                Jumlah kok berubah setelah nominal
                transaksi diisi. Periksa nominal
                sebelum menandai koreksi selesai.
            </p>
        </div>

        <span class="adm-pill adm-pill--warning">
            Perlu Review
        </span>

    </div>

    @endif


    {{-- RIWAYAT TRANSAKSI --}}

    <section class="adm-card adm-ca-panel">

        <div class="adm-ca-panel__header">
            <div>
                <h3>Riwayat Transaksi</h3>
                <p>
                    {{ number_format(
                    $transactions->total(),
                    0, ',', '.'
                    ) }} transaksi ditemukan
                </p>
            </div>
        </div>


        {{-- FILTER --}}

        <form method="GET" action="{{ route('admin.cash.index') }}" class="adm-ca-filters">

            <div class="adm-field adm-ca-filter-search">

                <label for="ca-search">
                    Cari Keterangan
                </label>

                <input id="ca-search" type="search" name="cari" class="adm-input" value="{{ $cari }}"
                    placeholder="Cari transaksi..." maxlength="100">

            </div>

            <div class="adm-field">

                <label for="ca-type">
                    Jenis
                </label>

                <div class="adm-select-shell">
                    <select id="ca-type" name="jenis" class="adm-select adm-premium-select" data-admin-premium-select>
                        <option value="">Semua Jenis</option>

                        <option value="pemasukan" @selected($jenis==='pemasukan' )>
                            Pemasukan
                        </option>

                        <option value="pengeluaran" @selected($jenis==='pengeluaran' )>
                            Pengeluaran
                        </option>
                    </select>
                </div>

            </div>

            <div class="adm-field">

                <label for="ca-source">
                    Sumber
                </label>

                <div class="adm-select-shell">
                    <select id="ca-source" name="sumber" class="adm-select adm-premium-select"
                        data-admin-premium-select>
                        <option value="">Semua Sumber</option>

                        <option value="manual" @selected($sumber==='manual' )>
                            Manual
                        </option>

                        <option value="otomatis" @selected($sumber==='otomatis' )>
                            Kok Otomatis
                        </option>
                    </select>
                </div>

            </div>

            <div class="adm-field">

                <label for="ca-per-page">
                    Tampilkan
                </label>

                <div class="adm-select-shell">
                    <select id="ca-per-page" name="per_page" class="adm-select adm-premium-select"
                        data-admin-premium-select>
                        @foreach([25, 50, 100] as $size)
                        <option value="{{ $size }}" @selected($perPage===$size)>
                            {{ $size }} Data
                        </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="adm-ca-filter-actions">

                <button type="submit" class="adm-btn adm-btn--primary">
                    Terapkan
                </button>

                <a href="{{ route('admin.cash.index') }}" class="adm-btn adm-btn--secondary">
                    Reset
                </a>

            </div>

        </form>


        {{-- TABEL --}}

        <div class="adm-table-wrap adm-ca-table-wrap">

            <table class="adm-table adm-ca-table">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Sumber</th>
                        <th>Jenis</th>
                        <th>Kok</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($transactions as $transaction)

                    @php
                    $isAuto =
                    $transaction->shuttlecock_player_id !== null;

                    $needsReview =
                    $isAuto
                    && $transaction->shuttlecock_review_required;
                    @endphp

                    <tr>

                        <td class="adm-ca-muted">
                            {{ $transactions->firstItem() + $loop->index }}
                        </td>

                        <td class="adm-ca-nowrap">
                            {{ $transaction->transaction_date?->format('d/m/Y') }}
                        </td>

                        <td>
                            <strong class="adm-ca-description">
                                {{ $transaction->description }}
                            </strong>
                        </td>

                        <td>
                            <span class="adm-ca-source {{ $isAuto
                                    ? 'adm-ca-source--auto'
                                    : 'adm-ca-source--manual' }}">
                                {{ $isAuto
                                ? 'Kok Otomatis'
                                : 'Manual' }}
                            </span>
                        </td>

                        <td>
                            <span class="adm-ca-type {{ $transaction->type === 'pemasukan'
                                    ? 'adm-ca-type--income'
                                    : 'adm-ca-type--expense' }}">
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </td>

                        <td class="adm-ca-nowrap">
                            {{ $isAuto
                            ? number_format(
                            $transaction->shuttlecock_count ?? 0,
                            0, ',', '.'
                            )
                            : '—' }}
                        </td>

                        <td class="adm-ca-nowrap">
                            <strong class="{{ $transaction->type === 'pemasukan'
                                    ? 'adm-ca-positive'
                                    : 'adm-ca-negative' }}">
                                {{ $transaction->type === 'pemasukan' ? '+' : '−' }}
                                Rp {{ number_format(
                                $transaction->amount,
                                0, ',', '.'
                                ) }}
                            </strong>
                        </td>

                        <td>
                            @if($needsReview)

                            <span class="adm-pill adm-pill--warning">
                                Perlu Review
                            </span>

                            @elseif($isAuto)

                            <span class="adm-pill adm-pill--info">
                                Otomatis
                            </span>

                            @else

                            <span class="adm-pill adm-pill--success">
                                Tercatat
                            </span>

                            @endif
                        </td>

                        <td>
                            <div class="adm-ca-actions">

                                <a href="{{ route('admin.cash.show', $transaction) }}"
                                    class="adm-player-action adm-player-action--detail">
                                    Detail
                                </a>

                                <a href="{{ route('admin.cash.edit', $transaction) }}"
                                    class="adm-player-action adm-player-action--edit">
                                    Edit
                                </a>

                            </div>
                        </td>

                    </tr>

                    @empty

                    <tr>
                        <td colspan="9" class="adm-empty">
                            Tidak ada transaksi yang cocok
                            dengan filter.
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- PAGINATION --}}

        @if($transactions->hasPages())

        <div class="adm-ca-pagination">

            <span>
                Halaman {{ $transactions->currentPage() }}
                dari {{ $transactions->lastPage() }}
            </span>

            <div>

                @if($transactions->onFirstPage())
                <span class="adm-btn adm-btn--secondary" aria-disabled="true">
                    Sebelumnya
                </span>
                @else
                <a href="{{ $transactions->previousPageUrl() }}" class="adm-btn adm-btn--secondary">
                    Sebelumnya
                </a>
                @endif

                @if($transactions->hasMorePages())
                <a href="{{ $transactions->nextPageUrl() }}" class="adm-btn adm-btn--secondary">
                    Berikutnya
                </a>
                @else
                <span class="adm-btn adm-btn--secondary" aria-disabled="true">
                    Berikutnya
                </span>
                @endif

            </div>

        </div>

        @endif

        <div class="adm-ca-panel__footer">
            Ringkasan saldo di atas menghitung seluruh transaksi
            aktif, bukan hanya hasil pencarian atau halaman ini.
        </div>

    </section>

</div>

@endsection