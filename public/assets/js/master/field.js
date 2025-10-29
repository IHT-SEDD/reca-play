let fieldTable, selectVenue, selectCategory, editSelectVenue, editSelectCategory ,buttonActionIndex;
buttonActionIndex = 10;
fieldTable = () => {
    initCustomDatatable({
        tableId: "field-table",
        tableDataUrl: "/master/field/data",
        tableColumns: [
            { data: "DT_RowIndex", name: "DT_RowIndex" },
            { data: "code", name: "code", orderable: false },
            { data: "name", name: "name", orderable: false },
            { data: "initial", name: "initial", orderable: false },
            { data: "category.name", name: "category.name", orderable: false },
            { data: "venue.name", name: "venue.name", orderable: false },
            {
                data: "description",
                name: "description",
                searchable: false,
                orderable: false,
            },
             {
                data: "pict_path",
                name: "pict_path",
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    if (!data) return '<span class="text-gray-400">No Image</span>';
                    return `<img src="/${data}" alt="${row.pict_filename}" width="250" height="250" class="rounded">`;
                }
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
        withData: ["category", "venue"],
    });
};

selectCategory = () => {
    new TomSelect("#select-category", {
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
                url: "/select/category",
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

    editSelectCategory  =  new TomSelect("#edit-select-category", {
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
                url: "/select/category",
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
    new TomSelect("#select-venue", {
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

   editSelectVenue =  new TomSelect("#edit-select-venue", {
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

formEdit = (data) => {
    console.log(data);

       $('#edit-form input[name="id"]').val(data.id);
       $('#edit-form input[name="name"]').val(data.name);
       $('#edit-form input[name="initial"]').val(data.initial);
       $('#edit-form textarea[name="description"]').val(data.description);
       $('#edit-form #edit-pictLabel').text(data.pict_filename ?? 'No file chosen');
    //    EditSelectVenueType.addOption({
    //         id: data.venue_type.id,
    //         text: data.venue_type.name,
    //     });
        editSelectVenue.setValue(data.venue_id);
        editSelectCategory.setValue(data.category_id);
      $('#modal_master').get(0).showModal();
}

document.addEventListener("DOMContentLoaded", function () {
    fieldTable();
    selectCategory();
    selectVenue();
    FormValidation.init({
        rules: {
            name: { required: true, min: 3 },
            category_id: { required: true },
            venue_id: { required: true },
        },
        messages: {
            name: {
                required: "Name cannot be empty.",
                min: "Name minimum is a 3 characters",
            },
            category_id: { required: "Category cannot be empty." },
            venue_id: { required: "Venue cannot be empty." },
        },
    });
});
