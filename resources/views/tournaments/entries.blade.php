@extends('layouts.app')
@section('title', 'Atur Pemain - PB Alfa')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}?v=2">
@endpush

@section('content')
<div class="tr-page tr-page--narrow">
    <header class="tr-page-heading">
        <div class="tr-page-heading__main">
            <a class="tr-back" href="{{ route('tournaments.rules.edit', $tournament) }}">← Ubah Aturan</a>
            <span class="tr-eyebrow">PB ALFA / TOURNAMENT SETUP</span>
            <h1>ATUR <span>PEMAIN.</span></h1>
            <p>{{ $tournament->match_type === 'ganda' ? 'Tentukan dua pemain untuk setiap pasangan tetap.' : 'Pilih pemain yang mengikuti turnamen.' }} Minimal 2 dan maksimal 32 peserta.</p>
        </div>
        <span class="tr-hero__decoration" aria-hidden="true">02</span>
    </header>

    @include('tournaments._steps', ['step' => 2])

    <form method="POST" action="{{ route('tournaments.entries.save', $tournament) }}" class="tr-panel tr-form" id="tr-entries-form" data-single-submit data-type="{{ $tournament->match_type }}">
        @csrf @method('PUT')
        @if($errors->any())
            <div class="tr-alert" role="alert"><strong>Periksa peserta:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <div class="tr-form-section-title">
            <div><span class="tr-eyebrow">DAFTAR PESERTA</span><h2>Peserta {{ $tournament->match_type === 'ganda' ? 'Ganda' : 'Tunggal' }}</h2></div>
            <small class="tr-hint">Setiap pemain hanya boleh dipilih sekali.</small>
        </div>

        @php
            $existing = old('entries');
            if ($existing === null) {
                $existing = $tournament->entries->map(fn ($entry) => ['players' => $entry->players->pluck('id')->all()])->all();
            }
            if (count($existing) === 0) $existing = array_fill(0, 2, ['players' => []]);
        @endphp
        <div id="tr-entry-rows" class="tr-entry-rows">
            @foreach($existing as $i => $entry)
                <div class="tr-entry" data-entry-row>
                    <div class="tr-entry-heading">
                        <strong>Peserta <span data-entry-label>{{ $i + 1 }}</span></strong>
                        <button type="button" class="tr-text-btn" data-entry-remove>Hapus</button>
                    </div>
                    <div class="tr-entry-selects">
                        <div class="tr-field">
                            <label for="tr-player-{{ $i }}-0">{{ $tournament->match_type === 'ganda' ? 'Pemain 1' : 'Pemain' }}</label>
                            <select id="tr-player-{{ $i }}-0" name="entries[{{ $i }}][players][0]" required>
                                <option value="">Pilih pemain</option>
                                @foreach($players as $player)
                                    <option value="{{ $player->id }}" @selected((string)($entry['players'][0] ?? '') === (string)$player->id)>{{ $player->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($tournament->match_type === 'ganda')
                            <div class="tr-field">
                                <label for="tr-player-{{ $i }}-1">Pemain 2</label>
                                <select id="tr-player-{{ $i }}-1" name="entries[{{ $i }}][players][1]" required>
                                    <option value="">Pilih pemain</option>
                                    @foreach($players as $player)
                                        <option value="{{ $player->id }}" @selected((string)($entry['players'][1] ?? '') === (string)$player->id)>{{ $player->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <template id="tr-entry-template">
            <div class="tr-entry" data-entry-row>
                <div class="tr-entry-heading">
                    <strong>Peserta <span data-entry-label></span></strong>
                    <button type="button" class="tr-text-btn" data-entry-remove>Hapus</button>
                </div>
                <div class="tr-entry-selects">
                    <div class="tr-field">
                        <label>{{ $tournament->match_type === 'ganda' ? 'Pemain 1' : 'Pemain' }}</label>
                        <select data-player-slot="0" required>
                            <option value="">Pilih pemain</option>
                            @foreach($players as $player)<option value="{{ $player->id }}">{{ $player->name }}</option>@endforeach
                        </select>
                    </div>
                    @if($tournament->match_type === 'ganda')
                        <div class="tr-field">
                            <label>Pemain 2</label>
                            <select data-player-slot="1" required>
                                <option value="">Pilih pemain</option>
                                @foreach($players as $player)<option value="{{ $player->id }}">{{ $player->name }}</option>@endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>
        </template>

        <div class="tr-actions">
            <button type="button" class="tr-btn tr-btn--outline" id="tr-add-entry">+ Tambah Peserta</button>
        </div>
        <p id="tr-entry-error" class="tr-hint tr-hint--error" role="alert" hidden></p>

        <div class="tr-form-footer">
            <a class="tr-btn tr-btn--muted" href="{{ route('tournaments.index') }}">Simpan Nanti</a>
            <button class="tr-btn" type="submit">Berikutnya: Lihat Bagan →</button>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script src="{{ asset('assets/js/tournaments.js') }}?v=1" defer></script>
@endpush
