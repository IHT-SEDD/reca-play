let roleTable, buttonActionIndex;
buttonActionIndex = 5;
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


formEdit = (data) => {
       $('#edit-form input[name="id"]').val(data.id);
       $('#edit-form input[name="name"]').val(data.name);
       $('#edit-form input[name="guard_name"]').val(data.guard_name);
      $('#modal_master').get(0).showModal();
}

document.addEventListener("DOMContentLoaded", function () {
    roleTable();
});
