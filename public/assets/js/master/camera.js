let cameraTable, selectField, selectNvr, selectFieldEdit, selectNvrEdit ,buttonActionIndex, hasAction, withData;
buttonActionIndex = 12;
hasAction = true; // Set to true if action buttons are needed
withData = []; // Set with relationship if needed
cameraTable = () => {
    initCustomDatatable({
        tableId: "camera-table",
        tableDataUrl: "/master/camera/data",
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
            { data: "nvr.name", name: "nvr.name", orderable: false },
            { data: "channel", name: "channel", orderable: false },
           {
                data: "is_active",
                name: "is_active",
                searchable: false,
                orderable: false,
                render: function (data) {
                    console.log(data);
                    if (data == true) {
                        return `<span class="px-2 py-1 rounded-full text-xs font-semibold bg-lilliputian-lime/90 text-green-600 flex justify-start w-fit items-center gap-1">
                                    <i data-lucide="circle-check" class="w-4 h-4"></i>
                                    Active
                                </span>`;
                    } else {
                        return `<span class="px-2 py-1 rounded-full text-xs font-semibold bg-vivaldi-red/90 text-red-500 flex justify-start w-fit items-center gap-1">
                                    <i data-lucide="circle-x" class="w-4 h-4"></i>
                                    Active
                                </span>`;
                    }
                },
            }
        ],
    });
};

selectField = () => {
    new TomSelect("#select-field", {
        valueField: "id",
        labelField: "label",
        searchField: ["label"],
        preload: true,
        create: false,
        sortField: { field: "label", direction: "asc" },
        load: function (query, callback) {
            $.ajax({
                url: "/select/field",
                data: { q: query, with: "venue" },
                dataType: "json",
                success: function (res) {
                    const formatted = res.map(item => ({
                        id: item.id,
                        label: item.venue ? `${item.venue.name} - ${item.text}` : item.text,
                    }));
                    callback(formatted);
                },
                error: function () {
                    callback();
                },
            });
        },
    });

 selectFieldEdit = new TomSelect("#select-field-edit", {
        valueField: "id",
        labelField: "label",
        searchField: ["label"],
        preload: true,
        create: false,
        sortField: { field: "label", direction: "asc" },
        load: function (query, callback) {
            $.ajax({
                url: "/select/field",
                data: { q: query, with: "venue" },
                dataType: "json",
                success: function (res) {
                    const formatted = res.map(item => ({
                        id: item.id,
                        label: item.venue ? `${item.venue.name} - ${item.text}` : item.text,
                    }));
                    callback(formatted);
                },
                error: function () {
                    callback();
                },
            });
        },
    });
};

selectNvr = () => {
    new TomSelect("#select-nvr", {
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
                url: "/select/nvr",
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

 selectNvrEdit = new TomSelect("#select-nvr-edit", {
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
                url: "/select/nvr",
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
       $('#edit-form input[name="channel"]').val(data.channel);
       selectFieldEdit.setValue(data.field_id);
       selectNvrEdit.setValue(data.nvr_id);
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
    cameraTable();
    selectField();
    selectNvr();
    FormValidation.init({
        rules: {
            name: { required: true, min: 3 },
            field_id: { required: true },
            nvr_id: { required: true },
            is_active: { required: true },
        },
        messages: {
            name: {
                required: "Name cannot be empty.",
                min: "Name minimum is a 3 characters",
            },
            field_id: { required: "Field cannot be empty." },
            nvr_id: { required: "NVR cannot be empty." },
            is_active: { required: "Is Active cannot be empty." },
        },
    });
});
