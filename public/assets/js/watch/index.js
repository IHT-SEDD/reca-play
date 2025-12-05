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

    const placeholders = [
        "#video_player_placeholder",
        "#video_name_placeholder",
        "#share_btn_placeholder",
        "#like_btn_placeholder",
        "#dislike_btn_placeholder",
        "#owner_video_placeholder",
        "#owner_follower_placeholder",
        "#follow_button_placeholder",
        "#date_created_placeholder",
    ];

    const realElements = [
        "#video_player",
        "#video_name",
        ".share-btn",
        ".like-btn",
        ".dislike-btn",
        "#owner_video",
        "#owner_follower",
        ".follow-btn",
        "#date_created",
    ];

    placeholders.forEach((sel) => $(sel).removeClass("hidden"));
    realElements.forEach((sel) => $(sel).addClass("hidden"));

    $.ajax({
        url: `/video/watch/data/${videoEncrypt}`,
        method: "GET",
        dataType: "json",
        success: function (data) {
            if (!data.video_path) {
                console.error("video_path kosong atau tidak ditemukan.");
                return;
            }

            placeholders.forEach((sel) => $(sel).addClass("hidden"));
            realElements.forEach((sel) => $(sel).removeClass("hidden"));

            const videoSrc = `/storage/${data.video_path}`;
            sourceEl.attr("src", videoSrc);
            videoEl[0].load();

            videoNameEl.text(data.recording?.video_name ?? "Unknown Title");
            ownerEl.text(data.recording?.user?.name ?? "Unknown Owner");
            ownerEl.attr("data-id", data.recording?.user?.id);

            const viewerId = window.authUserId;
            const ownerId = data.recording?.user?.id;

            if (viewerId && ownerId && viewerId === ownerId) {
                $(".follow-btn")
                    .addClass("opacity-50 cursor-not-allowed")
                    .prop("disabled", true)
                    .attr("data-tip", "You cannot follow yourself");
                $(".like-btn")
                    .addClass("opacity-50 cursor-not-allowed")
                    .prop("disabled", true)
                    .attr("data-tip", "Cannot like your own video");
                $(".dislike-btn")
                    .addClass("opacity-50 cursor-not-allowed")
                    .prop("disabled", true)
                    .attr("data-tip", "Cannot dislike your own video");
            }

            const start = dayjs(data.recording?.start_time);
            const end = dayjs(data.recording?.end_time);

            if (!start.isValid() || !end.isValid()) {
                dateEl.text("Recorded at Unknown Date");
                return;
            }

            let output = "";

            if (start.isSame(end, "day")) {
                output = `Recorded at ${start.format(
                    "DD MMM YYYY"
                )}, at ${start.format("HH:mm")} to ${end.format("HH:mm")}`;
            } else if (
                start.month() === end.month() &&
                start.year() === end.year()
            ) {
                output = `Recorded at ${start.format("DD")}-${end.format(
                    "DD"
                )} ${start.format("MMM YYYY")}, ${start.format(
                    "HH:mm"
                )} to ${end.format("HH:mm")}`;
            }

            dateEl.text(output);

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
    if (e.target.closest(".like-btn")) {
        const videoId = e.target.closest(".like-btn").dataset.id;
        $.ajax({
            url: "/video/watch/like",
            method: "POST",
            data: { id: videoId },
            success: function (res) {
                console.log(res);
                notyf.success("You liked this video!");
            },
            error: function () {
                notyf.error("Failed to like the video");
            },
        });
    }
    if (e.target.closest(".dislike-btn")) {
        const videoId = e.target.closest(".dislike-btn").dataset.id;
        $.ajax({
            url: "/video/watch/dislike",
            method: "POST",
            data: { id: videoId },
            success: function (res) {
                console.log(res);
                notyf.success("You disliked this video!");
            },
            error: function () {
                notyf.error("Failed to dislike the video");
            },
        });
    }
    if (e.target.closest(".follow-btn")) {
        const ownerId = $("#owner_video").data("id");
        $.ajax({
            url: "/video/watch/follow",
            method: "POST",
            data: { id: ownerId },
            success: function (res) {
                console.log(res);
                $("#owner_follower").text(`${res.followers} Followers`);
                notyf.success("You followed this user!");
            },
            error: function () {
                notyf.error("Failed to follow the user");
            },
        });
    }
});

$(window).on("load", function () {
    videoPlayer();
});
