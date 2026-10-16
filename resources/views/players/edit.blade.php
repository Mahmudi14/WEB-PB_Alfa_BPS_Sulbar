
@extends('layouts.app')

@section('title', 'Edit Pemain - PB Alfa')

@push('styles')
<link rel="stylesheet"
      href="{{ asset('assets/css/players.css') }}?v=2">
@endpush

@section('content')

<div class="player-form-page">

    <header class="player-form-hero">

        <span class="player-eyebrow">
            PB ALFA / PLAYER MANAGEMENT
        </span>

        <h1>EDIT <span>PEMAIN.</span></h1>

        <p>
            Perbarui informasi pemain yang terdaftar.
        </p>

    </header>

    <section class="player-form-card">

        <div class="player-form-card__heading">
            <div>
                <span class="player-eyebrow player-eyebrow--green">
                    PLAYER INFORMATION
                </span>

                <h2>{{ $player->name }}</h2>
            </div>

            <span class="player-form-card__tag">
                EDIT DATA
            </span>
        </div>

        @include('players._form', [
            'player' => $player
        ])

    </section>

</div>

@endsection
