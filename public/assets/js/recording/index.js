let renderList,
    updateInfo,
    renderPagination,
    goToPage,
    fetchRecordings,
    formatDate,
    flattenVideos;

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

// ==== Fetch Recordings ==== //
fetchRecordings = () => {
    return $.ajax({
        url: "/my-recording/recording-data",
        method: "GET",
        dataType: "json",
        success: (response) => {
            recordings = response ?? [];

            total = recordings.reduce(
                (sum, rec) => sum + (rec.recorded_video?.length || 0),
                0
            );

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
                        ${rec.duration_formatted ?? "-"}
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
                <div class="mt-2">
                    <button class="flex items-center justify-center rounded-full h-8 w-8 bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white transition tooltip tooltip-bottom"
                            data-tip="share">
                        <i data-lucide="forward" class="w-4 h-4"></i>
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

// ==== Event Listeners ==== //
prevBtn.addEventListener("click", () => goToPage(currentPage - 1));
nextBtn.addEventListener("click", () => goToPage(currentPage + 1));

// ==== Init ==== //
document.addEventListener("DOMContentLoaded", () => {
    fetchRecordings();
});
