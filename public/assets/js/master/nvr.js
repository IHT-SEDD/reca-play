let nvrTable, selectField, selectPort;

nvrTable = () => {
    initCustomDatatable({
        tableId: "nvr-table",
        tableDataUrl: "/master/nvr/data",
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
            {
                data: "port.port_number",
                name: "port.port_number",
                searchable: false,
                orderable: false,
            },
            {
                data: "auth_type",
                name: "auth_type",
                searchable: false,
                orderable: false,
            },
            {
                data: "username",
                name: "username",
                searchable: false,
                orderable: false,
            },
            {
                data: "password",
                name: "password",
                searchable: false,
                orderable: false,
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

selectPort = () => {
    new TomSelect("#select-port", {
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
                url: "/select/port",
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
    nvrTable();
    selectField();
    selectPort();
    FormValidation.init({
        rules: {
            name: { required: true, min: 3 },
            ip_address: { required: true, min: 4 },
            auth_type: { required: true },
            username: { required: true },
            password: { required: true },
            is_active: { required: true },
        },
        messages: {
            name: {
                required: "Name cannot be empty.",
                min: "Name minimum is a 3 characters",
            },
            ip_address: {
                required: "IP Address cannot be empty.",
                min: "IP Address minimum is a 4 characters",
            },
            auth_type: { required: "Auth Type cannot be empty." },
            username: { required: "Username cannot be empty." },
            password: { required: "Password cannot be empty." },
            is_active: { required: "Is Active cannot be empty." },
        },
    });
});
