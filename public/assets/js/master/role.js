let roleTable;

roleTable = () => {
    initCustomDatatable({
        tableId: "role-table",
        tableDataUrl: "/master/role/data",
        tableColumns: [
            { data: "DT_RowIndex", name: "DT_RowIndex" },
            { data: "name", name: "name" },
            { data: "guard_name", name: "guard_name" },
            {
                data: "created_at",
                name: "created_at",
                render: function (data) {
                    return dayjs(data).format("YYYY-MM-DD HH:mm:ss");
                },
            },
            {
                data: "updated_at",
                name: "updated_at",
                render: function (data) {
                    return dayjs(data).format("YYYY-MM-DD HH:mm:ss");
                },
            },
        ],
    });
};

document.addEventListener("DOMContentLoaded", function () {
    roleTable();
});
