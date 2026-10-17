
"use strict";

document.addEventListener("DOMContentLoaded", function () {

    // Navigasi mobile

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

    // Elemen popup

    const dialog = document.getElementById("pb-dialog");

    if (!dialog) return;

    const title = document.getElementById("pb-dialog-title");
    const message = document.getElementById("pb-dialog-message");
    const errors = document.getElementById("pb-dialog-errors");
    const symbol = document.getElementById("pb-dialog-symbol");
    const cancelButton = document.getElementById("pb-dialog-cancel");
    const okButton = document.getElementById("pb-dialog-ok");

    let confirmAction = null;
    let previousFocus = null;

    // Tampilkan popup

    function showDialog(options = {}) {
        if (dialog.open) return;

        const mode = options.mode || "error";
        const items = Array.isArray(options.items)
            ? options.items
            : [];

        previousFocus = document.activeElement;
        confirmAction = options.onConfirm || null;

        dialog.dataset.mode = mode;
        dialog.dataset.destructive =
            options.destructive ? "true" : "false";

        title.textContent = options.title || (
            mode === "confirm" ? "Konfirmasi" : "Terjadi Kesalahan"
        );

        message.textContent = options.message || "";
        message.hidden = !options.message;

        symbol.textContent = mode === "confirm" ? "?" : "!";

        // Daftar pesan validasi

        errors.replaceChildren();

        items.forEach(function (item) {
            const li = document.createElement("li");
            li.textContent = String(item);
            errors.appendChild(li);
        });

        errors.hidden = items.length === 0;

        // Konfirmasi mempunyai dua tombol

        cancelButton.hidden = mode !== "confirm";

        okButton.textContent = mode === "confirm"
            ? (options.confirmText || "Ya, Lanjutkan")
            : "Mengerti";

        dialog.showModal();

        if (mode === "confirm") {
            cancelButton.focus();
        } else {
            okButton.focus();
        }
    }

    // API popup untuk form lainnya

    window.PBAlfaDialog = {
        error: function (text, heading = "Gagal Memproses") {
            showDialog({
                mode: "error",
                title: heading,
                message: text
            });
        },

        validation: function (items) {
            showDialog({
                mode: "error",
                title: "Periksa Kembali Data",
                message: "Ada data yang perlu diperbaiki.",
                items: items
            });
        },

        confirm: function (text, callback, options = {}) {
            showDialog({
                mode: "confirm",
                title: options.title || "Konfirmasi Tindakan",
                message: text,
                confirmText: options.confirmText,
                destructive: options.destructive || false,
                onConfirm: callback
            });
        }
    };

    // Tombol popup

    cancelButton.addEventListener("click", function () {
        dialog.close();
    });

    okButton.addEventListener("click", function () {
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
            previousFocus
            && typeof previousFocus.focus === "function"
            && previousFocus.isConnected
        ) {
            previousFocus.focus();
        }

        previousFocus = null;
    });

    // Pesan gagal dari Laravel

    const serverErrors = document.getElementById(
        "pb-dialog-server-errors"
    );

    if (serverErrors) {
        const messages = Array.from(
            serverErrors.querySelectorAll("[data-pb-error]")
        )
            .map(item => item.textContent.trim())
            .filter(Boolean);

        if (messages.length) {
            window.PBAlfaDialog.validation(messages);
        }
    }

    // Pesan validasi input HTML

    let invalidPending = false;

    function fieldName(field) {
        const label = field.getAttribute("aria-label")
            || field.labels?.[0]?.textContent
            || field.getAttribute("placeholder")
            || field.name
            || "Kolom ini";

        return label.replace(/\*/g, "").trim();
    }

    function fieldError(field) {
        const name = fieldName(field);
        const state = field.validity;

        if (state.valueMissing) {
            return name + " wajib diisi.";
        }

        if (state.typeMismatch || state.patternMismatch) {
            return name + " tidak valid.";
        }

        if (state.rangeUnderflow) {
            return name + " minimal " + field.min + ".";
        }

        if (state.rangeOverflow) {
            return name + " maksimal " + field.max + ".";
        }

        if (state.tooShort) {
            return name + " terlalu pendek.";
        }

        if (state.tooLong) {
            return name + " terlalu panjang.";
        }

        if (state.stepMismatch || state.badInput) {
            return name + " harus berupa angka yang valid.";
        }

        return name + ": " + field.validationMessage;
    }

    document.addEventListener("invalid", function (event) {
        const field = event.target;
        const form = field.form;

        if (!form) return;

        // Matikan tooltip validasi bawaan browser

        event.preventDefault();

        if (invalidPending) return;

        invalidPending = true;

        setTimeout(function () {
            invalidPending = false;

            const invalidFields = Array.from(form.elements)
                .filter(element =>
                    element.willValidate
                    && !element.validity.valid
                );

            const messages = [
                ...new Set(invalidFields.map(fieldError))
            ].slice(0, 5);

            if (messages.length && !dialog.open) {
                window.PBAlfaDialog.validation(messages);
            }
        }, 0);

    }, true);

    // Submit form dan konfirmasi

    document.addEventListener("submit", function (event) {
        const form = event.target;

        if (!form.matches("form")) return;

        // Hormati validasi khusus pada form

        if (event.defaultPrevented) return;

        // Cegah pengiriman berulang

        if (form.dataset.submitting === "1") {
            event.preventDefault();
            return;
        }

        // Tampilkan konfirmasi jika diperlukan

        if (
            form.dataset.confirm
            && form.dataset.pbApproved !== "1"
        ) {
            event.preventDefault();

            const submitter = event.submitter;

            const methodInput = form.querySelector(
                'input[name="_method"]'
            );

            const destructive =
                methodInput?.value.toUpperCase() === "DELETE";

            window.PBAlfaDialog.confirm(
                form.dataset.confirm,
                function () {

                    // Lanjutkan submit setelah disetujui

                    form.dataset.pbApproved = "1";

                    try {
                        if (
                            submitter
                            && submitter.form === form
                            && !submitter.disabled
                        ) {
                            form.requestSubmit(submitter);
                        } else {
                            form.requestSubmit();
                        }
                    } finally {
                        delete form.dataset.pbApproved;
                    }

                },
                {
                    title: form.dataset.confirmTitle
                        || (
                            destructive
                                ? "Konfirmasi Penghapusan"
                                : "Konfirmasi Tindakan"
                        ),
                    confirmText: form.dataset.confirmText
                        || (
                            destructive
                                ? "Ya, Hapus"
                                : "Ya, Lanjutkan"
                        ),
                    destructive: destructive
                }
            );

            return;
        }

        // Anti double-click

        if (!form.hasAttribute("data-single-submit")) return;

        form.dataset.submitting = "1";

        const button = event.submitter
            || form.querySelector('button[type="submit"]');

        if (button) {
            button.dataset.originalLabel = button.textContent;
            button.disabled = true;
            button.textContent = "Memproses...";
        }
    });

});

// Pulihkan tombol saat kembali dari browser

window.addEventListener("pageshow", function () {
    document.querySelectorAll("form[data-single-submit]")
        .forEach(function (form) {
            delete form.dataset.submitting;
            delete form.dataset.pbApproved;

            form.querySelectorAll('button[type="submit"]')
                .forEach(function (button) {
                    button.disabled = false;

                    if (button.dataset.originalLabel) {
                        button.textContent =
                            button.dataset.originalLabel;
                    }
                });
        });
});
