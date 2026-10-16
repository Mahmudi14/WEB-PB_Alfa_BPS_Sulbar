
@extends('layouts.app')

@section('title', 'Tambah Transaksi - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cash.css') }}?v=1">
@endpush

@section('content')

<div class="cash-form-page">

    <header class="cash-form-hero">
        <span class="cash-eyebrow">
            PB ALFA / FINANCIAL MANAGEMENT
        </span>

        <h1>TAMBAH <span>TRANSAKSI.</span></h1>
    </header>

    <section class="cash-form-card">

        <div class="cash-form-card__heading">
            <div>
                <span class="cash-eyebrow cash-eyebrow--green">
                    CASH INFORMATION
                </span>

                <h2>Data Transaksi Baru</h2>
            </div>

            <span class="cash-form-card__tag">
                BARU
            </span>
        </div>

        @include('cash._form', [
            'transaction' => null
        ])

    </section>

</div>

@endsection
