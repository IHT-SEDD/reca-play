function showLoading() {
    const el = document.getElementById("loadingIndicator");
    el.classList.remove("invisible", "opacity-0");
    el.classList.add("visible", "opacity-100");
    document.body.style.pointerEvents = "none";
}

function hideLoading() {
    const el = document.getElementById("loadingIndicator");
    el.classList.remove("visible", "opacity-100");
    el.classList.add("invisible", "opacity-0");
    document.body.style.pointerEvents = "auto";
}
