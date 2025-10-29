$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
    },
});

document.addEventListener("DOMContentLoaded", function () {
    FormValidation.init({
        rules: {
            email: { required: true, min: 3 },
            password: { required: true },
        },
        messages: {
            email: {
                required: "Email cannot be empty.",
                min: "Email minimum is a 3 characters",
            },
            password: { required: "Password cannot be empty." },
        },
    });
});
