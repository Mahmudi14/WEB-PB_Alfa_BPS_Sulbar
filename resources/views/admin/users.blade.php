@extends('layouts.app')

@section('title', 'Kelola Pengguna - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}?v=3">
@endpush

@section('content')

@php
    $resetCredentials = session()->pull('reset_credentials');
@endphp

<div class="adm-page">

    <header class="adm-hero">
        <div>
            <span class="adm-eyebrow">
                PB ALFA / USER MANAGEMENT
            </span>

            <h1>KELOLA <span>PENGGUNA.</span></h1>

            <p>Pengaturan role dan keamanan akun pemain.</p>
        </div>

        <span class="adm-hero__decoration" aria-hidden="true">
            04
        </span>
    </header>

    {{-- Password sementara --}}

    @if($resetCredentials)
        <section class="adm-reset-result" role="status">

            <h2>Password Berhasil Direset</h2>

            <p>
                Akun:
                <strong>{{ $resetCredentials['name'] }}</strong>
                ({{ $resetCredentials['email'] }})
            </p>

            <label for="temporary-password">
                Password Sementara
            </label>

            <input
                id="temporary-password"
                type="text"
                value="{{ $resetCredentials['password'] }}"
                readonly
                autocomplete="off"
                spellcheck="false"
                onclick="this.select()"
            >

            <small>
                Salin password sekarang. Password ini hanya
                ditampilkan satu kali dan wajib diganti
                oleh pengguna saat login.
            </small>

        </section>
    @endif

    {{-- Daftar --}}

    <section class="adm-section">

        <div class="adm-section__heading">
            <div>
                <span class="adm-eyebrow adm-eyebrow--green">
                    USER ACCESS CONTROL
                </span>

                <h2>Daftar Pengguna</h2>
            </div>

            <span class="adm-count">
                {{ $users->total() }} pengguna
            </span>
        </div>

        @if($errors->any())
            <div class="adm-alert" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="adm-user-list">

            @forelse($users as $user)

                <article class="adm-user-row">

                    <div class="adm-user-row__identity">

                        <span class="adm-user-avatar"
                              aria-hidden="true">
                            {{ mb_strtoupper(
                                mb_substr($user->name, 0, 1)
                            ) }}
                        </span>

                        <div class="adm-user-row__info">
                            <strong>{{ $user->name }}</strong>
                            <span>{{ $user->email }}</span>
                        </div>

                    </div>

                    <div class="adm-user-controls">

                        {{-- Ubah role --}}

                        <form
                            method="POST"
                            action="{{ route('admin.users.update', $user) }}"
                            class="adm-role-form"
                            data-single-submit
                        >
                            @csrf
                            @method('PATCH')

                            <div class="adm-role-form__field">
                                <label for="role-{{ $user->id }}">
                                    Hak Akses
                                </label>

                                <select
                                    id="role-{{ $user->id }}"
                                    name="role"
                                    required
                                >
                                    <option value="pemain"
                                        @selected($user->role === 'pemain')>
                                        Pemain
                                    </option>

                                    <option value="pengelola"
                                        @selected($user->role === 'pengelola')>
                                        Pengelola
                                    </option>
                                </select>
                            </div>

                            <button
                                type="submit"
                                class="adm-button adm-button--primary"
                            >
                                Simpan Role
                            </button>
                        </form>

                        {{-- Reset password --}}

                        <form
                            method="POST"
                            action="{{ route('admin.users.reset-password', $user) }}"
                            class="adm-user-reset-form"
                            data-single-submit
                            data-confirm="Reset password akun {{ $user->name }}?"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="adm-button adm-button--danger"
                            >
                                Reset Password
                            </button>
                        </form>

                    </div>

                </article>

            @empty

                <div class="adm-empty">
                    Belum ada pemain yang memiliki akun login.
                </div>

            @endforelse

        </div>

        {{-- Pagination --}}

        @if($users->hasPages())
            <nav class="adm-pagination"
                 aria-label="Halaman pengguna">

                @if($users->onFirstPage())
                    <span class="is-disabled">
                        ← Sebelumnya
                    </span>
                @else
                    <a href="{{ $users->previousPageUrl() }}">
                        ← Sebelumnya
                    </a>
                @endif

                <span class="adm-pagination__info">
                    {{ $users->currentPage() }}
                    / {{ $users->lastPage() }}
                </span>

                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}">
                        Berikutnya →
                    </a>
                @else
                    <span class="is-disabled">
                        Berikutnya →
                    </span>
                @endif

            </nav>
        @endif

    </section>

</div>

@endsection