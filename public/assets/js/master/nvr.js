let nvrTable, selectCamera, selectPort;

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
            { data: "camera.name", name: "camera.name", orderable: false },
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

selectCamera = () => {
    new TomSelect("#select-camera", {
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
                url: "/select/camera",
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
    selectCamera();
    selectPort();
});
