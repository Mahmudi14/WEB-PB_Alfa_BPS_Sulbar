@extends('layouts.app')

@section('title', 'Beranda - PB Alfa BPS Sulbar')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/ranking-tabs.css') }}?v=1">

<link rel="stylesheet" href="{{ asset('assets/css/home-v3.css') }}?v=5">

@endpush

@section('content')

<div class="nova-home">

    {{-- Ikon kok SVG yang dipakai berulang --}}
    <svg class="nova-svg-defs" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <symbol id="nova-shuttle" viewBox="0 0 80 110">
                <path d="M31 83 7 19Q3 8 16 12L38 77Z" fill="#C9E8FC" stroke="white" stroke-width="2" />
                <path d="M38 81 25 10Q25 2 36 9L44 77Z" fill="#E9F8FF" stroke="white" stroke-width="2" />
                <path d="M45 81 43 7Q48 0 54 10L52 79Z" fill="white" stroke="white" stroke-width="2" />
                <path d="M52 84 66 17Q75 11 76 24L59 86Z" fill="#C9E8FC" stroke="white" stroke-width="2" />
                <path d="M31 78Q46 71 60 79L56 91Q45 97 35 90Z" fill="#F4F9FF" />
                <path d="M35 90Q46 97 56 91L54 101Q46 109 39 101Z" fill="#F39222" />
                <path d="M20 37Q45 27 68 41M27 58Q45 51 63 61" stroke="#80BCE4" stroke-width="1.4" />
            </symbol>
        </defs>
    </svg>

    {{-- HERO --}}
    <section class="nova-hero">
        <div class="nova-hero__mesh" aria-hidden="true"></div>

        <div class="nova-hero__copy">
            <span class="nova-eyebrow nova-eyebrow--glass">
                <span class="nova-live-dot"></span>
                PB ALFA · BPS SULAWESI BARAT
            </span>

            <h1>
                SATU<br>
                <span class="nova-hero__outline">LAPANGAN.</span><br>
                <span class="nova-hero__accent">SEJUTA CERITA.</span>
            </h1>

            <p>
                Bukan sekadar mengejar kemenangan.
                Setiap reli membawa semangat, sportivitas,
                dan kebersamaan yang tak terlupakan.
            </p>

            <div class="nova-hero__actions">
                <a class="nova-btn nova-btn--orange" href="#ranking">
                    Jelajahi Ranking <span aria-hidden="true">↗</span>
                </a>

                <a class="nova-btn nova-btn--glass" href="{{ route('matches.index') }}">
                    Lihat Pertandingan <span aria-hidden="true">→</span>
                </a>
            </div>

            <div class="nova-hero__keywords">
                <span>PLAY HARD</span>
                <i></i>
                <span>STAY SOLID</span>
                <i></i>
                <span>GROW TOGETHER</span>
            </div>
        </div>


        <div class="nova-hero__scene nova-rally" data-rally-scene role="img"
            aria-label="Animasi dua pemain bulu tangkis sedang melakukan rally di lapangan">

            <canvas class="nova-rally__canvas" data-rally-canvas aria-hidden="true">
            </canvas>

            <div class="nova-rally__topline" aria-hidden="true">


                <span class="nova-rally__court-number">
                    PB ALFA / COURT 01
                </span>
            </div>

            <span class="nova-rally__smash" data-rally-smash aria-hidden="true">
                SMASH!
            </span>


        </div>


        <span class="nova-hero__corner" aria-hidden="true">01 / RALLY ON</span>
    </section>


    {{-- TENTANG KAMI --}}
    <section class="nova-about" id="tentang">
        <span class="nova-section-number" aria-hidden="true">01</span>

        <svg class="nova-float-shuttle nova-float-shuttle--about" aria-hidden="true" viewBox="0 0 80 110">
            <use href="#nova-shuttle"></use>
        </svg>

        <div class="nova-about__intro">
            <span class="nova-eyebrow nova-eyebrow--light">
                THE SPIRIT OF PB ALFA
            </span>

            <h2>
                LEBIH DARI<br>
                <em>SEKADAR MAIN.</em>
            </h2>

            <span class="nova-about__rule"></span>
        </div>

        <div class="nova-about__body">
            <h3>{{ $siteSettings?->about_title ?? 'Tentang Kami' }}</h3>

            <p>{{ $siteSettings?->about_text ?? 'Informasi klub belum tersedia.' }}</p>

            <div class="nova-about__values">
                <div>
                    <span class="nova-about__value-symbol nova-blue">01</span>
                    <strong>Sportivitas</strong>
                </div>
                <div>
                    <span class="nova-about__value-symbol nova-green">02</span>
                    <strong>Kebersamaan</strong>
                </div>
                <div>
                    <span class="nova-about__value-symbol nova-orange">03</span>
                    <strong>Semangat</strong>
                </div>
            </div>
        </div>
    </section>


    {{-- PODIUM RANKING --}}
    <section class="nova-ranking" id="ranking">
        <span class="nova-section-number" aria-hidden="true">02</span>

        <svg class="nova-float-shuttle nova-float-shuttle--ranking" aria-hidden="true" viewBox="0 0 80 110">
            <use href="#nova-shuttle"></use>
        </svg>

        <div class="nova-heading nova-heading--center">
            <span class="nova-eyebrow nova-eyebrow--green">
                LEADERBOARD · PB ALFA
            </span>

            <h2>HALL OF <span>CHAMPIONS.</span></h2>

            <p>Setiap pertandingan membentuk cerita.
                Setiap poin menentukan posisi.</p>
        </div>


        {{-- Pilihan kategori podium --}}

        <div class="ranking-tabs ranking-tabs--home" role="tablist" aria-label="Kategori podium ranking">
            <button type="button" class="ranking-tabs__button is-active" id="home-rank-tab-L" data-home-rank-tab="L"
                role="tab" aria-controls="home-podium-L" aria-selected="true" tabindex="0">
                <span>MEN</span>
                <small>PUTRA</small>
            </button>

            <button type="button" class="ranking-tabs__button" id="home-rank-tab-P" data-home-rank-tab="P" role="tab"
                aria-controls="home-podium-P" aria-selected="false" tabindex="-1">
                <span>WOMEN</span>
                <small>PUTRI</small>
            </button>
        </div>

        {{-- Podium Putra dan Putri --}}

        @foreach(['L', 'P'] as $gender)

        <div class="nova-podium" id="home-podium-{{ $gender }}" data-home-rank-panel="{{ $gender }}" role="tabpanel"
            aria-labelledby="home-rank-tab-{{ $gender }}" tabindex="0" @if($gender==='P' ) hidden @endif>

            @foreach([2, 1, 3] as $position)

            @php
            $rankedPlayers = $topPlayers
            ->where('gender', $gender)
            ->where('rank_position', $position);
            @endphp

            <div class="nova-podium__lane nova-podium__lane--{{ $position }}">

                <div class="nova-podium__identity">

                    <div class="nova-podium__medal">
                        {{ $position === 1 ? '★' : '◆' }}
                    </div>

                    <span class="nova-podium__label">
                        {{ $position === 1
                        ? 'THE CHAMPION'
                        : 'RANK ' . $position }}
                    </span>

                    @forelse($rankedPlayers as $player)

                    <div class="nova-podium__player">

                        <strong>
                            {{ $player->name }}
                        </strong>

                        <span>
                            {{ $player->total_score }} POIN
                        </span>

                        <small>
                            {{ $player->wins }} Menang
                        </small>

                    </div>

                    @empty

                    <div class="nova-podium__player nova-podium__player--empty">

                        <strong>Belum ada</strong>
                        <span>—</span>

                    </div>

                    @endforelse

                </div>

                <div class="nova-podium__block">
                    <span>{{ $position }}</span>
                </div>

            </div>

            @endforeach

        </div>

        @endforeach


        <div class="nova-ranking__bottom">
            <p>
                <span>+30 Menang</span>
                <span>+10 Seri</span>
                <span>−30 Kalah</span>
            </p>

            <a href="{{ route('players.index') }}" class="nova-text-link">
                Lihat Semua Peringkat ↗
            </a>
        </div>
    </section>


    {{-- GALERI --}}
    <section class="nova-gallery">
        <span class="nova-section-number" aria-hidden="true">03</span>

        <svg class="nova-float-shuttle nova-float-shuttle--gallery" aria-hidden="true" viewBox="0 0 80 110">
            <use href="#nova-shuttle"></use>
        </svg>

        <div class="nova-heading">
            <span class="nova-eyebrow nova-eyebrow--light">
                CAPTURED MOMENTS
            </span>

            <h2>BEYOND <span>THE COURT.</span></h2>
            <p>Momen latihan, pertandingan, dan kebersamaan PB Alfa.</p>
        </div>

        <div class="nova-gallery__grid">
            @foreach($galleryImages as $image)
            <a class="nova-gallery__tile" href="{{ asset('storage/' . $image->image_path) }}" target="_blank"
                rel="noopener noreferrer" title="{{ $image->caption ?: 'Galeri PB Alfa' }}">
                <img src="{{ asset('storage/' . $image->image_path) }}"
                    alt="{{ $image->caption ?: 'Kegiatan PB Alfa' }}" loading="lazy" decoding="async">
                <span class="nova-gallery__overlay">
                    {{ $image->caption ?: 'PB Alfa Moments' }}
                    <span aria-hidden="true">↗</span>
                </span>
            </a>
            @endforeach

            @for($i = $galleryImages->count(); $i < 6; $i++) <div class="nova-gallery__tile nova-gallery__tile--empty">
                <svg viewBox="0 0 80 110" aria-hidden="true">
                    <use href="#nova-shuttle"></use>
                </svg>
                <span>Belum ada gambar</span>
                <small>PB ALFA MOMENTS</small>
        </div>
        @endfor
</div>
</section>


{{-- PENUTUP / CTA --}}
<section class="nova-cta">
    <div>
        <span class="nova-eyebrow nova-eyebrow--light">
            READY FOR THE NEXT RALLY?
        </span>
        <h2>PERTANDINGAN BERIKUTNYA<br>
            <span>MENUNGGU CERITAMU.</span>
        </h2>
    </div>

    <a href="{{ route('matches.index') }}" class="nova-btn nova-btn--orange">
        Jelajahi Pertandingan ↗
    </a>
</section>

</div>

@endsection


@push('scripts')

<script src="{{ asset('assets/js/hero-rally.js') }}?v=1" defer></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

    const tabList = document.querySelector(
        '.ranking-tabs--home'
    );

    if (!tabList) return;

    const tabs = Array.from(
        tabList.querySelectorAll('[data-home-rank-tab]')
    );

    const panels = Array.from(
        document.querySelectorAll(
            '[data-home-rank-panel]'
        )
    );

    let activeGender = 'L';

    function activateTab(gender) {

        if (!['L', 'P'].includes(gender)) return;

        activeGender = gender;

        tabs.forEach(function (tab) {

            const active =
                tab.dataset.homeRankTab === gender;

            tab.classList.toggle(
                'is-active',
                active
            );

            tab.setAttribute(
                'aria-selected',
                String(active)
            );

            tab.tabIndex = active ? 0 : -1;

        });

        panels.forEach(function (panel) {
            panel.hidden =
                panel.dataset.homeRankPanel !== gender;
        });

    }

    tabs.forEach(function (tab) {

        tab.addEventListener('click', function () {
            activateTab(tab.dataset.homeRankTab);
        });

    });

    tabList.addEventListener('keydown', function (event) {

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
            tab => tab.dataset.homeRankTab === nextGender
        );

        activeTab?.focus();

    });

    activateTab('L');

});
</script>
@endpush