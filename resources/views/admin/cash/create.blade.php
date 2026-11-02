@extends('layouts.admin')

@section('title', 'Tambah Transaksi - Admin PB Alfa')
@section('page_title', 'Tambah Transaksi')

@section('content')

<div class="adm-page adm-ca-page">

    <div class="adm-page__heading">
        <div>
            <span class="adm-eyebrow">
                FINANCIAL MANAGEMENT / CREATE
            </span>

            <h2>Tambah Transaksi</h2>

            <p>
                Catat pemasukan atau pengeluaran kas klub.
            </p>
        </div>

        <a href="{{ route('admin.cash.index') }}" class="adm-btn adm-btn--secondary">
            Kembali
        </a>
    </div>


    <form method="POST" action="{{ route('admin.cash.store') }}" data-single-submit>
        @csrf

        <section class="adm-card adm-ca-form-card">

            <div class="adm-ca-section-head">
                <h3>Informasi Transaksi</h3>

                <p>
                    Lengkapi rincian transaksi kas baru.
                </p>
            </div>


            <div class="adm-ca-form-grid">

                <div class="adm-field">

                    <label for="ca-date">
                        Tanggal Transaksi *
                    </label>

                    <input id="ca-date" type="date" name="transaction_date" class="adm-input" value="{{ old(
                            'transaction_date',
                            now()->toDateString()
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

                        <input id="ca-amount" type="number" name="amount" value="{{ old('amount') }}" placeholder="0"
                            min="1" max="999999999999" step="1" inputmode="numeric" required>

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

                            <input type="radio" name="type" value="pemasukan" @checked( old('type', 'pemasukan'
                                )==='pemasukan' ) required>

                            <span>
                                <strong>Pemasukan</strong>
                                <small>
                                    Uang masuk ke kas klub
                                </small>
                            </span>

                        </label>

                        <label class="adm-ca-type-choice">

                            <input type="radio" name="type" value="pengeluaran" @checked( old('type', 'pemasukan'
                                )==='pengeluaran' )>

                            <span>
                                <strong>Pengeluaran</strong>
                                <small>
                                    Uang keluar dari kas klub
                                </small>
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
                        placeholder="Tuliskan keterangan transaksi..." required>{{ old('description') }}</textarea>

                    <small class="adm-field-help">
                        Tuliskan keterangan yang jelas
                        agar transaksi mudah diperiksa.
                    </small>

                    @error('description')
                    <small class="adm-field-error">
                        {{ $message }}
                    </small>
                    @enderror

                </div>

            </div>


            <div class="adm-ca-info-note">

                <strong>Transaksi Manual</strong>

                <p>
                    Form ini digunakan untuk mencatat
                    pemasukan dan pengeluaran secara manual.
                    Pencatatan pemakaian kok otomatis
                    tetap dibuat melalui sistem pertandingan.
                </p>

            </div>


            <div class="adm-ca-form-actions">

                <a href="{{ route('admin.cash.index') }}" class="adm-btn adm-btn--secondary">
                    Batal
                </a>

                <button type="submit" class="adm-btn adm-btn--primary">
                    Simpan Transaksi
                </button>

            </div>

        </section>

    </form>

</div>

@endsection