@extends('layouts.admin')

@section('title', 'Edit Pemain - Admin PB Alfa')
@section('page_title', 'Edit Pemain')

@section('content')

@php
$user = $player->user;

$isProtected = $user && (
$user->role === 'admin' ||
(int) $user->id === (int) auth()->id()
);

$canManageAccount = $user
&& !$isProtected
&& in_array(
$user->role,
['pemain', 'pengelola'],
true
);

$roleLabel = match ($user?->role) {
'admin' => 'Administrator',
'pengelola' => 'Pengelola',
'pemain' => 'Pemain',
default => 'Tanpa Akun',
};
@endphp

<div class="adm-page adm-player-form-page adm-player-full adm-player-editor">

    <div class="adm-page__heading">

        <div>
            <span class="adm-eyebrow">
                PLAYER MANAGEMENT / EDIT
            </span>

            <h2>Edit Pemain</h2>

            <p>
                Perbarui informasi dan pengaturan akun pemain.
            </p>
        </div>

        <div class="adm-player-page-actions">

            <a href="{{ route('admin.players.index') }}" class="adm-btn adm-btn--secondary">
                Kembali
            </a>

            <a href="{{ route('admin.players.show', $player->id) }}" class="adm-btn adm-btn--primary">
                Lihat Detail
            </a>

        </div>

    </div>


    {{-- IDENTITAS PEMAIN --}}

    <div class="adm-pe-hero">

        <div class="adm-pe-hero__identity">

            <div class="adm-pe-hero__avatar">
                {{ mb_strtoupper(
                mb_substr($player->name, 0, 1)
                ) }}
            </div>

            <div class="adm-pe-hero__info">

                <span>PROFIL PEMAIN</span>

                <h3>{{ $player->name }}</h3>

                <p>
                    {{ $player->email ?: 'Belum memiliki email' }}
                </p>

            </div>

        </div>

        <div class="adm-pe-hero__badges">

            <span class="adm-pe-badge adm-pe-badge--role">
                {{ $roleLabel }}
            </span>

            <span class="adm-pe-badge adm-pe-badge--active">
                <span></span>
                Pemain Aktif
            </span>

        </div>

    </div>


    <div class="adm-pe-layout">

        {{-- KOLOM KIRI: INFORMASI PEMAIN --}}

        <section class="adm-card adm-pe-card">

            <div class="adm-pe-card__header">

                <div class="adm-pe-card__icon">

                    @include(
                    'admin.partials.nav-icon',
                    ['name' => 'users']
                    )

                </div>

                <div>

                    <h3>Informasi Pemain</h3>

                    <p>
                        Kelola identitas dan skor awal pemain.
                    </p>

                </div>

            </div>


            <form method="POST" action="{{ route('admin.players.update', $player->id) }}" data-single-submit>

                @csrf
                @method('PUT')

                <div class="adm-pe-card__body">

                    <div class="adm-pe-form-grid">

                        {{-- NAMA --}}

                        <div class="adm-field">

                            <label for="player-name">
                                Nama Lengkap
                                <span class="adm-pe-required">*</span>
                            </label>

                            <input id="player-name" type="text" name="name" class="adm-input"
                                value="{{ old('name', $player->name) }}" placeholder="Masukkan nama pemain"
                                maxlength="255" autocomplete="name" required autofocus>

                            @error('name')
                            <small class="adm-field-error">
                                {{ $message }}
                            </small>
                            @enderror

                        </div>


                        {{-- EMAIL --}}

                        <div class="adm-field">

                            <label for="player-email">
                                Email

                                @if($user?->role === 'admin')
                                <span class="adm-pe-required">*</span>
                                @endif
                            </label>

                            <input id="player-email" type="email" name="email" class="adm-input"
                                value="{{ old('email', $player->email) }}" placeholder="nama@email.com" maxlength="255"
                                autocomplete="email" @if($user?->role === 'admin') required @endif
                            >

                            @error('email')
                            <small class="adm-field-error">
                                {{ $message }}
                            </small>
                            @enderror

                        </div>


                        {{-- JENIS KELAMIN --}}

                        <div class="adm-field">

                            <label for="player-gender">
                                Jenis Kelamin
                                <span class="adm-pe-required">*</span>
                            </label>

                            <div class="adm-select-shell">

                                <select id="player-gender" name="gender" class="adm-select adm-premium-select"
                                    data-admin-premium-select required>

                                    <option value="L" @selected( old('gender', $player->gender) === 'L'
                                        )
                                        >
                                        Laki-laki
                                    </option>

                                    <option value="P" @selected( old('gender', $player->gender) === 'P'
                                        )
                                        >
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


                        {{-- SKOR AWAL --}}

                        <div class="adm-field">

                            <label for="player-score">
                                Skor Awal
                                <span class="adm-pe-required">*</span>
                            </label>

                            <input id="player-score" type="number" name="initial_score" class="adm-input"
                                value="{{ old('initial_score', $player->initial_score) }}" min="-999999" max="999999"
                                step="1" inputmode="numeric" required>

                            <small class="adm-pe-field-hint">
                                Skor dasar untuk perhitungan ranking.
                            </small>

                            @error('initial_score')
                            <small class="adm-field-error">
                                {{ $message }}
                            </small>
                            @enderror

                        </div>

                    </div>


                    <div class="adm-pe-note">

                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 11v5" />
                            <path d="M12 8h.01" />
                        </svg>

                        <p>
                            Perubahan profil tidak akan mereset
                            password atau mengubah role akun.
                            Mengosongkan email akan menonaktifkan
                            akses login akun yang terhubung.
                        </p>

                    </div>

                </div>


                <div class="adm-pe-card__footer">

                    <a href="{{ route('admin.players.show', $player->id) }}" class="adm-btn adm-btn--secondary">
                        Batal
                    </a>

                    <button type="submit" class="adm-btn adm-btn--primary">
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </section>


        {{-- KOLOM KANAN: PENGATURAN AKUN --}}

        <div class="adm-pe-aside">

            <section class="adm-card adm-pe-card">

                <div class="adm-pe-card__header">

                    <div class="adm-pe-card__icon">

                        @include(
                        'admin.partials.nav-icon',
                        ['name' => 'settings']
                        )

                    </div>

                    <div>

                        <h3>Pengaturan Akun</h3>

                        <p>
                            Kelola hak akses dan keamanan.
                        </p>

                    </div>

                </div>


                <div class="adm-pe-card__body">




                    @if($canManageAccount)

                    {{-- UBAH ROLE --}}

                    <div class="adm-pe-section">

                        <div class="adm-pe-section__heading">

                            <h4>Ubah Role Pengguna</h4>

                            <p>
                                Tentukan hak akses pemain
                                terhadap sistem PB Alfa.
                            </p>

                        </div>

                        <form method="POST" action="{{ route('admin.players.role', $player->id) }}" data-single-submit
                            data-confirm="Ubah role akun {{ $player->name }}?"
                            data-confirm-title="Konfirmasi Perubahan Role">

                            @csrf
                            @method('PATCH')

                            <div class="adm-field">

                                <label for="player-role">
                                    Role Pengguna
                                </label>

                                <div class="adm-select-shell">

                                    <select id="player-role" name="role" class="adm-select adm-premium-select"
                                        data-admin-premium-select required>

                                        <option value="pemain" @selected( old('role', $user->role) === 'pemain'
                                            )
                                            >
                                            Pemain
                                        </option>

                                        <option value="pengelola" @selected( old('role', $user->role) === 'pengelola'
                                            )
                                            >
                                            Pengelola
                                        </option>

                                    </select>

                                </div>

                            </div>

                            <p class="adm-pe-field-hint">
                                Pengelola yang baru dipromosikan
                                wajib mengganti password.
                            </p>

                            <button type="submit" class="adm-btn adm-btn--primary adm-pe-action-btn">
                                Simpan Role
                            </button>

                        </form>

                    </div>


                    {{-- RESET PASSWORD --}}

                    <div class="adm-pe-section">

                        <div class="adm-pe-section__heading">

                            <h4>Keamanan Akun</h4>

                            <p>
                                Reset password pengguna
                                ke password awal PB Alfa.
                            </p>

                        </div>

                        <div class="adm-pe-security">

                            <div class="adm-pe-security__info">

                                <strong>Reset Password</strong>

                                <span>
                                    Sesi login lama akan
                                    otomatis terputus.
                                </span>

                            </div>

                            <form method="POST" action="{{ route('admin.players.reset-password', $player->id) }}"
                                data-single-submit
                                data-confirm="Reset password {{ $player->name }} ke password default?"
                                data-confirm-title="Konfirmasi Reset Password">

                                @csrf

                                <button type="submit" class="adm-btn adm-btn--secondary adm-pe-action-btn">
                                    Reset Password
                                </button>

                            </form>

                        </div>

                    </div>

                    @elseif(!$user)

                    <div class="adm-pe-section">

                        <div class="adm-pe-notice">

                            <strong>Belum Memiliki Akun Login</strong>

                            <p>
                                Tambahkan email melalui
                                formulir profil untuk membuat
                                akun login pemain.
                            </p>

                        </div>

                    </div>

                    @else

                    <div class="adm-pe-section">

                        <div class="adm-pe-notice">

                            <strong>Akun Administrator Dilindungi</strong>

                            <p>
                                Role dan password administrator
                                tidak dapat diubah melalui
                                pengaturan akun pemain ini.
                            </p>

                        </div>

                    </div>

                    @endif

                </div>

            </section>

        </div>

    </div>

</div>

@endsection