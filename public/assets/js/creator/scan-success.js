let modeBtnHandler, checkScannedQr;

// ======== Initialize component ========
const recordingBtn = $("#recordBtn");
const streamingBtn = $("#streamingBtn");

const formPanel = $("#formPanel");
const choosedModeText = $("#choosedMode");
const descriptionText = $("#descriptionChoosedMode");

formPanel.addClass("hidden").removeClass("inline-block");

const formRecord = $("#formRecording");
const formStreaming = $("#formStreaming");

formRecord.addClass("hidden");
formStreaming.addClass("hidden");

// ======== Record or streaming btn handling ========
modeBtnHandler = () => {
    // If record btn clicked
    recordingBtn.on("click", function () {
        recordingBtn
            .removeClass("bg-hot-shot/20 text-hot-shot")
            .addClass("bg-hot-shot text-white")
            .prop("disabled", true);
        // Disable streaming btn
        streamingBtn
            .removeClass("bg-hot-shot text-white")
            .addClass("bg-hot-shot/20 text-hot-shot")
            .prop("disabled", false);
        // Show the form panel
        formPanel.removeClass("hidden").addClass("inline-block");
        choosedModeText.text("You choose recording mode!");
        descriptionText.text(
            "Capture the moment, tell your story, and make it unforgettable!"
        );
        formRecord.removeClass("hidden")
        formStreaming.addClass("hidden")
    });

    // If streaming btn clicked
    streamingBtn.on("click", function () {
        streamingBtn
            .removeClass("bg-hot-shot/20 text-hot-shot")
            .addClass("bg-hot-shot text-white")
            .prop("disabled", true);
        // Disable record btn
        recordingBtn
            .removeClass("bg-hot-shot text-white")
            .addClass("bg-hot-shot/20 text-hot-shot")
            .prop("disabled", false);
        // Show the form panel
        formPanel.removeClass("hidden").addClass("inline-block");
        choosedModeText.text("You choose streaming mode!");
        descriptionText.text(
            "Go live, share the vibe, and let the world join the fun!"
        );
        formRecord.addClass("hidden")
        formStreaming.removeClass("hidden")
    });
};

// ======== Check scanned QR ========
checkScannedQr = () => {
    $.ajax({
        url: "/creator/new/check",
        method: "GET",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
        },
        success: function (data, textStatus, xhr) {
            if (data?.status === "error") {
                notyf.error(data.message);
            } else {
                console.log(data.message)
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX error:", error);
        },
    });
};

document.addEventListener("DOMContentLoaded", () => {
    modeBtnHandler();
    checkScannedQr();
});
