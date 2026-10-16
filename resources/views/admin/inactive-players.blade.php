
@extends('layouts.app')

@section('title', 'Pemain Nonaktif - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}?v=1">
@endpush

@section('content')

<div class="adm-page">

    <header class="adm-hero">
        <div>
            <span class="adm-eyebrow">
                PB ALFA / PLAYER MANAGEMENT
            </span>

            <h1>PEMAIN <span>NONAKTIF.</span></h1>

            <p>Kelola pemulihan data pemain PB Alfa.</p>
        </div>

        <span class="adm-hero__decoration" aria-hidden="true">
            05
        </span>
    </header>

    {{-- Daftar pemain --}}

    <section class="adm-section">

        <div class="adm-section__heading">
            <div>
                <span class="adm-eyebrow adm-eyebrow--green">
                    INACTIVE PLAYER RECORDS
                </span>

                <h2>Daftar Pemain Nonaktif</h2>
            </div>

            <span class="adm-count">
                {{ $players->total() }} pemain
            </span>
        </div>

        <div class="adm-inactive-list">

            @forelse($players as $player)

                <article class="adm-inactive-row">

                    <div class="adm-inactive-row__identity">

                        <span class="adm-user-avatar"
                              aria-hidden="true">
                            {{ mb_strtoupper(
                                mb_substr($player->name, 0, 1)
                            ) }}
                        </span>

                        <div class="adm-user-row__info">

                            <strong>{{ $player->name }}</strong>

                            <span>
                                {{ $player->email ?? 'Tanpa email' }}
                            </span>

                        </div>

                    </div>

                    {{-- Status --}}

                    <div class="adm-inactive-row__status">

                        <span class="adm-status adm-status--inactive">
                            Nonaktif
                        </span>

                        <small>
                            {{ $player->deleted_at->format('d/m/Y H:i') }}
                        </small>

                    </div>

                    {{-- Aktifkan --}}

                    <form
                        method="POST"
                        action="{{ route('admin.players.restore', $player->id) }}"
                        data-single-submit
                        data-confirm="Aktifkan kembali pemain {{ $player->name }}?"
                        class="adm-restore-form"
                    >
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="adm-button adm-button--success"
                        >
                            Aktifkan Kembali
                        </button>

                    </form>

                </article>

            @empty

                <div class="adm-empty">
                    Tidak ada pemain nonaktif.
                </div>

            @endforelse

        </div>

        {{-- Pagination --}}

        @if($players->hasPages())
            <nav class="adm-pagination"
                 aria-label="Halaman pemain nonaktif">

                @if($players->onFirstPage())
                    <span class="is-disabled">
                        ← Sebelumnya
                    </span>
                @else
                    <a href="{{ $players->previousPageUrl() }}">
                        ← Sebelumnya
                    </a>
                @endif

                <span class="adm-pagination__info">
                    {{ $players->currentPage() }}
                    / {{ $players->lastPage() }}
                </span>

                @if($players->hasMorePages())
                    <a href="{{ $players->nextPageUrl() }}">
                        Berikutnya →
                    </a>
                @else
                    <span class="is-disabled">
                        Berikutnya →
                    </span>
                @endif

            </nav>
        @endif

    </section>

</div>

@endsection
