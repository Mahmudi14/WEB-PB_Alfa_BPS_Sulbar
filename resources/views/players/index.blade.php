
@extends('layouts.app')

@section('title', 'Ranking Pemain - PB Alfa')

@push('styles')
<link rel="stylesheet"
      href="{{ asset('assets/css/players.css') }}?v=2">
@endpush

@section('content')

@php
    $viewer = auth()->user();
    $canManage = $viewer?->canManage() ?? false;
    $isAdmin = $viewer?->role === 'admin';
@endphp

<div class="player-page">

    <header class="player-hero">
        <div>
            <span class="player-eyebrow">
                PB ALFA / PLAYER RANKING
            </span>

            <h1>RANKING <span>PEMAIN.</span></h1>

            <p>
                Menang +30 · Seri +10 · Kalah -30
            </p>
        </div>

        <span class="player-hero__decoration" aria-hidden="true">
            #1
        </span>
    </header>

    {{-- Pencarian --}}

    <div class="player-toolbar">

        <div class="player-search">
            <label for="rank-live-input" class="player-sr-only">
                Cari pemain
            </label>

            <input
                id="rank-live-input"
                type="search"
                placeholder="Ketik nama pemain..."
                autocomplete="off"
                spellcheck="false"
            >

            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <circle cx="11" cy="11" r="7"/>
                <path d="m20 20-4-4"/>
            </svg>
        </div>

        @if($canManage)
            <a
                href="{{ route('players.create') }}"
                class="player-add"
            >
                <span aria-hidden="true">+</span>
                Tambah Pemain
            </a>
        @endif

    </div>

    {{-- Ranking --}}

    <section class="player-board">

        <div class="player-board__heading">
            <div>
                <span class="player-eyebrow player-eyebrow--green">
                    OFFICIAL PLAYER RANKING
                </span>

                <h2>Daftar Pemain</h2>
            </div>

            <span class="player-board__count"
                  id="rank-result-count">
                {{ $players->count() }} pemain
            </span>
        </div>

        <div class="player-list" id="rank-player-list">

            @foreach($players as $player)

                @php
                    $position = (int) $player->rank_position;

                    $rankClass = match ($position) {
                        1 => 'player-row--gold',
                        2 => 'player-row--silver',
                        3 => 'player-row--bronze',
                        default => ''
                    };

                    $linkedRole = $player->user?->role;

                    $canEdit = $canManage && (
                        $isAdmin ||
                        (
                            !in_array(
                                $linkedRole,
                                ['admin', 'pengelola'],
                                true
                            ) &&
                            (int) $player->user_id !== (int) $viewer?->id
                        )
                    );

                    $canDelete = $canEdit &&
                        (int) $player->user_id !== (int) $viewer?->id;

                    $nameClass = mb_strlen($player->name) > 28
                        ? 'player-row--long-name'
                        : '';
                @endphp

                <article
                    class="player-row {{ $rankClass }} {{ $nameClass }} {{ ($canEdit || $canDelete) ? 'player-row--actions' : '' }}"
                    data-player-name="{{ $player->name }}"
                    aria-label="Peringkat {{ $position }}, {{ $player->name }}, skor {{ $player->total_score }}"
                >

                    {{-- Peringkat --}}

                    <span class="player-row__rank">
                        {{ $position }}
                    </span>

                    {{-- Avatar --}}

                    <span class="player-avatar {{ $player->gender === 'P' ? 'player-avatar--female' : 'player-avatar--male' }}"
                          aria-label="{{ $player->gender === 'P' ? 'Perempuan' : 'Laki-laki' }}"
                          role="img">

                        @if($player->gender === 'P')

                            <svg viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.7"
                                 stroke-linecap="round"
                                 stroke-linejoin="round"
                                 aria-hidden="true">
                                <path d="M6 11V8a6 6 0 0 1 12 0v3"/>
                                <circle cx="12" cy="9" r="4"/>
                                <path d="M5 21c0-4 3-7 7-7s7 3 7 7"/>
                                <path d="M6 11c-1 2-1 4-2 5M18 11c1 2 1 4 2 5"/>
                            </svg>

                        @else

                            <svg viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.7"
                                 stroke-linecap="round"
                                 stroke-linejoin="round"
                                 aria-hidden="true">
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M4 21v-2a8 8 0 0 1 16 0v2"/>
                            </svg>

                        @endif

                    </span>

                    {{-- Data pemain --}}

                    <div class="player-row__info">

                        <strong class="player-row__name">
                            {{ $player->name }}
                        </strong>

                        <div class="player-row__stats">
                            <span>
                                <b>{{ $player->wins }}</b> Menang
                            </span>

                            <span>
                                <b>{{ $player->draws }}</b> Seri
                            </span>

                            <span>
                                <b>{{ $player->losses }}</b> Kalah
                            </span>
                        </div>

                    </div>

                    {{-- Skor --}}

                    <strong class="player-row__score">
                        {{ $player->total_score }}
                    </strong>

                    {{-- Aksi --}}

                    @if($canEdit || $canDelete)

                        <div class="player-row__actions">

                            @if($canEdit)
                                <a
                                    href="{{ route('players.edit', $player) }}"
                                    class="player-action player-action--edit"
                                    aria-label="Edit {{ $player->name }}"
                                    title="Edit pemain"
                                >
                                    <svg viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="1.8"
                                         stroke-linecap="round"
                                         stroke-linejoin="round"
                                         aria-hidden="true">
                                        <path d="M12 20h9"/>
                                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L9 17l-4 1 1-4Z"/>
                                    </svg>
                                </a>
                            @endif

                            @if($canDelete)
                                <form
                                    method="POST"
                                    action="{{ route('players.destroy', $player) }}"
                                    data-single-submit
                                    data-confirm="Hapus pemain {{ $player->name }}?"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="player-action player-action--delete"
                                        aria-label="Hapus {{ $player->name }}"
                                        title="Hapus pemain"
                                    >
                                        <svg viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="1.8"
                                             stroke-linecap="round"
                                             stroke-linejoin="round"
                                             aria-hidden="true">
                                            <path d="M3 6h18"/>
                                            <path d="M8 6V4h8v2"/>
                                            <path d="M5 6l1 15h12l1-15"/>
                                            <path d="M10 10v7M14 10v7"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif

                        </div>

                    @endif

                </article>

            @endforeach

        </div>

        {{-- Pencarian kosong --}}

        <div
            class="player-empty"
            id="rank-live-empty"
            @if($players->count() > 0) hidden @endif
        >
            <strong>
                {{ $players->count() > 0
                    ? 'Pemain tidak ditemukan.'
                    : 'Belum ada pemain.' }}
            </strong>

            <p id="rank-empty-description">
                {{ $players->count() > 0
                    ? 'Coba gunakan nama lain.'
                    : 'Daftar pemain akan tampil di sini.' }}
            </p>
        </div>

        {{-- Pagination --}}

        <nav
            class="player-pagination"
            id="rank-live-pagination"
            aria-label="Halaman ranking"
            hidden
        >
            <button
                type="button"
                id="rank-page-prev"
            >
                ← Sebelumnya
            </button>

            <span id="rank-page-info"></span>

            <button
                type="button"
                id="rank-page-next"
            >
                Berikutnya →
            </button>
        </nav>

    </section>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('rank-live-input');
    const rows = Array.from(
        document.querySelectorAll('#rank-player-list .player-row')
    );
    const count = document.getElementById('rank-result-count');
    const empty = document.getElementById('rank-live-empty');
    const emptyDescription = document.getElementById('rank-empty-description');
    const pagination = document.getElementById('rank-live-pagination');
    const prev = document.getElementById('rank-page-prev');
    const next = document.getElementById('rank-page-next');
    const pageInfo = document.getElementById('rank-page-info');

    if (!input) return;

    const pageSize = 20;
    let currentPage = 1;

    const normalize = value => String(value)
        .normalize('NFKC')
        .toLocaleLowerCase('id-ID')
        .trim();

    function render() {
        const keyword = normalize(input.value);

        const filtered = rows.filter(row =>
            normalize(row.dataset.playerName).includes(keyword)
        );

        const totalPages = Math.max(
            1,
            Math.ceil(filtered.length / pageSize)
        );

        currentPage = Math.min(currentPage, totalPages);

        const start = (currentPage - 1) * pageSize;
        const visible = new Set(
            filtered.slice(start, start + pageSize)
        );

        rows.forEach(row => {
            row.hidden = !visible.has(row);
        });

        count.textContent = filtered.length + ' pemain';
        empty.hidden = filtered.length > 0;

        if (filtered.length === 0) {
            empty.querySelector('strong').textContent = keyword
                ? 'Pemain tidak ditemukan.'
                : 'Belum ada pemain.';

            emptyDescription.textContent = keyword
                ? 'Coba gunakan nama lain.'
                : 'Daftar pemain akan tampil di sini.';
        }

        pagination.hidden = filtered.length <= pageSize;

        pageInfo.textContent =
            currentPage + ' / ' + totalPages;

        prev.disabled = currentPage === 1;
        next.disabled = currentPage === totalPages;
    }

    input.addEventListener('input', function () {
        currentPage = 1;
        render();
    });

    prev.addEventListener('click', function () {
        if (currentPage > 1) {
            currentPage--;
            render();
        }
    });

    next.addEventListener('click', function () {
        const pages = Math.ceil(
            rows.filter(row =>
                normalize(row.dataset.playerName).includes(
                    normalize(input.value)
                )
            ).length / pageSize
        );

        if (currentPage < pages) {
            currentPage++;
            render();
        }
    });

    render();
});
</script>
@endpush
