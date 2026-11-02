@extends('layouts.admin')

@section('title', 'Tambah Pertandingan - Admin PB Alfa')
@section('page_title', 'Tambah Pertandingan')

@section('content')

@php
$type = old('type', 'ganda');

$slots = [
'a1' => 'Pemain 1 Tim A',
'a2' => 'Pemain 2 Tim A',
'b1' => 'Pemain 1 Tim B',
'b2' => 'Pemain 2 Tim B',
];
@endphp

<div class="adm-page adm-match-page">

    <div class="adm-page__heading">
        <div>
            <span class="adm-eyebrow">
                MATCH MANAGEMENT / CREATE
            </span>
            <h2>Tambah Pertandingan</h2>
            <p>
                Buat pertandingan reguler baru,
                dengan hasil selesai maupun pending.
            </p>
        </div>

        <a href="{{ route('admin.matches.index') }}" class="adm-btn adm-btn--secondary">
            Kembali
        </a>
    </div>

    <form id="admin-match-create" method="POST" action="{{ route('admin.matches.store') }}" data-single-submit
        data-admin-match-form>
        @csrf

        <section class="adm-card adm-match-form-card">

            <div class="adm-player-section-title">
                <h3>Informasi Pertandingan</h3>
                <p>Jenis dan tanggal pertandingan.</p>
            </div>

            <div class="adm-match-form-grid">

                <div class="adm-field">
                    <label for="match-date">
                        Tanggal Bermain *
                    </label>

                    <input id="match-date" type="date" name="played_on" class="adm-input"
                        value="{{ old('played_on', now()->toDateString()) }}" required>
                </div>

                <div class="adm-field">
                    <label for="match-type">
                        Jenis Pertandingan *
                    </label>

                    <div class="adm-select-shell">
                        <select id="match-type" name="type" class="adm-select adm-premium-select"
                            data-admin-premium-select required>
                            <option value="ganda" @selected($type==='ganda' )>
                                Ganda
                            </option>
                            <option value="tunggal" @selected($type==='tunggal' )>
                                Tunggal
                            </option>
                        </select>
                    </div>
                </div>

            </div>

        </section>


        <section class="adm-card adm-match-form-card">

            <div class="adm-player-section-title">
                <h3>Peserta Pertandingan</h3>
                <p>
                    Pilih peserta Tim A dan Tim B.
                    Pemain yang sama tidak boleh mengisi dua posisi.
                </p>
            </div>

            <div class="adm-match-teams">

                @foreach(['a' => 'Tim A', 'b' => 'Tim B'] as $team => $label)

                <div class="adm-match-team">

                    <div class="adm-match-team__heading">
                        {{ $label }}
                    </div>

                    @foreach([1, 2] as $number)

                    @php
                    $slot = $team . $number;
                    @endphp

                    <div class="adm-field" @if($number===2) data-match-doubles @endif @if($type==='tunggal' &&
                        $number===2) hidden @endif>
                        <label for="match-{{ $slot }}">
                            {{ $slots[$slot] }}
                        </label>

                        <div class="adm-select-shell">

                            <select id="match-{{ $slot }}" name="{{ $slot }}" class="adm-select adm-premium-select"
                                data-admin-premium-select @if($type==='tunggal' && $number===2) disabled @endif
                                @if($number===1 || $type==='ganda' ) required @endif>
                                <option value="">Pilih Pemain</option>

                                <option value="guest" @selected(old($slot)==='guest' )>
                                    Pemain Luar
                                </option>

                                @foreach($players as $person)
                                <option value="{{ $person->id }}" @selected( (string) old($slot)===(string) $person->id
                                    )
                                    >
                                    {{ $person->name }}
                                </option>
                                @endforeach

                            </select>

                        </div>

                    </div>

                    @endforeach

                </div>

                @endforeach

            </div>

        </section>


        <section class="adm-card adm-match-form-card">

            <div class="adm-player-section-title">
                <h3>Skor dan Pemakaian Kok</h3>
                <p>
                    Kosongkan seluruh skor jika pertandingan
                    belum selesai.
                </p>
            </div>

            <div class="adm-match-score-grid">

                @foreach([1, 2] as $set)

                <div class="adm-match-score-box">

                    <span class="adm-match-score-box__title">
                        SET {{ $set }}
                    </span>

                    <div class="adm-match-score-box__inputs">

                        <input type="number" name="set{{ $set }}_a" min="0" max="99" step="1" inputmode="numeric"
                            placeholder="A" aria-label="Set {{ $set }} Tim A" value="{{ old('set' . $set . '_a') }}">

                        <span>:</span>

                        <input type="number" name="set{{ $set }}_b" min="0" max="99" step="1" inputmode="numeric"
                            placeholder="B" aria-label="Set {{ $set }} Tim B" value="{{ old('set' . $set . '_b') }}">

                    </div>

                    <small>Tim A : Tim B</small>

                </div>

                @endforeach

                <div class="adm-match-score-box adm-match-score-box--cock">

                    <label for="match-cock" class="adm-match-score-box__title">
                        JUMLAH KOK
                    </label>

                    <input id="match-cock" type="number" name="shuttlecock_count" class="adm-input" min="0" max="65535"
                        step="1" inputmode="numeric" value="{{ old('shuttlecock_count', 0) }}" required>

                    <small>Pemakaian kok pertandingan</small>

                </div>

            </div>

            <div class="adm-match-preview" data-match-preview aria-live="polite">
                Hasil akan dihitung otomatis setelah skor diisi.
            </div>

            <div class="adm-pe-note">
                <p>
                    Hasil dihitung otomatis dari dua set.
                    Skor harus lengkap atau seluruhnya kosong.
                    Pertandingan pending dapat diedit setelah disimpan.
                </p>
            </div>

        </section>

        <div class="adm-match-form-actions">

            <a href="{{ route('admin.matches.index') }}" class="adm-btn adm-btn--secondary">
                Batal
            </a>

            <button type="submit" class="adm-btn adm-btn--primary">
                Simpan Pertandingan
            </button>

        </div>

    </form>

</div>

@endsection