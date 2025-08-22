document.addEventListener("DOMContentLoaded", function () {
    initCustomDatatable({
        tableId: "users-table",
        tableDataUrl: "/user-management/users-data",
        tableColumns: [
            { data: "DT_RowIndex", name: "DT_RowIndex" },
            { data: "name", name: "name" },
            { data: "username", name: "username" },
            { data: "email", name: "email" },
            { data: "email", name: "email" },
            { data: "email", name: "email" },
            { data: "status", name: "status" },
            { data: "created_at", name: "created_at" },
            { data: "updated_at", name: "updated_at" },
        ],
    });
});
