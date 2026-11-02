@extends('layouts.admin')

@php
$editing = isset($tournament);

$format = old(
'format',
$tournament->format ?? 'liga'
);

$matchType = old(
'match_type',
$tournament->match_type ?? 'tunggal'
);

$setFormat = old(
'set_format',
$tournament->set_format ?? 'two_draw'
);
@endphp

@section('title', ($editing ? 'Edit Aturan' : 'Buat Turnamen') . ' - Admin PB Alfa')
@section('page_title', $editing ? 'Edit Aturan' : 'Buat Turnamen')

@section('content')

<div class="adm-page adm-tr-page">

    <div class="adm-page__heading">
        <div>
            <span class="adm-eyebrow">
                TOURNAMENT SETUP / LANGKAH 1
            </span>

            <h2>{{ $editing ? 'Edit Aturan Turnamen' : 'Buat Turnamen' }}</h2>

            <p>
                Tentukan format kompetisi, sistem skor,
                dan aturan klasemen.
            </p>
        </div>

        <a href="{{ $editing
                ? route('admin.tournaments.show', $tournament)
                : route('admin.tournaments.index') }}" class="adm-btn adm-btn--secondary">
            Kembali
        </a>
    </div>

    <form method="POST" action="{{ $editing
              ? route('admin.tournaments.rules.update', $tournament)
              : route('admin.tournaments.store') }}" data-single-submit data-admin-tournament-rules>

        @csrf
        @if($editing)
        @method('PUT')
        @endif

        <section class="adm-card adm-tr-form-card">
            <div class="adm-tr-section-title">
                <span>01</span>
                <div>
                    <h3>Identitas Turnamen</h3>
                    <p>Nama dan keterangan kompetisi.</p>
                </div>
            </div>

            <div class="adm-field">
                <label for="tr-name">Nama Turnamen *</label>
                <input id="tr-name" type="text" name="name" class="adm-input" maxlength="150" required
                    value="{{ old('name', $tournament->name ?? '') }}" placeholder="Contoh: PB Alfa Cup 2026">
            </div>

            <div class="adm-field adm-tr-space">
                <label for="tr-description">Deskripsi</label>
                <textarea id="tr-description" name="description" rows="3" maxlength="1500" class="adm-textarea"
                    placeholder="Keterangan turnamen...">{{ old('description', $tournament->description ?? '') }}</textarea>
            </div>
        </section>

        <section class="adm-card adm-tr-form-card">
            <div class="adm-tr-section-title">
                <span>02</span>
                <div>
                    <h3>Format Kompetisi</h3>
                    <p>Pilih sistem turnamen dan jumlah pemain per tim.</p>
                </div>
            </div>

            <div class="adm-field">
                <label>Jenis Turnamen *</label>

                <div class="adm-tr-choice-grid">
                    <label class="adm-tr-choice">
                        <input type="radio" name="format" value="liga" @checked($format==='liga' )>
                        <span>
                            <strong>Liga</strong>
                            <small>Peserta saling bertemu berdasarkan jadwal.</small>
                        </span>
                    </label>

                    <label class="adm-tr-choice">
                        <input type="radio" name="format" value="cup" @checked($format==='cup' )>
                        <span>
                            <strong>Cup</strong>
                            <small>Sistem gugur sampai tersisa satu juara.</small>
                        </span>
                    </label>
                </div>
            </div>

            <div class="adm-field adm-tr-space">
                <label>Jenis Pertandingan *</label>

                <div class="adm-tr-choice-grid">
                    <label class="adm-tr-choice">
                        <input type="radio" name="match_type" value="tunggal" @checked($matchType==='tunggal' )>
                        <span>
                            <strong>Tunggal</strong>
                            <small>Satu pemain setiap peserta.</small>
                        </span>
                    </label>

                    <label class="adm-tr-choice">
                        <input type="radio" name="match_type" value="ganda" @checked($matchType==='ganda' )>
                        <span>
                            <strong>Ganda</strong>
                            <small>Dua pemain per pasangan.</small>
                        </span>
                    </label>
                </div>
            </div>
        </section>

        <section class="adm-card adm-tr-form-card">
            <div class="adm-tr-section-title">
                <span>03</span>
                <div>
                    <h3>Aturan Skor</h3>
                    <p>Sistem set dan target poin setiap set.</p>
                </div>
            </div>

            <div class="adm-field">
                <label>Sistem Set *</label>

                <div class="adm-tr-choice-grid adm-tr-choice-grid--three">
                    @foreach([
                    'two_draw' => [
                    '2 Set — Boleh Seri',
                    'Hasil 1–1 dinyatakan seri. Hanya untuk Liga.'
                    ],
                    'two_aggregate' => [
                    '2 Set — Total Poin',
                    'Pemenang dari total poin kedua set.'
                    ],
                    'best_of_three' => [
                    'Best of Three',
                    'Pemenang lebih dahulu menang dua set.'
                    ],
                    ] as $value => $label)
                    <label class="adm-tr-choice">
                        <input type="radio" name="set_format" value="{{ $value }}" @checked($setFormat===$value)
                            data-tr-draw-option="{{ $value === 'two_draw' ? '1' : '0' }}">
                        <span>
                            <strong>{{ $label[0] }}</strong>
                            <small>{{ $label[1] }}</small>
                        </span>
                    </label>
                    @endforeach
                </div>

                <small class="adm-field-help" data-tr-cup-notice hidden>
                    Cup tidak dapat menggunakan sistem yang memungkinkan seri.
                </small>
            </div>

            <div class="adm-field adm-tr-space">
                <label>Target Poin *</label>

                <div class="adm-tr-choice-grid">
                    @foreach([15, 21] as $point)
                    <label class="adm-tr-choice">
                        <input type="radio" name="target_points" value="{{ $point }}" @checked( (string)
                            old( 'target_points' , $tournament->target_points ?? 21
                        ) === (string) $point
                        )>
                        <span>
                            <strong>{{ $point }} Poin</strong>
                            <small>Target kemenangan tiap set.</small>
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="adm-card adm-tr-form-card" data-tr-league-settings @if($format !=='liga' ) hidden @endif>

            <div class="adm-tr-section-title">
                <span>04</span>
                <div>
                    <h3>Pengaturan Liga</h3>
                    <p>Poin klasemen dan jumlah pertemuan antarpeserta.</p>
                </div>
            </div>

            <div class="adm-tr-three-fields">
                @foreach([
                'league_win_points' => [
                'Menang',
                $tournament->league_win_points ?? 3
                ],
                'league_draw_points' => [
                'Seri',
                $tournament->league_draw_points ?? 1
                ],
                'league_loss_points' => [
                'Kalah',
                $tournament->league_loss_points ?? 0
                ],
                ] as $name => $field)

                <div class="adm-field">
                    <label for="tr-{{ $name }}">{{ $field[0] }}</label>
                    <input id="tr-{{ $name }}" type="number" name="{{ $name }}" class="adm-input" min="-100" max="100"
                        step="1" value="{{ old($name, $field[1]) }}" @if($format !=='liga' ) disabled @endif required>
                </div>
                @endforeach
            </div>

            <div class="adm-field adm-tr-space">
                <label>Jumlah Pertemuan *</label>

                <div class="adm-tr-choice-grid">
                    @foreach([1, 2] as $legs)
                    <label class="adm-tr-choice">
                        <input type="radio" name="league_legs" value="{{ $legs }}" @checked( (string) old( 'league_legs'
                            , $tournament->league_legs ?? 1
                        ) === (string) $legs
                        )
                        @if($format !== 'liga') disabled @endif
                        >
                        <span>
                            <strong>{{ $legs }} Kali Bertemu</strong>
                            <small>
                                {{ $legs === 1
                                ? 'Satu putaran per pasangan.'
                                : 'Dua putaran dengan posisi tim bergantian.' }}
                            </small>
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="adm-tr-form-actions">
            <a href="{{ route('admin.tournaments.index') }}" class="adm-btn adm-btn--secondary">
                Batal
            </a>

            <button type="submit" class="adm-btn adm-btn--primary">
                {{ $editing
                ? 'Simpan Aturan'
                : 'Berikutnya: Atur Peserta' }}
            </button>
        </div>
    </form>

</div>

@endsection