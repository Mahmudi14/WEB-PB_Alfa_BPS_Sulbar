
@extends('layouts.app')

@section('title', 'Pengaturan Website - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}?v=1">
@endpush

@section('content')

<div class="adm-page">

    <header class="adm-hero">
        <div>
            <span class="adm-eyebrow">
                PB ALFA / SITE MANAGEMENT
            </span>

            <h1>PENGATURAN <span>WEBSITE.</span></h1>

            <p>Kelola identitas dan informasi PB Alfa.</p>
        </div>

        <span class="adm-hero__decoration" aria-hidden="true">
            06
        </span>
    </header>

    {{-- Pengaturan --}}

    <section class="adm-section">

        <div class="adm-section__heading">
            <div>
                <span class="adm-eyebrow adm-eyebrow--green">
                    WEBSITE CONFIGURATION
                </span>

                <h2>Informasi Website</h2>
            </div>

            <span class="adm-count">Pengaturan</span>
        </div>

        @if($errors->any())
            <div class="adm-alert" role="alert">
                <strong>Periksa kembali pengaturan.</strong>
                <ul>
                    @foreach($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('admin.settings.update') }}"
            enctype="multipart/form-data"
            data-single-submit
            class="adm-settings-form"
        >
            @csrf
            @method('PUT')

            {{-- Logo --}}

            <div class="adm-settings-group">

                <div class="adm-settings-group__heading">
                    <h3>Logo Klub</h3>
                </div>

                <div class="adm-logo-layout">

                    <div class="adm-logo-preview">

                        @if($settings->logo_path)
                            <img
                                src="{{ asset('storage/' . $settings->logo_path) }}"
                                alt="Logo PB Alfa"
                                loading="lazy"
                                decoding="async"
                            >
                        @else
                            <span>PB</span>
                        @endif

                    </div>

                    <div class="adm-field">

                        <label for="logo">
                            Upload / Ganti Logo
                        </label>

                        <input
                            id="logo"
                            type="file"
                            name="logo"
                            accept="image/jpeg,image/png,image/webp"
                        >

                        <small>
                            JPG, PNG, atau WebP. Maksimal 2 MB.
                        </small>

                    </div>

                </div>

            </div>

            {{-- Tentang Kami --}}

            <div class="adm-settings-group">

                <div class="adm-settings-group__heading">
                    <h3>Tentang Kami</h3>
                </div>

                <div class="adm-settings-fields">

                    <div class="adm-field">
                        <label for="about_title">
                            Judul Bagian
                        </label>

                        <input
                            id="about_title"
                            type="text"
                            name="about_title"
                            maxlength="120"
                            value="{{ old(
                                'about_title',
                                $settings->about_title
                            ) }}"
                            required
                        >
                    </div>

                    <div class="adm-field">
                        <label for="about_text">
                            Deskripsi Klub
                        </label>

                        <textarea
                            id="about_text"
                            name="about_text"
                            maxlength="2000"
                            rows="5"
                            required
                        >{{ old('about_text', $settings->about_text) }}</textarea>
                    </div>

                </div>

            </div>

            {{-- Simpan --}}

            <div class="adm-settings-actions">

                <button
                    type="submit"
                    class="adm-button adm-button--primary"
                >
                    Simpan Pengaturan
                </button>

            </div>

        </form>

        {{-- Hapus logo --}}

        @if($settings->logo_path)

            <form
                method="POST"
                action="{{ route('admin.settings.logo.delete') }}"
                class="adm-remove-logo"
                data-single-submit
                data-confirm="Hapus logo klub?"
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="adm-button adm-button--danger"
                >
                    Hapus Logo
                </button>

            </form>

        @endif

    </section>

</div>

@endsection
