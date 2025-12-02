let shareVideo, showShareModal;

const pathParts = window.location.pathname.split("/");
const videoEncrypt = pathParts[pathParts.length - 1];

console.log("Inisialisasi Watch Page:", videoEncrypt);

const modal = document.getElementById("shareModal");
const input = document.getElementById("shareLinkInput");
const copyBtn = document.getElementById("copyShareLink");

$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
    },
});

function videoPlayer() {
    const videoEl = $("#video_player");
    const sourceEl = videoEl.find("source");
    const videoNameEl = $("#video_name");
    const ownerEl = $("#owner_video");
    const dateEl = $("#date_created");

    console.log("Memuat data video...");

    $.ajax({
        url: `/video/watch/data/${videoEncrypt}`,
        method: "GET",
        dataType: "json",
        success: function (data) {
            console.log("Data video diterima:", data);

            if (!data.video_path) {
                console.error("video_path kosong atau tidak ditemukan.");
                return;
            }

            const videoSrc = `/storage/${data.video_path}`;
            sourceEl.attr("src", videoSrc);
            videoEl[0].load();

            videoNameEl.text(data.recording?.video_name ?? "Unknown Title");
            ownerEl.text(data.recording?.user?.name ?? "Unknown Owner");
            dateEl.text(
                `${
                    data.recording?.start_time
                        ? dayjs(data.recording.start_time).format(
                              "YYYY-MM-DD HH:mm:ss"
                          )
                        : "Unknown Date"
                } - ${
                    data.recording?.end_time
                        ? dayjs(data.recording.end_time).format(
                              "YYYY-MM-DD HH:mm:ss"
                          )
                        : "Unknown Date"
                }`
            );

            $(".share-btn").attr("data-id", data.id);

            console.log("Video siap diputar:", videoSrc);
        },
        error: function (xhr, status, error) {
            console.error("Gagal mengambil data video:", {
                status: status,
                error: error,
                response: xhr.responseText,
            });
        },
    });

    videoEl.on("loadeddata", function () {
        console.log("Video berhasil dimuat dan siap diputar.");
    });

    videoEl.on("error", function (e) {
        const video = e.target;
        console.error("Video gagal dimuat:", {
            src: video.currentSrc || video.src,
            networkState: video.networkState,
            error: video.error
                ? {
                      code: video.error.code,
                      message: video.error.message || "Unknown media error",
                  }
                : "No media error info",
        });
    });
}

// ==== Share Video ==== //
shareVideo = (videoId) => {
    $.ajax({
        url: `/share/${videoId}`,
        method: "POST",
        success: (response) => {
            console.log(response);
            showShareModal(response.url);
        },
        error: (xhr) => {
            if (xhr.status === 401) {
                notyf.error(
                    "You are not logged in. Redirecting to the login page..."
                );
                setTimeout(() => {
                    window.location.href = "/login";
                }, 2000);
            } else {
                notyf.error("Failed to generate share link.");
            }
        },
    });
};

// ==== Show Share Modal ==== //
showShareModal = (shareUrl) => {
    if (!modal || !input) return;

    input.value = shareUrl;

    modal.showModal();
    requestAnimationFrame(() => modal.classList.add("show"));
};

// ==== Modal Events ==== //
if (modal) {
    modal.addEventListener("close", () => {
        modal.classList.remove("show");
    });

    if (copyBtn) {
        copyBtn.addEventListener("click", () => {
            navigator.clipboard
                .writeText(input.value)
                .then(() => notyf.success("Link copied to clipboard!"))
                .catch(() => notyf.error("Failed to copy link."));
        });
    }
}

// ==== Event Delegation ==== //
document.addEventListener("click", (e) => {
    if (e.target.closest(".share-btn")) {
        const videoId = e.target.closest(".share-btn").dataset.id;
        shareVideo(videoId);
    }
});

$(window).on("load", function () {
    videoPlayer();
});
