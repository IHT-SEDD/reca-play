let getUserData, showEditModal;

const modal = document.querySelector("#editUserModal");
const editBtn = document.querySelector("#edit_btn");

// ==== Retrieve user data ==== //
getUserData = () => {
    showLoading();

    setTimeout(() => {
        $.ajax({
            url: "/my-profile/user-data",
            method: "GET",
            dataType: "json",
            success: function (res) {
                console.log("Success retrieve user data:", res);
            },
            error: function (xhr, status, error) {
                console.error("Failed to retrieve user data:", error);
            },
            complete: function () {
                hideLoading();
            },
        });
    }, 300);
};

// ==== Edit user ==== //
editUser = () => {
    $.ajax({
        url: `/my-profile/${userId}/edit`,
        method: "POST",
        success: (response) => {
            console.log(response);
            showShareModal(response.url);
        },
        error: (xhr) => {
            if (xhr.status === 401) {
                notyf.error(
                    "You are not logged in. Redirecting to the login page..."
                );
                setTimeout(() => {
                    window.location.href = "/login";
                }, 2000);
            } else {
                notyf.error("Failed to generate share link.");
            }
        },
    });
};

// ==== Show edit modal ==== //
showEditModal = () => {
    if (!modal) return;
    modal.showModal();
    requestAnimationFrame(() => modal.classList.add("show"));
};

// ==== Event Delegation ==== //
if (editBtn) {
    editBtn.addEventListener("click", () => {
        showEditModal();
    });
}

document.addEventListener("DOMContentLoaded", function () {
    getUserData();
});
