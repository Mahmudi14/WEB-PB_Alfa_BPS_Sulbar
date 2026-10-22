@php
$type = old('type', 'ganda');
$selected = $selected ?? [];
@endphp

<form id="match-form" class="match-form" method="POST" action="{{ route('matches.store') }}" data-single-submit>
    @csrf

    {{-- Pesan validasi server --}}
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

    {{-- Pesan validasi client --}}
    <div id="match-client-error" class="match-alert" role="alert" hidden></div>

    {{-- Informasi Pertandingan --}}
    <div class="match-form__fields">

        <div class="match-field">
            <label for="played_on">
                Tanggal Pertandingan
            </label>

            <input id="played_on" type="date" name="played_on" value="{{ old('played_on', now()->toDateString()) }}"
                required>
        </div>

        <div class="match-field">
            <span class="match-field__label">
                Jenis Pertandingan
            </span>

            <div class="match-choice match-choice--type">

                <label>
                    <input type="radio" name="type" value="ganda" @checked($type==='ganda' ) required>
                    <span>Ganda</span>
                </label>

                <label>
                    <input type="radio" name="type" value="tunggal" @checked($type==='tunggal' )>
                    <span>Tunggal</span>
                </label>

            </div>
        </div>

    </div>

    {{-- Pemilihan Pemain --}}
    <section class="match-section">

        <div class="match-section__heading">
            <h3>Pilih Pemain</h3>
        </div>

        <div class="match-teams">

            @foreach(['a' => 'Tim A', 'b' => 'Tim B'] as $team => $teamName)

            <div class="match-team-column">

                <div class="match-team-column__heading
                        {{ $team === 'b'
                            ? 'match-team-column__heading--b'
                            : '' }}">
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

                $pickedPlayer = $players->first(
                fn ($player) =>
                (string) $player->id === $value
                );

                $pickedName = $value === 'guest'
                ? 'Pemain Luar'
                : ($pickedPlayer?->name
                ?? 'Pemain ' . $number);
                @endphp

                <div class="match-picker" data-slot="{{ $slot }}" data-placeholder="Pemain {{ $number }}"
                    @if($number===2 && $type==='tunggal' ) hidden @endif>
                    <input type="hidden" name="{{ $slot }}" value="{{ $value }}">

                    <button type="button" class="match-picker__trigger" aria-expanded="false"
                        aria-label="{{ $teamName }} pemain {{ $number }}">
                        <span class="match-picker__label">
                            {{ $pickedName }}
                        </span>

                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>

                    <div class="match-picker__menu" hidden>

                        <input type="search" class="match-picker__search" placeholder="Cari pemain..."
                            autocomplete="off" aria-label="Cari {{ $teamName }} pemain {{ $number }}">

                        <div class="match-picker__options">

                            <button type="button" class="match-picker__option match-picker__option--guest"
                                data-id="guest" data-name="Pemain Luar">
                                Pemain Luar
                            </button>

                            @foreach($players as $player)
                            <button type="button" class="match-picker__option" data-id="{{ $player->id }}"
                                data-name="{{ $player->name }}">
                                {{ $player->name }}
                            </button>
                            @endforeach

                        </div>

                        <p class="match-picker__empty" hidden>
                            Pemain tidak ditemukan.
                        </p>

                    </div>
                </div>

                @endforeach

            </div>

            @endforeach

        </div>

    </section>

    {{-- Skor dan Kok --}}
    <section class="match-section">

        <div class="match-section__heading">
            <h3>Skor Pertandingan</h3>
        </div>

        <div class="match-scoreboard">

            {{-- Set 1 --}}
            <div class="match-scoreboard__set">

                <label>SET 1</label>

                <div class="match-scoreboard__inputs">

                    <input type="number" name="set1_a" min="0" max="99" step="1" inputmode="numeric"
                        aria-label="Set 1 Tim A" placeholder="A" value="{{ old('set1_a') }}">

                    <span>:</span>

                    <input type="number" name="set1_b" min="0" max="99" step="1" inputmode="numeric"
                        aria-label="Set 1 Tim B" placeholder="B" value="{{ old('set1_b') }}">

                </div>

                <small>Tim A : Tim B</small>

            </div>

            {{-- Jumlah Kok --}}
            <div class="match-scoreboard__cock">

                <label for="shuttlecock_count">
                    KOK
                </label>

                <input id="shuttlecock_count" type="number" name="shuttlecock_count" min="0" max="65535" step="1"
                    inputmode="numeric" value="{{ old('shuttlecock_count', 0) }}" required>

            </div>

            {{-- Set 2 --}}
            <div class="match-scoreboard__set">

                <label>SET 2</label>

                <div class="match-scoreboard__inputs">

                    <input type="number" name="set2_a" min="0" max="99" step="1" inputmode="numeric"
                        aria-label="Set 2 Tim A" placeholder="A" value="{{ old('set2_a') }}">

                    <span>:</span>

                    <input type="number" name="set2_b" min="0" max="99" step="1" inputmode="numeric"
                        aria-label="Set 2 Tim B" placeholder="B" value="{{ old('set2_b') }}">

                </div>

                <small>Tim A : Tim B</small>

            </div>

        </div>

        {{-- Preview hasil --}}
        <div class="match-result-preview" aria-live="polite">
            <span>Hasil Pertandingan</span>

            <strong id="match-result-text">
                Belum ada hasil
            </strong>
        </div>

        <p style="
            margin: 10px 0 0;
            color: #64748b;
            font-size: 12px;
            line-height: 1.6;
        ">
            Skor boleh dikosongkan apabila pertandingan
            belum selesai. Jika ingin mengisi skor,
            semua skor kedua set wajib dilengkapi.
        </p>

    </section>

    {{-- Aksi --}}
    <div class="match-form__actions">

        <button type="submit" class="match-submit">
            Simpan Pertandingan
        </button>

        <a href="{{ route('matches.index') }}" class="match-cancel">
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
    const resultText = document.getElementById('match-result-text');

    const scoreFields = [
        'set1_a',
        'set1_b',
        'set2_a',
        'set2_b'
    ];

    const normalize = value => String(value)
        .normalize('NFKC')
        .toLocaleLowerCase('id-ID')
        .trim();

    // Jenis pertandingan
    function currentType() {
        return form.querySelector(
            'input[name="type"]:checked'
        )?.value || 'ganda';
    }

    // Tutup semua pilihan pemain
    function closeAll() {
        pickers.forEach(picker => {
            const menu = picker.querySelector(
                '.match-picker__menu'
            );

            const trigger = picker.querySelector(
                '.match-picker__trigger'
            );

            menu.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
        });
    }

    // Perbarui nama pemain yang dipilih
    function updateLabel(picker) {
        const value = picker.querySelector(
            'input[type="hidden"]'
        ).value;

        const option = Array.from(
            picker.querySelectorAll('.match-picker__option')
        ).find(item => item.dataset.id === value);

        picker.querySelector(
            '.match-picker__label'
        ).textContent = option?.dataset.name
            || picker.dataset.placeholder;
    }

    // Filter dan cegah pemain ganda
    function refreshOptions() {
        const active = pickers.filter(
            picker => !picker.hidden
        );

        active.forEach(picker => {
            const keyword = normalize(
                picker.querySelector(
                    '.match-picker__search'
                ).value
            );

            const used = new Set(
                active
                    .filter(other => other !== picker)
                    .map(other => other.querySelector(
                        'input[type="hidden"]'
                    ).value)
                    .filter(value =>
                        value && value !== 'guest'
                    )
            );

            let available = 0;

            picker.querySelectorAll(
                '.match-picker__option'
            ).forEach(option => {

                const taken =
                    option.dataset.id !== 'guest'
                    && used.has(option.dataset.id);

                const matches = normalize(
                    option.dataset.name
                ).includes(keyword);

                option.hidden = !matches || taken;
                option.disabled = taken;

                if (matches && !taken) {
                    available++;
                }
            });

            picker.querySelector(
                '.match-picker__empty'
            ).hidden = available > 0;
        });
    }

    // Tunggal hanya menggunakan A1 dan B1
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

    // Event pilihan pemain
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

                trigger.setAttribute(
                    'aria-expanded',
                    'true'
                );

                search.value = '';
                refreshOptions();
                search.focus();
            }
        });

        search.addEventListener(
            'input',
            refreshOptions
        );

        picker.querySelectorAll(
            '.match-picker__option'
        ).forEach(option => {

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

    form.querySelectorAll(
        'input[name="type"]'
    ).forEach(input => {
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

    // Ambil kondisi skor saat ini
    function getScoreState() {
        const inputs = scoreFields.map(
            name => form.elements.namedItem(name)
        );

        const values = inputs.map(input => input.value);

        if (values.every(value => value === '')) {
            return {
                status: 'pending',
                text: 'Belum ada hasil'
            };
        }

        if (values.some(value => value === '')) {
            return {
                status: 'incomplete',
                text: 'Lengkapi semua skor kedua set'
            };
        }

        if (inputs.some(input => !input.validity.valid)) {
            return {
                status: 'invalid',
                text: 'Skor harus antara 0 sampai 99'
            };
        }

        const [a1, b1, a2, b2] = values.map(Number);

        if (a1 === b1 || a2 === b2) {
            return {
                status: 'invalid',
                text: 'Skor dalam satu set tidak boleh seri'
            };
        }

        const winsA =
            Number(a1 > b1)
            + Number(a2 > b2);

        return {
            status: 'complete',
            text: winsA === 2
                ? 'Tim A menang 2–0'
                : winsA === 0
                    ? 'Tim B menang 2–0'
                    : 'Seri 1–1'
        };
    }

    // Preview hasil otomatis
    function updateResult() {
        const state = getScoreState();

        resultText.textContent = state.text;
    }

    scoreFields.forEach(name => {
        form.elements.namedItem(name)
            .addEventListener('input', updateResult);
    });

    // Validasi sebelum submit
    form.addEventListener('submit', function (event) {

        const slots = currentType() === 'ganda'
            ? ['a1', 'a2', 'b1', 'b2']
            : ['a1', 'b1'];

        const values = slots.map(slot =>
            form.elements.namedItem(slot)?.value || ''
        );

        const realIds = values.filter(
            value => value && value !== 'guest'
        );

        let message = '';

        // Semua pemain harus dipilih
        if (values.some(value => !value)) {
            message = 'Pilih semua pemain terlebih dahulu.';
        }

        // Pemain tidak boleh dipilih dua kali
        else if (
            new Set(realIds).size !== realIds.length
        ) {
            message =
                'Pemain yang sama tidak boleh dipilih dua kali.';
        }

        // Validasi skor
        if (!message) {
            const scoreState = getScoreState();

            if (
                scoreState.status === 'incomplete'
                || scoreState.status === 'invalid'
            ) {
                message = scoreState.text + '.';
            }
        }

        if (!message) {
            error.hidden = true;
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        if (window.PBAlfaDialog) {
            error.hidden = true;

            window.PBAlfaDialog.error(
                message,
                'Pertandingan Belum Disimpan'
            );
        } else {
            error.textContent = message;
            error.hidden = false;

            error.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    });

    // Inisialisasi
    updateType();
    updateResult();

});
</script>
@endpush