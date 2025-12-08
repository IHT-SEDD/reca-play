let getUserData, showEditModal, profileUploader;

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
                $('.account-email').text(res.data.email);
                $('#name').text(res.data.name);
                $('#username').text(res.data.username);
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
             const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const response = await fetch(`/my-profile/${this.userId}/upload-photo`, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "X-CSRF-TOKEN": token
                },
                body: form
            });

            let result = await response.json();
            console.log(result);

            if (result.success) {
                this.savedPhoto = result.url;
                notyf.success(result.message);
            }else{
                 notyf.error(result.message)
            }
        },

        remove() {
            this.preview = null;
            this.savedPhoto = null;
            this.$refs.fileInput.value = null;
        }
    }
}

document.addEventListener("DOMContentLoaded", function () {
    getUserData();
});
