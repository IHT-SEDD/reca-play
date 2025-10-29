let nvrTable, selectVenue, selectVenueEdit , selectPortEdit, hasAction, withData;
buttonActionIndex = 15;
hasAction = true; // Set to true if action buttons are needed
withData = []; // Set with relationship if needed
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
            { data: "venue.name", name: "venue.name", orderable: false },
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

 selectVenueEdit = new TomSelect("#select-venue-edit", {
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

  selectPortEdit =  new TomSelect("#select-port-edit", {
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

formEdit = (data) => {
       $('#edit-form input[name="id"]').val(data.id);
       $('#edit-form input[name="name"]').val(data.name);
       $('#edit-form input[name="initial"]').val(data.initial);
       $('#edit-form input[name="brand"]').val(data.brand);
       $('#edit-form input[name="type"]').val(data.type);
       $('#edit-form input[name="ip_address"]').val(data.ip_address);
       $('#edit-form input[name="auth_type"]').val(data.auth_type);
       $('#edit-form input[name="username"]').val(data.username);
       $('#edit-form input[name="password"]').val(data.password);
       selectVenueEdit.setValue(data.venue_id);
       selectPortEdit.setValue(data.port_id);
        $('#edit-form textarea[name="description"]').val(data.description);
           // toggle-input component renders a hidden input (value=0) and a checkbox (value=1)
       // set the checkbox checked state according to data.is_active
       const isActive = data.is_active == true;
       const $checkbox = $('#edit-form input[type="checkbox"][name="is_active"]');
       const $hidden = $('#edit-form input[type="hidden"][name="is_active"]');
       if ($checkbox.length) {
           $checkbox.prop('checked', isActive);
           // trigger change so any UI bound styles update
           $checkbox.trigger('change');
       }
       // ensure hidden input stays correct (0 when unchecked, 1 when checked) to keep consistency
       if ($hidden.length) {
           $hidden.val(isActive ? '0' : '0');
       }
      $('#modal_master').get(0).showModal();
}

document.addEventListener("DOMContentLoaded", function () {
    nvrTable();
    selectVenue();
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
