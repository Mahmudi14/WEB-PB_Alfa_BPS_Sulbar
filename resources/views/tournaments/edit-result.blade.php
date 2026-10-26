@extends('layouts.app')
@section('title', 'Edit Hasil - '.$tournament->name)
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}?v=2">
@endpush

@section('content')
<div class="tr-page tr-page--narrow">
    <header class="tr-page-heading">
        <div class="tr-page-heading__main">
            <a class="tr-back" href="{{ route('tournaments.show', $tournament) }}?ronde={{ $fixture->round_number }}">← Kembali ke Turnamen</a>
            <span class="tr-eyebrow">PB ALFA / RONDE {{ $fixture->round_number }}</span>
            <h1>EDIT <span>HASIL.</span></h1>
            <p>Ubah skor setiap set dan jumlah kok. Admin serta pengelola tidak dibatasi waktu edit.</p>
        </div>
        <span class="tr-hero__decoration" aria-hidden="true">VS</span>
    </header>

    <div class="tr-match tr-edit-match">
        <div class="tr-match-sides">
            <div class="tr-team"><small>TIM A</small><strong>{{ $fixture->entryA->display_name }}</strong></div>
            <span class="tr-versus">VS</span>
            <div class="tr-team tr-team--b"><small>TIM B</small><strong>{{ $fixture->entryB->display_name }}</strong></div>
        </div>
    </div>

    <form class="tr-panel tr-form" method="POST" action="{{ route('tournaments.fixtures.update', [$tournament, $fixture]) }}" data-single-submit>
        @csrf @method('PATCH')
        @if($errors->any())
            <div class="tr-alert" role="alert"><strong>Hasil belum disimpan:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <div class="tr-section-head" style="margin-bottom:0">
            <div><span class="tr-eyebrow">MATCH MANAGEMENT</span><h2>Skor Pertandingan</h2></div>
            <span class="tr-board-count">#{{ $fixture->id }}</span>
        </div>
        <div class="tr-result-meta">
            <strong>{{ $tournament->set_format_label }}</strong>
            <span>Target {{ $tournament->target_points }} poin per set</span>
        </div>

        @php $max = $tournament->set_format === 'best_of_three' ? 3 : 2; @endphp
        @for($i = 0; $i < $max; $i++)
            @php $set = $fixture->sets->firstWhere('set_number', $i + 1); @endphp
            <div class="tr-score-set">
                <strong>SET {{ $i + 1 }}</strong>
                <div class="tr-score-inputs">
                    <div class="tr-field">
                        <label for="a-score-{{ $i }}">Tim A</label>
                        <input type="number" id="a-score-{{ $i }}" name="scores[{{ $i }}][a]" min="0" max="999" inputmode="numeric" placeholder="—" value="{{ old('scores.'.$i.'.a', $set?->a_score) }}">
                    </div>
                    <span class="tr-score-colon">:</span>
                    <div class="tr-field">
                        <label for="b-score-{{ $i }}">Tim B</label>
                        <input type="number" id="b-score-{{ $i }}" name="scores[{{ $i }}][b]" min="0" max="999" inputmode="numeric" placeholder="—" value="{{ old('scores.'.$i.'.b', $set?->b_score) }}">
                    </div>
                </div>
            </div>
        @endfor

        @if($tournament->set_format === 'two_aggregate')
            <div class="tr-score-set tr-jus-inputs">
                <strong>JUS AKUMULASI</strong>
                <p class="tr-hint">Isi hanya bila jumlah poin kedua set sama. Selisih akhir jus harus 2 poin.</p>
                <div class="tr-score-inputs">
                    <div class="tr-field">
                        <label for="jus-a">Jus Tim A</label>
                        <input type="number" min="0" max="999" id="jus-a" name="jus_a" value="{{ old('jus_a', $fixture->jus_a) }}" placeholder="—">
                    </div>
                    <span class="tr-score-colon">:</span>
                    <div class="tr-field">
                        <label for="jus-b">Jus Tim B</label>
                        <input type="number" min="0" max="999" id="jus-b" name="jus_b" value="{{ old('jus_b', $fixture->jus_b) }}" placeholder="—">
                    </div>
                </div>
            </div>
        @endif

        <div class="tr-field tr-field--kok">
            <label for="tr-kok">Jumlah kok yang digunakan</label>
            <input id="tr-kok" name="shuttlecock_count" type="number" min="0" max="65535" required value="{{ old('shuttlecock_count', $fixture->shuttlecock_count) }}">
        </div>

        @if($tournament->format === 'cup' && $fixture->next_fixture_id)
            <label class="tr-checkbox">
                <input type="checkbox" name="confirm_reset" value="1" @checked(old('confirm_reset'))>
                <span>Jika pemenang berubah, saya setuju hasil pertandingan lanjutan yang terdampak dikosongkan supaya bagan konsisten.</span>
            </label>
        @endif
        <p class="tr-hint">Untuk mengosongkan hasil, hapus semua skor set. Jumlah kok masih dapat disimpan. Set selesai ketika mencapai target dengan selisih minimal 2 poin.</p>

        <div class="tr-form-footer">
            <a class="tr-btn tr-btn--muted" href="{{ route('tournaments.show', $tournament) }}?ronde={{ $fixture->round_number }}">Batal</a>
            <button class="tr-btn" type="submit">Simpan Hasil</button>
        </div>
    </form>
</div>
@endsection
