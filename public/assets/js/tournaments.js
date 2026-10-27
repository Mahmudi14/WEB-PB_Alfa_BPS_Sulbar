/* PB Alfa tournament UI: no framework or build step required. */
(() => {
    "use strict";
    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) =>
        Array.from(root.querySelectorAll(selector));

    function rulesForm() {
    const formatInputs = $$('input[name="format"]');

    const scoreDraw = $(
        'input[name="set_format"][value="two_draw"]'
    );

    if (!formatInputs.length || !scoreDraw) {
        return;
    }

    const notice = $('#tr-cup-notice');
    const leagueSettings = $('#tr-league-settings');

    const update = () => {
        const isCup = $(
            'input[name="format"]:checked'
        )?.value === 'cup';

        // Cup tidak boleh seri
        scoreDraw.disabled = isCup;

        if (isCup && scoreDraw.checked) {
            $(
                'input[name="set_format"][value="two_aggregate"]'
            ).checked = true;
        }

        if (notice) {
            notice.hidden = !isCup;
        }

        // Pengaturan poin dan pertemuan khusus Liga
        if (leagueSettings) {
            leagueSettings.hidden = isCup;

            $$('input', leagueSettings).forEach(input => {
                input.disabled = isCup;
            });
        }
    };

    formatInputs.forEach(input => {
        input.addEventListener('change', update);
    });

    update();
}

    
function entryForm() {
    const form = document.getElementById('tr-entries-form');

    if (!form) return;

    const wrap = document.getElementById('tr-entry-rows');
    const template = document.getElementById('tr-entry-template');
    const addButton = document.getElementById('tr-add-entry');
    const error = document.getElementById('tr-entry-error');

    const getRows = () =>
        Array.from(wrap.querySelectorAll('[data-entry-row]'));

    const getPickers = () =>
        Array.from(wrap.querySelectorAll('[data-player-picker]'));

    const normalize = (text) =>
        String(text || '')
            .normalize('NFKC')
            .toLocaleLowerCase('id-ID')
            .trim();


    // Tutup semua menu pemilihan pemain
    function closeMenus() {
        getPickers().forEach(picker => {

            picker.querySelector(
                '[data-player-menu]'
            ).hidden = true;

            picker.querySelector(
                '[data-player-trigger]'
            ).setAttribute('aria-expanded', 'false');

        });
    }


    // Perbarui label dan daftar pemain tersedia
    function refresh() {
        const all = getPickers();

        all.forEach(picker => {
            const input = picker.querySelector(
                '[data-player-value]'
            );

            const selected = input.value;

            const option = Array.from(
                picker.querySelectorAll('[data-player-option]')
            ).find(item => item.dataset.id === selected);

            picker.querySelector(
                '[data-player-label]'
            ).textContent = option?.dataset.name || 'Pilih pemain';

            picker.querySelector(
                '[data-player-trigger]'
            ).classList.toggle('is-selected', !!option);


            // Cari pemain yang dipilih di slot lain
            const used = new Set(
                all
                    .filter(other => other !== picker)
                    .map(other => other.querySelector(
                        '[data-player-value]'
                    ).value)
                    .filter(Boolean)
            );

            const keyword = normalize(
                picker.querySelector('[data-player-search]').value
            );

            let available = 0;

            picker.querySelectorAll(
                '[data-player-option]'
            ).forEach(item => {

                const taken = used.has(item.dataset.id);

                const match = normalize(
                    item.dataset.name
                ).includes(keyword);

                item.disabled = taken;
                item.hidden = taken || !match;

                if (!item.hidden) available++;

            });

            picker.querySelector(
                '[data-player-empty]'
            ).hidden = available > 0;
        });
    }


    // Nomor peserta dan nama input ke Laravel
    function updateRows() {
        const rows = getRows();

        rows.forEach((row, i) => {

            row.querySelector(
                '[data-entry-label]'
            ).textContent = String(i + 1);

            row.querySelectorAll(
                '[data-player-picker]'
            ).forEach((picker, slot) => {

                picker.querySelector(
                    '[data-player-value]'
                ).name = `entries[${i}][players][${slot}]`;

                picker.querySelector(
                    '[data-player-trigger]'
                ).setAttribute(
                    'aria-label',
                    `Peserta ${i + 1}, pemain ${slot + 1}`
                );
            });

            row.querySelector(
                '[data-entry-remove]'
            ).disabled = rows.length <= 2;

        });

        addButton.disabled = rows.length >= 32;

        refresh();
    }


    // Klik pilihan pemain, buka menu, dan hapus peserta
    wrap.addEventListener('click', (event) => {

        const option = event.target.closest(
            '[data-player-option]'
        );

        if (option && !option.disabled) {
            const picker = option.closest(
                '[data-player-picker]'
            );

            picker.querySelector(
                '[data-player-value]'
            ).value = option.dataset.id;

            closeMenus();
            refresh();

            error.hidden = true;
            return;
        }


        // Buka dropdown
        const trigger = event.target.closest(
            '[data-player-trigger]'
        );

        if (trigger) {
            const picker = trigger.closest(
                '[data-player-picker]'
            );

            const menu = picker.querySelector(
                '[data-player-menu]'
            );

            const wasOpen = !menu.hidden;

            closeMenus();

            if (!wasOpen) {
                menu.hidden = false;

                trigger.setAttribute(
                    'aria-expanded',
                    'true'
                );

                const search = picker.querySelector(
                    '[data-player-search]'
                );

                search.value = '';

                refresh();
                search.focus();
            }

            return;
        }


        // Hapus peserta
        const remove = event.target.closest(
            '[data-entry-remove]'
        );

        if (remove && getRows().length > 2) {
            closeMenus();

            remove.closest(
                '[data-entry-row]'
            ).remove();

            updateRows();

            error.hidden = true;
        }
    });


    // Pencarian langsung ketika mengetik
    wrap.addEventListener('input', (event) => {
        if (event.target.matches('[data-player-search]')) {
            refresh();
        }
    });


    // Tambah peserta atau pasangan baru
    addButton.addEventListener('click', () => {
        if (getRows().length >= 32) return;

        closeMenus();

        wrap.appendChild(
            template.content.cloneNode(true)
        );

        updateRows();

        getRows().at(-1)?.querySelector(
            '[data-player-trigger]'
        )?.focus();
    });


    // Tutup menu ketika klik di luar
    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-player-picker]')) {
            closeMenus();
        }
    });


    // Tutup dengan tombol Escape
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenus();
        }
    });


    // Validasi sebelum menyimpan
    form.addEventListener('submit', (event) => {

        const values = getPickers().map(picker =>
            picker.querySelector('[data-player-value]').value
        );

        let message = '';

        if (getRows().length < 2) {
            message = 'Minimal harus ada 2 peserta.';
        } else if (values.some(value => !value)) {
            message = 'Pilih semua pemain terlebih dahulu.';
        } else if (new Set(values).size !== values.length) {
            message =
                'Pemain yang sama tidak boleh dipilih dua kali dalam turnamen.';
        }

        if (!message) {
            error.hidden = true;
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        error.textContent = message;
        error.hidden = false;

        error.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest'
        });

    }, true);


    // Inisialisasi
    updateRows();
}


    function orderForm() {
        const form = $("#tr-preview-form");
        if (!form) return;
        const selects = $$("[data-order-select]", form);
        const initial = selects.map((s) => s.value);
        const error = $("#tr-order-error");
        const start = $('#tr-start-form button[type="submit"]');
        let previousValues = selects.map((s) => s.value);
        const dirty = () => {
            // Block creation with unpreviewed changes: generated fixtures must match preview.
            if (start)
                start.disabled = selects.some((s, i) => s.value !== initial[i]);
        };
        selects.forEach((select, index) =>
            select.addEventListener("change", () => {
                const old = previousValues[index];
                const duplicateIndex = selects.findIndex(
                    (s, i) => i !== index && s.value === select.value,
                );
                if (duplicateIndex >= 0) selects[duplicateIndex].value = old;
                previousValues = selects.map((s) => s.value);
                error.hidden = true;
                dirty();
            }),
        );
        form.addEventListener("submit", (event) => {
            const values = selects.map((s) => s.value);
            if (
                values.some((v) => !v) ||
                new Set(values).size !== values.length
            ) {
                event.preventDefault();
                error.hidden = false;
            }
        });
        $("#tr-shuffle")?.addEventListener("click", () => {
            const values = selects.map((s) => s.value);
            for (let i = values.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [values[i], values[j]] = [values[j], values[i]];
            }
            selects.forEach((s, i) => {
                s.value = values[i];
            });
            previousValues = values;
            form.requestSubmit();
        });
        dirty();
    }

    function roundWidget() {
        const container = $("[data-round-widget]");
        if (!container) return;
        const panes = $$("[data-round-pane]", container);
        if (!panes.length) return;
        const heading = $("[data-round-title]", container);
        const prev = $("[data-round-prev]", container);
        const next = $("[data-round-next]", container);
        const search = new URLSearchParams(window.location.search);
        const requested = Number(search.get("ronde"));
        let index =
            Number.isInteger(requested) &&
            requested > 0 &&
            requested <= panes.length
                ? requested - 1
                : 0;
        const render = () => {
            panes.forEach((pane, i) => {
                pane.hidden = i !== index;
            });
            heading.textContent = `Ronde ${index + 1}`;
            prev.disabled = index === 0;
            next.disabled = index === panes.length - 1;
            const url = new URL(window.location.href);
            if (index === 0) url.searchParams.delete("ronde");
            else url.searchParams.set("ronde", String(index + 1));
            window.history.replaceState(null, "", url.toString());
        };
        prev.addEventListener("click", () => {
            index = Math.max(0, index - 1);
            render();
        });
        next.addEventListener("click", () => {
            index = Math.min(panes.length - 1, index + 1);
            render();
        });
        render();
    }
    document.addEventListener("DOMContentLoaded", () => {
        rulesForm();
        entryForm();
        orderForm();
        roundWidget();
    });
})();
