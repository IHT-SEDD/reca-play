let videoPlayer;

const pathParts = window.location.pathname.split("/");
const videoEncrypt = pathParts[pathParts.length - 1];

console.log(videoEncrypt);

videoPlayer = () => {
    const videoEl = $("#video_player");
    const sourceEl = videoEl.find("source");
    const videoNameEl = $("#video_name");
    const ownerEl = $("#owner_video");
    const dateEl = $("#date_created");

    $.ajax({
        url: `/video/watch/data/${videoEncrypt}`,
        method: "GET",
        dataType: "json",
        success: function (data) {
            if (data.video_path) {
                sourceEl.attr("src", `/storage/${data.video_path}`);
                videoEl[0].load();
                videoNameEl.text(data.recording.video_name ?? "Unknown Title");
                ownerEl.text(data.recording.user.name ?? "Unknown Owner");
                dateEl.text(
                    dayjs(data.created_at).format("YYYY-MM-DD HH:mm:ss") ??
                        "Unknown Date"
                );
            }
        },
        error: function (err) {
            console.error(err);
        },
    });
};

$(document).ready(() => {
    videoPlayer();
});
