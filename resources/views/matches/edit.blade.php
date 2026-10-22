@extends('layouts.app')

@section('title', 'Edit Pertandingan - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/matches.css') }}?v=7">
@endpush

@section('content')

@php
$user = auth()->user();
$canEdit = $match->editableBy($user);
$canDelete = $user?->canManage() ?? false;

$teamA = $match->participants
->where('team', 'a')
->sortBy('slot')
->map(fn ($p) => $p->display_name)
->implode(' / ');

$teamB = $match->participants
->where('team', 'b')
->sortBy('slot')
->map(fn ($p) => $p->display_name)
->implode(' / ');

$resultLabel = match ($match->result) {
'a' => 'Tim A Menang',
'b' => 'Tim B Menang',
'seri' => 'Seri',
default => 'Belum Ada Hasil',
};
@endphp

<div class="match-form-page">

    <header class="match-form-hero">
        <div>
            <span class="match-eyebrow">
                PB ALFA / MATCH CENTER
            </span>

            <h1>EDIT <span>PERTANDINGAN.</span></h1>
        </div>
    </header>

    <section class="match-form-card">

        <div class="match-form-card__heading">
            <div>
                <span class="match-eyebrow match-eyebrow--green">
                    MATCH MANAGEMENT
                </span>

                <h2>Data Pertandingan</h2>
            </div>

            <span class="match-form-card__tag">
                #{{ $match->id }}
            </span>
        </div>

        <div class="match-edit-info">

            <div>
                <small>Tanggal Pertandingan</small>
                <strong>
                    {{ $match->played_on
                    ->locale('id')
                    ->translatedFormat('d F Y') }}
                </strong>
            </div>

            <div>
                <small>Jenis Pertandingan</small>
                <strong>{{ ucfirst($match->type) }}</strong>
            </div>

            <div>
                <small>Tim A</small>
                <strong>{{ $teamA }}</strong>
            </div>

            <div>
                <small>Tim B</small>
                <strong>{{ $teamB }}</strong>
            </div>

        </div>

        @if(!$canEdit)
        <div class="match-edit-locked" role="status">
            <strong>Pengeditan terkunci</strong>
            <p>
                Batas waktu 24 jam telah berakhir.
                Skor dan jumlah kok tidak dapat diubah.
            </p>
        </div>
        @elseif(!$user->isAdmin())
        <div class="match-edit-notice">
            Batas pengeditan:
            <strong>
                {{ $match->created_at
                ->copy()
                ->addHours(24)
                ->locale('id')
                ->translatedFormat('d F Y H:i') }}
            </strong>
        </div>
        @else
        <div class="match-edit-notice">
            Akses Admin: pengeditan tanpa batas waktu.
        </div>
        @endif

        @if($errors->any())
        <div class="match-alert" role="alert">
            <strong>Periksa data pertandingan.</strong>
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('matches.update', $match) }}" class="match-form" data-single-submit>
            @csrf
            @method('PATCH')

            <section class="match-section">

                <div class="match-section__heading">
                    <h3>Skor Pertandingan</h3>
                </div>

                <div class="match-scoreboard">

                    <div class="match-scoreboard__set">
                        <label>SET 1</label>

                        <div class="match-scoreboard__inputs">
                            <input type="number" name="set1_a" aria-label="Skor Set 1 Tim A" min="0" max="99"
                                value="{{ old('set1_a', $match->set1_a) }}" placeholder="A" @disabled(!$canEdit)>

                            <span>:</span>

                            <input type="number" name="set1_b" aria-label="Skor Set 1 Tim B" min="0" max="99"
                                value="{{ old('set1_b', $match->set1_b) }}" placeholder="B" @disabled(!$canEdit)>
                        </div>

                        <small>Tim A : Tim B</small>
                    </div>

                    <div class="match-scoreboard__cock">
                        <label for="shuttlecock_count">
                            KOK
                        </label>

                        <input id="shuttlecock_count" type="number" name="shuttlecock_count" min="0" max="65535"
                            value="{{ old('shuttlecock_count', $match->shuttlecock_count) }}" required
                            @disabled(!$canEdit)>
                    </div>

                    <div class="match-scoreboard__set">
                        <label>SET 2</label>

                        <div class="match-scoreboard__inputs">
                            <input type="number" name="set2_a" aria-label="Skor Set 2 Tim A" min="0" max="99"
                                value="{{ old('set2_a', $match->set2_a) }}" placeholder="A" @disabled(!$canEdit)>

                            <span>:</span>

                            <input type="number" name="set2_b" aria-label="Skor Set 2 Tim B" min="0" max="99"
                                value="{{ old('set2_b', $match->set2_b) }}" placeholder="B" @disabled(!$canEdit)>
                        </div>

                        <small>Tim A : Tim B</small>
                    </div>

                </div>

                <div class="match-result-preview" aria-live="polite">
                    <span>Hasil Pertandingan</span>

                    <strong id="match-edit-result">
                        {{ $resultLabel }}
                    </strong>
                </div>

            </section>

            <div class="match-form__actions">

                @if($canEdit)
                <button type="submit" class="match-submit">
                    Simpan Perubahan
                </button>
                @endif

                <a href="{{ route('matches.index') }}" class="match-cancel">
                    Kembali
                </a>

            </div>

        </form>

        @if($canDelete)
        <div class="match-edit-danger">

            <div>
                <strong>Hapus Pertandingan</strong>
                <p>
                    Pertandingan yang dihapus tidak lagi
                    muncul di riwayat maupun perhitungan ranking.
                </p>
            </div>

            <form method="POST" action="{{ route('matches.destroy', $match) }}" data-single-submit
                data-confirm="Yakin ingin menghapus pertandingan ini?" data-confirm-title="Hapus Pertandingan">
                @csrf
                @method('DELETE')

                <button type="submit" class="match-edit-delete">
                    Hapus Pertandingan
                </button>

            </form>

        </div>
        @endif

    </section>

</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.match-form');
    const result = document.getElementById('match-edit-result');

    if (!form || !result) return;

    const names = ['set1_a', 'set1_b', 'set2_a', 'set2_b'];

    function updatePreview() {
        const inputs = names.map(
            name => form.elements.namedItem(name)
        );

        if (inputs.some(input => !input)) return;

        const values = inputs.map(input => input.value);

        if (values.every(value => value === '')) {
            result.textContent = 'Belum Ada Hasil';
            return;
        }

        if (
            values.some(value => value === '') ||
            inputs.some(input => !input.validity.valid)
        ) {
            result.textContent = 'Lengkapi semua skor';
            return;
        }

        const [a1, b1, a2, b2] = values.map(Number);

        if (a1 === b1 || a2 === b2) {
            result.textContent = 'Skor set tidak boleh seri';
            return;
        }

        const winsA = Number(a1 > b1) + Number(a2 > b2);

        result.textContent = winsA === 2
            ? 'Tim A Menang 2–0'
            : winsA === 0
                ? 'Tim B Menang 2–0'
                : 'Seri 1–1';
    }

    names.forEach(name => {
        form.elements.namedItem(name)
            ?.addEventListener('input', updatePreview);
    });
});
</script>
@endpush