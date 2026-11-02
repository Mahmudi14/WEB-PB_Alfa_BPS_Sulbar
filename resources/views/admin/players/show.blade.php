@extends('layouts.admin')

@section('title', 'Detail Pemain - Admin PB Alfa')
@section('page_title', 'Detail Pemain')

@section('content')

@php
$user = $player->user;
$isActive = !$player->trashed();

$isProtected = $user
&& (
$user->role === 'admin'
|| (int) $user->id === (int) auth()->id()
);

$roleLabel = match ($user?->role) {
'admin' => 'Admin',
'pengelola' => 'Pengelola',
'pemain' => 'Pemain',
default => 'Tanpa Akun',
};
@endphp

<div class="adm-page adm-player-detail">

    <div class="adm-page__heading">

        <div>

            <span class="adm-eyebrow">
                PLAYER MANAGEMENT / DETAIL
            </span>

            <h2>Detail Pemain</h2>

            <p>
                Informasi profil, statistik pertandingan,
                dan status pemain.
            </p>

        </div>

        <div class="adm-player-page-actions">

            <a href="{{ route('admin.players.index') }}" class="adm-btn adm-btn--secondary">
                Kembali
            </a>

            @if($isActive)

            <a href="{{ route('admin.players.edit', $player->id) }}" class="adm-btn adm-btn--primary">
                Edit Pemain
            </a>

            @endif

        </div>

    </div>


    <section class="adm-card adm-player-detail__hero">

        <div class="adm-player-detail__identity">

            <span class="adm-player-detail__avatar">
                {{ mb_strtoupper(
                mb_substr($player->name, 0, 1)
                ) }}
            </span>

            <div>

                <h3>{{ $player->name }}</h3>

                <p>
                    {{ $player->email ?: 'Belum memiliki email' }}
                </p>

                <div class="adm-player-detail__badges">

                    <span class="adm-pill {{ $isActive
                        ? 'adm-pill--success'
                        : 'adm-pill--danger' }}">
                        {{ $isActive ? 'Aktif' : 'Nonaktif' }}
                    </span>

                    <span class="adm-player-role-tag adm-player-role-tag--{{ $user?->role ?? 'tanpa-akun' }}">
                        {{ $roleLabel }}
                    </span>

                </div>

            </div>

        </div>

    </section>


    <div class="adm-player-detail__grid">

        <section class="adm-card">

            <div class="adm-player-section-title">
                <h3>Informasi Pemain</h3>
                <p>Data identitas yang tersimpan.</p>
            </div>

            <dl class="adm-player-detail__list">

                <div>
                    <dt>Nama Lengkap</dt>
                    <dd>{{ $player->name }}</dd>
                </div>

                <div>
                    <dt>Email</dt>
                    <dd>{{ $player->email ?: 'Belum diisi' }}</dd>
                </div>

                <div>
                    <dt>Jenis Kelamin</dt>
                    <dd>
                        {{ $player->gender === 'P'
                        ? 'Perempuan'
                        : 'Laki-laki' }}
                    </dd>
                </div>

                <div>
                    <dt>Skor Awal</dt>
                    <dd>
                        {{ number_format(
                        $player->initial_score,
                        0, ',', '.'
                        ) }}
                    </dd>
                </div>

                <div>
                    <dt>Role</dt>
                    <dd>{{ $roleLabel }}</dd>
                </div>

                <div>
                    <dt>Akses Login</dt>
                    <dd>
                        @if(!$user)
                        Tanpa Akun
                        @elseif($user->is_active)
                        Aktif
                        @else
                        Nonaktif
                        @endif
                    </dd>
                </div>

            </dl>

        </section>


        <section class="adm-card">

            <div class="adm-player-section-title">
                <h3>Statistik Pertandingan</h3>
                <p>
                    Statistik dari pertandingan
                    yang memenuhi aturan ranking.
                </p>
            </div>

            <div class="adm-player-detail__stats">

                <div>
                    <span>Ranking</span>
                    <strong>
                        {{ $isActive && $stats?->rank_position !== null
                        ? '#' . $stats->rank_position
                        : '—' }}
                    </strong>
                </div>

                <div>
                    <span>Skor Total</span>
                    <strong>
                        {{ $isActive && $stats
                        ? number_format(
                        $stats->total_score,
                        0, ',', '.'
                        )
                        : '—' }}
                    </strong>
                </div>

                <div>
                    <span>Menang</span>
                    <strong>
                        {{ $isActive && $stats
                        ? $stats->wins
                        : '—' }}
                    </strong>
                </div>

                <div>
                    <span>Seri</span>
                    <strong>
                        {{ $isActive && $stats
                        ? $stats->draws
                        : '—' }}
                    </strong>
                </div>

                <div>
                    <span>Kalah</span>
                    <strong>
                        {{ $isActive && $stats
                        ? $stats->losses
                        : '—' }}
                    </strong>
                </div>

                <div>
                    <span>Total Bermain</span>
                    <strong>
                        {{ $isActive && $stats
                        ? $stats->played_count
                        : '—' }}
                    </strong>
                </div>

            </div>

            @unless($isActive)
            <p class="adm-player-detail__note">
                Pemain nonaktif tidak tercantum dalam
                ranking aktif. Riwayat pertandingan tetap
                tersimpan dan dapat dihitung kembali
                setelah pemain diaktifkan.
            </p>
            @endunless

        </section>

    </div>


    <section class="adm-card adm-player-detail__status">

        <div>

            <span class="adm-eyebrow">
                PLAYER STATUS
            </span>

            <h3>Pengaturan Status Pemain</h3>

            <p>
                {{ $isActive
                ? 'Menonaktifkan pemain akan mengeluarkannya dari daftar ranking aktif dan menonaktifkan akses login
                terkait.'
                : 'Mengaktifkan kembali pemain akan memulihkan profilnya tanpa menghapus riwayat pertandingan
                sebelumnya.' }}
            </p>

            @if($isProtected)
            <p class="adm-player-detail__protected">
                Akun administrator dilindungi dan tidak
                dapat dinonaktifkan dari halaman ini.
            </p>
            @endif

        </div>

        @unless($isProtected)

        <form method="POST" action="{{ route('admin.players.status', $player->id) }}" data-single-submit data-confirm="{{ $isActive
                    ? 'Yakin ingin menonaktifkan pemain ' . $player->name . '?'
                    : 'Yakin ingin mengaktifkan kembali pemain ' . $player->name . '?' }}" data-confirm-title="{{ $isActive
                    ? 'Nonaktifkan Pemain'
                    : 'Aktifkan Pemain' }}">

            @csrf
            @method('PATCH')

            <input type="hidden" name="status" value="{{ $isActive ? 'nonaktif' : 'aktif' }}">

            <button type="submit" class="adm-btn {{ $isActive
                        ? 'adm-btn--danger'
                        : 'adm-btn--primary' }}">
                {{ $isActive
                ? 'Nonaktifkan Pemain'
                : 'Aktifkan Kembali' }}
            </button>

        </form>

        @endunless

    </section>

    @if($isActive && $user && !$user->is_active && $player->email && !$isProtected)

    <section class="adm-card adm-player-detail__status">

        <div>
            <h3>Akses Login Nonaktif</h3>

            <p>
                Profil pemain masih aktif, tetapi akun
                login belum diaktifkan. Aktifkan login
                tanpa mengubah status profil.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.players.status', $player->id) }}" data-single-submit
            data-confirm="Aktifkan akses login {{ $player->name }}?">
            @csrf
            @method('PATCH')

            <input type="hidden" name="status" value="aktif">

            <button type="submit" class="adm-btn adm-btn--primary">
                Aktifkan Login
            </button>

        </form>

    </section>

    @endif

</div>

@endsection