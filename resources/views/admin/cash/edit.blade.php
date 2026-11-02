@extends('layouts.admin')

@section('title', 'Edit Transaksi - Admin PB Alfa')
@section('page_title', 'Edit Transaksi')

@section('content')

@php
$isAuto =
$transaction->shuttlecock_player_id !== null;

$type = $isAuto
? 'pemasukan'
: old('type', $transaction->type);
@endphp

<div class="adm-page adm-ca-page">

    <div class="adm-page__heading">

        <div>
            <span class="adm-eyebrow">
                FINANCIAL MANAGEMENT / EDIT
            </span>

            <h2>Edit Transaksi</h2>

            <p>
                {{ $isAuto
                ? 'Koreksi nominal catatan kok otomatis.'
                : 'Perbarui rincian transaksi manual.' }}
            </p>
        </div>

        <div class="adm-player-page-actions">

            <a href="{{ route('admin.cash.index') }}" class="adm-btn adm-btn--secondary">
                Kembali
            </a>

            <a href="{{ route('admin.cash.show', $transaction) }}" class="adm-btn adm-btn--primary">
                Lihat Detail
            </a>

        </div>

    </div>


    <form method="POST" action="{{ route('admin.cash.update', $transaction) }}" data-single-submit>

        @csrf
        @method('PUT')

        <section class="adm-card adm-ca-form-card">

            <div class="adm-ca-section-head">

                <h3>
                    {{ $isAuto
                    ? 'Koreksi Catatan Kok'
                    : 'Informasi Transaksi' }}
                </h3>

                <p>
                    {{ $isAuto
                    ? 'Periksa jumlah kok dan isi nominal yang sesuai.'
                    : 'Perbarui data transaksi yang diperlukan.' }}
                </p>

            </div>


            @if($isAuto)

            {{-- DATA OTOMATIS TIDAK DAPAT DIUBAH --}}

            <div class="adm-ca-locked-grid">

                <div>
                    <span>Tanggal Transaksi</span>

                    <strong>
                        {{ $transaction->transaction_date?->translatedFormat('d F Y') }}
                    </strong>
                </div>

                <div>
                    <span>Jenis</span>
                    <strong>Pemasukan</strong>
                </div>

                <div>
                    <span>Jumlah Kok</span>

                    <strong>
                        {{ number_format(
                        $transaction->shuttlecock_count ?? 0,
                        0, ',', '.'
                        ) }} kok
                    </strong>
                </div>

                <div>
                    <span>Keterangan</span>

                    <strong>
                        {{ $transaction->description }}
                    </strong>
                </div>

            </div>


            @if($transaction->shuttlecock_review_required)

            <div class="adm-ca-review-alert">
                <div>
                    <strong>Perlu Koreksi Nominal</strong>

                    <p>
                        Jumlah kok berubah setelah
                        nominal sebelumnya diisi.
                        Pastikan nilai nominal sudah sesuai.
                    </p>
                </div>
            </div>

            @endif


            <div class="adm-ca-auto-amount">

                <div class="adm-field">

                    <label for="ca-amount">
                        Nominal Kok *
                    </label>

                    <div class="adm-ca-money-input">

                        <span>Rp</span>

                        <input id="ca-amount" type="number" name="amount" value="{{ old(
                                    'amount',
                                    $transaction->amount
                                ) }}" min="0" max="999999999999" step="1" inputmode="numeric" required>

                    </div>

                    <small class="adm-field-help">
                        Gunakan 0 jika memang belum ada
                        nominal yang dicatat.
                    </small>

                    @error('amount')
                    <small class="adm-field-error">
                        {{ $message }}
                    </small>
                    @enderror

                </div>

            </div>

            <div class="adm-ca-info-note">

                <strong>Aturan Catatan Kok Otomatis</strong>

                <p>
                    Tanggal, jumlah kok, dan keterangan
                    mengikuti data pertandingan. Saat disimpan,
                    tanda perlu review akan dihapus.
                    Jika kok dan nominal sama-sama nol,
                    catatan akan disembunyikan oleh sistem.
                </p>

            </div>

            @else

            {{-- FORM TRANSAKSI MANUAL --}}

            <div class="adm-ca-form-grid">

                <div class="adm-field">

                    <label for="ca-date">
                        Tanggal Transaksi *
                    </label>

                    <input id="ca-date" type="date" name="transaction_date" class="adm-input" value="{{ old(
                                'transaction_date',
                                $transaction->transaction_date?->format('Y-m-d')
                            ) }}" required>

                    @error('transaction_date')
                    <small class="adm-field-error">
                        {{ $message }}
                    </small>
                    @enderror

                </div>


                <div class="adm-field">

                    <label for="ca-amount">
                        Nominal Transaksi *
                    </label>

                    <div class="adm-ca-money-input">

                        <span>Rp</span>

                        <input id="ca-amount" type="number" name="amount" value="{{ old(
                                    'amount',
                                    $transaction->amount
                                ) }}" min="1" max="999999999999" step="1" inputmode="numeric" required>

                    </div>

                    @error('amount')
                    <small class="adm-field-error">
                        {{ $message }}
                    </small>
                    @enderror

                </div>


                <fieldset class="adm-ca-fieldset">

                    <legend>
                        Jenis Transaksi *
                    </legend>

                    <div class="adm-ca-type-grid">

                        <label class="adm-ca-type-choice">

                            <input type="radio" name="type" value="pemasukan" @checked($type==='pemasukan' ) required>

                            <span>
                                <strong>Pemasukan</strong>
                                <small>Uang masuk kas</small>
                            </span>

                        </label>

                        <label class="adm-ca-type-choice">

                            <input type="radio" name="type" value="pengeluaran" @checked($type==='pengeluaran' )>

                            <span>
                                <strong>Pengeluaran</strong>
                                <small>Uang keluar kas</small>
                            </span>

                        </label>

                    </div>

                    @error('type')
                    <small class="adm-field-error">
                        {{ $message }}
                    </small>
                    @enderror

                </fieldset>


                <div class="adm-field adm-ca-full-row">

                    <label for="ca-description">
                        Keterangan Transaksi *
                    </label>

                    <textarea id="ca-description" name="description" class="adm-textarea" rows="4" maxlength="1000"
                        required>{{ old(
                            'description',
                            $transaction->description
                        ) }}</textarea>

                    @error('description')
                    <small class="adm-field-error">
                        {{ $message }}
                    </small>
                    @enderror

                </div>

            </div>

            @endif


            <div class="adm-ca-form-actions">

                <a href="{{ route('admin.cash.show', $transaction) }}" class="adm-btn adm-btn--secondary">
                    Batal
                </a>

                <button type="submit" class="adm-btn adm-btn--primary">
                    Simpan Perubahan
                </button>

            </div>

        </section>

    </form>


    {{-- PENGHAPUSAN HANYA UNTUK TRANSAKSI MANUAL --}}

    @unless($isAuto)

    <section class="adm-card adm-ca-danger-zone">

        <div>

            <h3>Hapus Transaksi</h3>

            <p>
                Transaksi akan dihapus dari riwayat
                aktif dan perhitungan saldo kas.
                Data tetap tersimpan sebagai soft delete.
            </p>

        </div>

        <form method="POST" action="{{ route('admin.cash.destroy', $transaction) }}" data-single-submit
            data-confirm="Yakin ingin menghapus transaksi ini?" data-confirm-title="Hapus Transaksi"
            data-confirm-text="Ya, Hapus">

            @csrf
            @method('DELETE')

            <button type="submit" class="adm-btn adm-btn--danger">
                Hapus Transaksi
            </button>

        </form>

    </section>

    @endunless

</div>

@endsection