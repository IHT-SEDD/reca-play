let shareVideo, showShareModal, populateDataVideo;

const videoEncrypt = window.location.pathname.split("/").pop();
const modal = document.getElementById("shareModal");
const input = document.getElementById("shareLinkInput");
const copyBtn = document.getElementById("copyShareLink");

const videoEl = $("#video_player");
const sourceEl = videoEl.find("source");

const videoNameEl = $("#video_name");
const ownerEl = $("#owner_video");
const dateEl = $("#date_created");

$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
    },
});

/* ---------------------------------------------
 * UTILITIES
---------------------------------------------- */
const toggleState = (btn, disable = false) => {
    btn.prop("disabled", disable);
    btn.toggleClass("opacity-50 cursor-not-allowed", disable);
};

const setFollowBtn = (status_follow) => {
    const btn = $(".follow-btn");
    const text = $("#follow_btn_text");

    if (status_follow === "follow") {
        text.text("Followed");
        btn.attr("data-tip", "Unfollow this user");
    } else {
        text.text("Follow");
        btn.attr("data-tip", "Follow this user");
    }
};

/* ---------------------------------------------
 * MAIN LOADER
---------------------------------------------- */
function videoPlayer() {
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
        "#download_btn_placeholder",
    ];

    const real = [
        "#video_player",
        "#video_name",
        ".share-btn",
        ".like-btn",
        ".dislike-btn",
        "#owner_video",
        "#owner_follower",
        ".follow-btn",
        ".download-btn",
        "#date_created",
    ];

    placeholders.forEach((e) => $(e).removeClass("hidden"));
    real.forEach((e) => $(e).addClass("hidden"));

    $.getJSON(`/video/watch/data/${videoEncrypt}`)
        .done((res) => {
            placeholders.forEach((e) => $(e).addClass("hidden"));
            real.forEach((e) => $(e).removeClass("hidden"));
            populateDataVideo(res.video);
        })
        .fail((xhr) => {
            console.error("Gagal mengambil data video:", xhr.responseText);
        });

    videoEl.on("loadeddata", () => console.log("Video siap dimainkan"));
    videoEl.on("error", (e) => {
        const v = e.target;
        console.error("Video gagal dimuat:", {
            src: v.currentSrc,
            networkState: v.networkState,
            error: v.error,
        });
    });
}

/* ---------------------------------------------
 * POPULATE DATA
---------------------------------------------- */
populateDataVideo = (data) => {
    if (!data.video_path) return console.error("Video not found");

    const start = dayjs(data.recording?.start_time);
    const end = dayjs(data.recording?.end_time);
    const userLikes = data.video_user_like;
    const authId = Number(window.authUserId);
    const userFollow = data.recording?.user?.user_followers;

    // load video
    sourceEl.attr("src", `/storage/${data.video_path}`);
    videoEl[0].load();

    // text info
    videoNameEl.text(data.recording.video_name ?? "Unknown Title");
    ownerEl.text(data.recording?.user?.name ?? "Unknown Owner");
    ownerEl.attr("data-id", data.recording?.user?.id);

    // follow count
    $("#owner_follower").text(`${data.recording?.user?.followers} Followers`);

    // date output
    if (!start.isValid() || !end.isValid()) {
        dateEl.text("Recorded at Unknown Date");
    } else if (start.isSame(end, "day")) {
        dateEl.text(
            `Recorded at ${start.format("DD MMM YYYY")}, at ${start.format(
                "HH:mm"
            )} to ${end.format("HH:mm")}`
        );
    } else {
        dateEl.text(
            `Recorded at ${start.format("DD")}-${end.format(
                "DD"
            )} ${start.format("MMM YYYY")}, ${start.format(
                "HH:mm"
            )} to ${end.format("HH:mm")}`
        );
    }

    // assign ID to action buttons
    $(".share-btn, .like-btn, .dislike-btn, .download-btn").attr(
        "data-id",
        data.id
    );

    // disable if owner is the viewer
    if (window.authUserId === data.recording?.user?.id) {
        toggleState($(".follow-btn"), true);
        $(".follow-btn").attr("data-tip", "You cannot follow yourself");
        toggleState($(".like-btn"), true);
        $(".like-btn").attr("data-tip", "You cannot like your own video");
        toggleState($(".dislike-btn"), true);
        $(".dislike-btn").attr("data-tip", "You cannot dislike your own video");
        toggleState($(".download-btn"), false);
        $(".download-btn").attr("data-tip", "Download this video");
        return;
    } else {
        toggleState($(".download-btn"), true);
        $(".download-btn").attr(
            "data-tip",
            "This video is not yours to download"
        );
    }

    /** -----------------------------------------
     * INITIAL STATE (LIKE / DISLIKE / FOLLOW)
     ----------------------------------------- **/
    if (Array.isArray(userLikes) && userLikes.length > 0) {
        const userLike = userLikes.find(
            (like) => Number(like.user_id) === authId
        );

        if (userLike) {
            if (userLike.type === "like") {
                toggleState($(".like-btn"), true);
                toggleState($(".dislike-btn"), false);
            }

            if (userLike.type === "dislike") {
                toggleState($(".dislike-btn"), true);
                toggleState($(".like-btn"), false);
            }
        }
    }

    if (Array.isArray(userFollow) && userFollow.length > 0) {
        const userFollower = userFollow.find(
            (follow) => Number(follow.follower_id) === authId
        );

        if (userFollower) {
            $("#follow_btn_text").text("Followed");
            $(".follow-btn").attr("data-tip", "Unfollow this user");
        } else {
            $("#follow_btn_text").text("Follow");
            $(".follow-btn").attr("data-tip", "Follow this user");
        }
    } else {
        $("#follow_btn_text").text("Follow");
        $(".follow-btn").attr("data-tip", "Follow this user");
    }
};

/* ---------------------------------------------
 * SHARE VIDEO
---------------------------------------------- */
shareVideo = (videoId) => {
    $.post(`/share/${videoId}`)
        .done((res) => showShareModal(res.url))
        .fail((xhr) => {
            if (xhr.status === 401) {
                notyf.error("You must log in first...");
                setTimeout(() => (window.location.href = "/login"), 1500);
            } else {
                notyf.error("Failed to generate share link.");
            }
        });
};

/* ---------------------------------------------
 * SHARE MODAL
---------------------------------------------- */
showShareModal = (url) => {
    input.value = url;
    modal.showModal();
    requestAnimationFrame(() => modal.classList.add("show"));
};

modal?.addEventListener("close", () => modal.classList.remove("show"));

copyBtn?.addEventListener("click", () => {
    navigator.clipboard
        .writeText(input.value)
        .then(() => notyf.success("Copied!"))
        .catch(() => notyf.error("Failed to copy"));
});

/* ---------------------------------------------
 * EVENT DELEGATION
---------------------------------------------- */
document.addEventListener("click", (e) => {
    const share = e.target.closest(".share-btn");
    const like = e.target.closest(".like-btn");
    const dislike = e.target.closest(".dislike-btn");
    const follow = e.target.closest(".follow-btn");
    const download = e.target.closest(".download-btn");

    if (share) {
        shareVideo(share.dataset.id);
    }

    if (like) {
        $.post("/video/watch/like", { id: like.dataset.id })
            .done(() => {
                toggleState($(".like-btn"), true);
                toggleState($(".dislike-btn"), false);
                notyf.success("You liked this video!");
            })
            .fail(() => notyf.error("Failed to like video"));
    }

    if (dislike) {
        $.post("/video/watch/dislike", { id: dislike.dataset.id })
            .done(() => {
                toggleState($(".dislike-btn"), true);
                toggleState($(".like-btn"), false);
                notyf.success("You disliked this video!");
            })
            .fail(() => notyf.error("Failed to dislike video"));
    }

    if (follow) {
        const owner = $("#owner_video").data("id");

        $.post("/video/watch/follow", { id: owner })
            .done((res) => {
                $("#owner_follower").text(`${res.followers} Followers`);
                setFollowBtn(res.status_follow);
                notyf.success(
                    res.status_follow === "follow"
                        ? "You followed this user!"
                        : "You unfollowed this user!"
                );
            })
            .fail(() => notyf.error("Failed to follow/unfollow"));
    }

    if (download) {
        notyf.success("Preparing video download...");

        $.post(`/download/${download.dataset.id}`)
            .done(() => {
                if (response.success && response.url) {
                    const a = document.createElement("a");
                    a.href = response.url;
                    a.download = "";
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                } else {
                    notyf.error("Failed to generate download link.");
                }
            })
            .fail(() => {
                if (xhr.status === 401) {
                    notyf.error(
                        "You are not logged in. Redirecting to login..."
                    );
                    setTimeout(() => (window.location.href = "/login"), 2000);
                } else {
                    notyf.error("Failed to download video.");
                }
            });
    }
});

/* ---------------------------------------------
 * INIT
---------------------------------------------- */
$(window).on("load", videoPlayer);
