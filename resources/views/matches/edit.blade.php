@extends('layouts.app')

@section('title', 'Edit Skor - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/matches.css') }}?v=8">
@endpush

@section('content')

@php
$user = auth()->user();

$canEdit = $match->editableBy($user);

$canDelete = $user
&& $user->is_active
&& $user->canManage();

$playersA = $match->participants
->where('team', 'a')
->sortBy('slot');

$playersB = $match->participants
->where('team', 'b')
->sortBy('slot');
@endphp

<div class="match-form-page match-simple-edit">

    {{-- HEADER --}}
    <header class="match-form-hero match-simple-hero">

        <a href="{{ route('matches.index') }}" class="match-simple-back">
            ← Kembali ke Pertandingan
        </a>

        <span class="match-eyebrow">
            PB ALFA / MATCH CENTER
        </span>

        <h1>
            EDIT <span>SKOR.</span>
        </h1>

    </header>


    {{-- FORM --}}
    <section class="match-form-card">

        @if($errors->any())

        <div class="match-alert" role="alert">

            <strong>
                Periksa skor pertandingan.
            </strong>

            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>

        @endif


        {{-- STATUS TERKUNCI --}}
        @if(!$canEdit)

        <div class="match-simple-locked" role="status">
            <strong>
                Pengeditan terkunci.
            </strong>
            Batas edit 24 jam telah berakhir.
        </div>

        @endif


        <form method="POST" action="{{ route('matches.update', $match) }}" class="match-form" data-single-submit>

            @csrf
            @method('PATCH')


            {{-- =========================
            TIM A DAN TIM B
            ========================= --}}
            <section class="match-section">

                <div class="match-teams">

                    {{-- TIM A --}}
                    <div class="match-team-column">

                        <div class="match-team-column__heading">
                            Tim A
                        </div>

                        @forelse($playersA as $player)

                        <div class="match-simple-player">
                            {{ $player->display_name }}
                        </div>

                        @empty

                        <div class="match-simple-player">
                            Menunggu
                        </div>

                        @endforelse

                    </div>


                    {{-- TIM B --}}
                    <div class="match-team-column">

                        <div class="match-team-column__heading
                            match-team-column__heading--b">
                            Tim B
                        </div>

                        @forelse($playersB as $player)

                        <div class="match-simple-player">
                            {{ $player->display_name }}
                        </div>

                        @empty

                        <div class="match-simple-player">
                            Menunggu
                        </div>

                        @endforelse

                    </div>

                </div>

            </section>


            {{-- =========================
            SKOR PERTANDINGAN
            ========================= --}}
            <section class="match-section">

                <div class="match-section__heading">
                    <h3>
                        Skor Pertandingan
                    </h3>
                </div>


                <div class="match-scoreboard">

                    {{-- SET 1 --}}
                    <div class="match-scoreboard__set">

                        <label>SET 1</label>

                        <div class="match-scoreboard__inputs">

                            <input type="number" name="set1_a" min="0" max="99" step="1" inputmode="numeric"
                                aria-label="Set 1 Tim A" placeholder="A" value="{{ old(
                                    'set1_a',
                                    $match->set1_a
                                ) }}" @disabled(!$canEdit)>

                            <span>:</span>

                            <input type="number" name="set1_b" min="0" max="99" step="1" inputmode="numeric"
                                aria-label="Set 1 Tim B" placeholder="B" value="{{ old(
                                    'set1_b',
                                    $match->set1_b
                                ) }}" @disabled(!$canEdit)>

                        </div>

                        <small>
                            Tim A : Tim B
                        </small>

                    </div>


                    {{-- KOK --}}
                    <div class="match-scoreboard__cock">

                        <label for="shuttlecock_count">
                            KOK
                        </label>

                        <input id="shuttlecock_count" type="number" name="shuttlecock_count" min="0" max="65535"
                            step="1" inputmode="numeric" required value="{{ old(
                                'shuttlecock_count',
                                $match->shuttlecock_count
                            ) }}" @disabled(!$canEdit)>

                    </div>


                    {{-- SET 2 --}}
                    <div class="match-scoreboard__set">

                        <label>SET 2</label>

                        <div class="match-scoreboard__inputs">

                            <input type="number" name="set2_a" min="0" max="99" step="1" inputmode="numeric"
                                aria-label="Set 2 Tim A" placeholder="A" value="{{ old(
                                    'set2_a',
                                    $match->set2_a
                                ) }}" @disabled(!$canEdit)>

                            <span>:</span>

                            <input type="number" name="set2_b" min="0" max="99" step="1" inputmode="numeric"
                                aria-label="Set 2 Tim B" placeholder="B" value="{{ old(
                                    'set2_b',
                                    $match->set2_b
                                ) }}" @disabled(!$canEdit)>

                        </div>

                        <small>
                            Tim A : Tim B
                        </small>

                    </div>

                </div>

            </section>


            {{-- =========================
            TOMBOL SIMPAN
            ========================= --}}
            <div class="match-form__actions">

                @if($canEdit)

                <button type="submit" class="match-submit">
                    Simpan Perubahan
                </button>

                @endif

                <a href="{{ route('matches.index') }}" class="match-cancel">
                    Batal
                </a>

            </div>

        </form>


        {{-- =========================
        HAPUS PERTANDINGAN
        KHUSUS ADMIN / PENGELOLA
        ========================= --}}
        @if($canDelete)

        <details class="match-simple-danger">

            <summary>
                Hapus Pertandingan
            </summary>

            <form method="POST" action="{{ route('matches.destroy', $match) }}" data-single-submit
                data-confirm="Yakin ingin menghapus pertandingan ini secara permanen?"
                data-confirm-title="Hapus Pertandingan" data-confirm-text="Ya, Hapus">

                @csrf
                @method('DELETE')

                <button type="submit" class="match-edit-delete">
                    Hapus Pertandingan
                </button>

            </form>

        </details>

        @endif

    </section>

</div>

@endsection