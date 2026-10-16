
@extends('layouts.app')

@section('title', 'Login - PB Alfa')

@section('content')

<div class="auth-container">

    <div class="auth-heading">
        <h1>Selamat Datang</h1>

        <p>
            Masuk ke akun PB Alfa BPS Sulbar
            untuk mengelola pertandingan dan kegiatan.
        </p>
    </div>

    <div class="card auth-card">

        @if($errors->any())
            <div class="alert-error" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('login.store') }}"
            data-single-submit
        >
            @csrf

            <div class="field">
                <label for="email">
                    Email
                </label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Masukkan email"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="field">
                <label for="password">
                    Password
                </label>

                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Masukkan password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button
                type="submit"
                class="btn auth-submit"
            >
                Masuk ke Website
            </button>

        </form>

    </div>

    <p style="
        text-align:center;
        color:#64748b;
        font-size:12px;
        margin-top:20px;
    ">
        Akun dibuat oleh Admin atau Pengelola PB Alfa.
    </p>

</div>

@endsection
