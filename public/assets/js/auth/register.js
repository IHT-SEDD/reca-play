$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
    },
});

document.addEventListener("DOMContentLoaded", function () {
    const input = document.querySelector("#password");
    const meter = document.querySelector("#strengthMeter");
    const label = document.querySelector("#strengthLabel");

    const labels = ["Very Weak", "Weak", "Enough", "Strong", "Very Strong"];

    if (input && meter && label) {
        input.addEventListener("input", () => {
            const val = input.value;

            if (/\s/.test(val)) {
                meter.value = 0;
                label.textContent = "The password must not contain spaces";
                label.style.color = "red";
                return;
            } else {
                label.style.color = "#555";
            }

            if (!val) {
                meter.value = 0;
                label.textContent = "There is no password yet";
                return;
            }

            const result = zxcvbn(val);
            meter.value = result.score;
            label.textContent = labels[result.score];

            if (result.feedback.warning) {
                label.textContent += ` — ${result.feedback.warning}`;
            }
            if (result.feedback.suggestions.length > 0) {
                label.textContent += ` (${result.feedback.suggestions.join(
                    ", "
                )})`;
            }
        });
    }

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
