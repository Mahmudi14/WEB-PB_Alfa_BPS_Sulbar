@extends('layouts.admin')

@section('title', 'Edit Pertandingan - Admin PB Alfa')
@section('page_title', 'Edit Pertandingan')

@section('content')

@php
$teamA = $match->participants
->where('team', 'a')
->sortBy('slot')
->pluck('display_name')
->implode(' / ');

$teamB = $match->participants
->where('team', 'b')
->sortBy('slot')
->pluck('display_name')
->implode(' / ');
@endphp

<div class="adm-page adm-match-page">

    <div class="adm-page__heading">
        <div>
            <span class="adm-eyebrow">
                MATCH MANAGEMENT / EDIT REGULER
            </span>

            <h2>Edit Pertandingan</h2>

            <p>
                Perbarui skor dan jumlah kok
                tanpa mengubah peserta atau tanggal.
            </p>
        </div>

        <a href="{{ route('admin.matches.show', $match) }}" class="adm-btn adm-btn--secondary">
            Kembali ke Detail
        </a>
    </div>


    <section class="adm-card adm-match-form-card">

        <div class="adm-match-locked-info">

            <div>
                <span>Jenis</span>

                <strong>
                    {{ $match->type === 'ganda'
                    ? 'Ganda'
                    : 'Tunggal' }}
                </strong>
            </div>

            <div>
                <span>Tanggal Bermain</span>

                <strong>
                    {{ $match->played_on?->translatedFormat('d F Y') ?? '—' }}
                </strong>
            </div>

        </div>

        <div class="adm-match-teams">

            <div class="adm-match-team">
                <div class="adm-match-team__heading">
                    Tim A
                </div>

                <div class="adm-match-team__players">
                    {{ $teamA ?: 'Menunggu' }}
                </div>
            </div>

            <div class="adm-match-team">
                <div class="adm-match-team__heading">
                    Tim B
                </div>

                <div class="adm-match-team__players">
                    {{ $teamB ?: 'Menunggu' }}
                </div>
            </div>

        </div>

    </section>


    <form id="admin-match-edit" method="POST" action="{{ route('admin.matches.update', $match) }}" data-single-submit
        data-admin-match-form>
        @csrf
        @method('PATCH')

        <section class="adm-card adm-match-form-card">

            <div class="adm-player-section-title">
                <h3>Skor dan Jumlah Kok</h3>
                <p>
                    Lengkapi kedua set atau kosongkan
                    semuanya untuk status pending.
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
                            placeholder="A" aria-label="Set {{ $set }} Tim A" value="{{ old(
                                    'set' . $set . '_a',
                                    $match->{'set' . $set . '_a'}
                                ) }}">

                        <span>:</span>

                        <input type="number" name="set{{ $set }}_b" min="0" max="99" step="1" inputmode="numeric"
                            placeholder="B" aria-label="Set {{ $set }} Tim B" value="{{ old(
                                    'set' . $set . '_b',
                                    $match->{'set' . $set . '_b'}
                                ) }}">

                    </div>

                    <small>Tim A : Tim B</small>

                </div>

                @endforeach

                <div class="adm-match-score-box adm-match-score-box--cock">

                    <label for="edit-match-cock" class="adm-match-score-box__title">
                        JUMLAH KOK
                    </label>

                    <input id="edit-match-cock" type="number" name="shuttlecock_count" class="adm-input" min="0"
                        max="65535" step="1" inputmode="numeric" value="{{ old(
                            'shuttlecock_count',
                            $match->shuttlecock_count
                        ) }}" required>

                    <small>Pemakaian kok pertandingan</small>

                </div>

            </div>

            <div class="adm-match-preview" data-match-preview aria-live="polite">
                Hasil pertandingan
            </div>

            <div class="adm-match-form-actions">

                <a href="{{ route('admin.matches.show', $match) }}" class="adm-btn adm-btn--secondary">
                    Batal
                </a>

                <button type="submit" class="adm-btn adm-btn--primary">
                    Simpan Perubahan
                </button>

            </div>

        </section>

    </form>


    <section class="adm-card adm-match-danger-zone">

        <div>
            <h3>Hapus Pertandingan</h3>

            <p>
                Penghapusan akan mengeluarkan pertandingan
                dari riwayat aktif, ranking, dan perhitungan
                kas kok. Data tetap tersimpan sebagai soft delete.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.matches.destroy', $match) }}" data-single-submit
            data-confirm="Yakin ingin menghapus pertandingan reguler ini?" data-confirm-title="Hapus Pertandingan"
            data-confirm-text="Ya, Hapus">
            @csrf
            @method('DELETE')

            <button type="submit" class="adm-btn adm-btn--danger">
                Hapus Pertandingan
            </button>
        </form>

    </section>

</div>

@endsection