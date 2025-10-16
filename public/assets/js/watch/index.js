const pathParts = window.location.pathname.split("/");
const videoEncrypt = pathParts[pathParts.length - 1];

console.log("Inisialisasi Watch Page:", videoEncrypt);

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
                data.created_at
                    ? dayjs(data.created_at).format("YYYY-MM-DD HH:mm:ss")
                    : "Unknown Date"
            );

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

$(window).on("load", function () {
    videoPlayer();
});
