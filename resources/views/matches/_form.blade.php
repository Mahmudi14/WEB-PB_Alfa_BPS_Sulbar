
@php
    $type = old('type', 'ganda');
    $result = old('result', '');
    $selected = $selected ?? [];
@endphp

<form
    id="match-form"
    class="match-form"
    method="POST"
    action="{{ route('matches.store') }}"
    data-single-submit
>
    @csrf

    {{-- Validasi --}}

    @if($errors->any())
        <div class="match-alert" role="alert">
            <strong>Periksa kembali data pertandingan.</strong>

            <ul>
                @foreach($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div
        id="match-client-error"
        class="match-alert"
        role="alert"
        hidden
    ></div>

    {{-- Informasi pertandingan --}}

    <div class="match-form__fields">

        <div class="match-field">
            <label for="played_on">Tanggal Pertandingan</label>

            <input
                id="played_on"
                type="date"
                name="played_on"
                value="{{ old('played_on', now()->toDateString()) }}"
                required
            >
        </div>

        <div class="match-field">
            <span class="match-field__label">Jenis Pertandingan</span>

            <div class="match-choice match-choice--type">

                <label>
                    <input
                        type="radio"
                        name="type"
                        value="ganda"
                        @checked($type === 'ganda')
                        required
                    >
                    <span>Ganda</span>
                </label>

                <label>
                    <input
                        type="radio"
                        name="type"
                        value="tunggal"
                        @checked($type === 'tunggal')
                    >
                    <span>Tunggal</span>
                </label>

            </div>
        </div>

        <div class="match-field">
            <label for="shuttlecock_count">Jumlah Kok</label>

            <input
                id="shuttlecock_count"
                type="number"
                name="shuttlecock_count"
                min="0"
                max="65535"
                step="1"
                inputmode="numeric"
                value="{{ old('shuttlecock_count', 0) }}"
                required
            >
        </div>

    </div>

    {{-- Pemain --}}

    <div class="match-section">

        <div class="match-section__heading">
            <h3>Pilih Pemain</h3>
        </div>

        <div class="match-teams">

            @foreach(['a' => 'Tim A', 'b' => 'Tim B'] as $team => $teamName)

                <div class="match-team-column">

                    <div class="match-team-column__heading
                        {{ $team === 'b' ? 'match-team-column__heading--b' : '' }}">
                        {{ $teamName }}
                    </div>

                    @foreach([1, 2] as $number)

                        @php
                            $slot = $team . $number;

                            $value = (string) old(
                                $slot,
                                $selected[$slot] ?? ''
                            );

                            if ($type === 'tunggal' && $number === 2) {
                                $value = '';
                            }

                            $selectedPlayer = $players->first(
                                fn ($candidate) => (string) $candidate->id === $value
                            );
                        @endphp

                        <div
                            class="match-picker"
                            data-slot="{{ $slot }}"
                            data-placeholder="Pemain {{ $number }}"
                            @if($number === 2 && $type === 'tunggal') hidden @endif
                        >

                            <input
                                type="hidden"
                                name="{{ $slot }}"
                                value="{{ $value }}"
                            >

                            <button
                                type="button"
                                class="match-picker__trigger"
                                aria-label="Pilih {{ $teamName }} pemain {{ $number }}"
                                aria-expanded="false"
                            >
                                <span class="match-picker__label">
                                    {{ $selectedPlayer?->name ?? 'Pemain ' . $number }}
                                </span>

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="m6 9 6 6 6-6"/>
                                </svg>
                            </button>

                            <div class="match-picker__menu" hidden>

                                <input
                                    type="search"
                                    class="match-picker__search"
                                    placeholder="Cari nama pemain..."
                                    aria-label="Cari pemain {{ $teamName }}"
                                    autocomplete="off"
                                >

                                <div class="match-picker__options">

                                    @foreach($players as $candidate)
                                        <button
                                            type="button"
                                            class="match-picker__option"
                                            data-id="{{ $candidate->id }}"
                                            data-name="{{ $candidate->name }}"
                                        >
                                            {{ $candidate->name }}
                                        </button>
                                    @endforeach

                                </div>

                                <p class="match-picker__empty" hidden>
                                    Pemain tidak ditemukan
                                </p>

                            </div>

                        </div>

                    @endforeach

                </div>

            @endforeach

        </div>

    </div>

    {{-- Hasil --}}

    <div class="match-section">

        <div class="match-section__heading">
            <h3>Hasil Pertandingan</h3>
        </div>

        <div class="match-choice match-choice--result">

            <label>
                <input
                    type="radio"
                    name="result"
                    value="a"
                    @checked($result === 'a')
                    required
                >
                <span>Tim A</span>
            </label>

            <label>
                <input
                    type="radio"
                    name="result"
                    value="seri"
                    @checked($result === 'seri')
                >
                <span>Seri</span>
            </label>

            <label>
                <input
                    type="radio"
                    name="result"
                    value="b"
                    @checked($result === 'b')
                >
                <span>Tim B</span>
            </label>

        </div>

    </div>

    {{-- Tombol --}}

    <div class="match-form__actions">

        <button type="submit" class="match-submit">
            Simpan Pertandingan
        </button>

        <a
            href="{{ route('matches.index') }}"
            class="match-cancel"
        >
            Batal
        </a>

    </div>

</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('match-form');
    if (!form) return;

    const pickers = Array.from(
        form.querySelectorAll('.match-picker')
    );

    const error = document.getElementById('match-client-error');

    const normalize = value => String(value)
        .normalize('NFKC')
        .toLocaleLowerCase('id-ID')
        .trim();

    function currentType() {
        return form.querySelector(
            'input[name="type"]:checked'
        )?.value || 'ganda';
    }

    function closeAll() {
        pickers.forEach(picker => {
            picker.querySelector('.match-picker__menu').hidden = true;

            picker.querySelector('.match-picker__trigger')
                .setAttribute('aria-expanded', 'false');
        });
    }

    function updateLabel(picker) {
        const value = picker.querySelector(
            'input[type="hidden"]'
        ).value;

        const option = Array.from(
            picker.querySelectorAll('.match-picker__option')
        ).find(item => item.dataset.id === value);

        picker.querySelector('.match-picker__label').textContent =
            option?.dataset.name || picker.dataset.placeholder;
    }

    function refreshOptions() {
        const active = pickers.filter(picker => !picker.hidden);

        active.forEach(picker => {
            const query = normalize(
                picker.querySelector('.match-picker__search').value
            );

            const used = new Set(
                active
                    .filter(other => other !== picker)
                    .map(other => other.querySelector(
                        'input[type="hidden"]'
                    ).value)
                    .filter(Boolean)
            );

            let available = 0;

            picker.querySelectorAll('.match-picker__option')
                .forEach(option => {
                    const matches = normalize(
                        option.dataset.name
                    ).includes(query);

                    const taken = used.has(option.dataset.id);

                    option.hidden = !matches || taken;
                    option.disabled = taken;

                    if (matches && !taken) {
                        available++;
                    }
                });

            picker.querySelector('.match-picker__empty').hidden =
                available > 0;
        });
    }

    function updateType() {
        const singles = currentType() === 'tunggal';

        pickers.forEach(picker => {
            if (!picker.dataset.slot.endsWith('2')) {
                return;
            }

            picker.hidden = singles;

            if (singles) {
                picker.querySelector(
                    'input[type="hidden"]'
                ).value = '';

                updateLabel(picker);
            }
        });

        closeAll();
        refreshOptions();
    }

    pickers.forEach(picker => {
        const trigger = picker.querySelector(
            '.match-picker__trigger'
        );

        const menu = picker.querySelector(
            '.match-picker__menu'
        );

        const search = picker.querySelector(
            '.match-picker__search'
        );

        const selected = picker.querySelector(
            'input[type="hidden"]'
        );

        updateLabel(picker);

        trigger.addEventListener('click', function () {
            const wasOpen = !menu.hidden;

            closeAll();

            if (!wasOpen) {
                menu.hidden = false;
                trigger.setAttribute('aria-expanded', 'true');
                search.value = '';
                refreshOptions();
                search.focus();
            }
        });

        search.addEventListener('input', refreshOptions);

        picker.querySelectorAll('.match-picker__option')
            .forEach(option => {
                option.addEventListener('click', function () {
                    if (option.disabled) return;

                    selected.value = option.dataset.id;
                    updateLabel(picker);
                    closeAll();
                    refreshOptions();

                    error.hidden = true;
                });
            });
    });

    form.querySelectorAll('input[name="type"]')
        .forEach(input => {
            input.addEventListener('change', updateType);
        });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.match-picker')) {
            closeAll();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeAll();
        }
    });

    form.addEventListener('submit', function (event) {
        const slots = currentType() === 'ganda'
            ? ['a1', 'a2', 'b1', 'b2']
            : ['a1', 'b1'];

        const values = slots.map(slot =>
            form.elements.namedItem(slot)?.value || ''
        );

        let message = '';

        if (values.some(value => !value)) {
            message = 'Pilih semua pemain terlebih dahulu.';
        } else if (new Set(values).size !== values.length) {
            message = 'Pemain tidak boleh dipilih lebih dari sekali.';
        }

        if (!message) {
            error.hidden = true;
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        error.textContent = message;
        error.hidden = false;

        error.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    });

    updateType();
});
</script>
@endpush
