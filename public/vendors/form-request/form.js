let formRequestInit, closeAnyModal;

formRequestInit = () => {
    // ============================
    // Reset helpers
    // ============================
    function resetStandardInputs($form) {
        $form[0].reset();
    }

    function clearFormValidation($form) {
        $form.find("[id^='input-'][id$='-error']").each(function () {
            $(this).addClass("hidden");
            $(this).find("p").text("");
        });
    }

    function showValidationErrors($form, errors) {
        console.log(errors);

        clearFormValidation($form);
        for (let field in errors) {
            let $errorContainer = $form.find(`#input-${field}-error`);
            if ($errorContainer.length) {
                $errorContainer.removeClass("hidden");
                $errorContainer.find("p").text(errors[field][0]);
            }
        }
    }

    function resetTomSelect($form) {
        $form.find("select").each(function () {
            if (this.tomselect) {
                this.tomselect.clear();
            }
        });
    }

    function resetCheckboxesAndRadios($form) {
        $form
            .find('input[type="checkbox"], input[type="radio"]')
            .each(function () {
                const defaultChecked = $(this).prop("defaultChecked");
                $(this).prop("checked", defaultChecked);
            });
    }

    function resetToggles($form) {
        $form.find(".toggle").each(function () {
            const defaultChecked = $(this).data("default") || false;
            $(this).prop("checked", defaultChecked);
        });
    }

    function resetForm($form) {
        resetStandardInputs($form);
        clearFormValidation($form);
        resetTomSelect($form);
        resetCheckboxesAndRadios($form);
        resetToggles($form);
    }

    // ============================
    // Custom session code message handler
    // ============================
    function handleSessionCodeError(message) {
        if (!message) return false;

        const lowerMsg = message.toLowerCase();

        if (lowerMsg.includes("session code not found")) {
            notyf.error(
                "Access code not found! Please go to cashier and ask for the access code."
            );
            return true;
        }

        if (lowerMsg.includes("session code has expired")) {
            notyf.error(
                "This access code has expired. Please ask cashier for a new code."
            );
            return true;
        }

        if (lowerMsg.includes("session code is already in use")) {
            notyf.error(
                "This access code is already being used by another user."
            );
            return true;
        }

        return false;
    }

    // ============================
    // Form submit handler
    // ============================
    function formAdd() {
        $("form.ajax-form").each(function () {
            $(this).on("submit", function (e) {
                e.preventDefault();

                let $form = $(this);
                let actionUrl = $form.attr("action");
                let formData = new FormData(this);
                let targetTable = $form.data("datatable");
                let resultWrapper = $form.data("result-wrapper");

                showLoading();

                setTimeout(() => {
                    $.ajax({
                        url: actionUrl,
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            "X-CSRF-TOKEN": $form
                                .find('input[name="_token"]')
                                .val(),
                            Accept: "*/*",
                        },
                        success: function (result) {
                            hideLoading();

                            setTimeout(() => {
                                const contentType =
                                    xhr.getResponseHeader("Content-Type");

                                if (
                                    contentType &&
                                    contentType.includes("application/xml")
                                ) {
                                    notyf.success("Success");
                                    if (resultWrapper) {
                                        $(resultWrapper).text(result);
                                    }
                                    resetForm($form);
                                }

                                if (typeof result === "string") {
                                    notyf.success("Success");
                                    if (resultWrapper) {
                                        $(resultWrapper).text(result);
                                    }
                                    resetForm($form);
                                }

                                if (result.status === "success") {
                                    notyf.success(result.message);
                                    resetForm($form);

                                    if (
                                        targetTable &&
                                        $.fn.DataTable.isDataTable(targetTable)
                                    ) {
                                        $(targetTable)
                                            .DataTable()
                                            .ajax.reload(null, false);
                                    }
                                    if (result.redirect) {
                                        window.location.href = result.redirect;
                                    }
                                } else if (result.status === "error") {
                                    if (
                                        !handleSessionCodeError(result.message)
                                    ) {
                                        notyf.error(
                                            result.message ||
                                                "An error occurred. Please try again."
                                        );
                                    }
                                } else {
                                    console.error(result.message);
                                    notyf.error(
                                        "An error occurred. Please try again."
                                    );
                                }
                            }, 150);
                        },
                        error: function (xhr) {
                            hideLoading();

                            setTimeout(() => {
                                const contentType =
                                    xhr.getResponseHeader("Content-Type");

                                if (
                                    contentType &&
                                    contentType.includes("application/xml")
                                ) {
                                    if (resultWrapper) {
                                        $(resultWrapper).text(xhr.responseText);
                                    }
                                    resetForm($form);
                                }

                                if (typeof xhr.responseText === "string") {
                                    if (resultWrapper) {
                                        $(resultWrapper).text(xhr.responseText);
                                    }
                                    resetForm($form);
                                }

                                if (xhr.status === 422) {
                                    showValidationErrors(
                                        $form,
                                        xhr.responseJSON.errors
                                    );
                                    notyf.error(
                                        "Please check the form for errors."
                                    );
                                } else {
                                    const response = xhr.responseJSON;
                                    if (
                                        !handleSessionCodeError(
                                            response?.message
                                        )
                                    ) {
                                        notyf.error(
                                            response?.message ||
                                                "An error occurred. Please try again."
                                        );
                                    }
                                    console.error(xhr);
                                }
                            }, 150);
                        },
                    });
                }, 200);
            });
        });
    }

    function formEdit() {
        $("form.ajax-edit-form").each(function () {
            $(this).on("submit", function (e) {
                e.preventDefault();

                let $form = $(this);
                let actionUrl = $form.attr("action");
                let formData = new FormData(this);
                let targetTable = $form.data("datatable");

                $.ajax({
                    url: actionUrl,
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "X-CSRF-TOKEN": $form
                            .find('input[name="_token"]')
                            .val(),
                    },
                    success: function (result) {
                        if (result.status === "success") {
                            notyf.success(result.message);

                            resetForm($form);

                            // $("#modal_master").get(0).close();
                            closeAnyModal("#modal_master", "#editUserModal");

                            // Refresh DataTable
                            if (
                                targetTable &&
                                $.fn.DataTable.isDataTable(targetTable)
                            ) {
                                $(targetTable)
                                    .DataTable()
                                    .ajax.reload(null, false);
                            }

                            if (typeof getUserData === "function") {
                                getUserData();
                            }
                        } else if (result.status === "error") {
                            if (!handleSessionCodeError(result.message)) {
                                notyf.error(
                                    result.message ||
                                        "An error occurred. Please try again."
                                );
                            }
                        } else {
                            console.error(result.message);
                            notyf.error("An error occurred. Please try again.");
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            showValidationErrors(
                                $form,
                                xhr.responseJSON.errors
                            );
                            notyf.error("Please check the form for errors.");
                        } else {
                            const response = xhr.responseJSON;
                            if (!handleSessionCodeError(response?.message)) {
                                notyf.error(
                                    response?.message ||
                                        "An error occurred. Please try again."
                                );
                            }
                            console.error(xhr);
                        }
                    },
                });
            });
        });
    }

    return { formAdd, formEdit };
};

 closeAnyModal = (...ids) => {
    for (const id of ids) {
        const modal = document.querySelector(id);
        if (modal) {
            modal.close();
            break;
        }
    }
}

document.addEventListener("DOMContentLoaded", () => {
    formRequestInit().formAdd();
    formRequestInit().formEdit();
});
