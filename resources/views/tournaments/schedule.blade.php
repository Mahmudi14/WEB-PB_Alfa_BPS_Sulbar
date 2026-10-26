@extends('layouts.app')

@section('title', 'Pratinjau Bagan - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}?v=3">
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
@endphp

<div class="tr-page">

    {{-- HEADER --}}
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
                Langkah 3 dari 4 — Periksa susunan lawan
                dan seluruh ronde sebelum turnamen dimulai.
            </p>

        </div>

        <span class="tr-hero__decoration" aria-hidden="true">
            03
        </span>

    </header>


    {{-- PROGRESS WIZARD --}}
    @include('tournaments._steps', [
    'step' => 3
    ])


    {{-- VALIDASI --}}
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


    {{-- RINGKASAN --}}
    <section class="tr-summary" aria-label="Ringkasan jadwal turnamen">

        <div>
            <small>Format</small>
            <strong>
                {{ $isLiga ? 'Liga' : 'Cup' }}
            </strong>
        </div>

        <div>
            <small>Peserta</small>
            <strong>{{ $totalPeserta }}</strong>
        </div>

        <div>
            <small>Total Ronde</small>
            <strong>{{ $totalRonde }}</strong>
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
                {{ $tournament->target_points }}
            </strong>

            @endif
        </div>

    </section>


    {{-- ATUR SUSUNAN LAWAN --}}
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
            Ubah urutan atau acak peserta untuk
            menyusun jadwal Liga.

            Setiap peserta akan bertemu
            {{ $jumlahPertemuan }} kali
            sesuai pengaturan turnamen.
        </p>

        @else

        <p class="tr-hint">
            Atur posisi peserta untuk menentukan
            siapa melawan siapa pada bagan Cup.

            Gunakan tombol Acak Peserta untuk
            melakukan pengundian otomatis.

            Jika jumlah peserta tidak sesuai
            kapasitas bagan, sistem akan
            memberikan BYE secara otomatis.
        </p>

        @endif


        {{-- FORM URUTAN PESERTA --}}
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


            {{-- TOMBOL PENGATURAN --}}
            <div class="tr-actions" style="margin-top: 15px;">

                <button type="button" id="tr-shuffle" class="tr-btn tr-btn--outline">

                    ↻ Acak Peserta

                </button>

                <button type="submit" class="tr-btn">

                    Perbarui Pratinjau

                </button>

            </div>


            <p id="tr-order-error" class="tr-hint tr-hint--error" role="alert" hidden>

                Peserta tidak boleh menempati
                lebih dari satu posisi.

            </p>

        </form>

    </section>


    {{-- INFORMASI ATURAN LIGA --}}
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


    {{-- PRATINJAU SELURUH RONDE --}}
    <section class="tr-panel tr-schedule-preview" aria-labelledby="tr-preview-title">

        <div class="tr-section-head">

            <div>

                <span class="tr-eyebrow">
                    PRATINJAU PERTANDINGAN
                </span>

                <h2 id="tr-preview-title">
                    Seluruh Ronde
                </h2>

            </div>

            <span class="tr-board-count">

                {{ $totalRonde }} Ronde
                · {{ $totalSlot }} Slot

            </span>

        </div>


        <p class="tr-hint">

            @if($isLiga)

            Seluruh jadwal Liga telah
            digenerate hingga ronde terakhir.

            Kamu dapat memeriksa setiap
            pertandingan sebelum memulai Liga.

            @else

            Berikut pratinjau bagan Cup dari
            ronde awal hingga final.

            Peserta yang menang akan otomatis
            maju ke babak berikutnya.

            @endif

        </p>


        <div class="tr-round-grid">

            @foreach($plan as $r => $matches)

            <div class="tr-preview-round">

                {{-- JUDUL RONDE --}}
                <h3>

                    @if(!$isLiga && $r === $totalRonde - 1)

                    Final

                    @else

                    Ronde {{ $r + 1 }}

                    @endif

                </h3>


                {{-- PERTANDINGAN --}}
                @foreach($matches as $m => $match)

                @php
                $a = $entryMap->get(
                $match['a']
                );

                $b = $entryMap->get(
                $match['b']
                );

                $isBye = (bool) (
                $match['bye'] ?? false
                );

                $placeholder = (
                !$isLiga && $r > 0
                )
                ? 'Menunggu pemenang'
                : '—';

                $namaA = $a?->display_name
                ?? ($isBye ? 'BYE' : $placeholder);

                $namaB = $b?->display_name
                ?? ($isBye ? 'BYE' : $placeholder);
                @endphp


                <article class="tr-preview-match">

                    <span class="tr-preview-no">
                        PERTANDINGAN {{ $m + 1 }}
                    </span>


                    {{-- PESERTA A --}}
                    <strong>
                        {{ $namaA }}
                    </strong>


                    {{-- VERSUS --}}
                    <small>
                        {{ $isBye ? 'LOLOS OTOMATIS' : 'VS' }}
                    </small>


                    {{-- PESERTA B --}}
                    <strong>
                        {{ $namaB }}
                    </strong>

                </article>

                @endforeach

            </div>

            @endforeach

        </div>

    </section>


    {{-- SELESAIKAN PEMBUATAN --}}
    <form id="tr-start-form" class="tr-finish" method="POST" action="{{ route('tournaments.start', $tournament) }}"
        data-single-submit>

        @csrf

        {{-- KIRIM URUTAN PESERTA YANG SUDAH DIPREVIEW --}}
        @foreach($order as $id)

        <input type="hidden" name="order[]" value="{{ $id }}">

        @endforeach


        <div>

            <strong>
                Susunan pertandingan sudah sesuai?
            </strong>

            <p>
                Setelah turnamen dimulai,
                aturan, peserta, dan jadwal terkunci.

                Admin serta pengelola tetap
                dapat mengubah skor dan
                jumlah kok pertandingan.
            </p>

        </div>


        <button class="tr-btn tr-btn--accent" type="submit"
            data-confirm="Mulai turnamen dengan susunan peserta dan ronde ini?" data-confirm-title="Mulai Turnamen">

            Selesaikan &amp; Mulai Turnamen →

        </button>

    </form>

</div>

@endsection


@push('scripts')
<script src="{{ asset('assets/js/tournaments.js') }}?v=3" defer>
</script>
@endpush