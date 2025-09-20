let cameraTable, selectField, selectNvr;

cameraTable = () => {
    initCustomDatatable({
        tableId: "camera-table",
        tableDataUrl: "/master/camera/data",
        tableColumns: [
            { data: "DT_RowIndex", name: "DT_RowIndex" },
            { data: "code", name: "code", orderable: false },
            { data: "brand", name: "brand", orderable: false },
            { data: "type", name: "type", orderable: false },
            { data: "name", name: "name", orderable: false },
            { data: "initial", name: "initial", orderable: false },
            {
                data: "description",
                name: "description",
                searchable: false,
                orderable: false,
            },
            { data: "ip_address", name: "ip_address", orderable: false },
            { data: "field.name", name: "field.name", orderable: false },
            { data: "nvr.name", name: "nvr.name", orderable: false },
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
    new TomSelect("#select-field", {
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

selectNvr = () => {
    new TomSelect("#select-nvr", {
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
                url: "/select/nvr",
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

document.addEventListener("DOMContentLoaded", function () {
    cameraTable();
    selectField();
    selectNvr();
    FormValidation.init({
        rules: {
            name: { required: true, min: 3 },
            field_id: { required: true },
            nvr_id: { required: true },
            is_active: { required: true },
        },
        messages: {
            name: {
                required: "Name cannot be empty.",
                min: "Name minimum is a 3 characters",
            },
            field_id: { required: "Field cannot be empty." },
            nvr_id: { required: "NVR cannot be empty." },
            is_active: { required: "Is Active cannot be empty." },
        },
    });
});
