@extends('layouts.admin')

@section('title', 'Turnamen - Admin PB Alfa')
@section('page_title', 'Turnamen')

@section('content')

<div class="adm-page adm-tr-page">

    <div class="adm-page__heading">
        <div>
            <span class="adm-eyebrow">TOURNAMENT MANAGEMENT</span>
            <h2>Data Turnamen</h2>
            <p>Kelola kompetisi Liga dan Cup PB Alfa.</p>
        </div>

        <a href="{{ route('admin.tournaments.create') }}" class="adm-btn adm-btn--primary">
            + Buat Turnamen
        </a>
    </div>

    <div class="adm-tr-stats">
        @foreach([
        ['label' => 'Total Turnamen', 'value' => $counts->total ?? 0],
        ['label' => 'Draft', 'value' => $counts->drafts ?? 0],
        ['label' => 'Berlangsung', 'value' => $counts->active ?? 0],
        ['label' => 'Selesai', 'value' => $counts->completed ?? 0],
        ] as $item)
        <div class="adm-card adm-tr-stat">
            <span>{{ $item['label'] }}</span>
            <strong>{{ number_format($item['value'], 0, ',', '.') }}</strong>
        </div>
        @endforeach
    </div>

    <section class="adm-card adm-tr-panel">

        <div class="adm-tr-panel__head">
            <div>
                <h3>Daftar Turnamen</h3>
                <p>{{ $tournaments->total() }} turnamen ditemukan</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.tournaments.index') }}" class="adm-tr-filters">

            <div class="adm-field">
                <label for="tr-search">Cari Turnamen</label>
                <input id="tr-search" type="search" name="cari" value="{{ $cari }}" maxlength="100" class="adm-input"
                    placeholder="Nama turnamen...">
            </div>

            <div class="adm-field">
                <label for="tr-format">Format</label>
                <div class="adm-select-shell">
                    <select id="tr-format" name="format" class="adm-select adm-premium-select"
                        data-admin-premium-select>
                        <option value="">Semua Format</option>
                        <option value="liga" @selected($filterFormat==='liga' )>Liga</option>
                        <option value="cup" @selected($filterFormat==='cup' )>Cup</option>
                    </select>
                </div>
            </div>

            <div class="adm-field">
                <label for="tr-status">Status</label>
                <div class="adm-select-shell">
                    <select id="tr-status" name="status" class="adm-select adm-premium-select"
                        data-admin-premium-select>
                        <option value="">Semua Status</option>
                        <option value="draft" @selected($filterStatus==='draft' )>Draft</option>
                        <option value="active" @selected($filterStatus==='active' )>Berlangsung</option>
                        <option value="completed" @selected($filterStatus==='completed' )>Selesai</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="adm-btn adm-btn--primary">
                Terapkan
            </button>

            <a href="{{ route('admin.tournaments.index') }}" class="adm-btn adm-btn--secondary">
                Reset
            </a>
        </form>

        <div class="adm-tr-list">
            @forelse($tournaments as $tournament)
            @php
            $statusLabel = match ($tournament->status) {
            'draft' => 'Draft',
            'active' => 'Berlangsung',
            'completed' => 'Selesai',
            default => 'Tidak diketahui',
            };
            $pill = match ($tournament->status) {
            'draft' => 'warning',
            'active' => 'info',
            'completed' => 'success',
            default => 'info',
            };
            @endphp

            <article class="adm-tr-list__item">

                <div class="adm-tr-list__identity">
                    <div class="adm-tr-emblem">
                        @include(
                        'admin.partials.nav-icon',
                        ['name' => 'trophy']
                        )
                    </div>

                    <div>
                        <div class="adm-tr-list__meta">
                            <span class="adm-tr-format">
                                {{ strtoupper($tournament->format) }}
                            </span>
                            <span class="adm-pill adm-pill--{{ $pill }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <h3>{{ $tournament->name }}</h3>

                        <p>
                            {{ $tournament->match_type === 'ganda'
                            ? 'Ganda' : 'Tunggal' }}
                            · {{ $tournament->set_format_label }}
                        </p>
                    </div>
                </div>

                <div class="adm-tr-list__counts">
                    <div>
                        <strong>{{ $tournament->entries_count }}</strong>
                        <span>Peserta</span>
                    </div>
                    <div>
                        <strong>{{ $tournament->fixtures_count }}</strong>
                        <span>Fixture</span>
                    </div>
                </div>

                <div class="adm-tr-list__actions">
                    <a href="{{ route('admin.tournaments.show', $tournament) }}" class="adm-btn adm-btn--primary">
                        Detail
                    </a>

                    @if($tournament->status === 'draft')
                    <a href="{{ route('admin.tournaments.rules.edit', $tournament) }}"
                        class="adm-btn adm-btn--secondary">
                        Atur
                    </a>
                    @endif
                </div>
            </article>

            @empty
            <div class="adm-empty">
                <strong>Belum ada turnamen.</strong>
                <p>Buat turnamen baru atau ubah filter pencarian.</p>
            </div>
            @endforelse
        </div>

        @if($tournaments->hasPages())
        <div class="adm-tr-pagination">
            {{ $tournaments->links() }}
        </div>
        @endif
    </section>

</div>

@endsection