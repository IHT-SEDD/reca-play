let userTable, selectRole, buttonActionIndex, hasAction, withData;

userTable = () => {
    buttonActionIndex = 7;
    hasAction = false; // Set to true if action buttons are needed
    withData = []; // Set with relationship if needed

    initCustomDatatable({
        tableId: "users-table",
        tableDataUrl: "/user-management/users-data",
        tableColumns: [
            { data: "DT_RowIndex", name: "DT_RowIndex" },
            { data: "name", name: "name" },
            { data: "username", name: "username" },
            { data: "email", name: "email" },
            { data: "google_id", name: "google_id" },
            { data: "status", name: "status" },
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

selectRole = () => {
    new TomSelect("#select-role", {
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
                url: "/select/role",
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
        onChange: function (value) {
            const needVenue = ["10", "11"];
            if (needVenue) {
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

                $("#select_venue_user").show();
                $("#select-venue").attr("required", true);

                FormValidation.addRule("venue_id", {
                    required: true,
                });
                FormValidation.addMessage("venue_id", {
                    required: "Venue cannot be empty.",
                });
            } else {
                $("#select_venue_user").hide();
                $("#select-venue").removeAttr("required");

                FormValidation.removeRule("venue_id");
            }
        },
    });

    $("#select_venue_user").hide();
};

document.addEventListener("DOMContentLoaded", function () {
    userTable();
    selectRole();
    FormValidation.init({
        rules: {
            name: { required: true, min: 3 },
            username: { required: true, min: 3 },
            email: { required: true, email: true },
            password: { required: true },
            role_id: { required: true },
        },
        messages: {
            name: {
                required: "Name cannot be empty.",
                min: "Name minimum is a 3 characters",
            },
            username: {
                required: "Username cannot be empty.",
                min: "Username minimum is a 3 characters",
            },
            email: {
                required: "Email cannot be empty.",
                email: "Email must be valid",
            },
            password: { required: "Password cannot be empty." },
            role_id: { required: "Role cannot be empty." },
        },
    });
});
