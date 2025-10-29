let getDataDetailVenue, populateDataDetail, populateFieldList, searchTimeout;

const pathParts = window.location.pathname.split("/");
const hashedId = pathParts[pathParts.length - 1];

let page = 1;
let perPage = 20;

$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

getDataDetailVenue = () => {
    $.ajax({
        url: `/venue/detail/data/${hashedId}`,
        method: "GET",
        dataType: "json",
        success: function (res) {
            console.log(res);
            populateDataDetail(res);
        },
        error: function (xhr, status, error) {
            console.error("AJAX error:", error);
        },
    });
};

populateDataDetail = (res) => {
    const venue = res.detailVenue;
    $("#venue_name").text(venue.name ?? "Venue name not found!");
    $("#venue_address").text(venue.address ?? "Venue address not found!");
    $("#venue_type").text(
        venue.venue_type.name + " Venue" ?? "Venue type not found!"
    );
    $("#total_court").text(res.total_court ?? 0);
};

populateFieldList = (search = "", reset = true) => {
    if (reset) {
        page = 1;
        $("#container_field_list").empty();
    }

    $("#field_loader").removeClass("hidden").addClass("flex");

    $.ajax({
        url: `/venue/detail/field/${hashedId}`,
        method: "GET",
        data: { search: search, page: page, per_page: perPage },
        dataType: "json",
        success: function (res) {
            let fields = res.data ?? res;
            let container = $("#container_field_list");

            if (fields.length === 0 && page === 1) {
                container.html(
                    `<p class="text-adhesion dark:text-white-owl w-full md:col-span-6 col-span-1 text-center text-lg font-semibold">No fields found.</p>`
                );
                $("#seemore_btn").addClass("hidden");
                return;
            }

            fields.forEach((field) => {
                let card = `
                    <div class="bg-white dark:bg-christmas-silver dark:border-transparent border border-base-200 shadow-sm rounded-xl py-3 px-4 h-fit w-full">
                        <div class="flex justify-start gap-4 items-center w-fit">
                            <!-- Venue Logo -->
                            <div class="flex items-center justify-center rounded-full p-2">
                                ${
                                    field.pict_filename
                                        ? `<img src="/storage/venue_logos/${field.pict_filename}" alt="${field.name}" class="w-10 h-10 rounded-full object-cover">`
                                        : `<i data-lucide="images" class="w-6 h-6 text-hot-shot"></i>`
                                }
                            </div>
                            <!-- Venue Desc -->
                            <div class="flex flex-col justify-center items-start">
                                <p class="text-sm font-medium text-after-midnight">${
                                    field.name
                                }</p>
                                <p class="text-sm text-carbon">${
                                    field.category.name
                                }</p>
                            </div>
                        </div>
                    </div>`;
                container.prepend(card);
            });

            window.lucide.createIcons({ icons: window.lucide.icons });

            if (fields.length >= perPage) {
                $("#seemore_btn").removeClass("hidden");
            } else {
                $("#seemore_btn").addClass("hidden");
            }
        },
        error: function (xhr, status, error) {
            console.error("Gagal load data fields:", error);
        },
        complete: function () {
            $("#field_loader").addClass("hidden").removeClass("flex");
        },
    });
};

document.addEventListener("DOMContentLoaded", () => {
    getDataDetailVenue();
    populateFieldList();

    $("#search_field").on("input", function () {
        clearTimeout(searchTimeout);
        let keyword = $(this).val();
        searchTimeout = setTimeout(() => {
            populateFieldList(keyword, true);
        }, 500);
    });

    $("#seemore_btn").on("click", function () {
        page++;
        let keyword = $("#search_field").val();
        populateFieldList(keyword, false);
    });
});
