@extends('layouts.app')

@section('title', 'Akun Pemain Baru - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/profile.css') }}?v=1">
@endpush

@section('content')

<div class="profile-page">

    <header class="profile-hero">
        <div>
            <span class="profile-eyebrow">
                PB ALFA / ACCOUNT CREATED
            </span>

            <h1>AKUN <span>BERHASIL.</span></h1>

            <p>Akun login pemain telah dibuat.</p>
        </div>
    </header>

    {{-- Informasi login --}}

    <section class="profile-card profile-credential">

        <h2>{{ $credentials['name'] }}</h2>

        <p>
            Email Login:
            <strong>{{ $credentials['email'] }}</strong>
        </p>

        <label for="new-player-password">
            Password Sementara
        </label>

        <input
            id="new-player-password"
            type="text"
            value="{{ $credentials['password'] }}"
            readonly
            autocomplete="off"
            spellcheck="false"
        >

        <small>
            Salin password ini sekarang dan sampaikan
            kepada pemain secara pribadi. Password hanya
            ditampilkan satu kali.
        </small>

        <a
            href="{{ route('players.index') }}"
            class="profile-button"
        >
            Kembali ke Daftar Pemain
        </a>

    </section>

</div>

@endsection