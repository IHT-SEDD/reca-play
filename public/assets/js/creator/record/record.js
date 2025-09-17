const RecordModule = (function ($) {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    /**
     * Automatically stop recording after given duration
     * and update countdown timer in UI.
     *
     * @param {string} startTime - Recording start time (YYYY-MM-DD HH:mm:ss)
     * @param {number} durationMinutes - Recording duration in minutes
     * @param {object} timerEl - jQuery element for timer display
     */
    function autoStopRecording(startTime, durationMinutes, timerEl) {
        const start = new Date(startTime.replace(" ", "T"));
        const end = new Date(start.getTime() + durationMinutes * 60000);

        function updateTimer() {
            const now = new Date();
            let remaining = Math.floor((end - now) / 1000);

            if (remaining <= 0) {
                timerEl
                    .text("00:00:00")
                    .removeClass()
                    .addClass(
                        "text-vivaldi-red text-lg tracking-wide font-bold"
                    );
                clearInterval(timerInterval);
                triggerStopRecording();
                return;
            }

            const hours = String(Math.floor(remaining / 3600)).padStart(2, "0");
            const minutes = String(
                Math.floor((remaining % 3600) / 60)
            ).padStart(2, "0");
            const seconds = String(remaining % 60).padStart(2, "0");

            timerEl.text(`${hours}:${minutes}:${seconds}`);

            if (remaining <= 60) {
                timerEl
                    .removeClass()
                    .addClass(
                        "text-vivaldi-red text-lg tracking-wide font-bold"
                    );
            } else if (remaining <= 300) {
                timerEl
                    .removeClass()
                    .addClass(
                        "text-kin-gold text-lg tracking-wide font-semibold"
                    );
            } else {
                timerEl
                    .removeClass()
                    .addClass(
                        "text-exit-light text-lg tracking-wide font-semibold"
                    );
            }
        }

        updateTimer();
        const timerInterval = setInterval(updateTimer, 1000);
    }

    /**
     * Send request to backend to stop recording immediately.
     */
    function triggerStopRecording() {
        $.ajax({
            url: "/creator/record/stop",
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            success: function (res) {
                console.log("Recording stopped automatically:", res);
                notyf.success("Recording stopped automatically.");
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", error);
            },
        });
    }

    /**
     * Attach click handler to stop recording manually.
     *
     * @param {object} stopRecordBtn - jQuery element for stop button
     */
    function stopRecordingManual(stopRecordBtn) {
        stopRecordBtn.on("click", function (e) {
            e.preventDefault();
            triggerStopRecording();
        });
    }

    // expose public functions to RecordModule
    return {
        autoStopRecording,
        stopRecordingManual,
    };
})(jQuery);
