@extends('layouts.admin')

@section('title', 'Profil Saya - Admin PB Alfa')
@section('page_title', 'Profil Saya')

@section('content')

<div class="adm-page adm-ap-page">

    <div class="adm-page__heading">

        <div>
            <span class="adm-eyebrow">
                ADMINISTRATOR / MY PROFILE
            </span>

            <h2>Profil Saya</h2>

            <p>
                Kelola nama yang ditampilkan
                pada akun administrator PB Alfa.
            </p>
        </div>

        <a href="{{ route('admin.index') }}" class="adm-btn adm-btn--secondary">
            Kembali
        </a>

    </div>


    <section class="adm-card adm-ap-card">

        {{-- IDENTITAS ADMIN --}}

        <div class="adm-ap-identity">

            <div class="adm-ap-avatar">
                {{ mb_strtoupper(
                mb_substr($user->name, 0, 1)
                ) }}
            </div>

            <div class="adm-ap-identity__info">

                <span class="adm-ap-eyebrow">
                    ADMINISTRATOR PB ALFA
                </span>

                <h3>{{ $user->name }}</h3>

                <p>{{ $user->email }}</p>

            </div>

            <span class="adm-ap-role">
                Administrator
            </span>

        </div>


        {{-- FORM UBAH NAMA --}}

        <div class="adm-ap-content">

            <div class="adm-ap-section-heading">

                <h3>Informasi Profil</h3>

                <p>
                    Perbarui nama lengkap administrator.
                    Email dan hak akses akun tidak dapat
                    diubah melalui halaman ini.
                </p>

            </div>

            <form method="POST" action="{{ route('admin.profile.name.update') }}" data-single-submit>

                @csrf
                @method('PATCH')

                <div class="adm-ap-fields">

                    <div class="adm-field">

                        <label for="admin-profile-name">
                            Nama Lengkap
                            <span class="adm-ap-required">*</span>
                        </label>

                        <input id="admin-profile-name" type="text" name="name" class="adm-input"
                            value="{{ old('name', $user->name) }}" maxlength="255" autocomplete="name" required
                            autofocus>

                        @error('name')
                        <small class="adm-ap-error">
                            {{ $message }}
                        </small>
                        @enderror

                    </div>


                    <div class="adm-field">

                        <label>
                            Email Akun
                        </label>

                        <div class="adm-ap-readonly">
                            {{ $user->email }}
                        </div>

                        <small class="adm-ap-hint">
                            Email akun tidak dapat diubah di sini.
                        </small>

                    </div>

                </div>


                <div class="adm-ap-actions">

                    <a href="{{ route('admin.index') }}" class="adm-btn adm-btn--secondary">
                        Batal
                    </a>

                    <button type="submit" class="adm-btn adm-btn--primary">
                        Simpan Nama
                    </button>

                </div>

            </form>

        </div>

    </section>

</div>

@endsection