@extends('layouts.app')

@section('title', 'Pratinjau Bagan - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}?v=5">
@endpush

@section('content')

@php
$isLiga = $tournament->format === 'liga';

$totalPeserta = count($order);
$totalRonde = count($plan);

$totalSlot = collect($plan)->sum(
fn ($round) => count($round)
);

$jumlahPertemuan = (int) ($tournament->league_legs ?? 1);

$poinMenang = (int) ($tournament->league_win_points ?? 3);
$poinSeri = (int) ($tournament->league_draw_points ?? 1);
$poinKalah = (int) ($tournament->league_loss_points ?? 0);

/*
* Data bagan Cup untuk tahap pratinjau.
* Sama dengan struktur yang digunakan
* pada halaman show.blade.php.
*/
$bracketRounds = [];

if (!$isLiga) {

$bracketRounds = collect($plan)
->map(function ($matches) use ($entryMap) {

return collect($matches)
->map(function ($match) use ($entryMap) {

$isBye = (bool) (
$match['bye'] ?? false
);

$aId = $match['a'] ?? null;
$bId = $match['b'] ?? null;

$a = $aId !== null
? $entryMap->get($aId)
: null;

$b = $bId !== null
? $entryMap->get($bId)
: null;

return [
'a' => $a?->display_name
?? ($isBye
? 'BYE'
: 'Menunggu pemenang'),

'b' => $b?->display_name
?? ($isBye
? 'BYE'
: 'Menunggu pemenang'),

'a_id' => $aId,
'b_id' => $bId,

'winner_id' => null,

'score' => '',

'status' => $isBye
? 'Lolos otomatis'
: 'Belum dimainkan',

'edit_url' => null,
];
})
->values()
->all();
})
->values()
->all();
}
@endphp


<div class="tr-page">

    {{-- =========================================
    HEADER HALAMAN
    ========================================= --}}
    <header class="tr-page-heading">

        <div class="tr-page-heading__main">

            <a href="{{ route('tournaments.entries', $tournament) }}" class="tr-back">
                ← Ubah Peserta
            </a>

            <span class="tr-eyebrow">
                PB ALFA / TOURNAMENT SETUP
            </span>

            <h1>
                {{ $isLiga ? 'JADWAL' : 'BAGAN' }}

                <span>
                    {{ $isLiga ? 'LIGA.' : 'CUP.' }}
                </span>
            </h1>

            <p>
                Langkah 3 dari 4 —
                Periksa susunan lawan dan seluruh
                ronde sebelum turnamen dimulai.
            </p>

        </div>

        <span class="tr-hero__decoration" aria-hidden="true">
            03
        </span>

    </header>


    {{-- =========================================
    PROGRESS WIZARD
    ========================================= --}}
    @include('tournaments._steps', [
    'step' => 3
    ])


    {{-- =========================================
    PESAN VALIDASI
    ========================================= --}}
    @if($errors->any())

    <div class="tr-alert" role="alert">

        <strong>
            Periksa kembali susunan pertandingan.
        </strong>

        <ul>
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>

    </div>

    @endif


    {{-- =========================================
    RINGKASAN TURNAMEN
    ========================================= --}}
    <section class="tr-summary" aria-label="Ringkasan jadwal turnamen">

        <div>

            <small>Format</small>

            <strong>
                {{ $isLiga ? 'Liga' : 'Cup' }}
            </strong>

        </div>

        <div>

            <small>Peserta</small>

            <strong>
                {{ $totalPeserta }}
            </strong>

        </div>

        <div>

            <small>Total Ronde</small>

            <strong>
                {{ $totalRonde }}
            </strong>

        </div>

        <div>

            @if($isLiga)

            <small>Pertemuan</small>

            <strong>
                {{ $jumlahPertemuan }} Kali
            </strong>

            @else

            <small>Target Poin</small>

            <strong>
                {{ $tournament->target_points }} Poin
            </strong>

            @endif

        </div>

    </section>


    {{-- =========================================
    PENGATURAN SUSUNAN PESERTA
    ========================================= --}}
    <section class="tr-panel" aria-labelledby="tr-order-title">

        <div class="tr-section-head">

            <div>

                <span class="tr-eyebrow">
                    PENGATURAN LAWAN
                </span>

                <h2 id="tr-order-title">
                    Susunan Peserta
                </h2>

            </div>

            <span class="tr-board-count">
                {{ $totalPeserta }} Peserta
            </span>

        </div>


        @if($isLiga)

        <p class="tr-hint">
            Atur urutan atau acak peserta
            untuk menyusun jadwal Liga.

            Setiap peserta akan bertemu
            {{ $jumlahPertemuan }} kali
            sesuai pengaturan turnamen.

            Seluruh ronde akan digenerate otomatis.
        </p>

        @else

        <p class="tr-hint">
            Tentukan posisi peserta untuk
            mengatur lawan pada bagan Cup.

            Gunakan Acak Peserta untuk
            mengundi posisi secara otomatis.

            Bagan akan membagi peserta menjadi
            Grup A dan Grup B yang bertemu
            pada Grand Final.

            Jika jumlah peserta tidak sesuai
            kapasitas bagan, sistem akan
            memberikan BYE secara otomatis.
        </p>

        @endif


        {{-- FORM PENGATURAN URUTAN --}}
        <form id="tr-preview-form" action="{{ route('tournaments.schedule', $tournament) }}" method="GET">

            <div class="tr-order-grid">

                @foreach($order as $i => $id)

                <div class="tr-field">

                    <label for="tr-order-{{ $i }}">
                        Posisi {{ $i + 1 }}
                    </label>

                    <select id="tr-order-{{ $i }}" name="order[]" data-order-select required>

                        @foreach($entryMap as $entry)

                        <option value="{{ $entry->id }}" @selected((int) $id===(int) $entry->id)>

                            {{ $entry->display_name }}

                        </option>

                        @endforeach

                    </select>

                </div>

                @endforeach

            </div>


            {{-- TOMBOL ACAK DAN PRATINJAU --}}
            <div class="tr-actions" style="margin-top: 15px;">

                <button type="button" id="tr-shuffle" class="tr-btn tr-btn--outline">

                    ↻ Acak Peserta

                </button>

                <button type="submit" class="tr-btn">

                    Perbarui Pratinjau

                </button>

            </div>


            {{-- ERROR PENGATURAN POSISI --}}
            <p id="tr-order-error" class="tr-hint tr-hint--error" role="alert" hidden>

                Peserta tidak boleh menempati
                lebih dari satu posisi.

            </p>

        </form>

    </section>


    {{-- =========================================
    INFORMASI ATURAN LIGA
    ========================================= --}}
    @if($isLiga)

    <section class="tr-panel" aria-labelledby="tr-league-rules-title">

        <div class="tr-section-head">

            <div>

                <span class="tr-eyebrow">
                    LEAGUE SETTINGS
                </span>

                <h2 id="tr-league-rules-title">
                    Aturan Klasemen
                </h2>

            </div>

        </div>


        <div class="tr-summary">

            <div>

                <small>Poin Menang</small>

                <strong>
                    {{ $poinMenang > 0 ? '+' : '' }}{{ $poinMenang }}
                </strong>

            </div>

            <div>

                <small>Poin Seri</small>

                <strong>
                    {{ $poinSeri > 0 ? '+' : '' }}{{ $poinSeri }}
                </strong>

            </div>

            <div>

                <small>Poin Kalah</small>

                <strong>
                    {{ $poinKalah > 0 ? '+' : '' }}{{ $poinKalah }}
                </strong>

            </div>

            <div>

                <small>Jumlah Pertemuan</small>

                <strong>
                    {{ $jumlahPertemuan }} Kali
                </strong>

            </div>

        </div>


        <p class="tr-hint" style="margin: 12px 0 0;">

            @if($jumlahPertemuan === 2)

            Liga menggunakan dua putaran.
            Pertemuan kedua membalik posisi
            Tim A dan Tim B.

            @else

            Liga menggunakan satu putaran.
            Setiap peserta bertemu satu kali.

            @endif

            Poin klasemen dihitung berdasarkan
            hasil pertandingan dan dapat
            bernilai negatif.

        </p>

    </section>

    @endif


    {{-- =========================================
    PRATINJAU SELURUH RONDE
    ========================================= --}}
    <section class="tr-panel tr-schedule-preview" aria-labelledby="tr-preview-title">

        <div class="tr-section-head">

            <div>

                <span class="tr-eyebrow">
                    PRATINJAU PERTANDINGAN
                </span>

                <h2 id="tr-preview-title">
                    {{ $isLiga
                    ? 'Seluruh Ronde'
                    : 'Bagan Pertandingan Cup' }}
                </h2>

            </div>

            <span class="tr-board-count">
                {{ $totalRonde }} Ronde
                · {{ $totalSlot }} Slot
            </span>

        </div>


        {{-- INFORMASI PRATINJAU --}}
        <p class="tr-hint">

            @if($isLiga)

            Seluruh jadwal Liga telah
            digenerate hingga ronde terakhir.

            Periksa setiap pertandingan
            sebelum memulai Liga.

            @else

            Berikut bagan pertandingan Cup
            dari Grup A dan Grup B menuju
            Grand Final.

            Pemenang setiap pertandingan
            akan otomatis maju ke ronde
            berikutnya.

            @endif

        </p>


        {{-- =====================================
        PRATINJAU LIGA
        ===================================== --}}
        @if($isLiga)

        <div class="tr-round-grid">

            @foreach($plan as $r => $matches)

            <div class="tr-preview-round">

                <h3>
                    Ronde {{ $r + 1 }}
                </h3>


                @forelse($matches as $m => $match)

                @php
                $a = $entryMap->get(
                $match['a']
                );

                $b = $entryMap->get(
                $match['b']
                );
                @endphp

                <article class="tr-preview-match">

                    <span class="tr-preview-no">
                        PERTANDINGAN {{ $m + 1 }}
                    </span>

                    <strong>
                        {{ $a?->display_name
                        ?? 'Menunggu peserta' }}
                    </strong>

                    <small>
                        VS
                    </small>

                    <strong>
                        {{ $b?->display_name
                        ?? 'Menunggu peserta' }}
                    </strong>

                </article>

                @empty

                <div class="tr-empty">
                    <strong>
                        Tidak ada pertandingan
                        pada ronde ini.
                    </strong>
                </div>

                @endforelse

            </div>

            @endforeach

        </div>


        {{-- =====================================
        PRATINJAU CUP KIRI-KANAN
        ===================================== --}}
        @else

        @if(count($bracketRounds) > 0)

        @include('tournaments._cup-bracket', [
        'bracketRounds' => $bracketRounds
        ])

        @else

        <div class="tr-empty">

            <strong>
                Bagan Cup belum tersedia.
            </strong>

        </div>

        @endif

        @endif

    </section>


    {{-- =========================================
    SELESAIKAN PEMBUATAN TURNAMEN
    ========================================= --}}
    <form id="tr-start-form" class="tr-finish" method="POST" action="{{ route('tournaments.start', $tournament) }}"
        data-single-submit data-confirm="Mulai turnamen dengan susunan peserta dan ronde ini?"
        data-confirm-title="Mulai Turnamen" data-confirm-text="Ya, Mulai">

        @csrf

        {{-- URUTAN PESERTA YANG TELAH DIPREVIEW --}}
        @foreach($order as $id)

        <input type="hidden" name="order[]" value="{{ $id }}">

        @endforeach


        <div>

            <strong>
                Susunan pertandingan sudah sesuai?
            </strong>

            <p>
                Setelah turnamen dimulai,
                aturan, peserta dan jadwal
                akan terkunci.

                Admin serta pengelola tetap dapat
                mengubah skor dan jumlah kok
                pertandingan kapan saja.
            </p>

        </div>


        <button class="tr-btn tr-btn--accent" type="submit">

            Selesaikan &amp; Mulai Turnamen →

        </button>

    </form>


    {{-- =========================================
    HAPUS TURNAMEN DRAFT
    ========================================= --}}
    <section class="tr-panel" aria-labelledby="tr-delete-draft-title">

        <div class="tr-section-head">

            <div>

                <span class="tr-eyebrow">
                    TOURNAMENT MANAGEMENT
                </span>

                <h2 id="tr-delete-draft-title">
                    Hapus Draft Turnamen
                </h2>

            </div>

        </div>

        <p class="tr-hint">
            Jika turnamen ini tidak jadi dibuat,
            admin dan pengelola dapat menghapusnya.

            Seluruh peserta dan data draft
            akan dihapus secara permanen.
        </p>


        <form method="POST" action="{{ route('tournaments.destroy', $tournament) }}" class="tr-delete-form"
            data-single-submit
            data-confirm="Hapus draft turnamen {{ $tournament->name }}? Seluruh data yang sudah disimpan akan dihapus permanen."
            data-confirm-title="Hapus Draft Turnamen" data-confirm-text="Ya, Hapus">

            @csrf
            @method('DELETE')

            <button type="submit" class="tr-delete-btn">

                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">

                    <path d="M3 6h18" />
                    <path d="M8 6V4h8v2" />
                    <path d="M5 6l1 15h12l1-15" />
                    <path d="M10 10v7M14 10v7" />

                </svg>

                Hapus Draft Turnamen

            </button>

        </form>

    </section>

</div>

@endsection


@push('scripts')
<script src="{{ asset('assets/js/tournaments.js') }}?v=5" defer>
</script>
@endpush