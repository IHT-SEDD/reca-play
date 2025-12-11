let getUserData, showEditModal, profileUploader;

const modal = document.querySelector("#editUserModal");
const editBtn = document.querySelector("#edit_btn");
const formEdit = document.querySelector("#edit-form");

function formatJoined(dateString) {
    const date = new Date(dateString);

    const month = date.toLocaleString("en-US", { month: "short" });
    const year = date.getFullYear();

    return `Joined ${month} ${year}`;
}

// ==== Retrieve user data ==== //
getUserData = () => {
    showLoading();

    setTimeout(() => {
        $.ajax({
            url: "/my-profile/user-data",
            method: "GET",
            dataType: "json",
            success: function (res) {
                $(".account-email").text(res.data.email);
                $("#name").text(res.data.name);
                $("#username").text(res.data.username);
                $("#join_time").text(formatJoined(res.data.created_at));
                $('#edit-form input[name="id"]').val(res.data.id);
                $('#edit-form input[name="name"]').val(res.data.name);
                $('#edit-form input[name="username"]').val(res.data.username);
                $('#edit-form input[name="phone_number"]').val(
                    res.data.phone_number
                );
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
    let formData = new FormData(formEdit);
    $.ajax({
        url: `/my-profile/edit`,
        method: "PATCH",
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

// ==== Upload pProfile Picture === //
profileUploader = (userId, initial, savedPhoto) => {
    return {
        userId,
        initial,
        savedPhoto,
        preview: null,

        browse() {
            this.$refs.fileInput.click();
        },

        fileChosen(e) {
            const file = e.target.files[0];
            if (file) this.upload(file);
        },

        dropFile(e) {
            const file = e.dataTransfer.files[0];
            if (file) this.upload(file);
        },

        async upload(file) {
            this.preview = URL.createObjectURL(file);

            let form = new FormData();
            form.append("photo", file);
            const token = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");

            const response = await fetch(
                `/my-profile/${this.userId}/upload-photo`,
                {
                    method: "POST",
                    credentials: "same-origin",
                    headers: {
                        "X-CSRF-TOKEN": token,
                    },
                    body: form,
                }
            );

            let result = await response.json();
            console.log(result);

            if (result.success) {
                this.savedPhoto = result.url;
                notyf.success(result.message);
            } else {
                notyf.error(result.message);
            }
        },

        remove() {
            this.preview = null;
            this.savedPhoto = null;
            this.$refs.fileInput.value = null;
        },
    };
};

document.addEventListener("DOMContentLoaded", function () {
    getUserData();
    FormValidation.init({
        rules: {
            name: { required: true, min: 3 },
            username: {
                required: true,
                min: 3,
            },
        },
        messages: {
            name: {
                required: "Name cannot be empty.",
                min: "Name minimum is a 3 characters",
            },
            username: {
                required: "username cannot be empty.",
                min: "username minimum is a 3 characters",
            },
        },
    });
});
