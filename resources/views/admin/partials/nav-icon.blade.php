@php
$iconName = $name ?? 'dashboard';
@endphp

<svg class="adm-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"
    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">

    @switch($iconName)

    @case('dashboard')

    <rect x="3" y="3" width="7" height="7" rx="1.5" />
    <rect x="14" y="3" width="7" height="7" rx="1.5" />
    <rect x="3" y="14" width="7" height="7" rx="1.5" />
    <rect x="14" y="14" width="7" height="7" rx="1.5" />

    @break


    @case('users')

    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
    <circle cx="9" cy="7" r="4" />
    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
    <path d="M16 3.13a4 4 0 0 1 0 7.75" />

    @break


    @case('match')

    <path d="M5 19 19 5" />
    <path d="M5 5 19 19" />
    <path d="M4 7 7 4" />
    <path d="M17 20 20 17" />
    <ellipse cx="7" cy="7" rx="4" ry="3" transform="rotate(-45 7 7)" />
    <ellipse cx="17" cy="7" rx="4" ry="3" transform="rotate(45 17 7)" />

    @break


    @case('trophy')

    <path d="M8 21h8" />
    <path d="M12 17v4" />
    <path d="M7 4h10v6a5 5 0 0 1-10 0V4Z" />
    <path d="M7 6H4v2a4 4 0 0 0 4 4" />
    <path d="M17 6h3v2a4 4 0 0 1-4 4" />

    @break


    @case('wallet')

    <rect x="3" y="6" width="18" height="15" rx="2" />
    <path d="M3 9V5a2 2 0 0 1 2-2h13" />
    <path d="M16 14h5" />
    <circle cx="16" cy="14" r="1" fill="currentColor" stroke="none" />

    @break


    @case('image')

    <rect x="3" y="3" width="18" height="18" rx="2" />
    <circle cx="8.5" cy="8.5" r="1.5" />
    <path d="m21 15-5-5L5 21" />

    @break


    @case('settings')

    <path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z" />
    <path
        d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2 2-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.57V21h-2.8v-.09a1.7 1.7 0 0 0-1.03-1.57 1.7 1.7 0 0 0-1.88.34l-.06.06-2-2 .06-.06A1.7 1.7 0 0 0 7.46 15a1.7 1.7 0 0 0-1.57-1.03H5.8v-2.8h.09a1.7 1.7 0 0 0 1.57-1.03 1.7 1.7 0 0 0-.34-1.88l-.06-.06 2-2 .06.06A1.7 1.7 0 0 0 11 6.6a1.7 1.7 0 0 0 1.03-1.57V5h2.8v.09a1.7 1.7 0 0 0 1.03 1.57 1.7 1.7 0 0 0 1.88-.34l.06-.06 2 2-.06.06a1.7 1.7 0 0 0-.34 1.88 1.7 1.7 0 0 0 1.57 1.03h.09v2.8h-.09A1.7 1.7 0 0 0 19.4 15Z" />

    @break


    @case('globe')

    <circle cx="12" cy="12" r="9" />
    <path d="M3 12h18" />
    <path d="M12 3a15 15 0 0 1 0 18" />
    <path d="M12 3a15 15 0 0 0 0 18" />

    @break


    @case('logout')

    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
    <path d="m16 17 5-5-5-5" />
    <path d="M21 12H9" />

    @break


    @default

    <circle cx="12" cy="12" r="9" />

    @endswitch

</svg>