@extends('layouts.app')

@section('title', 'Profil Saya - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/profile.css') }}?v=1">
@endpush

@section('content')

<div class="profile-page">

    <header class="profile-hero">
        <div>
            <span class="profile-eyebrow">
                PB ALFA / MY ACCOUNT
            </span>

            <h1>PROFIL <span>SAYA.</span></h1>

            <p>Kelola identitas dan keamanan akun PB Alfa.</p>
        </div>
    </header>

    {{-- Informasi akun --}}

    <section class="profile-card">

        <div class="profile-identity">

            <div class="profile-avatar">
                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
            </div>

            <div class="profile-identity__info">
                <strong>{{ $user->name }}</strong>
                <span>{{ $user->email }}</span>
            </div>

            <span class="profile-role">
                {{ ucfirst($user->role) }}
            </span>

        </div>

    </section>

    @if($mustChange)
        <div class="profile-warning" role="status">
            Anda menggunakan password sementara.
            Ganti password terlebih dahulu untuk melanjutkan.
        </div>
    @endif

    {{-- Ubah nama --}}

    @unless($mustChange)
        <section class="profile-card">

            <div class="profile-card__heading">
                <h2>Informasi Profil</h2>
            </div>

            <form
                method="POST"
                action="{{ route('profile.name.update') }}"
                class="profile-form"
                data-single-submit
            >
                @csrf
                @method('PATCH')

                <div class="profile-grid">

                    <div class="profile-field">
                        <label for="profile-name">Nama Lengkap</label>

                        <input
                            id="profile-name"
                            type="text"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            maxlength="255"
                            autocomplete="name"
                            required
                        >

                        @error('name')
                            <small class="profile-error">
                                {{ $message }}
                            </small>
                        @enderror
                    </div>

                    <div class="profile-field">
                        <label for="profile-email">Email</label>

                        <input
                            id="profile-email"
                            type="email"
                            value="{{ $user->email }}"
                            readonly
                        >
                    </div>

                </div>

                <div class="profile-actions">
                    <button
                        type="submit"
                        class="profile-button"
                    >
                        Simpan Nama
                    </button>
                </div>

            </form>

        </section>
    @endunless

    {{-- Ganti password --}}

    <section class="profile-card">

        <div class="profile-card__heading">
            <h2>
                {{ $mustChange
                    ? 'Buat Password Baru'
                    : 'Ganti Password' }}
            </h2>
        </div>

        <form
            method="POST"
            action="{{ route('profile.password.update') }}"
            class="profile-form"
            data-single-submit
        >
            @csrf
            @method('PUT')

            <div class="profile-grid">

                <div class="profile-field">
                    <label for="current_password">
                        {{ $mustChange
                            ? 'Password Sementara'
                            : 'Password Saat Ini' }}
                    </label>

                    <input
                        id="current_password"
                        type="password"
                        name="current_password"
                        autocomplete="current-password"
                        required
                    >

                    @error('current_password')
                        <small class="profile-error">
                            {{ $message }}
                        </small>
                    @enderror
                </div>

                <div class="profile-field">
                    <label for="password">Password Baru</label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        minlength="12"
                        autocomplete="new-password"
                        required
                    >

                    @error('password')
                        <small class="profile-error">
                            {{ $message }}
                        </small>
                    @enderror
                </div>

                <div class="profile-field">
                    <label for="password_confirmation">
                        Konfirmasi Password Baru
                    </label>

                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        minlength="12"
                        autocomplete="new-password"
                        required
                    >
                </div>

            </div>

            <div class="profile-actions">
                <button
                    type="submit"
                    class="profile-button"
                >
                    Perbarui Password
                </button>
            </div>

        </form>

    </section>

</div>

@endsection