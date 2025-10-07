(function ($, RecordModule) {
    const { autoStopRecording, stopRecordingManual } = RecordModule;

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

    // ---------- others ----------
    let currentCamIndex = 0;
    let cameraList = [];
    // ======== Initialize component ::end ========

    /**
     * Fetch record and camera data from backend
     */
    function getDataRecord() {
        $.ajax({
            url: "/creator/record/check?type=record",
            method: "GET",
            headers: { "X-Requested-With": "XMLHttpRequest" },
            success: function (res) {
                if (res?.status === "error") {
                    notyf.error(res.message);
                    setTimeout(() => {
                        window.location.href = "/my-recording";
                    }, 1300);
                    return;
                }

                const streamUrl = res.streamUrl;
                cameraList = res.cameraData || [];

                // Enable/disable change camera button
                if (cameraList.length <= 1) {
                    changeCamBtn.prop("disabled", true);
                } else {
                    changeCamBtn.prop("disabled", false);
                    changeCamera();
                }

                // Set first active camera
                const activeCamera =
                    cameraList.length > 0 ? cameraList[0] : null;
                if (activeCamera) {
                    camName.text(activeCamera.name);
                    camCode.text(activeCamera.code);
                }

                // Load stream (HLS or WebRTC)
                if (streamUrl) {
                    if (streamUrl.endsWith(".m3u8")) {
                        previewCam.src = streamUrl;
                        previewCam.play();
                    } else {
                        livePreviewCam(streamUrl);
                    }
                }

                // Fill data panel
                let recordData = res.recordData;
                let scannedQrData = res.scannedQrData;
                populateDataPanel(scannedQrData, recordData);

                // Start auto stop + timer
                if (recordData?.start_time && recordData?.duration) {
                    autoStopRecording(
                        recordData.start_time,
                        recordData.duration,
                        timer
                    );
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", error);
            },
        });
    }

    /**
     * Populate venue, field, and video details
     */
    function populateDataPanel(scannedQrData, recordData) {
        venueName.text(scannedQrData.field.venue.name);
        fieldName.text(scannedQrData.field.name);
        videoName.text(recordData.video_name);
        durationVideo.text(recordData.duration + " Min");
    }

    /**
     * Enable fullscreen mode for preview video
     */
    function fullScreenVideo() {
        fullScreenBtn.on("click", () => {
            if (previewCam.requestFullscreen) {
                previewCam.requestFullscreen();
            } else if (previewCam.webkitRequestFullscreen) {
                previewCam.webkitRequestFullscreen();
            } else if (previewCam.msRequestFullscreen) {
                previewCam.msRequestFullscreen();
            }
        });
    }

    /**
     * Initialize WebRTC live preview
     */
    async function livePreviewCam(streamUrl) {
        try {
            // Reset previous stream
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
    }

    /**
     * Change active camera with fallback to first camera
     */
    function changeCamera() {
        changeCamBtn.off("click");
        changeCamBtn.on("click", () => {
            console.log("Change camera clicked!");
            currentCamIndex = (currentCamIndex + 1) % cameraList.length;
            const nextCam = cameraList[currentCamIndex];

            $.ajax({
                url: `/creator/record/check?type=record&field_id=${nextCam.field_id}&camera_code=${nextCam.code}`,
                method: "GET",
                headers: { "X-Requested-With": "XMLHttpRequest" },
                success: function (res) {
                    if (res.status === "success" && res.streamUrl) {
                        notyf.success(
                            "Change camera for live preview success!"
                        );
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

                        // fallback to first camera
                        currentCamIndex = 0;
                        const firstCam = cameraList[0];

                        $.ajax({
                            url: `/creator/record/check?type=record&field_id=${firstCam.field_id}&camera_code=${firstCam.code}`,
                            method: "GET",
                            headers: { "X-Requested-With": "XMLHttpRequest" },
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
                                console.error(
                                    "Fallback camera AJAX error:",
                                    error
                                );
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
    }

    /**
     * Document ready initialization
     */
    $(document).ready(() => {
        getDataRecord();
        fullScreenVideo();
        stopRecordingManual(stopRecord);
    });
})(jQuery, RecordModule);
