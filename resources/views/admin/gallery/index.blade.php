
@extends('layouts.app')

@section('title', 'Galeri - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}?v=1">
@endpush

@section('content')

<div class="adm-page">

    <header class="adm-hero">
        <div>
            <span class="adm-eyebrow">
                PB ALFA / GALLERY MANAGEMENT
            </span>

            <h1>GALERI <span>KLUB.</span></h1>

            <p>Kelola foto kegiatan PB Alfa.</p>
        </div>

        <span class="adm-hero__decoration" aria-hidden="true">
            07
        </span>
    </header>

    {{-- Galeri --}}

    <section class="adm-section">

        <div class="adm-section__heading">
            <div>
                <span class="adm-eyebrow adm-eyebrow--green">
                    CLUB PHOTO GALLERY
                </span>

                <h2>Kelola Galeri</h2>
            </div>

            <span class="adm-count">
                {{ $images->count() }} / 5 Foto
            </span>
        </div>

        @if($errors->any())
            <div class="adm-alert" role="alert">
                <strong>Periksa kembali data foto.</strong>
                <ul>
                    @foreach($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Tambah foto --}}

        @if($images->count() < 5)

            <form
                method="POST"
                action="{{ route('admin.gallery.store') }}"
                enctype="multipart/form-data"
                data-single-submit
                class="adm-gallery-form"
            >
                @csrf

                <div class="adm-settings-group__heading">
                    <h3>Tambah Foto</h3>
                </div>

                <div class="adm-gallery-form__fields">

                    <div class="adm-field">
                        <label for="image">
                            Pilih Foto
                        </label>

                        <input
                            id="image"
                            type="file"
                            name="image"
                            accept="image/jpeg,image/png,image/webp"
                            required
                        >
                    </div>

                    <div class="adm-field">
                        <label for="caption">
                            Keterangan
                            <span class="adm-optional">Opsional</span>
                        </label>

                        <input
                            id="caption"
                            type="text"
                            name="caption"
                            maxlength="150"
                            value="{{ old('caption') }}"
                            placeholder="Contoh: Latihan bersama"
                        >
                    </div>

                </div>

                <div class="adm-gallery-form__footer">
                    <small>
                        JPG, PNG, atau WebP. Maksimal 2 MB.
                    </small>

                    <button
                        type="submit"
                        class="adm-button adm-button--primary"
                    >
                        Upload Foto
                    </button>
                </div>

            </form>

        @else

            <div class="adm-gallery-limit">
                Galeri sudah berisi 5 foto. Hapus satu foto
                untuk menambahkan foto baru.
            </div>

        @endif

        {{-- Foto tersimpan --}}

        <div class="adm-gallery-heading">
            <h3>Foto Tersimpan</h3>

            <span class="adm-gallery-heading__count">
                {{ $images->count() }} Foto
            </span>
        </div>

        @if($images->isEmpty())

            <div class="adm-empty">
                Belum ada foto kegiatan.
            </div>

        @else

            <div class="adm-gallery-grid">

                @foreach($images as $image)

                    <article class="adm-gallery-card">

                        <div class="adm-gallery-card__image">
                            <img
                                src="{{ asset('storage/' . $image->image_path) }}"
                                alt="{{ $image->caption ?: 'Foto kegiatan PB Alfa' }}"
                                loading="lazy"
                                decoding="async"
                            >
                        </div>

                        <div class="adm-gallery-card__body">

                            <p>
                                {{ $image->caption ?: 'Tanpa keterangan' }}
                            </p>

                            {{-- Hapus foto --}}

                            <form
                                method="POST"
                                action="{{ route('admin.gallery.destroy', $image) }}"
                                data-single-submit
                                data-confirm="Hapus foto ini?"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="adm-button adm-button--danger"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="M3 6h18"/>
                                        <path d="M8 6V4h8v2"/>
                                        <path d="M5 6l1 15h12l1-15"/>
                                        <path d="M10 10v7M14 10v7"/>
                                    </svg>

                                    Hapus Foto
                                </button>

                            </form>

                        </div>

                    </article>

                @endforeach

            </div>

        @endif

    </section>

</div>

@endsection
