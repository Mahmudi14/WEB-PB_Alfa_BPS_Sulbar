(() => {
    "use strict";

    document.addEventListener("DOMContentLoaded", () => {
        const pickers = Array.from(
            document.querySelectorAll("[data-index-picker]"),
        );

        if (!pickers.length) return;

        function closeAll(except = null) {
            pickers.forEach((picker) => {
                if (picker === except) return;

                const menu = picker.querySelector("[data-index-menu]");

                const trigger = picker.querySelector("[data-index-trigger]");

                menu.hidden = true;
                trigger.setAttribute("aria-expanded", "false");
                picker.classList.remove("is-open");
            });
        }

        pickers.forEach((picker) => {
            const trigger = picker.querySelector("[data-index-trigger]");

            const menu = picker.querySelector("[data-index-menu]");

            const input = picker.querySelector("[data-index-value]");

            const label = picker.querySelector("[data-index-label]");

            const options = Array.from(
                picker.querySelectorAll("[data-index-option]"),
            );

            function selectOption(option) {
                input.value = option.dataset.value;
                label.textContent = option.textContent.trim();

                options.forEach((item) => {
                    item.setAttribute(
                        "aria-selected",
                        item === option ? "true" : "false",
                    );
                });

                menu.hidden = true;
                trigger.setAttribute("aria-expanded", "false");
                picker.classList.remove("is-open");
                trigger.focus();
            }

            trigger.addEventListener("click", () => {
                const shouldOpen = menu.hidden;

                closeAll();

                if (shouldOpen) {
                    menu.hidden = false;
                    trigger.setAttribute("aria-expanded", "true");

                    picker.classList.add("is-open");
                }
            });

            options.forEach((option) => {
                option.addEventListener("click", () => {
                    selectOption(option);
                });
            });

            trigger.addEventListener("keydown", (event) => {
                if (event.key === "ArrowDown" || event.key === "ArrowUp") {
                    event.preventDefault();

                    closeAll();

                    menu.hidden = false;
                    picker.classList.add("is-open");
                    trigger.setAttribute("aria-expanded", "true");

                    const selectedIndex = options.findIndex(
                        (option) => option.dataset.value === input.value,
                    );

                    const target =
                        event.key === "ArrowDown"
                            ? Math.min(selectedIndex + 1, options.length - 1)
                            : Math.max(selectedIndex - 1, 0);

                    options[Math.max(0, target)]?.focus();
                }
            });

            menu.addEventListener("keydown", (event) => {
                const index = options.indexOf(document.activeElement);

                if (event.key === "ArrowDown") {
                    event.preventDefault();
                    options[(index + 1) % options.length]?.focus();
                }

                if (event.key === "ArrowUp") {
                    event.preventDefault();
                    options[
                        (index - 1 + options.length) % options.length
                    ]?.focus();
                }
            });
        });

        // Klik di luar menutup semua dropdown.
        document.addEventListener("click", (event) => {
            if (!event.target.closest("[data-index-picker]")) {
                closeAll();
            }
        });

        // Escape menutup dropdown.
        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                const openPicker = pickers.find((picker) =>
                    picker.classList.contains("is-open"),
                );

                closeAll();

                openPicker?.querySelector("[data-index-trigger]")?.focus();
            }
        });
    });
})();
