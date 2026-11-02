@extends('layouts.admin')

@section('title', 'Kelola Pemain - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin-modules.css') }}?v=1">
@endpush

@section('content')
<div class="am-page">
    <div class="am-heading">
        <div>
            <span class="am-kicker">PLAYER MANAGEMENT</span>
            <h2>Data Pemain</h2>
            <p>Kelola anggota, akun terkait, dan pantau hasil peringkat resmi.</p>
        </div>
        <a class="am-btn am-btn--primary" href="{{ route('admin.players.create') }}">+ Tambah Pemain</a>
    </div>

    <section class="am-card">
        <div class="am-card__top">
            <div>
                <h3>Daftar Pemain</h3>
                <p>{{ $players->count() }} pemain aktif</p>
            </div>
            <label class="am-search">
                <span class="am-sr-only">Cari nama pemain</span>
                <input type="search" id="am-player-search" placeholder="Cari nama pemain..." autocomplete="off">
            </label>
        </div>

        <div class="am-table-wrap">
            <table class="am-table" id="am-player-table">
                <thead><tr>
                    <th>Peringkat</th><th>Nama Pemain</th><th>Jenis Kelamin</th>
                    <th>Menang / Seri / Kalah</th><th>Skor</th><th class="am-table__actions">Aksi</th>
                </tr></thead>
                <tbody>
                    @forelse($players as $player)
                    @php
                        $isOwn = (int) ($player->user_id ?? 0) === (int) auth()->id();
                    @endphp
                    <tr data-am-player="{{ mb_strtolower($player->name) }}">
                        <td><span class="am-rank">#{{ $player->rank_position }}</span></td>
                        <td>
                            <strong>{{ $player->name }}</strong>
                            <small class="am-subline">{{ $player->email ?: 'Belum mempunyai email' }}</small>
                        </td>
                        <td>{{ $player->gender === 'P' ? 'Putri' : 'Putra' }}</td>
                        <td>{{ $player->wins }} / {{ $player->draws }} / {{ $player->losses }}</td>
                        <td><strong>{{ $player->total_score }}</strong></td>
                        <td>
                            <div class="am-actions">
                                <a href="{{ route('admin.players.edit', $player) }}" class="am-btn am-btn--small">Edit</a>
                                @unless($isOwn)
                                <form method="POST" action="{{ route('admin.players.destroy', $player) }}"
                                      data-single-submit data-confirm="Nonaktifkan pemain {{ $player->name }}?">
                                    @csrf @method('DELETE')
                                    <button class="am-btn am-btn--danger am-btn--small" type="submit">Nonaktifkan</button>
                                </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="am-empty">Belum ada pemain.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p id="am-player-empty" class="am-empty" hidden>Tidak ada pemain yang cocok dengan pencarian.</p>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('am-player-search');
    const rows = [...document.querySelectorAll('[data-am-player]')];
    const empty = document.getElementById('am-player-empty');
    if (!input) return;
    input.addEventListener('input', () => {
        const query = input.value.trim().toLocaleLowerCase('id-ID');
        let count = 0;
        rows.forEach(row => {
            row.hidden = !row.dataset.amPlayer.includes(query);
            if (!row.hidden) count++;
        });
        empty.hidden = count > 0 || rows.length === 0;
    });
});
</script>
@endpush
