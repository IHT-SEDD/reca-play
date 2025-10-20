let venueTypeTable, buttonActionIndex;
buttonActionIndex = 6;
venueTypeTable = () => {
    initCustomDatatable({
        tableId: "venue-type-table",
        tableDataUrl: "/master/venue-type/data",
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
                    console.log(data);
                    if (data == true) {
                        return `<span class="px-2 py-1 rounded-full text-xs font-semibold bg-lilliputian-lime/90 text-green-600 flex justify-start w-fit items-center gap-1">
                                    <i data-lucide="circle-check" class="w-4 h-4"></i>
                                    Active
                                </span>`;
                    } else {
                        return `<span class="px-2 py-1 rounded-full text-xs font-semibold bg-vivaldi-red/90 text-red-600 flex justify-start w-fit items-center gap-1">
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

       $('#edit-form input[name="id"]').val(data.id);
       $('#edit-form input[name="name"]').val(data.name);
       $('#edit-form input[name="description"]').val(data.description);
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
       window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-master-modal' }));
}

document.addEventListener("DOMContentLoaded", function () {
    venueTypeTable();
});
