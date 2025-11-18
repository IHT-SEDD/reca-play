let getDataRecord,
    fullScreenVideo,
    livePreviewCam,
    stopRecordingManual,
    changeCamera;

// ======== Initialize component ::begin ========
// ---------- live preview panel ----------
const previewCam = document.getElementById("preview_cam");
const camName = $("#cam_name");
const camCode = $("#cam_code");
const fullScreenBtn = $("#full_screen_btn");
const changeCamBtn = $("#change_cam_btn");

// ---------- tool panel ----------
const timer = $("#timer");
const stopRecord = $("#stop_record");
const venueName = $("#venue_name");
const fieldName = $("#field_name");
const videoName = $("#video_name");
const durationVideo = $("#duration_video");

// ---------- loading bar ----------
const loadingBar = $("#loading_bar");
const loadingProgress = $("#loading_progress");

// ---------- others ----------
const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");
let currentCamIndex = 0;
let cameraList = [];
let timerInterval = null;
// ======== Initialize component ::end ========

// ======== Progress Helpers ========
function startFakeProgress() {
    let progress = 0;
    loadingProgress.css("width", "0%");
    3;
    const interval = setInterval(() => {
        if (progress < 90) {
            progress += Math.floor(Math.random() * 6) + 2;
            if (progress > 90) progress = 90;
            loadingProgress.css("width", progress + "%");
        }
    }, 400);
    return interval;
}

function finishProgress(interval, success = true) {
    clearInterval(interval);
    loadingProgress.css("width", "100%");
    setTimeout(() => {
        loadingBar.addClass("hidden");
        loadingProgress.css("width", "0%");
    }, 500);
}

// ======== Utility: stop live preview safely ========
function stopLivePreview() {
    try {
        if (previewCam && previewCam.srcObject) {
            previewCam.srcObject.getTracks().forEach((t) => {
                try {
                    t.stop();
                } catch (_) {}
            });
            previewCam.srcObject = null;
        }
        if (previewCam) {
            try {
                previewCam.pause();
            } catch (_) {}
            previewCam.removeAttribute("src");
            try {
                previewCam.load();
            } catch (_) {}
        }
    } catch (e) {
        console.warn("stopLivePreview error:", e);
    }
}

// ======== Get data record ========
getDataRecord = () => {
    $.ajax({
        url: "/creator/record/check?type=record",
        method: "GET",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
        },
        dataType: "json",
        success: function (res) {
            console.log("Response from server:", res);

            if (res?.status === "error") {
                notyf.error(res.message);
                const redirectUrl = res.redirect || "/";
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 1200);
                return;
            }

            const streamUrl = res.streamUrl;
            cameraList = res.cameraData || [];
            console.log("Camera List:", cameraList);
            console.log("Stream URL:", streamUrl);

            if (cameraList.length <= 1) {
                changeCamBtn.prop("disabled", true);
            } else {
                changeCamBtn.prop("disabled", false);
                changeCamera();
            }

            const activeCamera = cameraList.length > 0 ? cameraList[0] : null;
            if (activeCamera) {
                camName.text(activeCamera.name);
                camCode.text(activeCamera.code);
            }

            if (streamUrl) {
                if (streamUrl.endsWith(".m3u8")) {
                    // HLS
                    previewCam.srcObject = null;
                    previewCam.src = streamUrl;
                    previewCam.play().catch(() => {});
                } else {
                    // WebRTC / WHEP
                    livePreviewCam(streamUrl);
                }
            }

            let recordData = res.recordData;
            let scannedQrData = res.scannedQrData;

            populateDataPanel(scannedQrData, recordData);

            if (recordData?.start_time && recordData?.duration) {
                autoStopRecording(recordData.start_time, recordData.duration);
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX error:", error, xhr.responseText);
            window.location.href = "/";
        },
    });
};

// ======== Auto stop recording function ========
function autoStopRecording(startTime, durationMinutes) {
    const start = new Date(startTime.replace(" ", "T"));
    const end = new Date(start.getTime() + durationMinutes * 60000);

    function updateTimer() {
        const now = new Date();
        let remaining = Math.floor((end - now) / 1000);

        if (remaining <= 0) {
            timer
                .text("00:00:00")
                .removeClass()
                .addClass("text-vivaldi-red text-lg tracking-wide font-bold");
            clearInterval(timerInterval);
            triggerStopRecording();
            return;
        }

        const hours = String(Math.floor(remaining / 3600)).padStart(2, "0");
        const minutes = String(Math.floor((remaining % 3600) / 60)).padStart(
            2,
            "0"
        );
        const seconds = String(remaining % 60).padStart(2, "0");

        timer.text(`${hours}:${minutes}:${seconds}`);

        if (remaining <= 60) {
            timer
                .removeClass()
                .addClass("text-vivaldi-red text-lg tracking-wide font-bold");
        } else if (remaining <= 300) {
            timer
                .removeClass()
                .addClass("text-kin-gold text-lg tracking-wide font-semibold");
        } else {
            timer
                .removeClass()
                .addClass(
                    "text-exit-light text-lg tracking-wide font-semibold"
                );
        }
    }

    updateTimer();
    if (timerInterval) clearInterval(timerInterval);
    timerInterval = setInterval(updateTimer, 1000);
}

// ======== Stop recording function for auto stop ========
function triggerStopRecording() {
    stopRecord.prop("disabled", true);

    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }

    timer
        .text("Recording stopped")
        .removeClass()
        .addClass("text-vivaldi-red text-lg tracking-wide font-bold");

    loadingBar.removeClass("hidden");
    const fakeInterval = startFakeProgress();

    stopLivePreview();

    $.ajax({
        url: "/creator/record/stop?type=record",
        method: "POST",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        success: function (res) {
            console.log("Recording stopped successfully:", res);
            finishProgress(fakeInterval, true);
            notyf.success("Recording stopped successfully.");

            setTimeout(() => {
                window.location.href = "/my-recording";
            }, 1200);
        },
        error: function (xhr, status, error) {
            console.error("AJAX error:", error);
            finishProgress(fakeInterval, false);
            notyf.error("Failed to stop recording.");
        },
    });
}

// ======== Stop recording via button ========
stopRecordingManual = () => {
    stopRecord.on("click", function (e) {
        e.preventDefault();

        stopRecord.prop("disabled", true);

        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }

        timer
            .text("Recording stopped")
            .removeClass()
            .addClass("text-vivaldi-red text-lg tracking-wide font-bold");

        loadingBar.removeClass("hidden");
        const fakeInterval = startFakeProgress();

        stopLivePreview();

        $.ajax({
            url: "/creator/record/stop?type=record",
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            success: function (res) {
                console.log("Recording stopped manually:", res);
                finishProgress(fakeInterval, true);
                notyf.success("Recording stopped successfully.");
                setTimeout(() => {
                    window.location.href = "/my-recording";
                }, 1200);
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", error);
                finishProgress(fakeInterval, false);
                notyf.error("Failed to stop recording.");
            },
        });
    });
};

// ======== Populate Data ========
populateDataPanel = (scannedQrData, recordData) => {
    try {
        const venue = scannedQrData?.qr_code?.field?.venue?.name || "N/A";
        const field = scannedQrData?.qr_code?.field?.name || "N/A";
        const video = recordData?.video_name || "N/A";
        const duration = recordData?.duration
            ? recordData.duration + " Min"
            : "N/A";

        venueName.text(venue);
        fieldName.text(field);
        videoName.text(video);
        durationVideo.text(duration);
    } catch (e) {
        console.error("populateDataPanel error:", e);
    }
};

// ======== Fullscreen function ========
fullScreenVideo = () => {
    const isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);

    function simulateFullscreen(video) {
        video.classList.add(
            "fixed",
            "top-0",
            "left-0",
            "w-screen",
            "h-screen",
            "z-50"
        );
        video.play();
    }

    function exitSimulatedFullscreen(video) {
        video.classList.remove(
            "fixed",
            "top-0",
            "left-0",
            "w-screen",
            "h-screen",
            "z-50"
        );
    }

    fullScreenBtn.on("click", () => {
        try {
            if (isIOS && previewCam.webkitEnterFullscreen) {
                previewCam.webkitEnterFullscreen();
            } else if (isIOS) {
                simulateFullscreen(previewCam);
            } else if (previewCam.requestFullscreen) {
                previewCam.requestFullscreen();
            } else if (previewCam.webkitRequestFullscreen) {
                previewCam.webkitRequestFullscreen();
            } else {
                alert("Fullscreen not supported on this device.");
            }
        } catch (err) {
            console.error("Fullscreen error:", err);
        }
    });
};

// ======== Live preview function ========
livePreviewCam = async (streamUrl) => {
    try {
        if (previewCam.srcObject) {
            previewCam.srcObject.getTracks().forEach((t) => t.stop());
            previewCam.srcObject = null;
        }

        const pc = new RTCPeerConnection({
            iceServers: [{ urls: "stun:stun.l.google.com:19302" }],
        });

        pc.addTransceiver("video", { direction: "recvonly" });

        pc.ontrack = (event) => {
            previewCam.srcObject = event.streams[0];
        };

        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);

        const resp = await fetch(streamUrl, {
            method: "POST",
            headers: { "Content-Type": "application/sdp" },
            body: offer.sdp,
        });

        const answerSDP = await resp.text();
        await pc.setRemoteDescription({ type: "answer", sdp: answerSDP });
    } catch (err) {
        console.error("WebRTC Error:", err);
    }
};

// ======== Change camera function ========
changeCamera = () => {
    changeCamBtn.off("click");
    changeCamBtn.on("click", () => {
        console.log("Change camera clicked!");
        currentCamIndex = (currentCamIndex + 1) % cameraList.length;
        const nextCam = cameraList[currentCamIndex];

        $.ajax({
            url: `/creator/record/change-cam?type=record&field_id=${nextCam.field_id}&camera_code=${nextCam.code}`,
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
            success: function (res) {
                if (res.status === "success" && res.streamUrl) {
                    notyf.success("Change camera for live preview success!");

                    camName.text(nextCam.name);
                    camCode.text(nextCam.code);

                    if (res.streamUrl.endsWith(".m3u8")) {
                        previewCam.srcObject = null;
                        previewCam.src = res.streamUrl;
                        previewCam.play();
                    } else {
                        livePreviewCam(res.streamUrl);
                    }
                } else {
                    console.warn(
                        "Change camera failed, fallback to first camera:",
                        res
                    );
                    notyf.warning(
                        "Change camera failed, fallback to first camera!"
                    );

                    currentCamIndex = 0;
                    const firstCam = cameraList[0];

                    $.ajax({
                        url: `/creator/record/change-cam?type=record&field_id=${firstCam.field_id}&camera_code=${firstCam.code}`,
                        method: "GET",
                        headers: {
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        success: function (resFirst) {
                            if (
                                resFirst.status === "success" &&
                                resFirst.streamUrl
                            ) {
                                camName.text(firstCam.name);
                                camCode.text(firstCam.code);

                                if (resFirst.streamUrl.endsWith(".m3u8")) {
                                    previewCam.srcObject = null;
                                    previewCam.src = resFirst.streamUrl;
                                    previewCam.play();
                                } else {
                                    livePreviewCam(resFirst.streamUrl);
                                }
                            } else {
                                notyf.error("No cameras can be show!");
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("Fallback camera AJAX error:", error);
                            notyf.error("Can't load the fallback camera.");
                        },
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error("Change camera AJAX error:", error);
                notyf.error("Failed to change camera!");
            },
        });
    });
};

// ======== Force reload if user presses Back ========
window.addEventListener("pageshow", function (event) {
    if (
        event.persisted ||
        performance.getEntriesByType("navigation")[0].type === "back_forward"
    ) {
        console.log("Back navigation detected → reload page");
        window.location.reload();
    }
});

document.addEventListener("DOMContentLoaded", () => {
    getDataRecord();
    fullScreenVideo();
    stopRecordingManual();
});
