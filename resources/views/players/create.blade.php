
@extends('layouts.app')

@section('title', 'Tambah Pemain - PB Alfa')

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

        <h1>TAMBAH <span>PEMAIN.</span></h1>

        <p>
            Daftarkan pemain baru ke dalam sistem PB Alfa.
        </p>

    </header>

    <section class="player-form-card">

        <div class="player-form-card__heading">
            <div>
                <span class="player-eyebrow player-eyebrow--green">
                    PLAYER INFORMATION
                </span>

                <h2>Data Pemain Baru</h2>
            </div>

            <span class="player-form-card__tag">
                PEMAIN BARU
            </span>
        </div>

        @include('players._form', [
            'player' => null
        ])

    </section>

</div>

@endsection
