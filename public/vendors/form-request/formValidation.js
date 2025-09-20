const FormValidation = (() => {
    let _rules = {};
    let _messages = {};
    let _formSelector = ".ajax-form";

    // ========== Helper: show error for a field ==========
    function _showError($field) {
        const fieldName = $field.attr("name");
        const $errorContainer = $(`#input-${fieldName}-error`);

        if ($errorContainer.length && _messages[fieldName]) {
            const fieldRules = _rules[fieldName] || {};
            for (let rule in fieldRules) {
                if (!_validateRule($field, rule, fieldRules[rule])) {
                    const message =
                        (_messages[fieldName] && _messages[fieldName][rule]) ||
                        "Invalid value.";
                    $errorContainer.removeClass("hidden");
                    $errorContainer.find("p").text(message);
                    return false;
                }
            }
            _clearError($field);
        }
        return true;
    }

    // ========== Helper: clear error for a field ==========
    function _clearError($field) {
        const fieldName = $field.attr("name");
        const $errorContainer = $(`#input-${fieldName}-error`);
        if ($errorContainer.length) {
            $errorContainer.addClass("hidden");
            $errorContainer.find("p").text("");
        }
    }

    // ========== Validate single rule ==========
    function _validateRule($field, rule, ruleValue) {
        let value;

        if (
            $field.hasClass("toggle") ||
            $field.attr("type") === "checkbox" ||
            $field.attr("type") === "radio"
        ) {
            value = $field.prop("checked") ? "1" : "0";
        } else if ($field[0].tomselect) {
            value = $field[0].tomselect.getValue();
        } else {
            value = $field.val();
        }

        switch (rule) {
            case "required":
                if (
                    $field.hasClass("toggle") ||
                    $field.attr("type") === "checkbox" ||
                    $field.attr("type") === "radio"
                ) {
                    return $field.is(":checked");
                }
                return value.trim() !== "";
            case "min":
                return value.length >= ruleValue;
            case "max":
                return value.length <= ruleValue;
            case "email":
                if (!value) return true;
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
            case "boolean":
                return ["true", "false", "0", "1"].includes(String(value));
            case "regex":
                if (!value) return true;
                return ruleValue.test(value);
            case "numeric":
                if (!value) return true;
                return /^\d+$/.test(value);
            default:
                return true;
        }
    }

    // ========== Validate all fields in a form ==========
    function validateForm($form) {
        let isValid = true;
        $form.find("input, select, textarea").each(function () {
            const $field = $(this);
            if (_rules[$field.attr("name")]) {
                const fieldValid = _showError($field);
                if (!fieldValid) isValid = false;
            }
        });
        return isValid;
    }

    // ========== Bind event for real-time validation ==========
    function _bindEvents($form) {
        $form.find("input, select, textarea").each(function () {
            const $field = $(this);
            const fieldName = $field.attr("name");

            if (_rules[fieldName]) {
                const eventType =
                    $field.hasClass("toggle") ||
                    $field.attr("type") === "checkbox" ||
                    $field.attr("type") === "radio"
                        ? "change"
                        : "input";

                $field.on(eventType, function () {
                    _showError($field);
                });

                // Special case for TomSelect
                if ($field[0].tomselect) {
                    $field[0].tomselect.on("change", function () {
                        _showError($field);
                    });
                }
            }
        });
    }

    // ========== Init function ==========
    function init({
        formSelector = ".ajax-form",
        rules = {},
        messages = {},
    } = {}) {
        _rules = rules;
        _messages = messages;
        _formSelector = formSelector;

        $(_formSelector).each(function () {
            const $form = $(this);
            _bindEvents($form);

            // Intercept submit for frontend validation
            $form.on("submit", function (e) {
                if (!validateForm($form)) {
                    e.preventDefault();
                    if (typeof notyf !== "undefined") {
                        notyf.error(
                            "Please correct the errors before submitting."
                        );
                    }
                    return false;
                }
            });
        });
    }

    return {
        init,
        validateForm,
    };
})();
