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
                            notyf.error("An error occurred. Please try again.");
                            console.error(xhr);
                        }
                    },
                });
            });
        });
    }

    return { formAdd };
};

document.addEventListener("DOMContentLoaded", () => {
    formRequestInit().formAdd();
});
