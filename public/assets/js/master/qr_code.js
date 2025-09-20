let qrCodeTable,
    selectField,
    handlingDisableSelect,
    fieldSelectInput,
    venueSelectInput,
    handlingDownloadQr;

qrCodeTable = () => {
    initCustomDatatable({
        tableId: "qr_code-table",
        tableDataUrl: "/master/qr_code/data",
        tableColumns: [
            { data: "DT_RowIndex", name: "DT_RowIndex" },
            { data: "code", name: "code", orderable: false },
            { data: "field.name", name: "field.name", orderable: false },
            { data: "venue.name", name: "venue.name", orderable: false },
            { data: "name", name: "name", orderable: false },
            {
                data: "description",
                name: "description",
                searchable: false,
                orderable: false,
            },
            { data: "type", name: "type", orderable: false },
            {
                data: "is_active",
                name: "is_active",
                orderable: false,
                render: function (data) {
                    console.log(data);
                    if (data == true) {
                        return `<span class="px-2 py-1 rounded-full text-xs font-semibold bg-lilliputian-lime/90 text-base-100 flex justify-start w-fit items-center gap-1">
                                    <i data-lucide="circle-check" class="w-4 h-4"></i>
                                    Active
                                </span>`;
                    } else {
                        return `<span class="px-2 py-1 rounded-full text-xs font-semibold bg-vivaldi-red/90 text-base-100 flex justify-start w-fit items-center gap-1">
                                    <i data-lucide="circle-x" class="w-4 h-4"></i>
                                    Active
                                </span>`;
                    }
                },
            },
            {
                data: "qr_file",
                name: "qr_file",
                searchable: false,
                orderable: false,
                render: function (data) {
                    console.log(data);
                    if (!data) {
                        return `<span class="text-carbon italic">No QR</span>`;
                    }
                    return `<button onclick="handlingDownloadQr('${
                        data ?? ""
                    }')" class="px-2 py-1 rounded-xl text-xs font-semibold bg-hot-shot/80 text-white flex justify-center items-center gap-1 hover:bg-hot-shot">
                        <i data-lucide="download" class="w-4 h-4"></i> Download
                    </button>`;
                },
            },
            {
                data: "created_at",
                name: "created_at",
                searchable: false,
                orderable: false,
                render: function (data) {
                    return dayjs(data).format("YYYY-MM-DD HH:mm:ss");
                },
            },
            {
                data: "updated_at",
                name: "updated_at",
                searchable: false,
                orderable: false,
                render: function (data) {
                    return dayjs(data).format("YYYY-MM-DD HH:mm:ss");
                },
            },
        ],
    });
};

selectField = () => {
    fieldSelectInput = new TomSelect("#select-field", {
        valueField: "id",
        labelField: "text",
        searchField: "text",
        preload: true,
        create: false,
        sortField: {
            field: "text",
            direction: "asc",
        },
        load: function (query, callback) {
            $.ajax({
                url: "/select/field",
                data: { q: query },
                dataType: "json",
                success: function (res) {
                    callback(res);
                },
                error: function () {
                    callback();
                },
            });
        },
    });
};

selectVenue = () => {
    venueSelectInput = new TomSelect("#select-venue", {
        valueField: "id",
        labelField: "text",
        searchField: "text",
        preload: true,
        create: false,
        sortField: {
            field: "text",
            direction: "asc",
        },
        load: function (query, callback) {
            $.ajax({
                url: "/select/venue",
                data: { q: query },
                dataType: "json",
                success: function (res) {
                    callback(res);
                },
                error: function () {
                    callback();
                },
            });
        },
    });
};

handlingDisableSelect = () => {
    const fieldCheckbox = document.getElementById("disable-field-select");
    const venueCheckbox = document.getElementById("disable-venue-select");

    function handleCheckboxChange(changed) {
        if (changed === "field") {
            if (fieldCheckbox.checked) {
                venueCheckbox.checked = false;
                fieldSelectInput.disable();
                venueSelectInput.enable();
            } else {
                fieldSelectInput.enable();
            }
        } else if (changed === "venue") {
            if (venueCheckbox.checked) {
                fieldCheckbox.checked = false;
                venueSelectInput.disable();
                fieldSelectInput.enable();
            } else {
                venueSelectInput.enable();
            }
        }
    }

    fieldCheckbox.addEventListener("change", () =>
        handleCheckboxChange("field")
    );
    venueCheckbox.addEventListener("change", () =>
        handleCheckboxChange("venue")
    );

    fieldCheckbox.checked = false;
    venueCheckbox.checked = true;
    fieldSelectInput.enable();
    venueSelectInput.disable();
};

handlingDownloadQr = (filename) => {
    $.ajax({
        url: `/master/qr_code/download/${filename}`,
        type: "GET",
        xhrFields: {
            responseType: "blob",
        },
        success: function (data, status, xhr) {
            const contentType = xhr.getResponseHeader("Content-Type");

            if (contentType && contentType.includes("application/json")) {
                const reader = new FileReader();
                reader.onload = function () {
                    const response = JSON.parse(reader.result);
                    notyf.error(response.message, "error");
                };
                reader.readAsText(data);
            } else {
                const blob = new Blob([data]);
                const link = document.createElement("a");
                const url = window.URL.createObjectURL(blob);

                link.href = url;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                window.URL.revokeObjectURL(url);
                link.remove();

                notyf.success("QR Code downloaded successfully!");
            }
        },
        error: function (xhr) {
            let message = "Failed to download QR Code.";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            notyf.error(message, "error");
        },
    });
};

document.addEventListener("DOMContentLoaded", function () {
    qrCodeTable();
    selectField();
    selectVenue();
    setTimeout(() => {
        handlingDisableSelect();
    }, 300);
    FormValidation.init({
        rules: {
            name: { required: true, min: 3 },
            is_active: { required: true },
        },
        messages: {
            name: {
                required: "Name cannot be empty.",
                min: "Name minimum is a 3 characters",
            },
            is_active: { required: "Is Active cannot be empty." },
        },
    });
});
