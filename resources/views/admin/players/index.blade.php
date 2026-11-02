@extends('layouts.admin')

@section('title', 'Data Pemain - Admin PB Alfa')
@section('page_title', 'Data Pemain')

@section('content')

<div class="adm-page adm-players">

    <div class="adm-page__heading">

        <div>
            <span class="adm-eyebrow">
                PLAYER MANAGEMENT
            </span>

            <h2>Data Pemain</h2>

            <p>
                Kelola informasi pemain, ranking,
                dan hak akses melalui satu halaman.
            </p>
        </div>

        <a href="{{ route('admin.players.create') }}" class="adm-btn adm-btn--primary">
            + Tambah Pemain
        </a>

    </div>


    <section class="adm-card adm-player-panel adm-player-list" data-admin-filter-scope>

        <div class="adm-player-list__head">

            <div>
                <h3>Daftar Pemain</h3>

                <p>
                    <strong data-admin-filter-count>
                        {{ $players->count() }}
                    </strong>
                    dari {{ $players->count() }} pemain
                </p>
            </div>

            <div class="adm-player-list__summary">

                <span>
                    <i class="adm-player-dot adm-player-dot--active"></i>
                    {{ $activeCount }} Aktif
                </span>

                <span>
                    <i class="adm-player-dot adm-player-dot--inactive"></i>
                    {{ $inactiveCount }} Nonaktif
                </span>

            </div>

        </div>


        <div class="adm-player-filters">

            <div class="adm-player-search">

                <label for="player-search">
                    Cari Pemain
                </label>

                <div class="adm-player-search__control">

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-4-4" />
                    </svg>

                    <input id="player-search" type="search" data-admin-search placeholder="Cari nama atau email..."
                        autocomplete="off">

                </div>

            </div>


            <div class="adm-player-filter">

                <label for="player-filter-role">
                    Role
                </label>

                <div class="adm-select-shell">

                    <select id="player-filter-role" class="adm-select adm-premium-select" data-admin-premium-select
                        data-admin-filter="role">
                        <option value="">Semua Role</option>
                        <option value="admin">Admin</option>
                        <option value="pengelola">Pengelola</option>
                        <option value="pemain">Pemain</option>
                        <option value="tanpa-akun">Tanpa Akun</option>
                    </select>

                </div>

            </div>


            <div class="adm-player-filter">

                <label for="player-filter-status">
                    Status
                </label>

                <div class="adm-select-shell">

                    <select id="player-filter-status" class="adm-select adm-premium-select" data-admin-premium-select
                        data-admin-filter="status">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>

                </div>

            </div>

        </div>


        <div class="adm-table-wrap adm-player-table-wrap">

            <table class="adm-table adm-player-table">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Ranking</th>
                        <th>Skor Total</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($players as $player)

                    @php
                    $role = $player->user?->role
                    ?? 'tanpa-akun';

                    $isActive = !$player->trashed();

                    $roleLabel = match ($role) {
                    'admin' => 'Admin',
                    'pengelola' => 'Pengelola',
                    'pemain' => 'Pemain',
                    default => 'Tanpa Akun',
                    };
                    @endphp

                    <tr data-admin-row
                        data-search="{{ $player->name }} {{ $player->email }} {{ $roleLabel }} {{ $isActive ? 'aktif' : 'nonaktif' }}"
                        data-role="{{ $role }}" data-status="{{ $isActive ? 'aktif' : 'nonaktif' }}">

                        <td class="adm-player-list__number">
                            {{ $loop->iteration }}
                        </td>


                        <td>

                            <div class="adm-player-name">

                                <span class="adm-player-name__avatar">
                                    {{ mb_strtoupper(
                                    mb_substr($player->name, 0, 1)
                                    ) }}
                                </span>

                                <div>

                                    <strong>
                                        {{ $player->name }}
                                    </strong>

                                    @unless($isActive)

                                    <small class="adm-player-list__inactive">
                                        Nonaktif
                                    </small>

                                    @endunless

                                </div>

                            </div>

                        </td>


                        <td>
                            <span class="adm-player-email">
                                {{ $player->email ?: '—' }}
                            </span>
                        </td>


                        <td>

                            @if($isActive && $player->rank_position !== null)

                            <span class="adm-player-rank">
                                #{{ $player->rank_position }}
                            </span>

                            @else
                            <span class="adm-player-list__muted">—</span>
                            @endif

                        </td>


                        <td class="adm-player-number">

                            @if($isActive && $player->total_score !== null)

                            <strong>
                                {{ number_format(
                                $player->total_score,
                                0, ',', '.'
                                ) }}
                            </strong>

                            @else
                            <span class="adm-player-list__muted">—</span>
                            @endif

                        </td>


                        <td>

                            <span class="adm-player-role-tag adm-player-role-tag--{{ $role }}">
                                {{ $roleLabel }}
                            </span>

                        </td>


                        <td>

                            <div class="adm-player-actions">

                                <a href="{{ route('admin.players.show', $player->id) }}"
                                    class="adm-player-action adm-player-action--detail">
                                    Detail
                                </a>

                                @if($isActive)

                                <a href="{{ route('admin.players.edit', $player->id) }}"
                                    class="adm-player-action adm-player-action--edit">
                                    Edit
                                </a>

                                @endif

                            </div>

                        </td>

                    </tr>

                    @empty

                    <tr>
                        <td colspan="7" class="adm-empty">
                            Belum ada data pemain.
                        </td>
                    </tr>

                    @endforelse

                    @if($players->isNotEmpty())

                    <tr data-admin-empty hidden>
                        <td colspan="7" class="adm-empty">
                            Tidak ada pemain yang cocok
                            dengan pencarian atau filter.
                        </td>
                    </tr>

                    @endif

                </tbody>

            </table>

        </div>


        <div class="adm-player-panel__footer">

            <p>
                Ranking dan skor total menggunakan perhitungan
                resmi PB Alfa. Data pemain nonaktif
                tetap tersimpan.
            </p>

        </div>

    </section>

</div>

@endsection