@extends('layouts.admin')

@section('title', 'Kelola Turnamen - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin-modules.css') }}?v=1">
@endpush

@section('content')
<div class="am-page">
    <div class="am-heading">
        <div>
            <span class="am-kicker">TOURNAMENT MANAGEMENT</span>
            <h2>Data Turnamen</h2>
            <p>Kelola Liga, Cup, penyusunan peserta, serta hasil pertandingan turnamen.</p>
        </div>
        <a class="am-btn am-btn--primary" href="{{ route('admin.tournaments.create') }}">+ Buat Turnamen</a>
    </div>

    <section class="am-card">
        <div class="am-card__top">
            <div>
                <h3>Daftar Turnamen</h3>
                <p>{{ $tournaments->total() }} turnamen</p>
            </div>
            <form method="GET" action="{{ route('admin.tournaments.index') }}" class="am-filter">
                <label class="am-sr-only" for="am-tournament-search">Nama turnamen</label>
                <input id="am-tournament-search" name="cari" type="search" value="{{ $cari }}" placeholder="Cari turnamen..." maxlength="100">
                <label class="am-sr-only" for="am-tournament-format">Format</label>
                <select id="am-tournament-format" name="format">
                    <option value="">Semua format</option>
                    <option value="liga" @selected($filterFormat === 'liga')>Liga</option>
                    <option value="cup" @selected($filterFormat === 'cup')>Cup</option>
                </select>
                <label class="am-sr-only" for="am-tournament-status">Status</label>
                <select id="am-tournament-status" name="status">
                    <option value="">Semua status</option>
                    <option value="draft" @selected($filterStatus === 'draft')>Draft</option>
                    <option value="active" @selected($filterStatus === 'active')>Aktif</option>
                    <option value="completed" @selected($filterStatus === 'completed')>Selesai</option>
                </select>
                <button type="submit" class="am-btn am-btn--small">Filter</button>
                <a href="{{ route('admin.tournaments.index') }}" class="am-btn am-btn--small">Reset</a>
            </form>
        </div>

        <div class="am-table-wrap">
            <table class="am-table">
                <thead><tr>
                    <th>Turnamen</th><th>Format</th><th>Jenis</th><th>Status</th>
                    <th>Dibuat</th><th class="am-table__actions">Aksi</th>
                </tr></thead>
                <tbody>
                @forelse($tournaments as $tournament)
                    <tr>
                        <td><strong>{{ $tournament->name }}</strong></td>
                        <td><span class="am-pill">{{ strtoupper($tournament->format) }}</span></td>
                        <td>{{ ucfirst($tournament->match_type) }}</td>
                        <td>
                            <span class="am-pill {{ $tournament->status === 'draft' ? 'am-pill--warning' : ($tournament->status === 'completed' ? 'am-pill--success' : 'am-pill--info') }}">
                                {{ match($tournament->status) { 'draft' => 'Draft', 'active' => 'Aktif', 'completed' => 'Selesai', default => $tournament->status } }}
                            </span>
                        </td>
                        <td>{{ $tournament->created_at?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            <div class="am-actions">
                                <a class="am-btn am-btn--small" href="{{ route('admin.tournaments.show', $tournament) }}">Detail</a>
                                @if($tournament->status === 'draft')
                                    <a class="am-btn am-btn--small" href="{{ route('admin.tournaments.rules.edit', $tournament) }}">Aturan</a>
                                @endif
                                <form method="POST" action="{{ route('admin.tournaments.destroy', $tournament) }}"
                                      data-single-submit data-confirm="Hapus turnamen {{ $tournament->name }} beserta seluruh hasilnya?">
                                    @csrf @method('DELETE')
                                    <button class="am-btn am-btn--danger am-btn--small" type="submit">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="am-empty">Belum ada turnamen sesuai filter.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($tournaments->hasPages())
            <div class="am-pagination">{{ $tournaments->links() }}</div>
        @endif
    </section>
</div>
@endsection
