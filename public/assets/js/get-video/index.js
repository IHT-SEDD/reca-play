document.addEventListener("DOMContentLoaded", function () {
    const startTimePicker = flatpickr("#start_time", {
        enableTime: true,
        noCalendar: false,
        dateFormat: "Y-m-d H:i",
        time_24hr: true,
    });

    const endTimePicker = flatpickr("#end_time", {
        enableTime: true,
        noCalendar: false,
        dateFormat: "Y-m-d H:i",
        time_24hr: true,
    });

    FormValidation.init({
        form: "#download-video-form",
        rules: {
            host: { required: true, min: 3 },
            username: { required: true, min: 3 },
            password: { required: true },
            uri: { required: true },
        },
        messages: {
            host: {
                required: "Host cannot be empty.",
                min: "Host minimum is a 3 characters",
            },
            username: {
                required: "Username cannot be empty.",
                min: "Username minimum is a 3 characters",
            },
            password: { required: "Password cannot be empty." },
            uri: { required: "URI cannot be empty." },
        },
    });

    FormValidation.init({
        form: "#search-video-form",
        rules: {
            host: { required: true, min: 3 },
            username: { required: true, min: 3 },
            password: { required: true },
            uri: { required: true },
            channel: { required: true, numeric: true },
            start_time: { required: true },
            end_time: { required: true },
        },
        messages: {
            host: {
                required: "Host cannot be empty.",
                min: "Host minimum is a 3 characters",
            },
            username: {
                required: "Username cannot be empty.",
                min: "Username minimum is a 3 characters",
            },
            password: { required: "Password cannot be empty." },
            uri: { required: "URI cannot be empty." },
            channel: {
                required: "Channel cannot be empty.",
                numeric: "Channel must be a number.",
            },
            start_time: { required: "Start time cannot be empty." },
            end_time: { required: "End time cannot be empty." },
        },
    });
});
