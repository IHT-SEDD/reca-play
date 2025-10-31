let venueTable,
    selectVenueType,
    buttonActionIndex,
    EditSelectVenueType,
    hasAction,
    withData;
buttonActionIndex = 9;
hasAction = true; // Set to true if action buttons are needed
withData = []; // Set with relationship if needed
venueTable = () => {
    initCustomDatatable({
        tableId: "venue-table",
        tableDataUrl: "/master/venue/data",
        tableColumns: [
            { data: "DT_RowIndex", name: "DT_RowIndex" },
            { data: "code", name: "code", orderable: false },
            {
                data: "venue_type.name",
                name: "venue_type.name",
                orderable: false,
            },
            { data: "name", name: "name", orderable: false },
            {
                data: "description",
                name: "description",
                searchable: false,
                orderable: false,
            },
            {
                data: "address",
                name: "address",
                searchable: false,
                orderable: false,
            },
            {
                data: "logo_filename",
                name: "logo_filename",
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    if (!data)
                        return '<span class="text-gray-400">No Image</span>';
                    return `<img src="${row.logo_path}" alt="${data}" width="250" height="250" class="rounded">`;
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

selectVenueType = () => {
    new TomSelect("#select-venue-type", {
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
                url: "/select/venue-type",
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

    EditSelectVenueType = new TomSelect("#edit-venue-type", {
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
                url: "/select/venue-type",
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

formEdit = (data) => {
    $('#edit-form input[name="id"]').val(data.id);
    $('#edit-form input[name="name"]').val(data.name);
    $('#edit-form textarea[name="description"]').val(data.description);
    $('#edit-form textarea[name="address"]').val(data.address);
    $("#edit-form #edit-logoLabel").text(
        data.logo_filename ?? "No file chosen"
    );
    EditSelectVenueType.addOption({
        id: data.venue_type.id,
        text: data.venue_type.name,
    });
    EditSelectVenueType.setValue(data.venue_type.id);
    $("#modal_master").get(0).showModal();
};

document.addEventListener("DOMContentLoaded", function () {
    venueTable();
    selectVenueType();
});
