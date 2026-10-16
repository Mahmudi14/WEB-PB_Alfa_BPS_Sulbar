
@extends('layouts.app')

@section('title', 'Tambah Pertandingan - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/matches.css') }}?v=3">
@endpush

@section('content')

<div class="match-form-page">

    <header class="match-form-hero">

        <span class="match-eyebrow">
            PB ALFA / MATCH CENTER
        </span>

        <h1>TAMBAH <span>PERTANDINGAN.</span></h1>

    </header>

    <section class="match-form-card">

        <div class="match-form-card__heading">
            <div>
                <span class="match-eyebrow match-eyebrow--green">
                    MATCH INFORMATION
                </span>

                <h2>Data Pertandingan</h2>
            </div>

            <span class="match-form-card__tag">BARU</span>
        </div>

        @include('matches._form', [
            'players' => $players,
            'selected' => $selected ?? []
        ])

    </section>

</div>

@endsection
