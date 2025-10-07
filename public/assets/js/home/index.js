let fetchVideo, renderList, shareVideo, showShareModal;

// ==== Selectors ==== //
const listContainer = document.querySelector("#latestVideoList");
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

// ==== Fetch Videos ==== //
fetchVideo = () => {
    return $.ajax({
        url: "/video-list",
        method: "GET",
        dataType: "json",
        success: (response) => {
            console.log(response);
            renderList(response);
        },
        error: () => {
            listContainer.innerHTML = `
                <p class="col-span-5 text-center text-sm text-magnesium">
                    Failed to load videos.
                </p>`;
        },
    });
};

// ==== Render Videos ==== //
renderList = (videos) => {
    listContainer.innerHTML = "";

    if (!videos || videos.length === 0) {
        listContainer.innerHTML = `
            <p class="col-span-5 text-center text-sm text-magnesium dark:text-white">
                No videos found.
            </p>`;
        return;
    }

    videos.forEach((videoItem) => {
        videoItem.recorded_video.forEach((video) => {
            listContainer.insertAdjacentHTML(
                "beforeend",
                `
                <a href="/video/watch/${
                    video.hashed_id
                }" target="_blank" rel="noopener noreferrer" class="block">
                    <div class="w-full">
                        <!-- Thumbnail -->
                        <div class="bg-base-200 rounded-xl p-3 min-h-44 mb-2 relative"
                            style="background-image: url('/storage/${
                                video.thumbnail_path
                            }');
                                    background-size: cover;
                                    background-position: center;">
                            <div class="absolute bottom-2 right-2 text-xs font-mono bg-eerie-black text-white p-2 rounded-xl">
                                ${videoItem.duration_formatted ?? "-"}
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="text-sm space-y-1">
                            <p class="font-medium text-hot-shot">${
                                videoItem.video_name ?? "-"
                            }</p>
                            <p class="text-after-midnight/50 dark:text-white-owl text-xs">
                                ${formatDateTime(videoItem.created_at)}
                            </p>
                            <p class="flex items-center text-xs text-after-midnight dark:text-white-owl">
                                <i data-lucide="user" class="w-4 h-4 md:me-2"></i>
                                ${videoItem.user?.name ?? "Unknown"}
                            </p>
                            <p class="text-xs font-medium tracking-wide text-after-midnight dark:text-white-owl">
                                ${videoItem.field?.name ?? "-"} at ${
                    videoItem.field?.venue?.name ?? "-"
                }
                            </p>
                        </div>

                        <!-- Share Button -->
                        <div class="mt-2">
                            <button class="share-btn flex items-center justify-center rounded-full h-8 w-8 bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white dark:hover:bg-white-owl transition tooltip tooltip-bottom"
                                    data-id="${video.id}"
                                    data-tip="share">
                                <i data-lucide="forward" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </a>
                `
            );
        });
    });

    // Render lucide icons
    window.lucide.createIcons({ icons: window.lucide.icons });
};

// ==== Format Date ==== //
const formatDateTime = (dateString) => {
    const date = new Date(dateString);

    return (
        date.toLocaleDateString("id-ID", {
            day: "numeric",
            month: "long",
            year: "numeric",
        }) +
        " at " +
        date.toLocaleTimeString("id-ID", {
            hour: "2-digit",
            minute: "2-digit",
        })
    );
};

// ==== Share Video ==== //
shareVideo = (videoId) => {
    $.ajax({
        url: `/share/${videoId}`,
        method: "POST",
        success: (response) => {
            showShareModal(response.url);
        },
        error: () => {
            notyf.error("Failed to generate share link.");
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

// ==== Init ==== //
document.addEventListener("DOMContentLoaded", () => {
    fetchVideo();
});
