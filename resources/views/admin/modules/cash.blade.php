@extends('layouts.admin')

@section('title', 'Kelola Keuangan - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin-modules.css') }}?v=1">
@endpush

@section('content')
<div class="am-page">
    <div class="am-heading">
        <div>
            <span class="am-kicker">FINANCIAL MANAGEMENT</span>
            <h2>Data Keuangan</h2>
            <p>Kelola catatan kas manual dan koreksi nominal kas kok otomatis.</p>
        </div>
        <a class="am-btn am-btn--primary" href="{{ route('admin.cash.create') }}">+ Tambah Transaksi</a>
    </div>

    <div class="am-finance">
        <article class="am-finance__card"><span>Saldo Kas</span>
            <strong class="{{ $balance < 0 ? 'am-negative' : '' }}">Rp {{ number_format($balance, 0, ',', '.') }}</strong>
        </article>
        <article class="am-finance__card"><span>Total Pemasukan</span>
            <strong class="am-positive">Rp {{ number_format($income, 0, ',', '.') }}</strong>
        </article>
        <article class="am-finance__card"><span>Total Pengeluaran</span>
            <strong>Rp {{ number_format($expense, 0, ',', '.') }}</strong>
        </article>
    </div>

    <section class="am-card">
        <div class="am-card__top">
            <div>
                <h3>Daftar Transaksi</h3>
                <p>{{ $transactions->total() }} transaksi</p>
            </div>
            <form method="GET" action="{{ route('admin.cash.index') }}" class="am-filter">
                <label for="am-per-page">Tampilkan</label>
                <select name="per_page" id="am-per-page">
                    @foreach([25,50,100] as $size)
                        <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }} data</option>
                    @endforeach
                </select>
                <button type="submit" class="am-btn am-btn--small">Terapkan</button>
            </form>
        </div>

        <div class="am-table-wrap">
            <table class="am-table">
                <thead><tr>
                    <th>Tanggal</th><th>Keterangan</th><th>Jenis</th><th>Nominal</th><th class="am-table__actions">Aksi</th>
                </tr></thead>
                <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                        <td>
                            <strong>{{ $transaction->description }}</strong>
                            @if($transaction->shuttlecock_player_id !== null)
                                <small class="am-subline">Catatan kok otomatis</small>
                            @endif
                        </td>
                        <td><span class="am-pill {{ $transaction->type === 'pemasukan' ? 'am-pill--success' : 'am-pill--warning' }}">{{ ucfirst($transaction->type) }}</span></td>
                        <td><strong>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</strong></td>
                        <td>
                            <div class="am-actions">
                                <a href="{{ route('admin.cash.edit', $transaction) }}" class="am-btn am-btn--small">Edit</a>
                                @if($transaction->shuttlecock_player_id === null)
                                <form method="POST" action="{{ route('admin.cash.destroy', $transaction) }}"
                                      data-single-submit data-confirm="Hapus transaksi ini?">
                                    @csrf @method('DELETE')
                                    <button class="am-btn am-btn--danger am-btn--small" type="submit">Hapus</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="am-empty">Belum ada transaksi kas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="am-pagination">{{ $transactions->links() }}</div>
        @endif
    </section>
</div>
@endsection
