@extends('layouts.admin')

@section('title', 'Pengaturan Website - Admin PB Alfa')
@section('page_title', 'Pengaturan Website')

@section('content')

@php
$logoUrl = $settings->logo_path
? asset('storage/' . $settings->logo_path)
: '';

$hasLogo = !empty($settings->logo_path);

$aboutTitle = old(
'about_title',
$settings->about_title
);

$aboutText = old(
'about_text',
$settings->about_text
);
@endphp

<div class="adm-page adm-ws-page">

    {{-- HEADER --}}

    <div class="adm-page__heading adm-ws-heading">

        <div>
            <span class="adm-eyebrow">
                WEBSITE MANAGEMENT
            </span>

            <h2>Pengaturan Website</h2>

            <p>
                Kelola identitas visual dan informasi
                klub yang ditampilkan di website PB Alfa.
            </p>
        </div>

        <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer" class="adm-btn adm-btn--secondary">
            Lihat Website
        </a>

    </div>


    {{-- INFORMASI --}}

    <div class="adm-ws-intro">

        <span class="adm-ws-intro__icon">
            @include(
            'admin.partials.nav-icon',
            ['name' => 'settings']
            )
        </span>

        <div>
            <strong>Pengaturan Identitas Klub</strong>

            <p>
                Perubahan akan digunakan oleh beranda,
                navbar publik, dan panel administrator
                setelah pengaturan disimpan.
            </p>
        </div>

    </div>


    <form id="admin-site-settings-form" method="POST" action="{{ route('admin.settings.update') }}"
        enctype="multipart/form-data" data-single-submit data-admin-site-settings>

        @csrf
        @method('PUT')

        <div class="adm-ws-workspace">

            {{-- KOLOM KIRI --}}

            <div class="adm-ws-editor">

                {{-- LOGO --}}

                <section class="adm-card adm-ws-card">

                    <div class="adm-ws-section-heading">

                        <span class="adm-ws-section-heading__number">
                            01
                        </span>

                        <div>
                            <h3>Logo Klub</h3>

                            <p>
                                Logo digunakan pada navbar,
                                sidebar admin, dan favicon.
                            </p>
                        </div>

                    </div>


                    <div class="adm-ws-current-logo">

                        <div class="adm-ws-logo-box">

                            <img data-admin-site-logo-image data-original-src="{{ $logoUrl }}" @if($hasLogo)
                                src="{{ $logoUrl }}" @else hidden @endif alt="Logo PB Alfa">

                            <span data-admin-site-logo-fallback @if($hasLogo) hidden @endif>
                                PB
                            </span>

                        </div>

                        <div class="adm-ws-current-logo__info">

                            <strong>Logo Website</strong>

                            <span data-admin-site-logo-status>
                                {{ $hasLogo
                                ? 'Logo kustom sedang digunakan'
                                : 'Menggunakan logo default' }}
                            </span>

                            <small>
                                Disarankan menggunakan logo
                                persegi dengan latar transparan.
                            </small>

                        </div>

                    </div>


                    <div class="adm-ws-upload-area">

                        <label for="site-logo">
                            Ganti Logo
                        </label>

                        <div class="adm-ws-upload-wrap">

                            <input id="site-logo" type="file" name="logo" accept="image/jpeg,image/png,image/webp"
                                class="adm-ws-file-input" data-admin-site-logo-input>

                            <label for="site-logo" class="adm-ws-upload">

                                <span class="adm-ws-upload__icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M12 16V4" />
                                        <path d="m7 9 5-5 5 5" />
                                        <path d="M4 17v3h16v-3" />
                                    </svg>
                                </span>

                                <span class="adm-ws-upload__text">

                                    <strong>
                                        Pilih logo baru
                                    </strong>

                                    <small>
                                        JPG, PNG, atau WebP
                                    </small>

                                </span>

                                <span class="adm-ws-upload__button">
                                    Pilih File
                                </span>

                            </label>

                        </div>

                        <div class="adm-ws-selected-file" data-admin-site-file-name hidden></div>

                        <p class="adm-ws-help">
                            Maksimal 3 MB, ukuran 128–3000 piksel.
                            PNG atau WebP transparan direkomendasikan.
                        </p>

                        @error('logo')
                        <small class="adm-field-error">
                            {{ $message }}
                        </small>
                        @enderror

                    </div>


                    @if($hasLogo)

                    <label class="adm-ws-remove">

                        <input type="checkbox" name="remove_logo" value="1" data-admin-site-remove-logo
                            @checked(old('remove_logo')=='1' )>

                        <span>

                            <strong>
                                Gunakan Logo Default
                            </strong>

                            <small>
                                Hapus logo kustom dari
                                pengaturan website.
                            </small>

                        </span>

                    </label>

                    @endif

                    @error('remove_logo')
                    <small class="adm-field-error">
                        {{ $message }}
                    </small>
                    @enderror

                </section>


                {{-- TENTANG KAMI --}}

                <section class="adm-card adm-ws-card">

                    <div class="adm-ws-section-heading">

                        <span class="adm-ws-section-heading__number">
                            02
                        </span>

                        <div>

                            <h3>Informasi Klub</h3>

                            <p>
                                Konten bagian Tentang Kami
                                pada halaman beranda.
                            </p>

                        </div>

                    </div>


                    <div class="adm-ws-fields">

                        <div class="adm-field">

                            <label for="site-about-title">
                                Judul Tentang Kami
                                <span class="adm-ws-required">*</span>
                            </label>

                            <input id="site-about-title" name="about_title" type="text" class="adm-input"
                                value="{{ $aboutTitle }}" maxlength="120" placeholder="Contoh: Tentang PB Alfa"
                                data-admin-site-about-title required>

                            <small class="adm-ws-help">
                                Maksimal 120 karakter.
                            </small>

                            @error('about_title')
                            <small class="adm-field-error">
                                {{ $message }}
                            </small>
                            @enderror

                        </div>


                        <div class="adm-field">

                            <label for="site-about-text">
                                Deskripsi Tentang Kami
                                <span class="adm-ws-required">*</span>
                            </label>

                            <textarea id="site-about-text" name="about_text" class="adm-textarea adm-ws-about-text"
                                rows="7" maxlength="10000" placeholder="Ceritakan tentang PB Alfa..."
                                data-admin-site-about-text required>{{ $aboutText }}</textarea>

                            <div class="adm-ws-text-meta">

                                <small>
                                    Deskripsi yang akan ditampilkan
                                    pada bagian Tentang Kami.
                                </small>

                                <small data-admin-site-character-count>
                                    {{ mb_strlen($aboutText) }} / 10000
                                </small>

                            </div>

                            @error('about_text')
                            <small class="adm-field-error">
                                {{ $message }}
                            </small>
                            @enderror

                        </div>

                    </div>

                </section>


                {{-- SIMPAN --}}

                <div class="adm-ws-save-bar">

                    <div>
                        <strong>Simpan Perubahan Website</strong>

                        <p>
                            Perubahan akan langsung diterapkan
                            setelah berhasil disimpan.
                        </p>
                    </div>

                    <button type="submit" class="adm-btn adm-btn--primary">
                        Simpan Pengaturan
                    </button>

                </div>

            </div>


            {{-- KOLOM KANAN: LIVE PREVIEW --}}

            <aside class="adm-ws-preview-column">

                <section class="adm-card adm-ws-preview-card">

                    <div class="adm-ws-preview-heading">

                        <div>
                            <span class="adm-eyebrow">
                                LIVE PREVIEW
                            </span>

                            <h3>Pratinjau Website</h3>

                            <p>
                                Gambaran perubahan sebelum disimpan.
                            </p>
                        </div>

                        <span class="adm-ws-live-indicator">
                            <span></span>
                            Pratinjau
                        </span>

                    </div>


                    <div class="adm-ws-browser">

                        <div class="adm-ws-browser__bar">

                            <span class="adm-ws-browser__dots">
                                <i></i>
                                <i></i>
                                <i></i>
                            </span>

                            <span>PB Alfa Website</span>

                        </div>


                        {{-- PREVIEW NAVBAR --}}

                        <div class="adm-ws-browser__navbar">

                            <div class="adm-ws-browser__brand">

                                <span class="adm-ws-browser__brand-logo">

                                    <img data-admin-site-logo-image data-original-src="{{ $logoUrl }}" @if($hasLogo)
                                        src="{{ $logoUrl }}" @else hidden @endif alt="Logo PB Alfa">

                                    <span data-admin-site-logo-fallback @if($hasLogo) hidden @endif>
                                        PB
                                    </span>

                                </span>

                                <span class="adm-ws-browser__brand-text">

                                    <strong>PB ALFA</strong>

                                    <small>
                                        BPS SULAWESI BARAT
                                    </small>

                                </span>

                            </div>

                            <span class="adm-ws-browser__menu">
                                BERANDA
                            </span>

                        </div>


                        {{-- PREVIEW TENTANG KAMI --}}

                        <div class="adm-ws-browser__about">

                            <span class="adm-ws-browser__eyebrow">
                                THE SPIRIT OF PB ALFA
                            </span>

                            <h4>
                                LEBIH DARI
                                <em>SEKADAR MAIN.</em>
                            </h4>

                            <div class="adm-ws-browser__line"></div>

                            <div class="adm-ws-browser__body">

                                <h5 data-admin-site-preview-title>
                                    {{ $aboutTitle }}
                                </h5>

                                <p data-admin-site-preview-text>
                                    {{ $aboutText }}
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="adm-ws-preview-footer">

                        <span>
                            <icon name="info" inline size="sm" />
                        </span>

                        <p>
                            Ini adalah simulasi tampilan.
                            Desain sebenarnya mengikuti
                            stylesheet website publik.
                        </p>

                    </div>

                    <a href="{{ route('home') }}#tentang" class="adm-ws-public-link" target="_blank"
                        rel="noopener noreferrer">
                        Lihat Bagian Tentang Kami
                        <span aria-hidden="true">&rarr;</span>
                    </a>

                </section>

            </aside>

        </div>

    </form>

</div>

@endsection