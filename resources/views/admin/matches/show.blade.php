@extends('layouts.admin')

@section('title', 'Detail Pertandingan - Admin PB Alfa')
@section('page_title', 'Detail Pertandingan')

@section('content')

@php
$pending = $detail['result'] === null;

$resultLabel = match ($detail['result']) {
'a' => 'Tim A Menang',
'b' => 'Tim B Menang',
'seri' => 'Seri',
default => 'Belum Ada Hasil',
};
@endphp

<div class="adm-page adm-match-page">

    <div class="adm-page__heading">
        <div>
            <span class="adm-eyebrow">
                MATCH MANAGEMENT / DETAIL
            </span>

            <h2>Detail Pertandingan</h2>

            <p>
                Informasi hasil dan peserta pertandingan.
            </p>
        </div>

        <div class="adm-player-page-actions">
            <a href="{{ route('admin.matches.index') }}" class="adm-btn adm-btn--secondary">
                Kembali
            </a>

            <a href="{{ $detail['edit_url'] }}" class="adm-btn adm-btn--primary">
                Edit Skor & Kok
            </a>
        </div>
    </div>

    <section class="adm-card adm-match-detail">

        <div class="adm-match-detail__meta">
            <div>
                <span class="adm-match-source adm-match-source--{{ $detail['source'] }}">
                    {{ $detail['source_label'] }}
                </span>

                <span>
                    {{ $detail['type_label'] }}
                </span>

                <span>
                    {{ $detail['date']?->translatedFormat('d F Y') ?? '—' }}
                </span>
            </div>

            <span class="adm-pill {{ $pending
                ? 'adm-pill--warning'
                : 'adm-pill--success' }}">
                {{ $pending ? 'Pending' : 'Selesai' }}
            </span>
        </div>

        @if($detail['tournament_name'])
        <p class="adm-match-detail__tournament">
            {{ $detail['tournament_name'] }}
        </p>
        @endif

        <div class="adm-match-versus">

            <div class="adm-match-versus__team">
                <span>TIM A</span>

                <h3>
                    {{ implode(' / ', $detail['team_a']) ?: 'Menunggu' }}
                </h3>
            </div>

            <div class="adm-match-versus__center">
                VS
            </div>

            <div class="adm-match-versus__team">
                <span>TIM B</span>

                <h3>
                    {{ implode(' / ', $detail['team_b']) ?: 'Menunggu' }}
                </h3>
            </div>

        </div>

        <div class="adm-match-detail__result">
            <span>Hasil Pertandingan</span>
            <strong>{{ $resultLabel }}</strong>
        </div>

    </section>


    <div class="adm-match-detail-grid">

        <section class="adm-card adm-match-section-card">

            <div class="adm-player-section-title">
                <h3>Skor Pertandingan</h3>
                <p>Skor tersimpan setiap set.</p>
            </div>

            <div class="adm-match-scores">

                @foreach($detail['scores'] as $index => $score)

                <div class="adm-match-score-line">
                    <span>Set {{ $index + 1 }}</span>

                    <strong>
                        {{ $score[0] !== null && $score[1] !== null
                        ? $score[0] . ' : ' . $score[1]
                        : '—' }}
                    </strong>
                </div>

                @endforeach

            </div>

            @if($pending)
            <p class="adm-match-detail__hint">
                Pertandingan belum selesai.
                Skor dapat dilengkapi melalui halaman Edit.
            </p>
            @endif

        </section>


        <section class="adm-card adm-match-section-card">

            <div class="adm-player-section-title">
                <h3>Informasi Kok</h3>
                <p>Pemakaian kok pertandingan.</p>
            </div>

            <div class="adm-match-cock-total">
                <strong>
                    {{ number_format(
                    $detail['shuttlecock_count'] ?? 0,
                    0, ',', '.'
                    ) }}
                </strong>

                <span>Kok</span>
            </div>

            <p class="adm-match-detail__hint">
                Perhitungan kas kok dilakukan oleh sistem
                berdasarkan pertandingan yang memenuhi
                ketentuan hasil valid.
            </p>

        </section>

    </div>

</div>

@endsection