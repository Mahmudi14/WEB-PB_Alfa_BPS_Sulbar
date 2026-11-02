@extends('layouts.admin')

@section('title', 'Kelola Pertandingan - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin-modules.css') }}?v=1">
@endpush

@section('content')
<div class="am-page">
    <div class="am-heading">
        <div>
            <span class="am-kicker">MATCH MANAGEMENT</span>
            <h2>Data Pertandingan</h2>
            <p>Pertandingan reguler termasuk pending, serta hasil Liga dan Cup yang sudah valid.</p>
        </div>
        <a class="am-btn am-btn--primary" href="{{ route('admin.matches.create') }}">+ Tambah Pertandingan</a>
    </div>

    <section class="am-card">
        <div class="am-card__top">
            <div>
                <h3>Riwayat Pertandingan</h3>
                <p>{{ $matches->total() }} pertandingan</p>
            </div>
            <form class="am-filter" method="GET" action="{{ route('admin.matches.index') }}">
                <label for="am-match-date">Tanggal</label>
                <input id="am-match-date" name="tanggal" type="date" value="{{ $tanggal ?? '' }}">
                <button class="am-btn am-btn--small" type="submit">Terapkan</button>
                @if($tanggal)
                    <a class="am-btn am-btn--small" href="{{ route('admin.matches.index') }}">Reset</a>
                @endif
            </form>
        </div>

        <div class="am-table-wrap">
            <table class="am-table">
                <thead><tr>
                    <th>Tanggal</th><th>Jenis</th><th>Tim A</th><th>Tim B</th>
                    <th>Skor Set</th><th>Status</th><th class="am-table__actions">Aksi</th>
                </tr></thead>
                <tbody>
                @forelse($matches as $match)
                    @php
                        $isTournament = in_array($match->history_source ?? 'reguler', ['liga','cup'], true);
                        $sideA = $match->participants->where('team', 'a')->sortBy('slot')->pluck('display_name')->implode(' / ');
                        $sideB = $match->participants->where('team', 'b')->sortBy('slot')->pluck('display_name')->implode(' / ');
                        $isPending = $match->result === null;
                        $score = $match->set1_a !== null && $match->set1_b !== null
                            ? $match->set1_a . ':' . $match->set1_b
                            : '—';
                        if ($match->set2_a !== null && $match->set2_b !== null) {
                            $score .= ' · ' . $match->set2_a . ':' . $match->set2_b;
                        }
                        if (($match->set3_a ?? null) !== null && ($match->set3_b ?? null) !== null) {
                            $score .= ' · ' . $match->set3_a . ':' . $match->set3_b;
                        }
                    @endphp
                    <tr>
                        <td>{{ $match->played_on->format('d/m/Y') }}</td>
                        <td>
                            <span class="am-pill">{{ $isTournament ? strtoupper($match->history_source) : 'Reguler' }}</span>
                            @if($isTournament)<small class="am-subline">{{ $match->tournament_name }}</small>@endif
                        </td>
                        <td><strong>{{ $sideA ?: '—' }}</strong></td>
                        <td><strong>{{ $sideB ?: '—' }}</strong></td>
                        <td>{{ $score }}</td>
                        <td>
                            <span class="am-pill {{ $isPending ? 'am-pill--warning' : 'am-pill--success' }}">
                                {{ $isPending ? 'Pending' : 'Selesai' }}
                            </span>
                        </td>
                        <td>
                            <div class="am-actions">
                                @if($isTournament)
                                    <a class="am-btn am-btn--small"
                                       href="{{ route('admin.tournaments.fixtures.edit', [$match->tournament_id, $match->fixture_id]) }}">Edit Skor</a>
                                @else
                                    <a class="am-btn am-btn--small" href="{{ route('admin.matches.edit', $match) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.matches.destroy', $match) }}"
                                          data-single-submit data-confirm="Hapus pertandingan reguler ini?">
                                        @csrf @method('DELETE')
                                        <button class="am-btn am-btn--danger am-btn--small" type="submit">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="am-empty">Tidak ada pertandingan pada filter ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($matches->hasPages())
            <div class="am-pagination">{{ $matches->links() }}</div>
        @endif
    </section>
</div>
@endsection
