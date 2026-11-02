"use strict";

document.addEventListener("DOMContentLoaded", function () {
    const body = document.body;
    const sidebar = document.getElementById("admin-sidebar");
    const toggle = document.querySelector("[data-admin-toggle]");
    const backdrop = document.querySelector("[data-admin-backdrop]");

    if (!sidebar || !toggle || !backdrop) {
        return;
    }

    const mobile = window.matchMedia("(max-width: 900px)");

    function isOpen() {
        return body.classList.contains("admin-sidebar-open");
    }

    function setOpen(open, restoreFocus = false) {
        body.classList.toggle("admin-sidebar-open", open);

        toggle.setAttribute("aria-expanded", String(open));

        toggle.setAttribute(
            "aria-label",
            open ? "Tutup menu administrator" : "Buka menu administrator",
        );

        if (open) {
            const firstLink = sidebar.querySelector("a.is-active, a");

            if (firstLink) {
                firstLink.focus();
            }
        } else if (restoreFocus && mobile.matches) {
            toggle.focus();
        }
    }

    toggle.addEventListener("click", function () {
        setOpen(!isOpen());
    });

    backdrop.addEventListener("click", function () {
        setOpen(false, true);
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && isOpen()) {
            setOpen(false, true);
        }

        // Pertahankan fokus keyboard di sidebar saat terbuka.
        if (event.key === "Tab" && isOpen() && mobile.matches) {
            const focusable = Array.from(
                sidebar.querySelectorAll("a[href], button:not([disabled])"),
            );

            if (!focusable.length) return;

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    sidebar.querySelectorAll("a").forEach(function (link) {
        link.addEventListener("click", function () {
            if (mobile.matches) {
                setOpen(false);
            }
        });
    });

    function handleViewportChange() {
        if (!mobile.matches && isOpen()) {
            setOpen(false);
        }
    }

    mobile.addEventListener("change", handleViewportChange);
});
