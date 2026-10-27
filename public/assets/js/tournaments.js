/* PB Alfa tournament UI: no framework or build step required. */
(() => {
    "use strict";
    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) =>
        Array.from(root.querySelectorAll(selector));

    function rulesForm() {
        const formatInputs = $$('input[name="format"]');

        const scoreDraw = $('input[name="set_format"][value="two_draw"]');

        if (!formatInputs.length || !scoreDraw) {
            return;
        }

        const notice = $("#tr-cup-notice");
        const leagueSettings = $("#tr-league-settings");

        const update = () => {
            const isCup = $('input[name="format"]:checked')?.value === "cup";

            // Cup tidak boleh seri
            scoreDraw.disabled = isCup;

            if (isCup && scoreDraw.checked) {
                $('input[name="set_format"][value="two_aggregate"]').checked =
                    true;
            }

            if (notice) {
                notice.hidden = !isCup;
            }

            // Pengaturan poin dan pertemuan khusus Liga
            if (leagueSettings) {
                leagueSettings.hidden = isCup;

                $$("input", leagueSettings).forEach((input) => {
                    input.disabled = isCup;
                });
            }
        };

        formatInputs.forEach((input) => {
            input.addEventListener("change", update);
        });

        update();
    }

    function entryForm() {
        const form = document.getElementById("tr-entries-form");

        if (!form) return;

        const wrap = document.getElementById("tr-entry-rows");
        const template = document.getElementById("tr-entry-template");
        const addButton = document.getElementById("tr-add-entry");
        const error = document.getElementById("tr-entry-error");

        const getRows = () =>
            Array.from(wrap.querySelectorAll("[data-entry-row]"));

        const getPickers = () =>
            Array.from(wrap.querySelectorAll("[data-player-picker]"));

        const normalize = (text) =>
            String(text || "")
                .normalize("NFKC")
                .toLocaleLowerCase("id-ID")
                .trim();

        // Tutup semua menu pemilihan pemain
        function closeMenus() {
            getPickers().forEach((picker) => {
                picker.querySelector("[data-player-menu]").hidden = true;

                picker
                    .querySelector("[data-player-trigger]")
                    .setAttribute("aria-expanded", "false");
            });
        }

        // Perbarui label dan daftar pemain tersedia
        function refresh() {
            const all = getPickers();

            all.forEach((picker) => {
                const input = picker.querySelector("[data-player-value]");

                const selected = input.value;

                const option = Array.from(
                    picker.querySelectorAll("[data-player-option]"),
                ).find((item) => item.dataset.id === selected);

                picker.querySelector("[data-player-label]").textContent =
                    option?.dataset.name || "Pilih pemain";

                picker
                    .querySelector("[data-player-trigger]")
                    .classList.toggle("is-selected", !!option);

                // Cari pemain yang dipilih di slot lain
                const used = new Set(
                    all
                        .filter((other) => other !== picker)
                        .map(
                            (other) =>
                                other.querySelector("[data-player-value]")
                                    .value,
                        )
                        .filter(Boolean),
                );

                const keyword = normalize(
                    picker.querySelector("[data-player-search]").value,
                );

                let available = 0;

                picker
                    .querySelectorAll("[data-player-option]")
                    .forEach((item) => {
                        const taken = used.has(item.dataset.id);

                        const match = normalize(item.dataset.name).includes(
                            keyword,
                        );

                        item.disabled = taken;
                        item.hidden = taken || !match;

                        if (!item.hidden) available++;
                    });

                picker.querySelector("[data-player-empty]").hidden =
                    available > 0;
            });
        }

        // Nomor peserta dan nama input ke Laravel
        function updateRows() {
            const rows = getRows();

            rows.forEach((row, i) => {
                row.querySelector("[data-entry-label]").textContent = String(
                    i + 1,
                );

                row.querySelectorAll("[data-player-picker]").forEach(
                    (picker, slot) => {
                        picker.querySelector("[data-player-value]").name =
                            `entries[${i}][players][${slot}]`;

                        picker
                            .querySelector("[data-player-trigger]")
                            .setAttribute(
                                "aria-label",
                                `Peserta ${i + 1}, pemain ${slot + 1}`,
                            );
                    },
                );

                row.querySelector("[data-entry-remove]").disabled =
                    rows.length <= 2;
            });

            addButton.disabled = rows.length >= 32;

            refresh();
        }

        // Klik pilihan pemain, buka menu, dan hapus peserta
        wrap.addEventListener("click", (event) => {
            const option = event.target.closest("[data-player-option]");

            if (option && !option.disabled) {
                const picker = option.closest("[data-player-picker]");

                picker.querySelector("[data-player-value]").value =
                    option.dataset.id;

                closeMenus();
                refresh();

                error.hidden = true;
                return;
            }

            // Buka dropdown
            const trigger = event.target.closest("[data-player-trigger]");

            if (trigger) {
                const picker = trigger.closest("[data-player-picker]");

                const menu = picker.querySelector("[data-player-menu]");

                const wasOpen = !menu.hidden;

                closeMenus();

                if (!wasOpen) {
                    menu.hidden = false;

                    trigger.setAttribute("aria-expanded", "true");

                    const search = picker.querySelector("[data-player-search]");

                    search.value = "";

                    refresh();
                    search.focus();
                }

                return;
            }

            // Hapus peserta
            const remove = event.target.closest("[data-entry-remove]");

            if (remove && getRows().length > 2) {
                closeMenus();

                remove.closest("[data-entry-row]").remove();

                updateRows();

                error.hidden = true;
            }
        });

        // Pencarian langsung ketika mengetik
        wrap.addEventListener("input", (event) => {
            if (event.target.matches("[data-player-search]")) {
                refresh();
            }
        });

        // Tambah peserta atau pasangan baru
        addButton.addEventListener("click", () => {
            if (getRows().length >= 32) return;

            closeMenus();

            wrap.appendChild(template.content.cloneNode(true));

            updateRows();

            getRows().at(-1)?.querySelector("[data-player-trigger]")?.focus();
        });

        // Tutup menu ketika klik di luar
        document.addEventListener("click", (event) => {
            if (!event.target.closest("[data-player-picker]")) {
                closeMenus();
            }
        });

        // Tutup dengan tombol Escape
        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                closeMenus();
            }
        });

        // Validasi sebelum menyimpan
        form.addEventListener(
            "submit",
            (event) => {
                const values = getPickers().map(
                    (picker) =>
                        picker.querySelector("[data-player-value]").value,
                );

                let message = "";

                if (getRows().length < 2) {
                    message = "Minimal harus ada 2 peserta.";
                } else if (values.some((value) => !value)) {
                    message = "Pilih semua pemain terlebih dahulu.";
                } else if (new Set(values).size !== values.length) {
                    message =
                        "Pemain yang sama tidak boleh dipilih dua kali dalam turnamen.";
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
                    behavior: "smooth",
                    block: "nearest",
                });
            },
            true,
        );

        // Inisialisasi
        updateRows();
    }

    function orderForm() {
        const form = $("#tr-preview-form");

        if (!form) return;

        const pickers = $$("[data-order-picker]", form);
        const error = $("#tr-order-error");
        const shuffle = $("#tr-shuffle");

        const startButton = $('#tr-start-form button[type="submit"]');

        const outdated = $("#tr-preview-outdated");

        const getInput = (picker) => $("[data-order-value]", picker);

        const getValues = () => pickers.map((picker) => getInput(picker).value);

        const originalValues = getValues();

        function normalize(value) {
            return String(value ?? "")
                .normalize("NFKC")
                .toLocaleLowerCase("id-ID")
                .trim();
        }

        function closeMenus() {
            pickers.forEach((picker) => {
                const menu = $("[data-order-menu]", picker);
                const trigger = $("[data-order-trigger]", picker);

                menu.hidden = true;
                trigger.setAttribute("aria-expanded", "false");
            });
        }

        function refreshOptions(picker) {
            const keyword = normalize($("[data-order-search]", picker).value);

            const selected = getInput(picker).value;

            let visible = 0;

            $$("[data-order-option]", picker).forEach((option) => {
                const matches = normalize(option.dataset.name).includes(
                    keyword,
                );

                option.hidden = !matches;

                option.setAttribute(
                    "aria-selected",
                    String(option.dataset.id === selected),
                );

                if (matches) visible++;
            });

            $("[data-order-empty]", picker).hidden = visible > 0;
        }

        function refreshLabels() {
            pickers.forEach((picker) => {
                const selected = getInput(picker).value;

                const option = $$("[data-order-option]", picker).find(
                    (item) => item.dataset.id === selected,
                );

                $("[data-order-label]", picker).textContent =
                    option?.dataset.name ?? "Pilih peserta";

                $("[data-order-trigger]", picker).classList.toggle(
                    "is-selected",
                    Boolean(option),
                );

                refreshOptions(picker);
            });
        }

        function refreshState() {
            const values = getValues();

            const changed = values.some(
                (value, index) => value !== originalValues[index],
            );

            // Jangan mulai turnamen dengan bagan lama.
            if (startButton) {
                startButton.disabled = changed;
            }

            if (outdated) {
                outdated.hidden = !changed;
            }
        }

        function validate() {
            const values = getValues();

            if (
                values.some((value) => !value) ||
                new Set(values).size !== values.length
            ) {
                error.textContent =
                    "Setiap posisi harus memiliki peserta yang berbeda.";

                error.hidden = false;
                return false;
            }

            if (
                values.some(
                    (value) =>
                        !$$("[data-order-option]", pickers[0]).some(
                            (option) => option.dataset.id === value,
                        ),
                )
            ) {
                error.textContent = "Terdapat peserta yang tidak valid.";

                error.hidden = false;
                return false;
            }

            error.hidden = true;
            return true;
        }

        // Buka dropdown, cari, lalu pilih peserta.
        form.addEventListener("click", (event) => {
            const option = event.target.closest("[data-order-option]");

            if (option) {
                const picker = option.closest("[data-order-picker]");

                const input = getInput(picker);

                const previous = input.value;
                const selected = option.dataset.id;

                if (previous !== selected) {
                    // Semua posisi telah terisi.
                    // Jika peserta menempati posisi lain,
                    // tukar kedua peserta.
                    const other = pickers.find(
                        (item) =>
                            item !== picker &&
                            getInput(item).value === selected,
                    );

                    if (other) {
                        getInput(other).value = previous;
                    }

                    input.value = selected;
                }

                closeMenus();
                refreshLabels();
                refreshState();

                error.hidden = true;
                return;
            }

            const trigger = event.target.closest("[data-order-trigger]");

            if (!trigger) return;

            const picker = trigger.closest("[data-order-picker]");

            const menu = $("[data-order-menu]", picker);
            const wasOpen = !menu.hidden;

            closeMenus();

            if (!wasOpen) {
                menu.hidden = false;

                trigger.setAttribute("aria-expanded", "true");

                const search = $("[data-order-search]", picker);

                search.value = "";
                refreshOptions(picker);
                search.focus();
            }
        });

        form.addEventListener("input", (event) => {
            if (!event.target.matches("[data-order-search]")) {
                return;
            }

            refreshOptions(event.target.closest("[data-order-picker]"));
        });

        // Klik di luar menutup dropdown.
        document.addEventListener("click", (event) => {
            if (!event.target.closest("[data-order-picker]")) {
                closeMenus();
            }
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                closeMenus();
            }
        });

        // Acak seluruh peserta.
        shuffle?.addEventListener("click", () => {
            const values = getValues();

            for (let i = values.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));

                [values[i], values[j]] = [values[j], values[i]];
            }

            pickers.forEach((picker, index) => {
                getInput(picker).value = values[index];
            });

            closeMenus();
            refreshLabels();
            refreshState();

            // Generate ulang pratinjau.
            form.requestSubmit();
        });

        form.addEventListener("submit", (event) => {
            if (!validate()) {
                event.preventDefault();
                error.scrollIntoView({
                    behavior: "smooth",
                    block: "nearest",
                });
            }
        });

        refreshLabels();
        refreshState();
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

    function pinchPreview() {
        const viewers = document.querySelectorAll("[data-pinch-viewport]");

        viewers.forEach((viewport) => {
            const stage = viewport.querySelector("[data-pinch-stage]");

            const content = viewport.querySelector("[data-pinch-content]");

            if (!stage || !content) return;

            const MIN = 0.25;
            const MAX = 3;

            let scale = 1;

            const baseWidth = Math.max(1, Math.ceil(content.scrollWidth));

            const baseHeight = Math.max(1, Math.ceil(content.scrollHeight));

            const pointers = new Map();

            let gesture = null;

            const clamp = (value) => Math.max(MIN, Math.min(MAX, value));

            function applyScale(
                nextScale,
                clientX,
                clientY,
                worldAnchor = null,
            ) {
                const rect = viewport.getBoundingClientRect();

                const localX = clientX - rect.left;
                const localY = clientY - rect.top;

                const worldX = worldAnchor
                    ? worldAnchor.x
                    : (viewport.scrollLeft + localX) / scale;

                const worldY = worldAnchor
                    ? worldAnchor.y
                    : (viewport.scrollTop + localY) / scale;

                scale = clamp(nextScale);

                stage.style.width = Math.ceil(baseWidth * scale) + "px";

                stage.style.height = Math.ceil(baseHeight * scale) + "px";

                content.style.transform = `scale(${scale})`;

                viewport.scrollLeft = worldX * scale - localX;

                viewport.scrollTop = worldY * scale - localY;
            }

            function midpoint(a, b) {
                return {
                    x: (a.x + b.x) / 2,
                    y: (a.y + b.y) / 2,
                };
            }

            function distance(a, b) {
                return Math.hypot(a.x - b.x, a.y - b.y);
            }

            function beginPan(pointer) {
                gesture = {
                    type: "pan",
                    x: pointer.x,
                    y: pointer.y,
                    scrollX: viewport.scrollLeft,
                    scrollY: viewport.scrollTop,
                };
            }

            function beginPinch() {
                const [a, b] = Array.from(pointers.values());

                if (!a || !b) return;

                const center = midpoint(a, b);

                const rect = viewport.getBoundingClientRect();

                gesture = {
                    type: "pinch",
                    distance: Math.max(1, distance(a, b)),
                    initialScale: scale,

                    anchor: {
                        x: (viewport.scrollLeft + center.x - rect.left) / scale,

                        y: (viewport.scrollTop + center.y - rect.top) / scale,
                    },
                };
            }

            // Siapkan ukuran kanvas zoom.
            stage.style.width = baseWidth + "px";
            stage.style.height = baseHeight + "px";

            stage.classList.add("is-ready");

            content.style.transform = "scale(1)";

            viewport.addEventListener("pointerdown", (event) => {
                if (event.pointerType === "mouse" && event.button !== 0) {
                    return;
                }

                // Link Edit tetap bisa diklik.
                if (event.target.closest("a, button, input, select")) {
                    return;
                }

                if (pointers.size >= 2) return;

                pointers.set(event.pointerId, {
                    x: event.clientX,
                    y: event.clientY,
                });

                viewport.setPointerCapture(event.pointerId);

                if (pointers.size === 2) {
                    beginPinch();
                } else {
                    beginPan({
                        x: event.clientX,
                        y: event.clientY,
                    });
                }
            });

            viewport.addEventListener(
                "pointermove",
                (event) => {
                    if (!pointers.has(event.pointerId)) return;

                    pointers.set(event.pointerId, {
                        x: event.clientX,
                        y: event.clientY,
                    });

                    if (pointers.size === 2) {
                        if (gesture?.type !== "pinch") {
                            beginPinch();
                        }

                        const [a, b] = Array.from(pointers.values());

                        const center = midpoint(a, b);

                        const ratio = distance(a, b) / gesture.distance;

                        applyScale(
                            gesture.initialScale * ratio,
                            center.x,
                            center.y,
                            gesture.anchor,
                        );
                    } else if (pointers.size === 1 && gesture?.type === "pan") {
                        const pointer = Array.from(pointers.values())[0];

                        viewport.scrollLeft =
                            gesture.scrollX - (pointer.x - gesture.x);

                        viewport.scrollTop =
                            gesture.scrollY - (pointer.y - gesture.y);
                    }

                    event.preventDefault();
                },
                { passive: false },
            );

            function endPointer(event) {
                pointers.delete(event.pointerId);

                if (pointers.size === 1) {
                    beginPan(Array.from(pointers.values())[0]);
                } else {
                    gesture = null;
                }
            }

            viewport.addEventListener("pointerup", endPointer);

            viewport.addEventListener("pointercancel", endPointer);

            // Dukungan pinch trackpad desktop.
            viewport.addEventListener(
                "wheel",
                (event) => {
                    if (!event.ctrlKey) return;

                    event.preventDefault();

                    const factor = Math.exp(-event.deltaY * 0.01);

                    applyScale(scale * factor, event.clientX, event.clientY);
                },
                { passive: false },
            );
        });
    }

    document.addEventListener("DOMContentLoaded", () => {
        rulesForm();
        entryForm();
        orderForm();
        pinchPreview();
        roundWidget();
    });
})();
