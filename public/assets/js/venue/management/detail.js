let fetchData,
    populateData,
    toggleState,
    currentState,
    lastActivityTable,
    accessCodeTable,
    generateCode,
    handlerFormAddAccessCode,
    startRecording;

// ======== Initialize component ::begin ========
const pathParts = window.location.pathname.split("/");
const hashedId = pathParts[pathParts.length - 1];

const typeInput = $("#type");
const recordInputWrapper = $("#record_inputs");
const streamInputWrapper = $("#stream_inputs");
// ======== Initialize component ::end ========

// ======== Ajax CSRF token setup ::begin ========
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});
// ======== Ajax CSRF token setup ::end ========

// ======== Fetch data detail field ========
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

// ======== Populate fetched data to frontend ========
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

// ======== Toggle status field ========
toggleState = () => {
    $.ajax({
        url: `/venue-management/detail/status/update/${hashedId}`,
        method: "POST",
        dataType: "json",
        success: function (res) {
            hideLoading();
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
            hideLoading();
            console.error("Failed to toggle state:", error);
            notyf.error("Failed to toggle state! Please try again later.");
        },
    });
};

// ======== Last activity datatable ========
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

// ======== Access code datatable ========
accessCodeTable = (hashedId) => {
    initCustomDatatable({
        tableId: "access-code-table",
        tableDataUrl: `/venue-management/detail/access-code/data/${hashedId}`,
        tableColumns: [
            { data: "DT_RowIndex", name: "DT_RowIndex" },
            {
                data: "user.name",
                name: "user.name",
                orderable: false,
                render: (data, type, row) => {
                    return data ? data : "-";
                },
            },
            {
                data: "qr_code.code",
                name: "qr_code.code",
                orderable: false,
                render: (data, type, row) => {
                    return data ? data : "-";
                },
            },
            { data: "venue.name", name: "venue.name", orderable: false },
            { data: "field.name", name: "field.name", orderable: false },
            { data: "type", name: "type", orderable: false },
            { data: "status", name: "status", orderable: false },
            {
                data: "generated_code",
                name: "generated_code",
                orderable: false,
            },
            {
                data: "start_time",
                name: "start_time",
                orderable: false,
                render: (data) => dayjs(data).format("YYYY-MM-DD HH:mm:ss"),
            },
            {
                data: "end_time",
                name: "end_time",
                orderable: false,
                render: (data) => dayjs(data).format("YYYY-MM-DD HH:mm:ss"),
            },
            {
                data: "duration",
                name: "duration",
                orderable: false,
                render: function (data) {
                    return data + " Min";
                },
            },
            {
                data: "expired_at",
                name: "expired_at",
                searchable: false,
                orderable: false,
                render: (data) => dayjs(data).format("YYYY-MM-DD HH:mm:ss"),
            },
            {
                data: "generated_by.name",
                name: "generated_by.name",
                orderable: false,
            },
            {
                data: "created_at",
                name: "created_at",
                searchable: false,
                orderable: false,
                render: (data) => dayjs(data).format("YYYY-MM-DD HH:mm:ss"),
            },
            {
                data: "id",
                name: "id",
                searchable: false,
                orderable: false,
                render: function (data) {
                    return `
                        <div class="flex flex-col gap-2 w-full items-stretch">
                            <button
                                data-id="${data}" 
                                class="start_record_btn rounded-lg px-3 py-2 text-xs text-white font-medium bg-temple-orange hover:bg-hot-shot w-full text-center">
                                Record
                            </button>
                            <button
                                data-id="${data}" 
                                class="stop_record_btn rounded-lg px-3 py-2 text-xs text-white font-medium bg-candy-heart hover:bg-vivaldi-red w-full text-center">
                                Stop
                            </button>
                        </div>
                    `;
                },
            },
        ],
    });
};

// ======== Form add access code handler ========
handlerFormAddAccessCode = () => {
    $(document).on("change", "input[name='type']", function () {
        const selectedType = $(this).val();
        if (selectedType === "record") {
            $("#name_label").text("Video Name");
        } else if (selectedType === "stream") {
            $("#name_label").text("Stream Name");
        }
    });

    const defaultType = $("input[name='type']:checked").val();
    if (defaultType === "record") {
        $("#name_label").text("Video Name");
    } else if (defaultType === "stream") {
        $("#name_label").text("Stream Name");
    }
};

// ======== Start recording function ========
startRecording = (hashedId) => {
    $(document).on("click", ".start_record_btn", function () {
        const sessionCodeId = $(this).data("id");
        console.log("Start record button clicked, ID:", sessionCodeId);

        showLoading();

        setTimeout(() => {
            $.ajax({
                url: `/venue-management/detail/handle/start-record/${hashedId}`,
                method: "POST",
                data: { sessionCodeId: sessionCodeId },
                dataType: "json",
                success: function (res) {
                    hideLoading();
                    notyf.success(res.message);
                },
                error: function (xhr, status, error) {
                    hideLoading();
                    notyf.error("Failed to start recording. Please try again.");
                },
            });
        }, 300);
    });
};

document.addEventListener("DOMContentLoaded", function () {
    fetchData(hashedId);
    lastActivityTable(hashedId);
    accessCodeTable(hashedId);

    $("#toggle_button").on("click", function () {
        showLoading();

        setTimeout(() => {
            toggleState();
        }, 300);
    });

    const startTimePicker = flatpickr(".timepicker, #start_time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        minTime: dayjs().format("HH:mm"),
        onChange: function (selectedDates) {
            if (!selectedDates[0]) return;
            const minEndTime = dayjs(selectedDates[0])
                .add(5, "minute")
                .format("HH:mm");

            if (endTimePicker) {
                endTimePicker.set("minTime", minEndTime);

                const currentEnd = endTimePicker.selectedDates[0];
                if (
                    currentEnd &&
                    dayjs(currentEnd).isBefore(
                        dayjs(selectedDates[0]).add(5, "minute")
                    )
                ) {
                    endTimePicker.clear();
                }
            }
        },
    });

    const endTimePicker = flatpickr("#end_time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        minTime: dayjs().add(5, "minute").format("HH:mm"),
    });

    FormValidation.init({
        rules: {
            name: { required: true, min: 3 },
            type: { required: true },
            start_time: { required: true },
            end_time: { required: true },
        },
        messages: {
            name: {
                required: "Name cannot be empty.",
                min: "Name must be at least 3 characters.",
            },
            type: {
                required: "Type is required.",
            },
            start_time: {
                required: "Start time is required.",
            },
            end_time: {
                required: "End time is required.",
            },
        },
    });

    handlerFormAddAccessCode();
    startRecording(hashedId);
});
