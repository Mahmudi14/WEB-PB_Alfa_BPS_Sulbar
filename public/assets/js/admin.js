"use strict";

document.addEventListener("DOMContentLoaded", function () {
    /*
    |--------------------------------------------------------------------------
    | SIDEBAR MOBILE
    |--------------------------------------------------------------------------
    */

    const body = document.body;

    const sidebar = document.getElementById("admin-sidebar");

    const toggle = document.querySelector("[data-admin-toggle]");

    const backdrop = document.querySelector("[data-admin-backdrop]");

    const mobile = window.matchMedia("(max-width: 900px)");

    if (sidebar && toggle && backdrop) {
        function setSidebarOpen(open, restoreFocus = false) {
            const shouldOpen = mobile.matches && open;

            if (!shouldOpen && sidebar.contains(document.activeElement)) {
                toggle.focus();
            }

            body.classList.toggle("adm-sidebar-open", shouldOpen);

            sidebar.inert = mobile.matches && !shouldOpen;

            toggle.setAttribute("aria-expanded", String(shouldOpen));

            toggle.setAttribute(
                "aria-label",
                shouldOpen
                    ? "Tutup menu administrator"
                    : "Buka menu administrator",
            );

            if (shouldOpen) {
                sidebar.querySelector("a")?.focus();
            } else if (restoreFocus) {
                toggle.focus();
            }
        }

        setSidebarOpen(false);

        toggle.addEventListener("click", function () {
            setSidebarOpen(!body.classList.contains("adm-sidebar-open"));
        });

        backdrop.addEventListener("click", function () {
            setSidebarOpen(false, true);
        });

        sidebar.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                if (mobile.matches) {
                    setSidebarOpen(false);
                }
            });
        });

        document.addEventListener("keydown", function (event) {
            if (
                event.key === "Escape" &&
                body.classList.contains("adm-sidebar-open")
            ) {
                setSidebarOpen(false, true);
            }
        });

        mobile.addEventListener("change", function () {
            setSidebarOpen(false);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | DIALOG GLOBAL ADMIN
    |--------------------------------------------------------------------------
    */

    const dialog = document.getElementById("adm-dialog");

    const dialogTitle = document.getElementById("adm-dialog-title");

    const dialogMessage = document.getElementById("adm-dialog-message");

    const dialogErrors = document.getElementById("adm-dialog-errors");

    const dialogSymbol = document.getElementById("adm-dialog-symbol");

    const dialogCancel = document.getElementById("adm-dialog-cancel");

    const dialogOk = document.getElementById("adm-dialog-ok");

    let confirmAction = null;
    let previousFocus = null;

    function showAdminDialog(options = {}) {
        if (!dialog || dialog.open) {
            return false;
        }

        const mode = options.mode || "info";

        const items = Array.isArray(options.items) ? options.items : [];

        previousFocus = document.activeElement;

        confirmAction =
            typeof options.onConfirm === "function" ? options.onConfirm : null;

        dialog.dataset.mode = mode;

        dialogTitle.textContent = options.title || "Pemberitahuan";

        dialogMessage.textContent = options.message || "";

        dialogMessage.hidden = !options.message;

        dialogSymbol.textContent = mode === "confirm" ? "?" : "!";

        dialogErrors.replaceChildren();

        items.forEach(function (message) {
            const item = document.createElement("li");

            item.textContent = String(message);

            dialogErrors.appendChild(item);
        });

        dialogErrors.hidden = items.length === 0;

        const isConfirm = mode === "confirm" || mode === "danger";

        dialogCancel.hidden = !isConfirm;

        dialogOk.textContent = isConfirm
            ? options.confirmText || "Ya, Lanjutkan"
            : "Mengerti";

        dialogOk.classList.toggle("adm-btn--danger", mode === "danger");

        dialogOk.classList.toggle("adm-btn--primary", mode !== "danger");

        dialog.showModal();

        if (isConfirm) {
            dialogCancel.focus();
        } else {
            dialogOk.focus();
        }

        return true;
    }

    window.AdminDialog = {
        info(message, title = "Pemberitahuan") {
            return showAdminDialog({
                mode: "info",
                title,
                message,
            });
        },

        error(message, title = "Terjadi Kesalahan") {
            return showAdminDialog({
                mode: "error",
                title,
                message,
            });
        },

        validation(items) {
            return showAdminDialog({
                mode: "error",
                title: "Periksa Kembali Data",
                message: "Ada data yang perlu diperbaiki.",
                items,
            });
        },

        confirm(message, callback, options = {}) {
            return showAdminDialog({
                mode: options.destructive ? "danger" : "confirm",

                title: options.title || "Konfirmasi Tindakan",

                message,

                confirmText: options.confirmText || "Ya, Lanjutkan",

                onConfirm: callback,
            });
        },
    };

    if (dialog) {
        dialogCancel.addEventListener("click", function () {
            dialog.close();
        });

        dialogOk.addEventListener("click", function () {
            const action = confirmAction;

            confirmAction = null;

            dialog.close();

            if (typeof action === "function") {
                action();
            }
        });

        dialog.addEventListener("close", function () {
            confirmAction = null;

            if (
                previousFocus &&
                previousFocus.isConnected &&
                typeof previousFocus.focus === "function"
            ) {
                previousFocus.focus();
            }

            previousFocus = null;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI SERVER
    |--------------------------------------------------------------------------
    */

    const serverErrors = document.getElementById("adm-server-errors");

    if (serverErrors) {
        const messages = Array.from(
            serverErrors.querySelectorAll("[data-admin-error]"),
        )
            .map((item) => item.textContent.trim())
            .filter(Boolean);

        if (messages.length > 0) {
            window.AdminDialog.validation(messages);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FORM CONFIRMATION
    |--------------------------------------------------------------------------
    */

    document.addEventListener("submit", function (event) {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (event.defaultPrevented) {
            return;
        }

        if (form.dataset.submitting === "1") {
            event.preventDefault();
            return;
        }

        const confirmation = form.dataset.confirm;

        if (confirmation && form.dataset.adminConfirmed !== "1") {
            event.preventDefault();

            const submitter = event.submitter;

            const methodInput = form.querySelector('input[name="_method"]');

            const isDestructive = methodInput?.value.toUpperCase() === "DELETE";

            const proceed = function () {
                form.dataset.adminConfirmed = "1";

                try {
                    if (
                        submitter &&
                        submitter.form === form &&
                        !submitter.disabled
                    ) {
                        form.requestSubmit(submitter);
                    } else {
                        form.requestSubmit();
                    }
                } finally {
                    delete form.dataset.adminConfirmed;
                }
            };

            const shown = window.AdminDialog.confirm(confirmation, proceed, {
                destructive: isDestructive,

                title:
                    form.dataset.confirmTitle ||
                    (isDestructive
                        ? "Konfirmasi Penghapusan"
                        : "Konfirmasi Tindakan"),

                confirmText:
                    form.dataset.confirmText ||
                    (isDestructive ? "Ya, Hapus" : "Ya, Lanjutkan"),
            });

            if (!shown && !dialog?.open) {
                if (window.confirm(confirmation)) {
                    proceed();
                }
            }

            return;
        }

        if (!form.hasAttribute("data-single-submit")) {
            return;
        }

        form.dataset.submitting = "1";

        const button =
            event.submitter || form.querySelector('button[type="submit"]');

        if (button) {
            button.disabled = true;
        }

        form.setAttribute("aria-busy", "true");
    });

    /*
    |--------------------------------------------------------------------------
    | PENCARIAN DAN FILTER LANGSUNG
    |--------------------------------------------------------------------------
    */

    const filterScopes = document.querySelectorAll("[data-admin-filter-scope]");

    filterScopes.forEach(function (scope) {
        const search = scope.querySelector("[data-admin-search]");

        const filters = Array.from(
            scope.querySelectorAll("select[data-admin-filter]"),
        );

        const rows = Array.from(scope.querySelectorAll("[data-admin-row]"));

        const empty = scope.querySelector("[data-admin-empty]");

        const counter = scope.querySelector("[data-admin-filter-count]");

        function normalize(value) {
            return String(value ?? "")
                .toLocaleLowerCase("id")
                .trim();
        }

        function applyFilters() {
            const keyword = normalize(search?.value);

            let visibleCount = 0;

            rows.forEach(function (row) {
                const searchable = normalize(
                    row.dataset.search || row.textContent,
                );

                const matchSearch =
                    keyword === "" || searchable.includes(keyword);

                const matchFilters = filters.every(function (filter) {
                    const field = filter.dataset.adminFilter;

                    const selected = normalize(filter.value);

                    if (!field || selected === "") {
                        return true;
                    }

                    const actual = normalize(row.dataset[field]);

                    return actual === selected;
                });

                const visible = matchSearch && matchFilters;

                row.hidden = !visible;

                if (visible) {
                    visibleCount++;
                }
            });

            if (empty) {
                empty.hidden = visibleCount > 0 || rows.length === 0;
            }

            if (counter) {
                counter.textContent = String(visibleCount);
            }
        }

        if (search) {
            search.addEventListener("input", applyFilters);
        }

        filters.forEach(function (filter) {
            filter.addEventListener("change", applyFilters);
        });

        applyFilters();
    });
});

/*
|--------------------------------------------------------------------------
| RESTORE FORM SAAT KEMBALI DARI BROWSER
|--------------------------------------------------------------------------
*/

window.addEventListener("pageshow", function () {
    document
        .querySelectorAll("form[data-single-submit]")
        .forEach(function (form) {
            delete form.dataset.submitting;
            delete form.dataset.adminConfirmed;

            form.removeAttribute("aria-busy");

            form.querySelectorAll('button[type="submit"]').forEach(
                function (button) {
                    button.disabled = false;
                },
            );
        });
});

/*
|--------------------------------------------------------------------------
| PROFILE DROPDOWN
|--------------------------------------------------------------------------
*/

document.addEventListener("DOMContentLoaded", function () {
    const profile = document.querySelector("[data-admin-profile]");

    const toggle = document.querySelector("[data-admin-profile-toggle]");

    const menu = document.querySelector("[data-admin-profile-menu]");

    if (!profile || !toggle || !menu) {
        return;
    }

    function isOpen() {
        return !menu.hidden;
    }

    function setOpen(open, restoreFocus = false) {
        menu.hidden = !open;

        toggle.setAttribute("aria-expanded", String(open));

        toggle.setAttribute(
            "aria-label",
            open ? "Tutup menu profil" : "Buka menu profil",
        );

        if (restoreFocus) {
            toggle.focus();
        }
    }

    // Buka atau tutup dropdown

    toggle.addEventListener("click", function () {
        setOpen(!isOpen());
    });

    // Tutup ketika klik di luar

    document.addEventListener("pointerdown", function (event) {
        if (isOpen() && !profile.contains(event.target)) {
            setOpen(false);
        }
    });

    // Tutup dengan Escape

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && isOpen()) {
            event.preventDefault();
            setOpen(false, true);
        }
    });

    // Tutup ketika fokus berpindah keluar

    document.addEventListener("focusin", function (event) {
        if (isOpen() && !profile.contains(event.target)) {
            setOpen(false);
        }
    });
});

/*
|--------------------------------------------------------------------------
| PREMIUM SELECT ADMIN
|--------------------------------------------------------------------------
*/

document.addEventListener("DOMContentLoaded", function () {
    const selects = document.querySelectorAll(
        "select[data-admin-premium-select]",
    );

    let openedDropdown = null;

    function closeDropdown(dropdown, restoreFocus = false) {
        if (!dropdown) return;

        const menu = dropdown.querySelector(".adm-dropdown__menu");

        const trigger = dropdown.querySelector(".adm-dropdown__trigger");

        menu.hidden = true;

        dropdown.classList.remove("is-open", "is-up");

        trigger.setAttribute("aria-expanded", "false");

        if (openedDropdown === dropdown) {
            openedDropdown = null;
        }

        if (restoreFocus) {
            trigger.focus();
        }
    }

    selects.forEach(function (select, index) {
        const shell = select.closest(".adm-select-shell");

        if (!shell || select.multiple) return;

        const dropdown = document.createElement("div");
        dropdown.className = "adm-dropdown";

        const trigger = document.createElement("button");

        trigger.type = "button";
        trigger.className = "adm-dropdown__trigger";
        trigger.setAttribute("aria-haspopup", "listbox");
        trigger.setAttribute("aria-expanded", "false");

        const selectId = select.id || "adm-premium-" + index;

        const menuId = selectId + "-choices";
        const triggerId = selectId + "-trigger";

        trigger.id = triggerId;
        trigger.setAttribute("aria-controls", menuId);

        const label = select.labels?.[0];

        if (label) {
            trigger.setAttribute("aria-label", label.textContent.trim());

            label.htmlFor = triggerId;
        } else {
            trigger.setAttribute(
                "aria-label",
                select.getAttribute("aria-label") || "Pilih opsi",
            );
        }

        const triggerText = document.createElement("span");
        triggerText.className = "adm-dropdown__trigger-text";

        const chevron = document.createElement("span");
        chevron.className = "adm-dropdown__chevron";
        chevron.setAttribute("aria-hidden", "true");

        trigger.append(triggerText, chevron);

        const menu = document.createElement("div");
        menu.id = menuId;
        menu.className = "adm-dropdown__menu";
        menu.setAttribute("role", "listbox");
        menu.setAttribute("aria-label", label?.textContent.trim() || "Pilihan");
        menu.hidden = true;

        const optionButtons = [];

        Array.from(select.options).forEach(function (option) {
            const button = document.createElement("button");

            button.type = "button";
            button.className = "adm-dropdown__option";
            button.setAttribute("role", "option");

            button.dataset.value = option.value;
            button.textContent = option.textContent;

            button.disabled = option.disabled;

            button.addEventListener("click", function () {
                const changed = select.value !== option.value;

                select.value = option.value;

                if (changed) {
                    select.dispatchEvent(
                        new Event("change", {
                            bubbles: true,
                        }),
                    );
                }

                updateDisplay();

                closeDropdown(dropdown, true);
            });

            menu.appendChild(button);
            optionButtons.push(button);
        });

        function updateDisplay() {
            const selected = select.selectedOptions[0];

            triggerText.textContent =
                selected?.textContent.trim() || "Pilih opsi";

            optionButtons.forEach(function (button) {
                const isSelected = button.dataset.value === select.value;

                button.classList.toggle("is-selected", isSelected);

                button.setAttribute("aria-selected", String(isSelected));
            });

            trigger.disabled = select.disabled;
        }

        function openDropdown() {
            if (select.disabled) return;

            if (openedDropdown && openedDropdown !== dropdown) {
                closeDropdown(openedDropdown);
            }

            menu.hidden = false;

            dropdown.classList.add("is-open");

            trigger.setAttribute("aria-expanded", "true");

            const bounds = trigger.getBoundingClientRect();

            const spaceBelow = window.innerHeight - bounds.bottom;

            dropdown.classList.toggle(
                "is-up",
                spaceBelow < 260 && bounds.top > 270,
            );

            openedDropdown = dropdown;

            const selectedButton = optionButtons.find(
                (button) =>
                    button.dataset.value === select.value && !button.disabled,
            );

            const firstButton = optionButtons.find(
                (button) => !button.disabled,
            );

            (selectedButton || firstButton)?.focus();
        }

        trigger.addEventListener("click", function () {
            if (menu.hidden) {
                openDropdown();
            } else {
                closeDropdown(dropdown, true);
            }
        });

        trigger.addEventListener("keydown", function (event) {
            if (event.key === "ArrowDown" || event.key === "ArrowUp") {
                event.preventDefault();
                openDropdown();
            }
        });

        menu.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                event.preventDefault();
                closeDropdown(dropdown, true);
                return;
            }

            if (event.key === "Tab") {
                closeDropdown(dropdown);
                return;
            }

            const enabled = optionButtons.filter((button) => !button.disabled);

            if (!enabled.length) return;

            const currentIndex = enabled.indexOf(document.activeElement);

            let nextIndex = currentIndex;

            if (event.key === "ArrowDown") {
                nextIndex = Math.min(currentIndex + 1, enabled.length - 1);
            } else if (event.key === "ArrowUp") {
                nextIndex = Math.max(currentIndex - 1, 0);
            } else if (event.key === "Home") {
                nextIndex = 0;
            } else if (event.key === "End") {
                nextIndex = enabled.length - 1;
            } else {
                return;
            }

            event.preventDefault();

            enabled[nextIndex]?.focus();
        });

        select.addEventListener("change", updateDisplay);

        dropdown.append(trigger, menu);

        shell.appendChild(dropdown);

        select.hidden = true;

        shell.classList.add("is-enhanced");

        updateDisplay();
    });

    document.addEventListener("pointerdown", function (event) {
        if (openedDropdown && !openedDropdown.contains(event.target)) {
            closeDropdown(openedDropdown);
        }
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && openedDropdown) {
            closeDropdown(openedDropdown, true);
        }
    });
});

/*
|--------------------------------------------------------------------------
| MATCH FORM - REGULER
|--------------------------------------------------------------------------
*/

document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("[data-admin-match-form]");

    if (!form) return;

    const type = form.querySelector('[name="type"]');

    const doubles = Array.from(form.querySelectorAll("[data-match-doubles]"));

    const preview = form.querySelector("[data-match-preview]");

    const fields = ["set1_a", "set1_b", "set2_a", "set2_b"];

    function updateType() {
        if (!type) return;

        const isSingles = type.value === "tunggal";

        doubles.forEach(function (field) {
            field.hidden = isSingles;

            const select = field.querySelector("select");

            if (!select) return;

            select.disabled = isSingles;

            if (isSingles) {
                select.value = "";
            }

            select.dispatchEvent(
                new Event("change", {
                    bubbles: true,
                }),
            );
        });
    }

    function scoreState() {
        const values = fields.map(function (name) {
            return form.elements.namedItem(name)?.value ?? "";
        });

        if (values.every((value) => value === "")) {
            return {
                state: "pending",
                text: "Belum ada hasil pertandingan",
            };
        }

        if (values.some((value) => value === "")) {
            return {
                state: "invalid",
                text: "Lengkapi seluruh skor kedua set",
            };
        }

        const numbers = values.map(Number);

        if (
            numbers.some(
                (value) => !Number.isInteger(value) || value < 0 || value > 99,
            )
        ) {
            return {
                state: "invalid",
                text: "Skor harus antara 0 sampai 99",
            };
        }

        const [a1, b1, a2, b2] = numbers;

        if (a1 === b1 || a2 === b2) {
            return {
                state: "invalid",
                text: "Skor dalam satu set tidak boleh seri",
            };
        }

        const winsA = Number(a1 > b1) + Number(a2 > b2);

        return {
            state: "complete",
            text:
                winsA === 2
                    ? "Tim A menang 2–0"
                    : winsA === 0
                      ? "Tim B menang 2–0"
                      : "Pertandingan seri 1–1",
        };
    }

    function updatePreview() {
        if (!preview) return;

        const state = scoreState();

        preview.textContent = state.text;
        preview.dataset.state = state.state;
    }

    if (type) {
        type.addEventListener("change", updateType);
        updateType();
    }

    fields.forEach(function (name) {
        const input = form.elements.namedItem(name);

        input?.addEventListener("input", updatePreview);
    });

    updatePreview();

    form.addEventListener("submit", function (event) {
        if (type) {
            const slots =
                type.value === "ganda"
                    ? ["a1", "a2", "b1", "b2"]
                    : ["a1", "b1"];

            const values = slots.map(function (name) {
                return form.elements.namedItem(name)?.value ?? "";
            });

            if (values.some((value) => value === "")) {
                event.preventDefault();
                event.stopPropagation();

                window.AdminDialog?.error(
                    "Pilih semua peserta pertandingan.",
                    "Peserta Belum Lengkap",
                );

                return;
            }

            const realIds = values.filter((value) => value !== "guest");

            if (new Set(realIds).size !== realIds.length) {
                event.preventDefault();
                event.stopPropagation();

                window.AdminDialog?.error(
                    "Pemain yang sama tidak boleh dipilih dua kali.",
                    "Peserta Tidak Valid",
                );

                return;
            }
        }

        const state = scoreState();

        if (state.state === "invalid") {
            event.preventDefault();
            event.stopPropagation();

            window.AdminDialog?.error(state.text, "Skor Belum Valid");
        }
    });
});

/*
|--------------------------------------------------------------------------
| TOURNAMENT RESULT EDITOR
|--------------------------------------------------------------------------
*/

document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("[data-admin-tournament-form]");

    if (!form) return;

    const format = form.dataset.scoreFormat;

    const decider = form.querySelector("[data-admin-score-decider]");

    const preview = form.querySelector("[data-tournament-result-preview]");

    const score = (set, side) =>
        form.elements.namedItem(`scores[${set}][${side}]`);

    function pair(set) {
        const a = score(set, "a")?.value ?? "";
        const b = score(set, "b")?.value ?? "";

        if (a === "" || b === "") {
            return null;
        }

        return [Number(a), Number(b)];
    }

    function updateDecider() {
        if (!decider) return;

        const first = pair(0);
        const second = pair(1);

        const needs =
            first !== null &&
            second !== null &&
            first[0] + second[0] === first[1] + second[1];

        decider.hidden = !needs;

        if (!needs) {
            decider.querySelectorAll("input").forEach(function (input) {
                input.value = "";
            });
        }
    }

    function winner() {
        const first = pair(0);
        const second = pair(1);

        if (!first || !second) return "";

        const setWinner = (values) =>
            values[0] > values[1] ? "a" : values[1] > values[0] ? "b" : "";

        if (format === "best_of_three") {
            const firstWinner = setWinner(first);
            const secondWinner = setWinner(second);

            if (firstWinner && firstWinner === secondWinner) {
                return firstWinner;
            }

            const third = pair(2);

            return third ? setWinner(third) : "";
        }

        if (format === "two_draw") {
            const firstWinner = setWinner(first);
            const secondWinner = setWinner(second);

            return firstWinner && firstWinner === secondWinner
                ? firstWinner
                : "";
        }

        const totalA = first[0] + second[0];
        const totalB = first[1] + second[1];

        if (totalA > totalB) return "a";
        if (totalB > totalA) return "b";

        const a = form.elements.namedItem("jus_a")?.value ?? "";
        const b = form.elements.namedItem("jus_b")?.value ?? "";

        if (a === "" || b === "") return "";

        return Number(a) > Number(b) ? "a" : Number(b) > Number(a) ? "b" : "";
    }

    function updatePreview() {
        if (!preview) return;

        const first = pair(0);
        const second = pair(1);

        if (!first && !second) {
            preview.textContent = "Belum ada hasil pertandingan";
            preview.dataset.state = "pending";
            return;
        }

        const result = winner();

        if (result === "a") {
            preview.textContent = "Prediksi hasil: Tim A menang";
            preview.dataset.state = "complete";
        } else if (result === "b") {
            preview.textContent = "Prediksi hasil: Tim B menang";
            preview.dataset.state = "complete";
        } else if (format === "two_draw" && first && second) {
            preview.textContent = "Prediksi hasil: Seri";
            preview.dataset.state = "complete";
        } else {
            preview.textContent = "Lengkapi skor sesuai aturan turnamen";
            preview.dataset.state = "pending";
        }
    }

    form.addEventListener("input", function (event) {
        if (event.target.name?.startsWith("scores[")) {
            updateDecider();
        }

        updatePreview();
    });

    updateDecider();
    updatePreview();

    /*
     * Konfirmasi perubahan pemenang Cup
     * ketika pertandingan lanjutan memiliki data.
     */

    form.addEventListener("submit", function (event) {
        if (
            form.dataset.confirmReset !== "1" ||
            winner() === form.dataset.originalWinner ||
            form.elements.namedItem("confirm_reset").value === "1"
        ) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        const message =
            "Perubahan pemenang dapat mereset hasil " +
            "pertandingan Cup lanjutan yang terdampak. Lanjutkan?";

        const proceed = function () {
            form.elements.namedItem("confirm_reset").value = "1";

            form.requestSubmit();
        };

        if (window.AdminDialog?.confirm) {
            window.AdminDialog.confirm(message, proceed, {
                title: "Konfirmasi Perubahan Pemenang",
                confirmText: "Ya, Lanjutkan",
            });
        } else if (window.confirm(message)) {
            proceed();
        }
    });
});

/*
|--------------------------------------------------------------------------
| ADMIN TOURNAMENT RULES
|--------------------------------------------------------------------------
*/

document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("[data-admin-tournament-rules]");

    if (!form) return;

    const leagueSettings = form.querySelector("[data-tr-league-settings]");

    const drawOption = form.querySelector(
        '[name="set_format"][value="two_draw"]',
    );

    const aggregateOption = form.querySelector(
        '[name="set_format"][value="two_aggregate"]',
    );

    const notice = form.querySelector("[data-tr-cup-notice]");

    function updateRules() {
        const format = form.querySelector('[name="format"]:checked')?.value;

        const isLiga = format === "liga";

        if (leagueSettings) {
            leagueSettings.hidden = !isLiga;

            leagueSettings.querySelectorAll("input").forEach(function (input) {
                input.disabled = !isLiga;
            });
        }

        if (drawOption) {
            drawOption.disabled = !isLiga;

            if (!isLiga && drawOption.checked && aggregateOption) {
                aggregateOption.checked = true;
            }
        }

        if (notice) {
            notice.hidden = isLiga;
        }
    }

    form.querySelectorAll('input[name="format"]').forEach(function (input) {
        input.addEventListener("change", updateRules);
    });

    updateRules();
});

/*
|--------------------------------------------------------------------------
| ADMIN TOURNAMENT ENTRIES
|--------------------------------------------------------------------------
*/

document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("[data-admin-tr-entries]");

    if (!form) return;

    const rows = Array.from(form.querySelectorAll("[data-tr-entry]"));

    const addButton = form.querySelector("[data-tr-add-entry]");

    const counter = form.querySelector("[data-tr-entry-count]");

    function activeRows() {
        return rows.filter((row) => !row.hidden);
    }

    function syncRow(row, enabled) {
        row.hidden = !enabled;

        row.querySelectorAll("[data-tr-entry-player]").forEach(
            function (select) {
                select.disabled = !enabled;

                if (!enabled) {
                    select.value = "";
                }

                select.dispatchEvent(new Event("change", { bubbles: true }));
            },
        );
    }

    function updateRows() {
        const active = activeRows();

        active.forEach(function (row, index) {
            row.querySelector("[data-tr-entry-number]").textContent = String(
                index + 1,
            );
        });

        if (counter) {
            counter.textContent = String(active.length);
        }

        if (addButton) {
            addButton.disabled = active.length >= 32;
        }

        rows.forEach(function (row) {
            const removeButton = row.querySelector("[data-tr-remove-entry]");

            if (removeButton) {
                removeButton.disabled = active.length <= 2;
            }
        });
    }

    addButton?.addEventListener("click", function () {
        const row = rows.find((item) => item.hidden);

        if (!row) return;

        syncRow(row, true);
        updateRows();

        row.scrollIntoView({
            behavior: "smooth",
            block: "nearest",
        });
    });

    rows.forEach(function (row) {
        row.querySelector("[data-tr-remove-entry]")?.addEventListener(
            "click",
            function () {
                if (activeRows().length <= 2) return;

                syncRow(row, false);
                updateRows();
            },
        );
    });

    form.addEventListener("submit", function (event) {
        const active = activeRows();

        if (active.length < 2 || active.length > 32) {
            event.preventDefault();

            window.AdminDialog?.error(
                "Peserta harus berjumlah 2 sampai 32.",
                "Jumlah Peserta Tidak Valid",
            );

            return;
        }

        const selected = [];

        for (const row of active) {
            const selects = row.querySelectorAll("[data-tr-entry-player]");

            for (const select of selects) {
                if (!select.value) {
                    event.preventDefault();

                    window.AdminDialog?.error(
                        "Lengkapi seluruh pemain pada setiap peserta.",
                        "Peserta Belum Lengkap",
                    );

                    return;
                }

                selected.push(select.value);
            }
        }

        if (new Set(selected).size !== selected.length) {
            event.preventDefault();

            window.AdminDialog?.error(
                "Satu pemain tidak boleh didaftarkan dua kali dalam turnamen.",
                "Peserta Duplikat",
            );
        }
    });

    updateRows();
});

/*
|--------------------------------------------------------------------------
| ADMIN TOURNAMENT SCHEDULE ORDER
|--------------------------------------------------------------------------
*/

document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("[data-admin-tr-order-form]");

    if (!form) return;

    const selects = Array.from(form.querySelectorAll("[data-tr-order]"));

    const shuffleButton = form.querySelector("[data-tr-shuffle]");

    const startButton = document.querySelector("[data-tr-start]");

    const staleMessage = document.querySelector("[data-tr-order-stale]");

    function markStale() {
        if (startButton) {
            startButton.disabled = true;
        }

        if (staleMessage) {
            staleMessage.hidden = false;
        }
    }

    selects.forEach(function (select) {
        select.addEventListener("change", markStale);
    });

    shuffleButton?.addEventListener("click", function () {
        const values = selects.map((select) => select.value);

        for (let i = values.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));

            [values[i], values[j]] = [values[j], values[i]];
        }

        selects.forEach(function (select, index) {
            select.value = values[index];

            select.dispatchEvent(new Event("change", { bubbles: true }));
        });

        markStale();
    });

    form.addEventListener("submit", function (event) {
        const values = selects.map((select) => select.value);

        if (
            values.some((value) => value === "") ||
            new Set(values).size !== values.length
        ) {
            event.preventDefault();

            window.AdminDialog?.error(
                "Urutan peserta tidak boleh kosong atau berulang.",
                "Urutan Peserta Tidak Valid",
            );
        }
    });
});

/*
|--------------------------------------------------------------------------
| ADMIN GALLERY UPLOAD
|--------------------------------------------------------------------------
*/

document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("[data-admin-gallery-form]");

    if (!form) return;

    const fileInput = form.querySelector("[data-admin-gallery-file]");

    const dropZone = form.querySelector("[data-admin-gallery-drop]");

    const preview = form.querySelector("[data-admin-gallery-preview]");

    const previewImage = form.querySelector(
        "[data-admin-gallery-preview-image]",
    );

    const fileName = form.querySelector("[data-admin-gallery-file-name]");

    if (!fileInput || !dropZone || !preview || !previewImage || !fileName) {
        return;
    }

    const maxSize = 5 * 1024 * 1024;

    const acceptedTypes = ["image/jpeg", "image/png", "image/webp"];

    let previewUrl = null;
    let dragDepth = 0;

    function clearPreview() {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        previewImage.removeAttribute("src");

        fileName.textContent = "";

        preview.hidden = true;
    }

    function showError(message) {
        if (window.AdminDialog?.error) {
            window.AdminDialog.error(message, "Foto Tidak Valid");
        } else {
            window.alert(message);
        }
    }

    function updatePreview() {
        clearPreview();

        const file = fileInput.files?.[0];

        if (!file) {
            return;
        }

        if (!acceptedTypes.includes(file.type)) {
            fileInput.value = "";

            showError("Gunakan foto dengan format JPG, PNG, atau WebP.");

            return;
        }

        if (file.size === 0 || file.size > maxSize) {
            fileInput.value = "";

            showError("Ukuran foto harus lebih dari 0 byte dan maksimal 5 MB.");

            return;
        }

        previewUrl = URL.createObjectURL(file);

        previewImage.src = previewUrl;

        const size = (file.size / (1024 * 1024)).toFixed(2);

        fileName.textContent = file.name + " · " + size + " MB";

        preview.hidden = false;
    }

    fileInput.addEventListener("change", updatePreview);

    /*
    |--------------------------------------------------------------------------
    | DRAG AND DROP
    |--------------------------------------------------------------------------
    */

    dropZone.addEventListener("dragenter", function (event) {
        event.preventDefault();

        dragDepth++;

        dropZone.classList.add("is-dragging");
    });

    dropZone.addEventListener("dragover", function (event) {
        event.preventDefault();

        if (event.dataTransfer) {
            event.dataTransfer.dropEffect = "copy";
        }
    });

    dropZone.addEventListener("dragleave", function (event) {
        event.preventDefault();

        dragDepth = Math.max(0, dragDepth - 1);

        if (dragDepth === 0) {
            dropZone.classList.remove("is-dragging");
        }
    });

    dropZone.addEventListener("drop", function (event) {
        event.preventDefault();

        dragDepth = 0;

        dropZone.classList.remove("is-dragging");

        const files = event.dataTransfer?.files;

        if (!files || files.length === 0) {
            return;
        }

        if (files.length > 1) {
            showError("Unggah satu foto untuk setiap pengiriman.");

            return;
        }

        try {
            fileInput.files = files;

            fileInput.dispatchEvent(
                new Event("change", {
                    bubbles: true,
                }),
            );
        } catch (error) {
            showError(
                "Browser tidak mendukung unggah seret. Gunakan tombol Pilih Foto.",
            );
        }
    });

    /*
    |--------------------------------------------------------------------------
    | VALIDASI SEBELUM SUBMIT
    |--------------------------------------------------------------------------
    */

    form.addEventListener("submit", function (event) {
        const file = fileInput.files?.[0];

        if (!file) {
            event.preventDefault();

            showError("Pilih foto terlebih dahulu.");

            return;
        }

        if (
            !acceptedTypes.includes(file.type) ||
            file.size === 0 ||
            file.size > maxSize
        ) {
            event.preventDefault();

            showError(
                "Foto tidak valid. Pilih JPG, PNG, atau WebP dengan ukuran maksimal 5 MB.",
            );
        }
    });

    window.addEventListener("pagehide", function () {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }
    });
});

/*
|--------------------------------------------------------------------------
| ADMIN WEBSITE SETTINGS
|--------------------------------------------------------------------------
*/

document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    const form = document.querySelector("[data-admin-site-settings]");

    if (!form) return;

    const fileInput = form.querySelector("[data-admin-site-logo-input]");

    const removeLogo = form.querySelector("[data-admin-site-remove-logo]");

    const logoImages = Array.from(
        form.querySelectorAll("[data-admin-site-logo-image]"),
    );

    const logoFallbacks = Array.from(
        form.querySelectorAll("[data-admin-site-logo-fallback]"),
    );

    const logoStatus = form.querySelector("[data-admin-site-logo-status]");

    const selectedFileName = form.querySelector("[data-admin-site-file-name]");

    const aboutTitle = form.querySelector("[data-admin-site-about-title]");

    const aboutText = form.querySelector("[data-admin-site-about-text]");

    const previewTitle = form.querySelector("[data-admin-site-preview-title]");

    const previewText = form.querySelector("[data-admin-site-preview-text]");

    const characterCount = form.querySelector(
        "[data-admin-site-character-count]",
    );

    const originalLogo = logoImages[0]?.dataset.originalSrc || "";

    const maxFileSize = 3 * 1024 * 1024;

    const acceptedTypes = ["image/jpeg", "image/png", "image/webp"];

    let temporaryLogoUrl = null;

    /*
    |--------------------------------------------------------------------------
    | ERROR DIALOG
    |--------------------------------------------------------------------------
    */

    function showError(message) {
        if (window.AdminDialog?.error) {
            window.AdminDialog.error(message, "Logo Tidak Valid");
        } else {
            window.alert(message);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TAMPILKAN LOGO DI SELURUH PREVIEW
    |--------------------------------------------------------------------------
    */

    function displayLogo(source) {
        const hasImage = Boolean(source);

        logoImages.forEach(function (image) {
            if (hasImage) {
                image.src = source;
                image.hidden = false;
            } else {
                image.hidden = true;
                image.removeAttribute("src");
            }
        });

        logoFallbacks.forEach(function (fallback) {
            fallback.hidden = hasImage;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RENDER STATUS LOGO
    |--------------------------------------------------------------------------
    */

    function refreshLogo() {
        const file = fileInput?.files?.[0];

        const useDefault = removeLogo?.checked === true;

        if (useDefault) {
            displayLogo("");

            if (logoStatus) {
                logoStatus.textContent = "Logo default akan digunakan";
            }

            return;
        }

        if (file && temporaryLogoUrl) {
            displayLogo(temporaryLogoUrl);

            if (logoStatus) {
                logoStatus.textContent = "Pratinjau logo baru";
            }

            return;
        }

        displayLogo(originalLogo);

        if (logoStatus) {
            logoStatus.textContent = originalLogo
                ? "Logo kustom sedang digunakan"
                : "Menggunakan logo default";
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MEMBERSIHKAN URL SEMENTARA
    |--------------------------------------------------------------------------
    */

    function clearTemporaryLogo() {
        if (temporaryLogoUrl) {
            URL.revokeObjectURL(temporaryLogoUrl);
            temporaryLogoUrl = null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PILIH FILE LOGO
    |--------------------------------------------------------------------------
    */

    fileInput?.addEventListener("change", function () {
        clearTemporaryLogo();

        const file = fileInput.files?.[0];

        if (!file) {
            if (selectedFileName) {
                selectedFileName.hidden = true;
                selectedFileName.textContent = "";
            }

            refreshLogo();
            return;
        }

        if (!acceptedTypes.includes(file.type)) {
            fileInput.value = "";

            showError("Gunakan logo berformat JPG, PNG, atau WebP.");

            refreshLogo();
            return;
        }

        if (file.size === 0 || file.size > maxFileSize) {
            fileInput.value = "";

            showError("Ukuran logo harus lebih dari 0 byte dan maksimal 3 MB.");

            refreshLogo();
            return;
        }

        if (removeLogo) {
            removeLogo.checked = false;
        }

        temporaryLogoUrl = URL.createObjectURL(file);

        const fileSize = (file.size / (1024 * 1024)).toFixed(2);

        if (selectedFileName) {
            selectedFileName.hidden = false;

            selectedFileName.textContent = file.name + " · " + fileSize + " MB";
        }

        refreshLogo();
    });

    /*
    |--------------------------------------------------------------------------
    | KEMBALIKAN LOGO DEFAULT
    |--------------------------------------------------------------------------
    */

    removeLogo?.addEventListener("change", function () {
        if (removeLogo.checked && fileInput) {
            fileInput.value = "";

            clearTemporaryLogo();

            if (selectedFileName) {
                selectedFileName.hidden = true;
                selectedFileName.textContent = "";
            }
        }

        refreshLogo();
    });

    /*
    |--------------------------------------------------------------------------
    | PREVIEW INFORMASI KLUB
    |--------------------------------------------------------------------------
    */

    function updateTextPreview() {
        if (previewTitle && aboutTitle) {
            previewTitle.textContent =
                aboutTitle.value.trim() || "Judul belum diisi";
        }

        if (previewText && aboutText) {
            previewText.textContent =
                aboutText.value.trim() || "Deskripsi belum diisi";
        }

        if (characterCount && aboutText) {
            const count = Array.from(aboutText.value).length;

            characterCount.textContent =
                count.toLocaleString("id-ID") + " / 10000";
        }
    }

    aboutTitle?.addEventListener("input", updateTextPreview);

    aboutText?.addEventListener("input", updateTextPreview);

    /*
    |--------------------------------------------------------------------------
    | VALIDASI SEBELUM SIMPAN
    |--------------------------------------------------------------------------
    */

    form.addEventListener("submit", function (event) {
        const file = fileInput?.files?.[0];

        if (!file) return;

        const invalidFile =
            !acceptedTypes.includes(file.type) ||
            file.size === 0 ||
            file.size > maxFileSize;

        if (invalidFile) {
            event.preventDefault();
            event.stopImmediatePropagation();

            showError(
                "Logo tidak valid. Gunakan JPG, PNG, atau WebP maksimal 3 MB.",
            );
        }
    });

    /*
    |--------------------------------------------------------------------------
    | INITIALIZE
    |--------------------------------------------------------------------------
    */

    refreshLogo();
    updateTextPreview();

    window.addEventListener("pagehide", function () {
        clearTemporaryLogo();
    });
});
