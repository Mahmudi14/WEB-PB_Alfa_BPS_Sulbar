@extends('layouts.admin')

@section('title', 'Peserta Turnamen - Admin PB Alfa')
@section('page_title', 'Peserta Turnamen')

@section('content')

@php
$isDouble = $tournament->match_type === 'ganda';
$slotCount = $isDouble ? 2 : 1;

$existing = old('entries');

if (!is_array($existing)) {
$existing = $tournament->entries
->map(fn ($entry) => [
'players' => $entry->players
->pluck('id')
->all()
])
->all();
}

$existing = array_values($existing);
$visibleCount = min(32, max(2, count($existing)));
@endphp

<div class="adm-page adm-tr-page">

    <div class="adm-page__heading">
        <div>
            <span class="adm-eyebrow">
                TOURNAMENT SETUP / LANGKAH 2
            </span>

            <h2>Atur Peserta Turnamen</h2>

            <p>
                {{ $tournament->name }}
                · {{ $isDouble ? 'Ganda' : 'Tunggal' }}
            </p>
        </div>

        <a href="{{ route('admin.tournaments.rules.edit', $tournament) }}" class="adm-btn adm-btn--secondary">
            Ubah Aturan
        </a>
    </div>

    <form id="admin-tr-entries-form" method="POST" action="{{ route('admin.tournaments.entries.save', $tournament) }}"
        data-admin-tr-entries data-single-submit>

        @csrf
        @method('PUT')

        <section class="adm-card adm-tr-form-card">

            <div class="adm-tr-section-title">
                <span>02</span>

                <div>
                    <h3>Daftar Peserta</h3>
                    <p>
                        {{ $isDouble
                        ? 'Pilih dua pemain berbeda dalam setiap pasangan.'
                        : 'Pilih satu pemain untuk setiap peserta.' }}
                        Maksimal 32 peserta.
                    </p>
                </div>
            </div>

            <div class="adm-tr-entry-toolbar">
                <span>
                    <strong data-tr-entry-count>
                        {{ $visibleCount }}
                    </strong>
                    peserta
                </span>

                <button type="button" class="adm-btn adm-btn--secondary" data-tr-add-entry>
                    + Tambah Peserta
                </button>
            </div>

            <div class="adm-tr-entry-list">

                @for($i = 0; $i < 32; $i++) @php $visible=$i < $visibleCount; @endphp <div class="adm-tr-entry"
                    data-tr-entry @if(!$visible) hidden @endif>

                    <div class="adm-tr-entry__head">

                        <strong>
                            Peserta <span data-tr-entry-number>
                                {{ $i + 1 }}
                            </span>
                        </strong>

                        <button type="button" class="adm-tr-remove" data-tr-remove-entry>
                            Hapus
                        </button>

                    </div>

                    <div class="adm-tr-entry__fields">

                        @for($slot = 0; $slot < $slotCount; $slot++) <div class="adm-field">

                            <label for="entry-{{ $i }}-{{ $slot }}">
                                {{ $isDouble
                                ? 'Pemain ' . ($slot + 1)
                                : 'Pilih Pemain' }}
                            </label>

                            <div class="adm-select-shell">

                                <select id="entry-{{ $i }}-{{ $slot }}" name="entries[{{ $i }}][players][{{ $slot }}]"
                                    class="adm-select adm-premium-select" data-admin-premium-select data-tr-entry-player
                                    @disabled(!$visible)>

                                    <option value="">
                                        Pilih pemain
                                    </option>

                                    @foreach($players as $person)
                                    <option value="{{ $person->id }}" @selected( (string) data_get( $existing, $i
                                        . '.players.' . $slot, '' )===(string) $person->id
                                        )>
                                        {{ $person->name }}
                                    </option>
                                    @endforeach

                                </select>

                            </div>
                    </div>

                    @endfor
            </div>

</div>

@endfor

</div>

<p class="adm-tr-form-hint">
    Satu pemain tidak boleh muncul pada dua peserta
    yang berbeda. Pilihan akan diperiksa ulang
    ketika formulir disimpan.
</p>

</section>

<div class="adm-tr-form-actions">

    <a href="{{ route('admin.tournaments.rules.edit', $tournament) }}" class="adm-btn adm-btn--secondary">
        Kembali
    </a>

    <button type="submit" class="adm-btn adm-btn--primary">
        Simpan & Lihat Jadwal
    </button>
</div>
</form>
</div>

@endsection