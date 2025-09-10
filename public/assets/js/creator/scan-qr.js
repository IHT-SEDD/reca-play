let statusOnline,
    statusOffline,
    statusBadSignal,
    qrResult,
    html5QrcodeScanner,
    setStatus,
    scanSuccess,
    QrReader;

// ======== Scanning result false in the first ========
let scanProcessed = false;
// ======== QR reader close in the first ========
let isRunning = false;

// ======== Initialize component ========
statusOnline = document.getElementById("status-online");
statusOffline = document.getElementById("status-offline");
statusBadSignal = document.getElementById("status-badsignal");
qrResult = document.getElementById("qr-result");
continueBtn = document.getElementById("continueBtn");
continueBtn.classList.add("hidden");

// ======== Camera Statuses ========
setStatus = (status) => {
    statusOnline.classList.add("hidden");
    statusOffline.classList.add("hidden");
    statusBadSignal.classList.add("hidden");

    if (status === "online") statusOnline.classList.remove("hidden");
    else if (status === "offline") statusOffline.classList.remove("hidden");
    else if (status === "badsignal") statusBadSignal.classList.remove("hidden");
};

// ======== Scanning success function ========
scanSuccess = (decodedText, decodedResult) => {
    if (scanProcessed) return;
    scanProcessed = true;

    let parsedToken;
    try {
        const jsonData = JSON.parse(decodedText);
        parsedToken = jsonData.token;
    } catch (e) {
        console.error("Invalid QR JSON:", e);
        qrResult.innerText = "Invalid QR code format.";
        qrResult.style.color = "#EA3A3A";
        stopScanner();
        return;
    }

    qrResult.innerText = `Scanning QR...`;
    qrResult.style.color = "#38383F";

    $.ajax({
        url: "/creator/scan-qr/process",
        method: "POST",
        data: JSON.stringify({ token: parsedToken }),
        contentType: "application/json",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (data) {
            console.log(data);
            if (data.status === "success") {
                const venueName = data.data.venue?.name ?? "Unknown Venue";
                const venueFieldName =
                    data.data.field?.venue?.name ?? "Unknown Venue";
                const fieldName = data.data.field?.name ?? "Unknown Field";

                if (data.data.type === "qr_field") {
                    qrResult.innerHTML = `
                        Venue: <span style="color:#EC5228">${venueFieldName}</span><br>
                        Field: <span style="color:#EC5228">${fieldName}</span>
                    `;
                    continueBtn.classList.remove("hidden");
                    continueBtn.classList.add("flex");
                } else if (data.data.type === "qr_venue") {
                    qrResult.innerHTML = `
                        Venue: <span style="color:#EC5228">${venueName}</span>
                    `;
                    continueBtn.classList.remove("hidden");
                    continueBtn.classList.add("flex");
                }
            } else {
                continueBtn.classList.add("hidden");
                continueBtn.classList.remove("flex");
                qrResult.innerText = `${data.message}`;
                qrResult.style.color = "#EA3A3A";
            }
            stopScanner();
        },
        error: function (xhr, status, error) {
            qrResult.innerText = "Error sending QR to system.";
            qrResult.style.color = "#EA3A3A";
            stopScanner();
        },
    });
};

// ======== Reset scan function ========
function resetScan() {
    scanProcessed = false;
    qrResult.innerText = "Ready to scan...";
    qrResult.style.color = "#38383F";
    continueBtn.classList.add("hidden");
    continueBtn.classList.remove("flex");
}

// ======== Stop scan function ========
function stopScanner() {
    if (html5QrcodeScanner) {
        html5QrcodeScanner
            .stop()
            .then(() => {
                isRunning = false;
                setStatus("offline");
                console.log("Camera stopped after scan.");
            })
            .catch((err) => {
                console.error("Failed to stop camera:", err);
            });
    }
}

// ======== Initialize QR reader ========
QrReader = () => {
    html5QrcodeScanner = new Html5Qrcode("qr-reader");

    html5QrcodeScanner
        .start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            scanSuccess
        )
        .then(() => {
            isRunning = true;
            setStatus("online");
        })
        .catch((err) => {
            console.error(err);
            setStatus("offline");
        });

    html5QrcodeScanner.onCameraError = (error) => {
        console.warn(error);
        setStatus("badsignal");
    };
};

// ======== Scan again function ========
function scanAgain() {
    resetScan();
    if (html5QrcodeScanner) {
        if (isRunning) {
            html5QrcodeScanner
                .stop()
                .then(() => {
                    isRunning = false;
                    console.log("Scanner stopped, restarting...");
                    return html5QrcodeScanner.start(
                        { facingMode: "environment" },
                        { fps: 10, qrbox: { width: 250, height: 250 } },
                        scanSuccess
                    );
                })
                .then(() => {
                    isRunning = true;
                    setStatus("online");
                    console.log("Scanner restarted.");
                })
                .catch((err) => {
                    console.error("Failed to restart scanner:", err);
                    setStatus("offline");
                });
        } else {
            html5QrcodeScanner
                .start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: { width: 250, height: 250 } },
                    scanSuccess
                )
                .then(() => {
                    isRunning = true;
                    setStatus("online");
                    console.log("Scanner started.");
                })
                .catch((err) => {
                    console.error("Failed to start scanner:", err);
                    setStatus("offline");
                });
        }
    } else {
        QrReader();
    }
}

// ======== Scan again button clicked ========
document.getElementById("scanAgain").addEventListener("click", scanAgain);

document.addEventListener("DOMContentLoaded", () => {
    QrReader();
});
