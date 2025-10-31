let modeBtnHandler, checkScannedQr, responseFormAndButton;

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

const submitBtnRecord = $("#start_recording");
const submitBtnStream = $("#start_streaming");

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
        formRecord.removeClass("hidden");
        formStreaming.addClass("hidden");
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
        formRecord.addClass("hidden");
        formStreaming.removeClass("hidden");
    });
};

// ======== Check scanned QR ========
checkScannedQr = () => {
    showLoading();

    $.ajax({
        url: "/creator/new/check",
        method: "GET",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
        },
        success: function (data, textStatus, xhr) {
            if (data?.status === "error") {
                hideLoading();

                notyf.error(data.message);
                setTimeout(() => {
                    window.location.href = "/my-recording/";
                }, 2500);
            }
        },
        error: function (xhr, status, error) {
            hideLoading();
            console.error("AJAX error:", error);

            setTimeout(() => {
                window.location.href = "/my-recording/";
            }, 2500);
        },
    });
};

// ======== Get response submit and check status button ========
responseFormAndButton = () => {
    if (submitBtnRecord.length) {
        submitBtnRecord.on("click", function (e) {
            showLoading();
            console.log("Start Recording button clicked");
            setTimeout(() => {
                window.location.href = "/creator/record";
                hideLoading();
            }, 2500);
        });
    }

    if (submitBtnStream.length) {
        submitBtnStream.on("click", function (e) {
            showLoading();
            console.log("Start Streaming button clicked");
            setTimeout(() => {
                window.location.href = "/creator/stream";
                hideLoading();
            }, 2500);
        });
    }
};

document.addEventListener("DOMContentLoaded", () => {
    modeBtnHandler();
    checkScannedQr();
    responseFormAndButton();
});
