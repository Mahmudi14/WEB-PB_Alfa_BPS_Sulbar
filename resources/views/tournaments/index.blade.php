@extends('layouts.app')
@section('title', 'Turnamen - PB Alfa')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}?v=2">
@endpush

@section('content')
<div class="tr-page">
    <header class="tr-hero">
        <div class="tr-hero__main">
            <span class="tr-eyebrow">PB ALFA / TOURNAMENT CENTER</span>
            <h1>TURNAMEN <span>PB ALFA.</span></h1>
            <p>Kompetisi, jadwal ronde, hasil pertandingan, dan klasemen PB Alfa BPS Sulawesi Barat.</p>
        </div>
        @if($canManage)
            <div class="tr-hero__actions">
                <a class="tr-btn tr-btn--accent" href="{{ route('tournaments.create') }}">+ Buat Turnamen</a>
            </div>
        @else
            <span class="tr-hero__decoration" aria-hidden="true">🏆</span>
        @endif
    </header>

    <section class="tr-board" aria-labelledby="tr-list-title">
        <div class="tr-board-head">
            <div>
                <span class="tr-eyebrow">OFFICIAL TOURNAMENTS</span>
                <h2 id="tr-list-title">Daftar Turnamen</h2>
            </div>
            <span class="tr-board-count">{{ $tournaments->total() }} turnamen</span>
        </div>

        @if($tournaments->isEmpty())
            <div class="tr-empty">
                <span class="tr-empty__icon" aria-hidden="true">🏆</span>
                <h2>Belum ada turnamen</h2>
                <p>Turnamen yang dibuat oleh admin atau pengelola akan muncul di sini.</p>
            </div>
        @else
            <div class="tr-list">
                @foreach($tournaments as $tournament)
                    <a href="{{ $tournament->status === 'draft' ? route('tournaments.entries', $tournament) : route('tournaments.show', $tournament) }}" class="tr-list-card">
                        <span class="tr-list-icon" aria-hidden="true">{{ $tournament->format === 'cup' ? '🏆' : '🏸' }}</span>
                        <div class="tr-list-body">
                            <div class="tr-mini">
                                <span>{{ $tournament->format === 'cup' ? 'CUP / SISTEM GUGUR' : 'LIGA / ROUND ROBIN' }}</span>
                                <span class="tr-badge tr-badge--{{ $tournament->status }}">
                                    {{ match($tournament->status) {'draft' => 'Draft', 'active' => 'Berlangsung', default => 'Selesai'} }}
                                </span>
                            </div>
                            <h2>{{ $tournament->name }}</h2>
                            <p>{{ ucfirst($tournament->match_type) }} · {{ $tournament->entries_count }} peserta · {{ $tournament->set_format_label }} · {{ $tournament->target_points }} poin</p>
                        </div>
                        <span class="tr-card-arrow" aria-hidden="true">›</span>
                    </a>
                @endforeach
            </div>
            <div class="tr-pagination">{{ $tournaments->links() }}</div>
        @endif
    </section>
</div>
@endsection
