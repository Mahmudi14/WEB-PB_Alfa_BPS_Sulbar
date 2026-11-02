@extends('layouts.admin')

@section('title', 'Tambah Pemain - Admin PB Alfa')
@section('page_title', 'Tambah Pemain')

@section('content')

<div class="adm-page adm-player-form-page adm-player-full">

    <div class="adm-page__heading">

        <div>
            <span class="adm-eyebrow">
                PLAYER MANAGEMENT / CREATE
            </span>

            <h2>Tambah Pemain</h2>

            <p>
                Lengkapi data pemain baru yang akan
                didaftarkan ke PB Alfa.
            </p>
        </div>

        <a href="{{ route('admin.players.index') }}" class="adm-btn adm-btn--secondary">
            Kembali
        </a>

    </div>


    <section class="adm-card adm-player-form-card">

        <div class="adm-player-section-title">
            <h3>Informasi Pemain</h3>
            <p>
                Kolom bertanda * wajib diisi.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.players.store') }}" data-single-submit>

            @csrf

            <div class="adm-player-form-grid">

                <div class="adm-field">

                    <label for="player-name">
                        Nama Lengkap *
                    </label>

                    <input id="player-name" type="text" name="name" class="adm-input" value="{{ old('name') }}"
                        placeholder="Masukkan nama lengkap" maxlength="255" autocomplete="name" required autofocus>

                    @error('name')
                    <small class="adm-field-error">
                        {{ $message }}
                    </small>
                    @enderror

                </div>


                <div class="adm-field">

                    <label for="player-email">
                        Email
                    </label>

                    <input id="player-email" type="email" name="email" class="adm-input" value="{{ old('email') }}"
                        placeholder="contoh@email.com" maxlength="255" autocomplete="email">

                    <small class="adm-field-help">
                        Opsional. Email diperlukan jika pemain
                        ingin memiliki akun login.
                    </small>

                    @error('email')
                    <small class="adm-field-error">
                        {{ $message }}
                    </small>
                    @enderror

                </div>


                <div class="adm-field">

                    <label for="player-gender">
                        Jenis Kelamin *
                    </label>

                    <div class="adm-select-shell">

                        <select id="player-gender" name="gender" class="adm-select adm-premium-select"
                            data-admin-premium-select required>
                            <option value="L" @selected(old('gender', 'L' )==='L' )>
                                Laki-laki
                            </option>

                            <option value="P" @selected(old('gender')==='P' )>
                                Perempuan
                            </option>
                        </select>

                    </div>

                    @error('gender')
                    <small class="adm-field-error">
                        {{ $message }}
                    </small>
                    @enderror

                </div>


                <div class="adm-field">

                    <label for="player-score">
                        Skor Awal *
                    </label>

                    <input id="player-score" type="number" name="initial_score" class="adm-input"
                        value="{{ old('initial_score', 0) }}" min="-999999" max="999999" step="1" inputmode="numeric"
                        required>

                    <small class="adm-field-help">
                        Skor dasar sebelum hasil pertandingan
                        diperhitungkan.
                    </small>

                    @error('initial_score')
                    <small class="adm-field-error">
                        {{ $message }}
                    </small>
                    @enderror

                </div>

            </div>


            <div class="adm-player-info">

                <strong>Informasi Akun Login</strong>

                <p>
                    Jika email diisi, sistem membuat akun
                    login dengan role Pemain dan password
                    default. Role dapat diubah pada halaman
                    Edit setelah pemain tersimpan.
                </p>

            </div>


            <div class="adm-player-form-actions">

                <a href="{{ route('admin.players.index') }}" class="adm-btn adm-btn--secondary">
                    Batal
                </a>

                <button type="submit" class="adm-btn adm-btn--primary">
                    Simpan Pemain
                </button>

            </div>

        </form>

    </section>

</div>

@endsection