document.addEventListener("DOMContentLoaded", () => {
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
});
