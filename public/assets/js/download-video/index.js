document.addEventListener("DOMContentLoaded", function () {
    FormValidation.init({
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
});
