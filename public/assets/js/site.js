
"use strict";

document.addEventListener("DOMContentLoaded", function () {

    // NAVIGASI MOBILE
    const toggle = document.querySelector("[data-nav-toggle]");
    const navigation = document.querySelector("[data-nav-links]");

    if (toggle && navigation) {
        function closeNavigation() {
            navigation.classList.remove("is-open");
            toggle.setAttribute("aria-expanded", "false");
        }

        toggle.addEventListener("click", function () {
            const isOpen = navigation.classList.toggle("is-open");
            toggle.setAttribute("aria-expanded", String(isOpen));
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                closeNavigation();
            }
        });

        navigation.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", closeNavigation);
        });
    }

    // ANTI DOUBLE-CLICK FRONTEND
    // Diterapkan untuk form data-single-submit.
    document.addEventListener("submit", function (event) {
        const form = event.target;

        if (!form.matches("form[data-single-submit]")) {
            return;
        }

        if (form.dataset.submitting === "1") {
            event.preventDefault();
            return;
        }

        if (form.dataset.confirm) {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
                return;
            }
        }

        form.dataset.submitting = "1";

        const button = form.querySelector('button[type="submit"]');

        if (button) {
            button.dataset.originalLabel = button.textContent;
            button.disabled = true;
            button.textContent = "Memproses...";
        }
    });
});

// Memulihkan tombol setelah pengguna
// kembali melalui tombol Back browser.
window.addEventListener("pageshow", function () {
    document.querySelectorAll("form[data-single-submit]")
        .forEach(function (form) {
            delete form.dataset.submitting;

            const button = form.querySelector(
                'button[type="submit"]'
            );

            if (button) {
                button.disabled = false;

                if (button.dataset.originalLabel) {
                    button.textContent =
                        button.dataset.originalLabel;
                }
            }
        });
});
