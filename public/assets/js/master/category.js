let categoryTable, buttonActionIndex, hasAction, withData;
buttonActionIndex = 6;
hasAction = true; // Set to true if action buttons are needed
withData = []; // Set with relationship if needed
categoryTable = () => {
    initCustomDatatable({
        tableId: "category-table",
        tableDataUrl: "/master/category/data",
        tableColumns: [
            { data: "DT_RowIndex", name: "DT_RowIndex" },
            { data: "name", name: "name", orderable: false },
            {
                data: "description",
                name: "description",
                searchable: false,
                orderable: false,
            },
            {
                data: "is_active",
                name: "is_active",
                searchable: false,
                orderable: false,
                render: function (data) {

                    if (data == true) {
                        return `<span class="py-1 rounded-full text-xs font-semibold bg-lilliputian-lime/90 text-green-600 flex justify-start w-fit items-center gap-1">
                                    <i data-lucide="circle-check" class="w-4 h-4"></i>
                                    Active
                                </span>`;
                    } else {
                        return `<span class="py-1 rounded-full text-xs font-semibold bg-vivaldi-red/90 text-red-500 flex justify-start w-fit items-center gap-1">
                                    <i data-lucide="circle-x" class="w-4 h-4"></i>
                                    Active
                                </span>`;
                    }
                },
            },
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
    console.log(data);

       $('#edit-form input[name="id"]').val(data.id);
       $('#edit-form input[name="name"]').val(data.name);
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
    categoryTable();
    FormValidation.init({
        rules: {
            name: { required: true, min: 3 },
            is_active: { required: true },
        },
        messages: {
            name: {
                required: "Name cannot be empty.",
                min: "Name minimum is a 3 characters",
            },
            is_active: { required: "Is Active cannot be empty." },
        },
    });
});
