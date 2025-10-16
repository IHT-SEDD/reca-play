document.addEventListener("DOMContentLoaded", function () {
    FormValidation.init({
        rules: {
            name: { required: true, min: 3 },
            username: { required: true, min: 3 },
            email: { required: true, min: 3 },
            password: { required: true },
            password_confirmation: { required: true },
        },
        messages: {
            name: {
                required: "Name cannot be empty.",
                min: "Name minimum is a 3 characters",
            },
            username: {
                required: "Username cannot be empty.",
                min: "Username minimum is a 3 characters",
            },
            email: {
                required: "Email cannot be empty.",
                min: "Email minimum is a 3 characters",
            },
            password: { required: "Password cannot be empty." },
            password_confirmation: {
                required: "Password confirmation cannot be empty.",
            },
        },
    });
});
