(() => {
    "use strict";

    function initializeBracket(viewport) {
        if (viewport.dataset.bracketReady === "1") return;

        const stage = viewport.querySelector("[data-bracket-stage]");

        const content = viewport.querySelector("[data-bracket-content]");

        if (!stage || !content) return;

        const MIN_SCALE = 0.3;
        const MAX_SCALE = 3;

        const baseWidth = Math.max(1, Math.ceil(content.scrollWidth));

        const baseHeight = Math.max(1, Math.ceil(content.scrollHeight));

        let scale = 1;

        const pointers = new Map();
        let gesture = null;

        const clamp = (value) =>
            Math.max(MIN_SCALE, Math.min(MAX_SCALE, value));

        function distance(a, b) {
            return Math.hypot(a.x - b.x, a.y - b.y);
        }

        function centerOf(a, b) {
            return {
                x: (a.x + b.x) / 2,
                y: (a.y + b.y) / 2,
            };
        }

        function localPoint(clientX, clientY) {
            const rect = viewport.getBoundingClientRect();

            return {
                x: clientX - rect.left,
                y: clientY - rect.top,
            };
        }

        function zoomTo(newScale, clientX, clientY, anchor = null) {
            const local = localPoint(clientX, clientY);

            const world = anchor || {
                x: (viewport.scrollLeft + local.x) / scale,

                y: (viewport.scrollTop + local.y) / scale,
            };

            scale = clamp(newScale);

            stage.style.width = Math.ceil(baseWidth * scale) + "px";

            stage.style.height = Math.ceil(baseHeight * scale) + "px";

            content.style.transform = `scale(${scale})`;

            viewport.scrollLeft = world.x * scale - local.x;

            viewport.scrollTop = world.y * scale - local.y;
        }

        function startPan(pointer) {
            gesture = {
                type: "pan",
                x: pointer.x,
                y: pointer.y,
                scrollX: viewport.scrollLeft,
                scrollY: viewport.scrollTop,
            };
        }

        function startPinch() {
            const [a, b] = [...pointers.values()];

            if (!a || !b) return;

            const center = centerOf(a, b);
            const local = localPoint(center.x, center.y);

            gesture = {
                type: "pinch",
                distance: Math.max(1, distance(a, b)),
                initialScale: scale,

                anchor: {
                    x: (viewport.scrollLeft + local.x) / scale,

                    y: (viewport.scrollTop + local.y) / scale,
                },
            };
        }

        // Ukuran asli sebelum ditransformasi.
        stage.style.width = baseWidth + "px";
        stage.style.height = baseHeight + "px";

        content.style.transformOrigin = "top left";
        content.style.transform = "scale(1)";

        stage.classList.add("is-ready");
        viewport.dataset.bracketReady = "1";

        viewport.addEventListener("pointerdown", (event) => {
            if (event.pointerType === "mouse" && event.button !== 0) {
                return;
            }

            // Tautan Edit tetap bisa diklik.
            if (event.target.closest("a, button, input, select")) {
                return;
            }

            if (pointers.size >= 2) return;

            pointers.set(event.pointerId, {
                x: event.clientX,
                y: event.clientY,
            });

            try {
                viewport.setPointerCapture(event.pointerId);
            } catch (_) {
                // Abaikan jika pointer sudah berakhir.
            }

            if (pointers.size === 2) {
                startPinch();
            } else {
                startPan({
                    x: event.clientX,
                    y: event.clientY,
                });
            }
        });

        viewport.addEventListener(
            "pointermove",
            (event) => {
                if (!pointers.has(event.pointerId)) {
                    return;
                }

                pointers.set(event.pointerId, {
                    x: event.clientX,
                    y: event.clientY,
                });

                if (pointers.size === 2) {
                    if (gesture?.type !== "pinch") {
                        startPinch();
                    }

                    const [a, b] = [...pointers.values()];

                    const center = centerOf(a, b);

                    const ratio = distance(a, b) / gesture.distance;

                    zoomTo(
                        gesture.initialScale * ratio,
                        center.x,
                        center.y,
                        gesture.anchor,
                    );
                } else if (pointers.size === 1 && gesture?.type === "pan") {
                    const pointer = [...pointers.values()][0];

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
                startPan([...pointers.values()][0]);
            } else {
                gesture = null;
            }
        }

        viewport.addEventListener("pointerup", endPointer);

        viewport.addEventListener("pointercancel", endPointer);

        viewport.addEventListener("lostpointercapture", endPointer);

        // Pinch menggunakan trackpad laptop.
        viewport.addEventListener(
            "wheel",
            (event) => {
                if (!event.ctrlKey) return;

                event.preventDefault();

                const factor = Math.exp(-event.deltaY * 0.01);

                zoomTo(scale * factor, event.clientX, event.clientY);
            },
            { passive: false },
        );
    }

    function initialize() {
        document
            .querySelectorAll("[data-bracket-viewport]")
            .forEach(initializeBracket);
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initialize);
    } else {
        initialize();
    }
})();
