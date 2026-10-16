@extends('layouts.app')

@section('title', 'Kas - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cash.css') }}?v=1">
@endpush

@section('content')

@php
$viewer = auth()->user();
$canManage = $viewer?->canManage() ?? false;
@endphp

<div class="cash-page">

    <header class="cash-hero">
        <div>
            <span class="cash-eyebrow">
                PB ALFA / FINANCIAL RECORDS
            </span>

            <h1>LAPORAN <span>KAS.</span></h1>

            <p>Catatan keuangan PB Alfa BPS Sulawesi Barat.</p>
        </div>

        <span class="cash-hero__decoration" aria-hidden="true">
            Rp
        </span>
    </header>

    {{-- Ringkasan --}}

    <section class="cash-summary">

        <div class="cash-summary__card cash-summary__card--balance">
            <span>TOTAL SALDO KAS</span>

            <strong class="{{ $balance < 0 ? 'is-negative' : '' }}">
                Rp {{ number_format($balance, 0, ',', '.') }}
            </strong>
        </div>

        <div class="cash-summary__card cash-summary__card--income">
            <span>TOTAL PEMASUKAN</span>

            <strong>
                Rp {{ number_format($income, 0, ',', '.') }}
            </strong>
        </div>

        <div class="cash-summary__card cash-summary__card--expense">
            <span>TOTAL PENGELUARAN</span>

            <strong>
                Rp {{ number_format($expense, 0, ',', '.') }}
            </strong>
        </div>

    </section>

    {{-- Filter --}}

    <div class="cash-toolbar">

        <form id="cash-per-page-form" method="GET" action="{{ route('cash.index') }}" class="cash-filter">
            <label for="cash-per-page">Tampilkan</label>

            <select id="cash-per-page" name="per_page" aria-label="Jumlah transaksi per halaman">
                @foreach([25, 50, 100] as $size)
                <option value="{{ $size }}" @selected(($perPage ?? 25)==$size)>
                    {{ $size }} data
                </option>
                @endforeach
            </select>

            <span>per halaman</span>

            <noscript>
                <button type="submit">Terapkan</button>
            </noscript>
        </form>

        @if($canManage)
        <a href="{{ route('cash.create') }}" class="cash-add">
            <span aria-hidden="true">+</span>
            Tambah Transaksi
        </a>
        @endif

    </div>

    {{-- Daftar --}}

    <section class="cash-board">

        <div class="cash-board__heading">
            <div>
                <span class="cash-eyebrow cash-eyebrow--green">
                    OFFICIAL CASH RECORDS
                </span>

                <h2>Riwayat Transaksi</h2>
            </div>

            <span class="cash-board__count">
                {{ $transactions->total() }} transaksi
            </span>
        </div>

        <div class="cash-table" role="table" aria-label="Riwayat transaksi kas">

            <div role="row" class="cash-row cash-row--head {{ $canManage ? 'cash-row--manage' : '' }}">
                <div role="columnheader">No.</div>
                <div role="columnheader">Tanggal</div>
                <div role="columnheader">Keterangan</div>
                <div role="columnheader" class="cash-align-right">
                    Nominal
                </div>

                @if($canManage)
                <div role="columnheader" class="cash-align-center">
                    Aksi
                </div>
                @endif
            </div>

            @forelse($transactions as $transaction)

            <div role="row" class="cash-row cash-row--data {{ $canManage ? 'cash-row--manage' : '' }}">

                <div role="cell" class="cash-row__number">
                    {{ $transactions->firstItem() + $loop->index }}
                </div>

                <div role="cell" class="cash-row__date">
                    {{ \Illuminate\Support\Carbon::parse(
                    $transaction->transaction_date
                    )->format('d/m/Y') }}
                </div>

                <div role="cell" class="cash-row__description">{{ trim($transaction->description) }}</div>


                {{-- Nominal dan jenis transaksi --}}

                <div role="cell" class="cash-row__amount cash-row__amount--{{ $transaction->type }}">
                    <strong>
                        Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                    </strong>

                    <span>
                        {{ ucfirst($transaction->type) }}
                    </span>
                </div>


                {{-- Aksi --}}

                @if($canManage)
                <div role="cell" class="cash-row__actions">

                    <a href="{{ route('cash.edit', $transaction) }}" class="cash-action cash-action--edit"
                        title="Edit transaksi" aria-label="Edit transaksi nomor {{ $transaction->id }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 20h9" />
                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L9 17l-4 1 1-4Z" />
                        </svg>
                    </a>

                    <form method="POST" action="{{ route('cash.destroy', $transaction) }}" data-single-submit
                        data-confirm="Hapus transaksi ini?">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="cash-action cash-action--delete" title="Hapus transaksi"
                            aria-label="Hapus transaksi nomor {{ $transaction->id }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 6h18" />
                                <path d="M8 6V4h8v2" />
                                <path d="M5 6l1 15h12l1-15" />
                                <path d="M10 10v7M14 10v7" />
                            </svg>
                        </button>
                    </form>

                </div>
                @endif

            </div>

            @empty

            <div class="cash-empty">
                <strong>Belum ada transaksi kas.</strong>
            </div>

            @endforelse

        </div>

        {{-- Pagination --}}

        @if($transactions->hasPages())

        <nav class="cash-pagination" aria-label="Halaman transaksi">

            @if($transactions->onFirstPage())
            <span class="is-disabled">← Sebelumnya</span>
            @else
            <a href="{{ $transactions->previousPageUrl() }}">
                ← Sebelumnya
            </a>
            @endif

            <span class="cash-pagination__info">
                {{ $transactions->currentPage() }}
                / {{ $transactions->lastPage() }}
            </span>

            @if($transactions->hasMorePages())
            <a href="{{ $transactions->nextPageUrl() }}">
                Berikutnya →
            </a>
            @else
            <span class="is-disabled">Berikutnya →</span>
            @endif

        </nav>

        @endif

    </section>

</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('cash-per-page');

    if (select) {
        select.addEventListener('change', function () {
            select.form.requestSubmit();
        });
    }
});
</script>
@endpush