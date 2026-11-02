@extends('layouts.admin')

@section('title', 'Galeri Klub - Admin PB Alfa')
@section('page_title', 'Galeri Klub')

@section('content')

<div class="adm-page adm-ga-page">

    <div class="adm-page__heading">

        <div>
            <span class="adm-eyebrow">
                CLUB GALLERY MANAGEMENT
            </span>

            <h2>Galeri Klub</h2>

            <p>
                Kelola foto kegiatan, pertandingan,
                dan kebersamaan PB Alfa.
            </p>
        </div>

        <a href="{{ route('home') }}#galeri" target="_blank" rel="noopener noreferrer"
            class="adm-btn adm-btn--secondary">
            Lihat Beranda
        </a>

    </div>


    <div class="adm-ga-summary">

        <div class="adm-ga-summary__icon">
            @include(
            'admin.partials.nav-icon',
            ['name' => 'image']
            )
        </div>

        <div class="adm-ga-summary__text">
            <strong>
                {{ $images->count() }} dari {{ $maxImages }} Foto
            </strong>

            <span>
                {{ $remainingSlots > 0
                ? $remainingSlots . ' slot foto masih tersedia'
                : 'Kapasitas galeri sudah penuh' }}
            </span>
        </div>

        <div class="adm-ga-progress" role="progressbar" aria-label="Kapasitas galeri"
            aria-valuenow="{{ $images->count() }}" aria-valuemin="0" aria-valuemax="{{ $maxImages }}">
            <span style="width: {{ min(
                    100,
                    ($images->count() / $maxImages) * 100
                ) }}%"></span>
        </div>

    </div>


    <div class="adm-ga-layout">

        {{-- UPLOAD FOTO --}}

        <section class="adm-card adm-ga-upload">

            <div class="adm-ga-section-heading">

                <span class="adm-ga-section-heading__eyebrow">
                    UPLOAD
                </span>

                <h3>Tambah Foto</h3>

                <p>
                    Unggah dokumentasi terbaru
                    untuk ditampilkan pada beranda.
                </p>

            </div>


            @if($remainingSlots > 0)

            <form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data"
                data-single-submit data-admin-gallery-form>

                @csrf

                <div class="adm-ga-drop" data-admin-gallery-drop>

                    <input id="gallery-image" type="file" name="image" accept="image/jpeg,image/png,image/webp"
                        class="adm-ga-file-input" data-admin-gallery-file required>

                    <label for="gallery-image">

                        <span class="adm-ga-drop__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="3" width="18" height="18" rx="3" />
                                <circle cx="8.5" cy="8.5" r="1.5" />
                                <path d="m21 15-5-5L5 21" />
                                <path d="m13 17 2-2" />
                            </svg>
                        </span>

                        <strong>
                            Pilih atau seret foto ke sini
                        </strong>

                        <small>
                            JPG, PNG, atau WebP
                        </small>

                        <span class="adm-ga-drop__choose">
                            Pilih Foto
                        </span>

                    </label>

                </div>


                <div class="adm-ga-preview" data-admin-gallery-preview hidden>

                    <div class="adm-ga-preview__image">
                        <img data-admin-gallery-preview-image alt="Pratinjau foto yang dipilih">
                    </div>

                    <div class="adm-ga-preview__info">
                        <strong>Foto Dipilih</strong>

                        <span data-admin-gallery-file-name></span>
                    </div>

                </div>


                <div class="adm-field adm-ga-caption-field">

                    <label for="gallery-caption">
                        Keterangan Foto
                    </label>

                    <textarea id="gallery-caption" name="caption" class="adm-textarea" rows="3" maxlength="150"
                        placeholder="Contoh: Latihan bersama PB Alfa...">{{ old('caption') }}</textarea>

                    <small class="adm-field-help">
                        Opsional, maksimal 150 karakter.
                    </small>

                    @error('caption')
                    <small class="adm-field-error">
                        {{ $message }}
                    </small>
                    @enderror

                </div>


                <div class="adm-ga-upload__notes">
                    <span>Ukuran maksimal: 5 MB</span>
                    <span>Dimensi maksimal: 4000 × 4000 px</span>
                    <span>Satu foto untuk setiap unggahan</span>
                </div>


                <button type="submit" class="adm-btn adm-btn--primary adm-ga-upload__submit">
                    Unggah Foto
                </button>

            </form>

            @else

            <div class="adm-ga-full">

                <div class="adm-ga-full__icon">
                    <icon name="image" inline />
                </div>

                <strong>Galeri Sudah Penuh</strong>

                <p>
                    Seluruh enam slot telah terisi.
                    Hapus foto lama apabila ingin
                    menggantinya dengan dokumentasi baru.
                </p>

            </div>

            @endif

        </section>


        {{-- DAFTAR FOTO --}}

        <section class="adm-card adm-ga-collection">

            <div class="adm-ga-collection__heading">

                <div class="adm-ga-section-heading">

                    <span class="adm-ga-section-heading__eyebrow">
                        COLLECTION
                    </span>

                    <h3>Koleksi Foto</h3>

                    <p>
                        Foto terbaru tampil terlebih dahulu
                        di halaman beranda.
                    </p>

                </div>

                <span class="adm-ga-collection__count">
                    {{ $images->count() }} Foto
                </span>

            </div>


            @if($images->isEmpty())

            <div class="adm-ga-empty">

                <div class="adm-ga-empty__icon">
                    @include(
                    'admin.partials.nav-icon',
                    ['name' => 'image']
                    )
                </div>

                <h4>Belum Ada Foto</h4>

                <p>
                    Unggah foto pertama untuk mulai
                    menampilkan dokumentasi klub
                    di halaman beranda PB Alfa.
                </p>

            </div>

            @else

            <div class="adm-ga-grid">

                @foreach($images as $image)

                <article class="adm-ga-photo">

                    <a href="{{ asset('storage/' . $image->image_path) }}" target="_blank" rel="noopener noreferrer"
                        class="adm-ga-photo__media" title="Lihat foto ukuran penuh">

                        <img src="{{ asset('storage/' . $image->image_path) }}"
                            alt="{{ $image->caption ?: 'Dokumentasi PB Alfa' }}" loading="lazy" decoding="async">

                        <span class="adm-ga-photo__view">
                            Lihat Foto
                        </span>

                    </a>


                    <div class="adm-ga-photo__content">

                        <div class="adm-ga-photo__number">
                            <span>
                                FOTO {{ str_pad(
                                $loop->iteration,
                                2,
                                '0',
                                STR_PAD_LEFT
                                ) }}
                            </span>

                            @if($loop->first)

                            <span class="adm-ga-photo__new">
                                Terbaru
                            </span>

                            @endif
                        </div>

                        <h4>
                            {{ $image->caption
                            ?: 'Dokumentasi PB Alfa' }}
                        </h4>

                        <p class="adm-ga-photo__meta">
                            {{ $image->created_at?->translatedFormat('d M Y') }}
                            ·
                            {{ $image->creator?->name ?? 'Admin' }}
                        </p>


                        <details class="adm-ga-photo__editor">

                            <summary>
                                Ubah Keterangan
                            </summary>

                            <form method="POST" action="{{ route(
                                            'admin.gallery.caption.update',
                                            $image
                                        ) }}" data-single-submit>

                                @csrf
                                @method('PATCH')

                                <label for="gallery-caption-{{ $image->id }}">
                                    Keterangan
                                </label>

                                <textarea id="gallery-caption-{{ $image->id }}" name="caption" class="adm-textarea"
                                    maxlength="150" rows="3"
                                    placeholder="Keterangan foto...">{{ $image->caption }}</textarea>

                                <button type="submit" class="adm-btn adm-btn--secondary">
                                    Simpan Keterangan
                                </button>

                            </form>

                        </details>


                        <form method="POST" action="{{ route(
                                        'admin.gallery.destroy',
                                        $image
                                    ) }}" data-single-submit data-confirm="Hapus foto ini dari Galeri Klub?"
                            data-confirm-title="Hapus Foto Galeri" data-confirm-text="Ya, Hapus">

                            @csrf
                            @method('DELETE')

                            <button type="submit" class="adm-ga-photo__delete">
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

</div>

@endsection