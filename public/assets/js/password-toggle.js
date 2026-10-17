
"use strict";

document.addEventListener("DOMContentLoaded", function () {

    const passwordFields = document.querySelectorAll(
        'input[type="password"]:not([data-no-toggle])'
    );

    passwordFields.forEach(function (input) {

        if (input.closest(".pb-password")) return;

        // Wrapper input dan tombol mata
        const wrapper = document.createElement("div");
        wrapper.className = "pb-password";

        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        // Ambil nama kolom untuk aksesibilitas
        const fieldName = input.labels?.[0]?.textContent
            ?.replace(/\s+/g, " ")
            .trim() || "password";

        // Tombol tampil/sembunyi
        const button = document.createElement("button");

        button.type = "button";
        button.className = "pb-password__toggle";
        button.setAttribute("aria-pressed", "false");
        button.setAttribute(
            "aria-label",
            "Tampilkan " + fieldName
        );

        button.title = "Tampilkan password";

        if (input.id) {
            button.setAttribute("aria-controls", input.id);
        }

        // Ikon mata
        button.innerHTML = `
            <svg
                class="pb-password__icon pb-password__eye"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>

            <svg
                class="pb-password__icon pb-password__eye-off"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path d="M3 3l18 18"/>
                <path d="M10.6 5.1A10 10 0 0 1 12 5c6.5 0 10 7 10 7a15 15 0 0 1-3.2 3.7"/>
                <path d="M6.5 6.5C3.7 8.2 2 12 2 12s3.5 7 10 7c1.5 0 2.9-.3 4.1-.8"/>
                <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
            </svg>
        `;

        wrapper.appendChild(button);

        button.addEventListener("click", function () {

            const visible = input.type === "password";

            input.type = visible ? "text" : "password";

            button.setAttribute(
                "aria-pressed",
                String(visible)
            );

            button.setAttribute(
                "aria-label",
                (visible ? "Sembunyikan " : "Tampilkan ")
                    + fieldName
            );

            button.title = visible
                ? "Sembunyikan password"
                : "Tampilkan password";

        });

    });

});
