let fetchData,
    populateData,
    toggleState,
    currentState,
    lastActivityTable,
    generateCode;

const pathParts = window.location.pathname.split("/");
const hashedId = pathParts[pathParts.length - 1];

$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

fetchData = (hashedId) => {
    $.ajax({
        url: `/venue-management/detail/data/${hashedId}`,
        method: "GET",
        dataType: "json",
        success: function (res) {
            currentState = res.field.is_active;
            populateData(res);
        },
        error: function (xhr, status, error) {
            console.error("Failed to fetch data:", error);
        },
    });
};

populateData = (res) => {
    const activeClass = "bg-base-200 text-hot-shot";
    const inactiveClass = "bg-transparent text-after-midnight";

    $("#field_name").text(res.field.name);
    $("#total_recorded_videos").text(res.dataTotalVideo);
    $("#total_visitor").text(res.dataTotalVisitor);

    if (res.dataPeakHour !== null && res.dataPeakHour !== undefined) {
        const hour = parseInt(res.dataPeakHour, 10);
        const start = hour.toString().padStart(2, "0") + ":00";
        const end = hour.toString().padStart(2, "0") + ":59";
        $("#peak_hour").text(`${start} - ${end}`);
    } else {
        $("#peak_hour").text("-");
    }

    if (res.dataMeanDuration) {
        $("#mean_duration_usage").text(res.dataMeanDuration + " minutes");
    } else {
        $("#mean_duration_usage").text("-");
    }

    if (res.field.is_active == 1) {
        $("#label_toggle").text("Set to inactive");
        $("#toggle_active").removeClass(inactiveClass).addClass(activeClass);
        $("#toggle_inactive").removeClass(activeClass).addClass(inactiveClass);
    } else {
        $("#label_toggle").text("Set to active");
        $("#toggle_inactive").removeClass(inactiveClass).addClass(activeClass);
        $("#toggle_active").removeClass(activeClass).addClass(inactiveClass);
    }
};

toggleState = () => {
    $.ajax({
        url: `/venue-management/detail/status/update/${hashedId}`,
        method: "POST",
        dataType: "json",
        success: function (res) {
            currentState = res.field.is_active;

            const activeClass = "bg-base-200 text-hot-shot";
            const inactiveClass = "bg-transparent text-after-midnight";

            if (currentState == 1) {
                $("#label_toggle").text("Set to inactive");
                $("#toggle_active")
                    .removeClass(inactiveClass)
                    .addClass(activeClass);
                $("#toggle_inactive")
                    .removeClass(activeClass)
                    .addClass(inactiveClass);
            } else {
                $("#label_toggle").text("Set to active");
                $("#toggle_inactive")
                    .removeClass(inactiveClass)
                    .addClass(activeClass);
                $("#toggle_active")
                    .removeClass(activeClass)
                    .addClass(inactiveClass);
            }
            notyf.success(res.message);
        },
        error: function (xhr, status, error) {
            console.error("Failed to toggle state:", error);
            notyf.error("Failed to toggle state! Please try again later.");
        },
    });
};

lastActivityTable = (hashedId) => {
    initCustomDatatable({
        tableId: "last-activity-table",
        tableDataUrl: `/venue-management/detail/last-activity/data/${hashedId}`,
        tableColumns: [
            { data: "DT_RowIndex", name: "DT_RowIndex" },
            { data: "user.name", name: "user.name", orderable: false },
            { data: "video_name", name: "video_name", orderable: false },
            {
                data: "duration",
                name: "duration",
                orderable: false,
                render: (data) => `${data} Minutes`,
            },
            {
                data: "start_time",
                name: "start_time",
                orderable: false,
                render: (data) => dayjs(data).format("HH:mm:ss"),
            },
            {
                data: "end_time",
                name: "end_time",
                orderable: false,
                render: (data) => dayjs(data).format("HH:mm:ss"),
            },
            {
                data: "created_at",
                name: "created_at",
                searchable: false,
                orderable: false,
                render: (data) => dayjs(data).format("YYYY-MM-DD HH:mm:ss"),
            },
            {
                data: "updated_at",
                name: "updated_at",
                searchable: false,
                orderable: false,
                render: (data) => dayjs(data).format("YYYY-MM-DD HH:mm:ss"),
            },
        ],
    });
};

generateCode = () => {
    $.ajax({
        url: `/venue-management/detail/code-access/generate/${hashedId}`,
        method: "POST",
        dataType: "json",
        success: function (res) {
            if (res.success) {
                notyf.success(
                    res.message || "Access code generated successfully"
                );
                $("#access_code").text(res.generated_code);

                const modal = document.getElementById("access_code_modal");
                if (modal) {
                    modal.showModal();
                }
            } else {
                notyf.error(res.message || "Failed to generate access code!");
            }
        },
        error: function (xhr, status, error) {
            console.error("Failed to generate access code:", error);
            notyf.error(
                "Failed to generate access code! Please try again later."
            );
        },
    });
};

$(document).on("click", "#copy_code_btn", function () {
    const code = $("#access_code").text().trim();
    navigator.clipboard.writeText(code);
    notyf.success("Code copied to clipboard!");
});

document.addEventListener("DOMContentLoaded", function () {
    fetchData(hashedId);
    lastActivityTable(hashedId);
    $("#toggle_button").on("click", function () {
        toggleState();
    });
    $("#generate_code_button").on("click", function () {
        generateCode();
    });
});
