let fieldTable, populateData;

fieldTable = () => {
    initCustomDatatable({
        tableId: "field-table",
        tableDataUrl: "/venue-management/field-data",
        tableColumns: [
            { data: "DT_RowIndex", name: "DT_RowIndex" },
            {
                data: "code",
                name: "code",
                orderable: false,
                render: function (data, type, row) {
                    return `
                    <a href="/venue-management/detail/${row.hashed_id}" class="hover:text-hot-shot" target="_blank" rel="noopener noreferrer">${data}</a>`;
                },
            },
            { data: "name", name: "name", orderable: false },
            { data: "initial", name: "initial", orderable: false },
            { data: "category.name", name: "category.name", orderable: false },
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

populateData = () => {
    $.ajax({
        url: "/venue-management/data",
        method: "GET",
        dataType: "json",
        success: function (res) {
            $("#venue_name").text(res.venue_name);
            $("#total_recorded_videos").text(res.total_video + " videos");
            $("#total_visitor").text(res.total_visitor + " user");
        },
        error: function (xhr, status, error) {},
        complete: function () {},
    });
};

document.addEventListener("DOMContentLoaded", function () {
    fieldTable();
    populateData();
});
