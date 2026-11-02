@extends('layouts.admin')

@section('title', 'Detail Transaksi - Admin PB Alfa')
@section('page_title', 'Detail Transaksi')

@section('content')

@php
$isAuto = $transaction->shuttlecock_player_id !== null;

$isIncome = $transaction->type === 'pemasukan';

$needsReview = $isAuto
&& $transaction->shuttlecock_review_required;
@endphp

<div class="adm-page adm-ca-page">

    <div class="adm-page__heading">

        <div>
            <span class="adm-eyebrow">
                FINANCIAL MANAGEMENT / DETAIL
            </span>

            <h2>Detail Transaksi</h2>

            <p>
                Informasi lengkap transaksi kas
                nomor #{{ $transaction->id }}.
            </p>
        </div>

        <div class="adm-player-page-actions">

            <a href="{{ route('admin.cash.index') }}" class="adm-btn adm-btn--secondary">
                Kembali
            </a>

            <a href="{{ route('admin.cash.edit', $transaction) }}" class="adm-btn adm-btn--primary">
                Edit Transaksi
            </a>

        </div>

    </div>


    {{-- NOMINAL UTAMA --}}

    <section class="adm-ca-detail-hero">

        <div>
            <span>
                {{ $isIncome
                ? 'TOTAL PEMASUKAN'
                : 'TOTAL PENGELUARAN' }}
            </span>

            <strong>
                Rp {{ number_format(
                $transaction->amount,
                0, ',', '.'
                ) }}
            </strong>

            <p>
                {{ $transaction->description }}
            </p>
        </div>

        <span class="adm-ca-detail-hero__type">
            {{ ucfirst($transaction->type) }}
        </span>

    </section>


    @if($needsReview)

    <div class="adm-ca-review-alert">

        <div>
            <strong>Nominal Kok Perlu Ditinjau</strong>

            <p>
                Jumlah kok pada catatan ini berubah
                setelah nominal sebelumnya diisi.
                Periksa nominal pada halaman Edit.
            </p>
        </div>

        <a href="{{ route('admin.cash.edit', $transaction) }}" class="adm-btn adm-btn--secondary">
            Periksa Nominal
        </a>

    </div>

    @endif


    <div class="adm-ca-detail-grid">

        <section class="adm-card">

            <div class="adm-ca-section-head">
                <h3>Informasi Transaksi</h3>
                <p>Data utama catatan keuangan.</p>
            </div>

            <dl class="adm-ca-detail-list">

                <div>
                    <dt>ID Transaksi</dt>
                    <dd>#{{ $transaction->id }}</dd>
                </div>

                <div>
                    <dt>Tanggal</dt>
                    <dd>
                        {{ $transaction->transaction_date?->translatedFormat('d F Y') }}
                    </dd>
                </div>

                <div>
                    <dt>Jenis</dt>
                    <dd>{{ ucfirst($transaction->type) }}</dd>
                </div>

                <div>
                    <dt>Nominal</dt>
                    <dd>
                        Rp {{ number_format(
                        $transaction->amount,
                        0, ',', '.'
                        ) }}
                    </dd>
                </div>

                <div>
                    <dt>Sumber</dt>
                    <dd>
                        {{ $isAuto
                        ? 'Pencatatan Kok Otomatis'
                        : 'Transaksi Manual' }}
                    </dd>
                </div>

                <div>
                    <dt>Keterangan</dt>
                    <dd>{{ $transaction->description }}</dd>
                </div>

            </dl>

        </section>


        <section class="adm-card">

            <div class="adm-ca-section-head">
                <h3>
                    {{ $isAuto
                    ? 'Informasi Pemakaian Kok'
                    : 'Riwayat Pencatatan' }}
                </h3>

                <p>
                    {{ $isAuto
                    ? 'Data kok yang dihasilkan sistem.'
                    : 'Informasi pencatatan dan perubahan.' }}
                </p>
            </div>

            <dl class="adm-ca-detail-list">

                @if($isAuto)

                <div>
                    <dt>Pemain</dt>
                    <dd>
                        {{ $player?->name
                        ?? 'Pemain tidak ditemukan' }}
                    </dd>
                </div>

                <div>
                    <dt>Jumlah Kok</dt>
                    <dd>
                        {{ number_format(
                        $transaction->shuttlecock_count ?? 0,
                        0, ',', '.'
                        ) }} kok
                    </dd>
                </div>

                <div>
                    <dt>Review Nominal</dt>
                    <dd>
                        {{ $needsReview
                        ? 'Perlu Ditinjau'
                        : 'Tidak Ada Review Tertunda' }}
                    </dd>
                </div>

                @endif

                <div>
                    <dt>Dibuat Oleh</dt>
                    <dd>
                        {{ $transaction->creator?->name
                        ?? 'Tidak diketahui' }}
                    </dd>
                </div>

                <div>
                    <dt>Dibuat Pada</dt>
                    <dd>
                        {{ $transaction->created_at?->format('d/m/Y H:i') }}
                    </dd>
                </div>

                <div>
                    <dt>Terakhir Diubah Oleh</dt>
                    <dd>
                        {{ $transaction->updater?->name
                        ?? 'Belum ada informasi' }}
                    </dd>
                </div>

                <div>
                    <dt>Terakhir Diperbarui</dt>
                    <dd>
                        {{ $transaction->updated_at?->format('d/m/Y H:i') }}
                    </dd>
                </div>

            </dl>

        </section>

    </div>


    @if($isAuto)

    <div class="adm-ca-info-note">
        <strong>Transaksi Kok Otomatis</strong>

        <p>
            Catatan ini dibuat dan disinkronkan oleh
            sistem berdasarkan pertandingan yang valid.
            Jumlah kok dan tanggal mengikuti data pertandingan.
            Admin hanya boleh memperbaiki nominalnya.
        </p>
    </div>

    @endif

</div>

@endsection