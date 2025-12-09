let renderList,
    updateInfo,
    renderPagination,
    goToPage,
    fetchRecordings,
    formatDate,
    flattenVideos,
    shareVideo,
    showShareModal,
    showGetVideosModal,
    getVideo;

const perPage = 10;
let currentPage = 1;
let recordings = [];
let total = 0;
let lastPage = 0;

// ==== DOM Elements ==== //
const listContainer = document.querySelector("#recordingList");
const showingInfo = document.querySelector("#showing-info");
const pageNumbers = document.querySelector("#pageNumbers");
const prevBtn = document.querySelector("#prevPage");
const nextBtn = document.querySelector("#nextPage");
const modal = document.getElementById("shareModal");
const modalGetVideo = document.getElementById("getVideoModal");
const input = document.getElementById("shareLinkInput");
const copyBtn = document.getElementById("copyShareLink");

// ==== Utils: Format Date ==== //
formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });
};

// ==== Utils: Flatten videos ==== //
flattenVideos = () => {
    return recordings.flatMap((rec) =>
        (rec.recorded_video || []).map((video) => ({
            ...video,
            recording: rec,
        }))
    );
};

$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
    },
});

// ==== Fetch Recordings ==== //
fetchRecordings = () => {
    return $.ajax({
        url: "/my-recording/recording-data",
        method: "GET",
        dataType: "json",
        success: (response) => {
            console.log(response);
            recordings = response ?? [];

            total = recordings.reduce(
                (sum, rec) => sum + (rec.recorded_video?.length || 0),
                0
            );

            if (total > 0) {
                $("#download_video_alert").removeClass("hidden");
            } else {
                $("#download_video_alert").addClass("hidden");
            }

            lastPage = Math.ceil(total / perPage);

            renderList();
            updateInfo();
            renderPagination();
        },
        error: () => {
            listContainer.innerHTML = `<p class="col-span-5 text-center text-sm text-magnesium">Failed to load recordings.</p>`;
        },
    });
};

// ==== Render List ==== //
renderList = () => {
    listContainer.innerHTML = "";

    const videos = flattenVideos();
    const start = (currentPage - 1) * perPage;
    const end = start + perPage;
    const data = videos.slice(start, end);

    if (data.length === 0) {
        listContainer.innerHTML = `<p class="col-span-5 text-center text-sm text-carbon dark:text-white-owl">No recordings found.</p>`;
        return;
    }

    data.forEach((item) => {
        const rec = item.recording;

        listContainer.insertAdjacentHTML(
            "beforeend",
            `
            <div class="w-full">
                <!-- Thumbnail -->
                <a href="/video/watch/${
                    item.hashed_id
                }" target="_blank" rel="noopener noreferrer" 
                   class="block bg-base-300 rounded-xl p-3 min-h-44 mb-2 relative"
                   style="background-image: url('/storage/${
                       item.thumbnail_path
                   }');
                          background-size: cover;
                          background-position: center;">
                    <div class="absolute bottom-2 right-2 text-xs font-mono bg-eerie-black text-white p-2 rounded-xl">
                        ${item.duration_formatted ?? item.duration ?? "-"}
                    </div>
                </a>
                
                <!-- Info -->
                <div class="text-sm space-y-1">
                    <div class="flex justify-between items-center gap-2">
                        <p class="font-semibold text-after-midnight dark:text-white-chalk">${
                            rec.video_name
                        }</p>
                        <p class="font-medium text-adhesion text-xs tracking-wide">
                            ${formatDate(rec.created_at)}
                        </p>
                    </div>
                    <p class="text-xs tracking-wide text-after-midnight dark:text-white-chalk">${
                        rec.field?.venue?.name ?? "-"
                    } - ${rec.field?.name ?? "-"}</p>
                </div>
                
                <!-- Share button -->
                <div class="mt-2 flex items-center gap-2">
                    <button class="share-btn flex items-center justify-center rounded-full h-8 w-8 bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white dark:hover:bg-white-owl transition tooltip tooltip-bottom"
                    data-id="${item.id}"
                            data-tip="share">
                        <i data-lucide="forward" class="w-4 h-4"></i>
                    </button>

                    <button class="download-btn flex items-center justify-center rounded-full h-8 w-8 bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white dark:hover:bg-white-owl transition tooltip tooltip-bottom"
                    data-id="${item.id}"
                            data-tip="download">
                        <i data-lucide="download" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
            `
        );
    });

    // Refresh icons
    if (window.lucide) {
        window.lucide.createIcons({ icons: window.lucide.icons });
    }
};

// ==== Update Info ==== //
updateInfo = () => {
    if (total === 0) {
        showingInfo.textContent = `Showing 0 to 0 of 0 videos`;
        return;
    }

    const from = (currentPage - 1) * perPage + 1;
    const to = Math.min(currentPage * perPage, total);
    showingInfo.textContent = `Showing ${from} to ${to} of ${total} videos`;
};

// ==== Render Pagination ==== //
renderPagination = () => {
    pageNumbers.innerHTML = "";

    for (let i = 1; i <= lastPage; i++) {
        const btn = document.createElement("button");
        btn.textContent = i;
        btn.className = `px-2 ${
            i === currentPage ? "text-hot-shot font-bold" : "text-magnesium"
        }`;
        btn.addEventListener("click", () => goToPage(i));
        pageNumbers.appendChild(btn);
    }

    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled = currentPage === lastPage;
};

// ==== Go To Page ==== //
goToPage = (page) => {
    if (page < 1 || page > lastPage) return;
    currentPage = page;
    renderList();
    updateInfo();
    renderPagination();
};

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

// ==== Download Video ==== //
downloadVideo = (videoId) => {
    notyf.success("Preparing video download...");

    $.ajax({
        url: `/download/${videoId}`,
        method: "POST",
        success: (response) => {
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
        },
        error: (xhr) => {
            if (xhr.status === 401) {
                notyf.error("You are not logged in. Redirecting to login...");
                setTimeout(() => (window.location.href = "/login"), 2000);
            } else {
                notyf.error("Failed to download video.");
            }
        },
    });
};

// ==== Get Video ==== //
getVideo = () => {
    document
        .getElementById("submitAccessCode")
        ?.addEventListener("click", () => {
            const accessCode = document
                .getElementById("access_code")
                .value.trim();

            if (!accessCode) {
                notyf.error("Access code cannot be empty.");
                return;
            }

            $.ajax({
                url: `/my-recording/get-video`,
                method: "POST",
                data: { access_code: accessCode },
                success: (response) => {
                    if (response.success) {
                        notyf.success("Access granted!");

                        modalGetVideo.classList.remove("show");
                        modalGetVideo.close();

                        if (response.url) {
                            window.location.href = response.url;
                        }
                    } else {
                        notyf.error(response.message || "Invalid access code.");
                    }
                },
                error: (xhr) => {
                    notyf.error(
                        xhr.responseJSON?.message ||
                            "Failed to verify access code."
                    );
                },
            });
        });
};

// ==== Show Share Modal ==== //
showShareModal = (shareUrl) => {
    if (!modal || !input) return;

    input.value = shareUrl;

    modal.showModal();
    requestAnimationFrame(() => modal.classList.add("show"));
};

// ==== Show Share Modal ==== //
showGetVideosModal = () => {
    if (!modalGetVideo) return;

    modalGetVideo.showModal();
    requestAnimationFrame(() => modalGetVideo.classList.add("show"));
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

// ==== Event Listeners ==== //
prevBtn.addEventListener("click", () => goToPage(currentPage - 1));
nextBtn.addEventListener("click", () => goToPage(currentPage + 1));
document.addEventListener("click", (e) => {
    if (e.target.closest(".share-btn")) {
        const videoId = e.target.closest(".share-btn").dataset.id;
        shareVideo(videoId);
    }
    if (e.target.closest(".download-btn")) {
        const videoId = e.target.closest(".download-btn").dataset.id;
        downloadVideo(videoId);
    }
    if (e.target.closest(".get-videos")) {
        showGetVideosModal();
    }
    if (e.target.closest("#close_download_video_alert")) {
        $("#download_video_alert").addClass("hidden");
    }
});

// ==== Init ==== //
document.addEventListener("DOMContentLoaded", () => {
    fetchRecordings();
    getVideo();
});
