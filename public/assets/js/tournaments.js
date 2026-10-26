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
        const form = $("#tr-entries-form");
        if (!form) return;
        const wrap = $("#tr-entry-rows");
        const template = $("#tr-entry-template");
        const addButton = $("#tr-add-entry");
        const error = $("#tr-entry-error");
        const update = () => {
            const rows = $$("[data-entry-row]", wrap);
            rows.forEach((row, i) => {
                $("[data-entry-label]", row).textContent = String(i + 1);
                $$("select", row).forEach((select, slot) => {
                    select.name = `entries[${i}][players][${slot}]`;
                });
                $("[data-entry-remove]", row).disabled = rows.length <= 2;
            });
            addButton.disabled = rows.length >= 32;
        };
        wrap.addEventListener("click", (event) => {
            const button = event.target.closest("[data-entry-remove]");
            if (!button) return;
            if ($$("[data-entry-row]", wrap).length > 2)
                button.closest("[data-entry-row]").remove();
            update();
        });
        addButton.addEventListener("click", () => {
            if ($$("[data-entry-row]", wrap).length >= 32) return;
            wrap.appendChild(template.content.cloneNode(true));
            update();
        });
        form.addEventListener(
            "submit",
            (event) => {
                const values = $$("select", wrap).map((select) => select.value);
                if (
                    values.some((v) => !v) ||
                    new Set(values).size !== values.length
                ) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    error.textContent =
                        "Isi semua pemain dan pastikan setiap pemain hanya terdaftar sekali.";
                    error.hidden = false;
                    error.scrollIntoView({ block: "nearest" });
                } else error.hidden = true;
            },
            true,
        );
        update();
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
