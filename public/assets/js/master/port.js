let portTable;

portTable = () => {
    initCustomDatatable({
        tableId: "port-table",
        tableDataUrl: "/master/port/data",
        tableColumns: [
            { data: "DT_RowIndex", name: "DT_RowIndex" },
            { data: "name", name: "name", orderable: false },
            { data: "port_number", name: "port_number", orderable: false },
            {
                data: "is_active",
                name: "is_active",
                searchable: false,
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

document.addEventListener("DOMContentLoaded", function () {
    portTable();
});
