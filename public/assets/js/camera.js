document.addEventListener("DOMContentLoaded", async () => {
    const videoEl = document.getElementById("cameraVideo");
    const startBtn = document.getElementById("btnStart");
    const stopBtn = document.getElementById("btnStop");
    const tableBody = document.getElementById("recordingsTable");
    let startTime = null;

    console.log(videoEl);
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    // ==== INIT WEBRTC STREAM (WHEP) ====
    try {
        const pc = new RTCPeerConnection();
        pc.addTransceiver("video", { direction: "recvonly" });
        pc.ontrack = (event) => {
            videoEl.srcObject = event.streams[0];
        };

        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);

        const resp = await fetch("http://127.0.0.1:8889/camera1/whep", {
            method: "POST",
            headers: { "Content-Type": "application/sdp" },
            body: offer.sdp,
        });

        const answerSDP = await resp.text();
        await pc.setRemoteDescription({ type: "answer", sdp: answerSDP });
    } catch (err) {
        console.error("WebRTC Error:", err);
    }

    startBtn.addEventListener("click", function (e) {
        e.preventDefault();

        fetch("/api/camera/start-recording", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.status === "success") {
                    startTime = data.start_time;
                    alert("Recording started at " + startTime);
                }
            });
    });

    stopBtn.addEventListener("click", function (e) {
        e.preventDefault();

        fetch("/api/camera/stop-recording", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.status === "success") {
                    alert(
                        `Recording stopped. Duration: ${data.duration_seconds} seconds`
                    );
                }
            });
    });
});
