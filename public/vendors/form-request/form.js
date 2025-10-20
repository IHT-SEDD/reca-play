let formRequestInit;

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
                        Accept: "application/json",
                    },
                    success: function (result) {
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
                    "X-CSRF-TOKEN": $form.find('input[name="_token"]').val(),
                },
                success: function (result) {
                    if (result.status === "success") {
                        notyf.success(result.message);

                          resetForm($form);

                            // Tutup modal edit (opsional)
                            window.dispatchEvent(
                                new CustomEvent("close-modal", {
                                    detail: "edit-master-modal",
                                })
                            );

                            // Refresh DataTable
                            if (
                                targetTable &&
                                $.fn.DataTable.isDataTable(targetTable)
                            ) {
                                $(targetTable)
                                    .DataTable()
                                    .ajax.reload(null, false);
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

document.addEventListener("DOMContentLoaded", () => {
    formRequestInit().formAdd();
    formRequestInit().formEdit();
});
