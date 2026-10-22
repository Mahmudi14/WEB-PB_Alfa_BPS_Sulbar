@extends('layouts.app')

@section('title', 'Masuk - PB Alfa BPS Sulbar')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/login-premium.css') }}?v=3">
@endpush

@section('content')

<section class="pb-login" aria-labelledby="login-title">

    {{-- Panel Informasi Klub --}}
    <aside class="pb-login__story">

        <div class="pb-login__glow pb-login__glow--one"
             aria-hidden="true"></div>

        <div class="pb-login__glow pb-login__glow--two"
             aria-hidden="true"></div>

        <div class="pb-login__court" aria-hidden="true">
            <span class="pb-login__court-line pb-login__court-line--top"></span>
            <span class="pb-login__court-line pb-login__court-line--bottom"></span>
            <span class="pb-login__court-line pb-login__court-line--middle"></span>
        </div>

        <div class="pb-login__story-content">

            <span class="pb-login__eyebrow">
                <span></span>
                OFFICIAL CLUB PORTAL
            </span>

            <h2>
                Lebih dari<br>
                sekadar <em>pertandingan.</em>
            </h2>

            <p>
                Satu tempat untuk mencatat pertandingan,
                mengikuti perkembangan pemain, dan menjaga
                kebersamaan PB Alfa.
            </p>

            <div class="pb-login__features"
                 aria-label="Fitur PB Alfa">

                <span>Riwayat Pertandingan</span>
                <span>Ranking Pemain</span>
                <span>Kas Klub</span>

            </div>

        </div>

        <div class="pb-login__story-footer">
            <span>SPORTIVITAS</span>
            <i aria-hidden="true"></i>
            <span>PRESTASI</span>
            <i aria-hidden="true"></i>
            <span>KEBERSAMAAN</span>
        </div>

    </aside>

    {{-- Panel Login --}}
    <div class="pb-login__panel">

        <div class="pb-login__form-container">

            <span class="pb-login__form-kicker">
                AREA ANGGOTA
            </span>

            <h1 id="login-title">
                Selamat datang<span class="pb-login__period">.</span>
            </h1>

            <p class="pb-login__intro">
                Masuk untuk mengakses akun dan mengelola
                aktivitas klub.
            </p>

            {{-- Pesan Kesalahan --}}
            @if($errors->any())

                <div class="pb-login__error"
                     id="login-error"
                     role="alert">

                    <svg viewBox="0 0 24 24"
                         width="19"
                         height="19"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         aria-hidden="true">

                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M12 7v6M12 17h.01"></path>

                    </svg>

                    <span>{{ $errors->first() }}</span>

                </div>

            @endif

            {{-- Form Login --}}
            <form
                method="POST"
                action="{{ route('login.store') }}"
                class="pb-login__form"
                data-single-submit
            >

                @csrf

                {{-- Email --}}
                <div class="pb-login__field">

                    <label for="email">
                        Alamat email
                    </label>

                    <div class="pb-login__input-wrap">

                        <svg
                            class="pb-login__input-icon"
                            viewBox="0 0 24 24"
                            width="20"
                            height="20"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <rect x="3" y="5"
                                  width="18" height="14"
                                  rx="2"></rect>

                            <path d="m4 7 8 6 8-6"></path>
                        </svg>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nama@email.com"
                            autocomplete="username"
                            @if($errors->has('email'))
                                aria-invalid="true"
                                aria-describedby="login-error"
                            @endif
                            required
                            autofocus
                        >

                    </div>

                </div>

                {{-- Password --}}
                <div class="pb-login__field">

                    <label for="password">
                        Kata sandi
                    </label>

                    <div class="pb-login__input-wrap">

                        <svg
                            class="pb-login__input-icon"
                            viewBox="0 0 24 24"
                            width="20"
                            height="20"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <rect x="4" y="10"
                                  width="16" height="11"
                                  rx="2"></rect>

                            <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                        </svg>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Masukkan kata sandi"
                            autocomplete="current-password"
                            @if($errors->has('password'))
                                aria-invalid="true"
                                aria-describedby="login-error"
                            @endif
                            required
                        >

                    </div>

                </div>

                {{-- Tombol Login --}}
                <button
                    type="submit"
                    class="pb-login__submit"
                >

                    <span>Masuk ke PB Alfa</span>

                    <svg
                        viewBox="0 0 24 24"
                        width="19"
                        height="19"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M5 12h14M13 6l6 6-6 6"></path>
                    </svg>

                </button>

            </form>

            {{-- Informasi Akun --}}
            <div class="pb-login__notice">

                <svg
                    viewBox="0 0 24 24"
                    width="17"
                    height="17"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M12 11v5M12 8h.01"></path>
                </svg>

                <span>
                    Akun anggota disediakan oleh Admin
                    atau Pengelola PB Alfa.
                </span>

            </div>

            {{-- Kembali --}}
            <a
                href="{{ route('home') }}"
                class="pb-login__back"
            >
                <span aria-hidden="true">←</span>
                Kembali ke beranda
            </a>

        </div>

        <p class="pb-login__panel-foot">
            © {{ date('Y') }} PB Alfa · BPS Sulawesi Barat
        </p>

    </div>

</section>

@endsection