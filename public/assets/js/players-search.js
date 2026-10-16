
"use strict";

(function () {
    function initializeLiveSearch() {

        const input = document.getElementById("rank-live-input");
        const list = document.querySelector(".rank-board__list");
        const count = document.getElementById("rank-result-count");
        const empty = document.getElementById("rank-live-empty");
        const emptyTitle = document.getElementById("rank-live-empty-title");
        const emptyDescription = document.getElementById(
            "rank-live-empty-description"
        );

        const pagination = document.getElementById(
            "rank-live-pagination"
        );

        const previous = document.getElementById("rank-page-prev");
        const next = document.getElementById("rank-page-next");
        const pageInfo = document.getElementById("rank-page-info");

        if (!input || !list || !pagination) {
            return;
        }

        const perPage = 20;
        let currentPage = 1;

        // Normalisasi huruf untuk pencarian.
        function normalize(value) {
            return String(value)
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .toLocaleLowerCase("id-ID")
                .trim();
        }

        // Semua pemain sudah tersedia di halaman.
        const players = Array.from(
            list.querySelectorAll(".rank-record")
        ).map(function (element) {
            return {
                element: element,
                searchName: normalize(
                    element.dataset.playerName || ""
                )
            };
        });

        const totalPlayers = players.length;

        function renderResults() {

            const query = normalize(input.value);

            const results = players.filter(function (player) {
                return player.searchName.includes(query);
            });

            const totalResults = results.length;

            const totalPages = Math.max(
                1,
                Math.ceil(totalResults / perPage)
            );

            if (currentPage > totalPages) {
                currentPage = totalPages;
            }

            const start = (currentPage - 1) * perPage;
            const end = start + perPage;

            // Sembunyikan seluruh baris terlebih dahulu.
            players.forEach(function (player) {
                player.element.hidden = true;
            });

            // Tampilkan pemain pada halaman aktif.
            results.slice(start, end).forEach(function (player) {
                player.element.hidden = false;
            });

            // Perbarui jumlah hasil.
            if (count) {
                count.textContent = query
                    ? totalResults + " dari " + totalPlayers + " pemain"
                    : totalPlayers + " pemain";
            }

            // Pesan ketika hasil pencarian kosong.
            if (empty) {
                empty.hidden = totalResults > 0;

                if (emptyTitle) {
                    emptyTitle.textContent = query
                        ? "Pemain tidak ditemukan."
                        : "Belum ada pemain.";
                }

                if (emptyDescription) {
                    emptyDescription.textContent = query
                        ? "Coba nama pemain lainnya."
                        : "Data pemain belum tersedia.";
                }
            }

            // Pagination hanya tampil jika diperlukan.
            pagination.hidden = totalResults <= perPage;

            if (pageInfo) {
                pageInfo.textContent =
                    "Halaman " + currentPage +
                    " dari " + totalPages;
            }

            previous.disabled = currentPage <= 1;
            next.disabled = currentPage >= totalPages;
        }

        // LANGSUNG MENCARI SETIAP INPUT BERUBAH.
        // Tidak ada debounce, timer, Enter, atau request HTTP.
        input.addEventListener("input", function () {
            currentPage = 1;
            renderResults();
        });

        previous.addEventListener("click", function () {
            if (currentPage > 1) {
                currentPage--;
                renderResults();
            }
        });

        next.addEventListener("click", function () {
            const query = normalize(input.value);

            const total = players.filter(function (player) {
                return player.searchName.includes(query);
            }).length;

            if (currentPage < Math.ceil(total / perPage)) {
                currentPage++;
                renderResults();
            }
        });

        // Render awal saat halaman selesai dimuat.
        renderResults();
    }

    if (document.readyState === "loading") {
        document.addEventListener(
            "DOMContentLoaded",
            initializeLiveSearch
        );
    } else {
        initializeLiveSearch();
    }
})();
