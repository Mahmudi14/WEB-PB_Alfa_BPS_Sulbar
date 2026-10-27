@extends('layouts.app')
@section('title', 'Buat Turnamen - PB Alfa')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}?v=2">
@endpush

@section('content')
<div class="tr-page tr-page--narrow">
    <header class="tr-page-heading">
        <div class="tr-page-heading__main">
            <a class="tr-back"
                href="{{ isset($tournament) ? route('tournaments.entries', $tournament) : route('tournaments.index') }}">←
                Kembali</a>
            <span class="tr-eyebrow">PB ALFA / TOURNAMENT SETUP</span>
            <h1>ATURAN <span>TURNAMEN.</span></h1>
            <p>Langkah 1 dari 4 — tentukan format kompetisi dan aturan skor terlebih dahulu.</p>
        </div>
        <span class="tr-hero__decoration" aria-hidden="true">01</span>
    </header>

    @include('tournaments._steps', ['step' => 1])

    <form method="POST"
        action="{{ isset($tournament) ? route('tournaments.rules.update', $tournament) : route('tournaments.store') }}"
        class="tr-panel tr-form" data-single-submit>
        @csrf
        @if(isset($tournament)) @method('PUT') @endif
        @if($errors->any())
        <div class="tr-alert" role="alert">
            <strong>Periksa aturan turnamen:</strong>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <section class="tr-form-section" aria-labelledby="tr-basic-heading">
            <div class="tr-form-section-title">
                <div><span class="tr-eyebrow">01 / INFORMASI</span>
                    <h2 id="tr-basic-heading">Informasi Turnamen</h2>
                </div>
            </div>
            <div class="tr-field">
                <label for="tr-name">Nama turnamen</label>
                <input id="tr-name" name="name" maxlength="150" required
                    value="{{ old('name', $tournament->name ?? '') }}" placeholder="Contoh: PB Alfa Cup 2026">
            </div>
            <div class="tr-field">
                <label for="tr-description">Deskripsi (opsional)</label>
                <textarea id="tr-description" name="description" rows="3" maxlength="1500"
                    placeholder="Keterangan kompetisi">{{ old('description', $tournament->description ?? '') }}</textarea>
            </div>
        </section>

        <section class="tr-form-section" aria-labelledby="tr-format-heading">
            <div class="tr-form-section-title">
                <div><span class="tr-eyebrow">02 / KOMPETISI</span>
                    <h2 id="tr-format-heading">Format Pertandingan</h2>
                </div>
            </div>
            <div class="tr-field">
                <span class="tr-label">Format turnamen</span>
                <div class="tr-radio-grid">
                    <label class="tr-choice">
                        <input type="radio" name="format" value="liga" @checked(old('format', $tournament->format ??
                        'liga') === 'liga')>
                        <span><strong>Liga</strong><small>Semua peserta saling bertemu di setiap ronde.</small></span>
                    </label>
                    <label class="tr-choice">
                        <input type="radio" name="format" value="cup" @checked(old('format', $tournament->format ??
                        'liga') === 'cup')>
                        <span><strong>Cup</strong><small>Sistem gugur hingga tersisa juara.</small></span>
                    </label>
                </div>
            </div>
            <div class="tr-field">
                <span class="tr-label">Jenis pertandingan</span>
                <div class="tr-radio-grid">
                    <label class="tr-choice">
                        <input type="radio" name="match_type" value="tunggal" @checked(old('match_type',
                            $tournament->match_type ?? 'tunggal') === 'tunggal')>
                        <span><strong>Tunggal</strong><small>Satu pemain per peserta.</small></span>
                    </label>
                    <label class="tr-choice">
                        <input type="radio" name="match_type" value="ganda" @checked(old('match_type',
                            $tournament->match_type ?? 'tunggal') === 'ganda')>
                        <span><strong>Ganda</strong><small>Dua pemain dalam satu pasangan tetap.</small></span>
                    </label>
                </div>
            </div>
        </section>

        <section class="tr-form-section" aria-labelledby="tr-scoring-heading">
            <div class="tr-form-section-title">
                <div><span class="tr-eyebrow">03 / SKOR</span>
                    <h2 id="tr-scoring-heading">Aturan Skor</h2>
                </div>
            </div>
            <div class="tr-field">
                <span class="tr-label">Sistem set</span>
                <div class="tr-stack">
                    <label class="tr-choice">
                        <input type="radio" name="set_format" value="two_draw" @checked(old('set_format',
                            $tournament->set_format ?? 'two_draw') === 'two_draw')>
                        <span><strong>2 Set — Boleh Seri</strong><small>Hasil 1–1 dinyatakan seri. Hanya untuk
                                Liga.</small></span>
                    </label>
                    <label class="tr-choice">
                        <input type="radio" name="set_format" value="two_aggregate" @checked(old('set_format',
                            $tournament->set_format ?? 'two_draw') === 'two_aggregate')>

                        <span>
                            <strong>2 Set — Total Poin</strong>
                            <small>
                                Pemenang ditentukan dari jumlah poin
                                kedua set.
                            </small>
                        </span>

                    </label>
                    <label class="tr-choice">
                        <input type="radio" name="set_format" value="best_of_three" @checked(old('set_format',
                            $tournament->set_format ?? 'two_draw') === 'best_of_three')>
                        <span><strong>3 Set — Best of 3</strong><small>Yang lebih dahulu menang 2 set menjadi
                                pemenang.</small></span>
                    </label>
                </div>
                <small id="tr-cup-notice" class="tr-hint" hidden>Cup memerlukan pemenang; pilihan 2 set boleh seri
                    dinonaktifkan.</small>
            </div>
            <div class="tr-field">
                <span class="tr-label">Target poin per set</span>
                <div class="tr-radio-grid">
                    <label class="tr-choice">
                        <input type="radio" name="target_points" value="15" @checked((string) old('target_points',
                            $tournament->target_points ?? 21) === '15')>
                        <span><strong>15 Poin</strong><small>Target kemenangan per set.</small></span>
                    </label>
                    <label class="tr-choice">
                        <input type="radio" name="target_points" value="21" @checked((string) old('target_points',
                            $tournament->target_points ?? 21) === '21')>
                        <span><strong>21 Poin</strong><small>Target kemenangan per set.</small></span>
                    </label>
                </div>
            </div>
        </section>

        {{-- Pengaturan khusus Liga --}}
        <section class="tr-form-section" id="tr-league-settings" aria-labelledby="tr-league-heading" @if(old('format',
            $tournament->format ?? 'liga') !== 'liga')
            hidden
            @endif
            >
            <div class="tr-form-section-title">
                <div>
                    <span class="tr-eyebrow">
                        04 / PENGATURAN LIGA
                    </span>

                    <h2 id="tr-league-heading">
                        Poin Klasemen & Pertemuan
                    </h2>
                </div>
            </div>

            {{-- Poin klasemen --}}
            <div class="tr-field">
                <span class="tr-label">
                    Poin untuk setiap hasil pertandingan
                </span>

                <div class="tr-league-points-grid">

                    <div class="tr-field">
                        <label for="tr-win-points">
                            Menang
                        </label>

                        <input id="tr-win-points" type="number" inputmode="numeric" name="league_win_points" value="{{ old(
                        'league_win_points',
                        $tournament->league_win_points ?? 3
                    ) }}" min="-100" max="100" step="1" required>
                    </div>

                    <div class="tr-field">
                        <label for="tr-draw-points">
                            Seri
                        </label>

                        <input id="tr-draw-points" type="number" inputmode="numeric" name="league_draw_points" value="{{ old(
                        'league_draw_points',
                        $tournament->league_draw_points ?? 1
                    ) }}" min="-100" max="100" step="1" required>
                    </div>

                    <div class="tr-field">
                        <label for="tr-loss-points">
                            Kalah
                        </label>

                        <input id="tr-loss-points" type="number" inputmode="numeric" name="league_loss_points" value="{{ old(
                        'league_loss_points',
                        $tournament->league_loss_points ?? 0
                    ) }}" min="-100" max="100" step="1" required>
                    </div>
                </div>

            </div>

            {{-- Jumlah pertemuan --}}
            <div class="tr-field">
                <span class="tr-label">
                    Jumlah pertemuan antarpeserta
                </span>

                <div class="tr-radio-grid">

                    <label class="tr-choice">
                        <input type="radio" name="league_legs" value="1" @checked( (string) old( 'league_legs' ,
                            $tournament->league_legs ?? 1
                        ) === '1'
                        )
                        >

                        <span>
                            <strong>1 Kali Bertemu</strong>
                            <small>
                                Setiap peserta saling bertemu
                                satu kali dalam satu putaran.
                            </small>
                        </span>
                    </label>

                    <label class="tr-choice">
                        <input type="radio" name="league_legs" value="2" @checked( (string) old( 'league_legs' ,
                            $tournament->league_legs ?? 1
                        ) === '2'
                        )
                        >

                        <span>
                            <strong>2 Kali Bertemu</strong>
                            <small>
                                Dua putaran dengan posisi
                                Tim A dan Tim B bergantian.
                            </small>
                        </span>
                    </label>

                </div>

            </div>
        </section>

        <div class="tr-form-footer">
            <a class="tr-btn tr-btn--muted" href="{{ route('tournaments.index') }}">Batal</a>
            <button class="tr-btn" type="submit">Berikutnya: Pilih Pemain →</button>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script src="{{ asset('assets/js/tournaments.js') }}?v=1" defer></script>
@endpush