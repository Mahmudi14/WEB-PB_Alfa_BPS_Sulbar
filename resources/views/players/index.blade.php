@extends('layouts.app')

@section('title', 'Ranking Pemain - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/players.css') }}?v=2">
<link rel="stylesheet" href="{{ asset('assets/css/ranking-tabs.css') }}?v=1">
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


    {{-- Tab ranking putra dan putri --}}

    <div class="ranking-tabs ranking-tabs--players" role="tablist" aria-label="Kategori ranking pemain">
        <button type="button" class="ranking-tabs__button is-active" id="rank-tab-men" role="tab" data-rank-tab="L"
            aria-controls="rank-player-list" aria-selected="true" tabindex="0">
            <span>MEN</span>
            <small>PUTRA</small>
        </button>

        <button type="button" class="ranking-tabs__button" id="rank-tab-women" role="tab" data-rank-tab="P"
            aria-controls="rank-player-list" aria-selected="false" tabindex="-1">
            <span>WOMEN</span>
            <small>PUTRI</small>
        </button>
    </div>


    {{-- Pencarian --}}

    <div class="player-toolbar">

        <div class="player-search">
            <label for="rank-live-input" class="player-sr-only">
                Cari pemain
            </label>

            <input id="rank-live-input" type="search" placeholder="Ketik nama pemain..." autocomplete="off"
                spellcheck="false">

            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                stroke-linejoin="round" aria-hidden="true">
                <circle cx="11" cy="11" r="7" />
                <path d="m20 20-4-4" />
            </svg>
        </div>

        @if($canManage)
        <a href="{{ route('players.create') }}" class="player-add">
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

                <h2 id="rank-category-title">Ranking Putra</h2>
            </div>

            <span class="player-board__count" id="rank-result-count">
                {{ $players->where('gender', 'L')->count() }} pemain
            </span>
        </div>

        <div class="player-list" id="rank-player-list" role="tabpanel" aria-labelledby="rank-tab-men" tabindex="0">

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

            $isOwnPlayer = $player->user_id !== null
            && (int) $player->user_id === (int) $viewer?->id;

            // Admin bebas edit.
            // Pengelola boleh edit semua kecuali profil Admin.
            $canEdit = $canManage && (
            $isAdmin || $linkedRole !== 'admin'
            );

            // Tidak boleh menghapus akun sendiri.
            $canDelete = $canEdit && !$isOwnPlayer;


            $nameClass = mb_strlen($player->name) > 28
            ? 'player-row--long-name'
            : '';
            @endphp


            <article
                class="player-row {{ $rankClass }} {{ $nameClass }} {{ ($canEdit || $canDelete) ? 'player-row--actions' : '' }}"
                data-player-name="{{ $player->name }}" data-player-gender="{{ $player->gender }}"
                aria-label="Peringkat {{ $position }}, {{ $player->name }}, skor {{ $player->total_score }}"
                @if($player->gender === 'P') hidden @endif
                >


                {{-- Peringkat --}}

                <span class="player-row__rank">
                    {{ $position }}
                </span>

                {{-- Avatar --}}

                <span
                    class="player-avatar {{ $player->gender === 'P' ? 'player-avatar--female' : 'player-avatar--male' }}"
                    aria-label="{{ $player->gender === 'P' ? 'Perempuan' : 'Laki-laki' }}" role="img">

                    @if($player->gender === 'P')

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">
                        <path d="M6 11V8a6 6 0 0 1 12 0v3" />
                        <circle cx="12" cy="9" r="4" />
                        <path d="M5 21c0-4 3-7 7-7s7 3 7 7" />
                        <path d="M6 11c-1 2-1 4-2 5M18 11c1 2 1 4 2 5" />
                    </svg>

                    @else

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="8" r="4" />
                        <path d="M4 21v-2a8 8 0 0 1 16 0v2" />
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
                    <a href="{{ route('players.edit', $player) }}" class="player-action player-action--edit"
                        aria-label="Edit {{ $player->name }}" title="Edit pemain">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 20h9" />
                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L9 17l-4 1 1-4Z" />
                        </svg>
                    </a>
                    @endif

                    @if($canDelete)
                    <form method="POST" action="{{ route('players.destroy', $player) }}" data-single-submit
                        data-confirm="Hapus pemain {{ $player->name }}?">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="player-action player-action--delete"
                            aria-label="Hapus {{ $player->name }}" title="Hapus pemain">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 6h18" />
                                <path d="M8 6V4h8v2" />
                                <path d="M5 6l1 15h12l1-15" />
                                <path d="M10 10v7M14 10v7" />
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

        <div class="player-empty" id="rank-live-empty" @if($players->count() > 0) hidden @endif
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

        <nav class="player-pagination" id="rank-live-pagination" aria-label="Halaman ranking" hidden>
            <button type="button" id="rank-page-prev">
                ← Sebelumnya
            </button>

            <span id="rank-page-info"></span>

            <button type="button" id="rank-page-next">
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
    const list = document.getElementById('rank-player-list');

    if (!input || !list) return;

    const rows = Array.from(
        list.querySelectorAll('.player-row')
    );

    const tabs = Array.from(
        document.querySelectorAll(
            '.ranking-tabs--players [data-rank-tab]'
        )
    );

    const count = document.getElementById(
        'rank-result-count'
    );

    const categoryTitle = document.getElementById(
        'rank-category-title'
    );

    const empty = document.getElementById(
        'rank-live-empty'
    );

    const emptyDescription = document.getElementById(
        'rank-empty-description'
    );

    const pagination = document.getElementById(
        'rank-live-pagination'
    );

    const prev = document.getElementById(
        'rank-page-prev'
    );

    const next = document.getElementById(
        'rank-page-next'
    );

    const pageInfo = document.getElementById(
        'rank-page-info'
    );

    const pageSize = 20;

    let currentPage = 1;
    let activeGender = 'L';

    function normalize(value) {
        return String(value)
            .normalize('NFKC')
            .toLocaleLowerCase('id-ID')
            .trim();
    }

    function getFilteredRows() {
        const keyword = normalize(input.value);

        return rows.filter(function (row) {
            const correctGender =
                row.dataset.playerGender === activeGender;

            const correctName = normalize(
                row.dataset.playerName
            ).includes(keyword);

            return correctGender && correctName;
        });
    }

    function render() {
        const filtered = getFilteredRows();

        const totalPages = Math.max(
            1,
            Math.ceil(filtered.length / pageSize)
        );

        currentPage = Math.min(
            currentPage,
            totalPages
        );

        const start = (currentPage - 1) * pageSize;

        const visible = new Set(
            filtered.slice(
                start,
                start + pageSize
            )
        );

        rows.forEach(function (row) {
            row.hidden = !visible.has(row);
        });

        count.textContent =
            filtered.length + ' pemain';

        const hasPlayers = filtered.length > 0;

        empty.hidden = hasPlayers;

        if (!hasPlayers) {
            const isSearching = input.value.trim() !== '';

            empty.querySelector('strong').textContent =
                isSearching
                    ? 'Pemain tidak ditemukan.'
                    : activeGender === 'L'
                        ? 'Belum ada pemain putra.'
                        : 'Belum ada pemain putri.';

            emptyDescription.textContent = isSearching
                ? 'Coba gunakan nama lain.'
                : 'Daftar pemain akan tampil di sini.';
        }

        pagination.hidden =
            filtered.length <= pageSize;

        pageInfo.textContent =
            currentPage + ' / ' + totalPages;

        prev.disabled = currentPage === 1;
        next.disabled = currentPage === totalPages;
    }

    function activateTab(gender) {
        if (!['L', 'P'].includes(gender)) return;

        activeGender = gender;
        currentPage = 1;

        // Reset pencarian ketika berganti kategori
        input.value = '';

        const isMen = gender === 'L';

        input.placeholder = isMen
            ? 'Cari pemain putra...'
            : 'Cari pemain putri...';

        categoryTitle.textContent = isMen
            ? 'Ranking Putra'
            : 'Ranking Putri';

        tabs.forEach(function (tab) {
            const active =
                tab.dataset.rankTab === gender;

            tab.classList.toggle(
                'is-active',
                active
            );

            tab.setAttribute(
                'aria-selected',
                String(active)
            );

            tab.tabIndex = active ? 0 : -1;

            if (active) {
                list.setAttribute(
                    'aria-labelledby',
                    tab.id
                );
            }
        });

        render();
    }

    // Klik tab

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            activateTab(tab.dataset.rankTab);
        });
    });

    // Navigasi tab menggunakan keyboard

    const tabList = document.querySelector(
        '.ranking-tabs--players'
    );

    tabList?.addEventListener('keydown', function (event) {
        if (
            event.key !== 'ArrowLeft'
            && event.key !== 'ArrowRight'
        ) {
            return;
        }

        event.preventDefault();

        const nextGender =
            activeGender === 'L' ? 'P' : 'L';

        activateTab(nextGender);

        const activeTab = tabs.find(
            tab => tab.dataset.rankTab === nextGender
        );

        activeTab?.focus();
    });

    // Pencarian

    input.addEventListener('input', function () {
        currentPage = 1;
        render();
    });

    // Pagination

    prev.addEventListener('click', function () {
        if (currentPage > 1) {
            currentPage--;
            render();
        }
    });

    next.addEventListener('click', function () {
        currentPage++;
        render();
    });

    // Tampilan awal: MEN
    activateTab('L');

});
</script>
@endpush