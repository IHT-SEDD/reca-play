let populateData, searchTimeout;

let page = 1;
let perPage = 20;

populateData = (search = "", reset = true) => {
    if (reset) {
        page = 1;
        $("#container_venue_list").empty();
    }

    $("#venue_loader").removeClass("hidden").addClass("flex");

    $.ajax({
        url: "/venue/data",
        method: "GET",
        data: { search: search, page: page, per_page: perPage },
        dataType: "json",
        success: function (res) {
            let venues = res.data ?? res;
            let container = $("#container_venue_list");

            if (venues.length === 0 && page === 1) {
                container.html(
                    `<p class="text-adhesion dark:text-white-owl w-full md:col-span-6 col-span-1 text-center text-lg font-semibold">No venues found.</p>`
                );
                $("#seemore_btn").addClass("hidden");
                return;
            }

            venues.forEach((venue) => {
                let card = `
                <a href="/venue/detail/${
                    venue.hashed_id
                }" target="_blank" rel="noopener noreferrer">
                    <div class="bg-white dark:bg-christmas-silver dark:border-transparent border border-white-edgar shadow-sm rounded-xl p-4 h-full w-full">
                        <div class="flex flex-col justify-start items-center gap-3 w-full text-center">
                            <!-- Venue Logo -->
                            <div class="flex items-center justify-center rounded-xl w-full p-2">
                                ${
                                    venue.logo_path
                                        ? `<img src="${venue.logo_path}" alt="${venue.name}" class="w-24 h-auto object-cover">`
                                        : `<img src="/assets/img/logos/reca-black.png" alt="Default logo venue" class="w-24 h-auto object-cover">`
                                }
                            </div>
                            <!-- Venue Desc -->
                            <div class="flex flex-col justify-center items-center w-full">
                                <p class="text-lg font-semibold text-after-midnight">${
                                    venue.name
                                }</p>
                                <p class="text-xs font-medium text-carbon">${
                                    venue.address
                                }</p>
                            </div>
                        </div>
                    </div>
                </a>`;
                container.prepend(card);
            });

            window.lucide.createIcons({ icons: window.lucide.icons });

            if (venues.length >= perPage) {
                $("#seemore_btn").removeClass("hidden");
            } else {
                $("#seemore_btn").addClass("hidden");
            }
        },
        error: function (xhr, status, error) {
            console.error("Gagal load data venues:", error);
        },
        complete: function () {
            $("#venue_loader").addClass("hidden").removeClass("flex");
        },
    });
};

document.addEventListener("DOMContentLoaded", function () {
    populateData();

    $("#search_venue").on("input", function () {
        clearTimeout(searchTimeout);
        let keyword = $(this).val();
        searchTimeout = setTimeout(() => {
            populateData(keyword, true);
        }, 500);
    });

    $("#seemore_btn").on("click", function () {
        page++;
        let keyword = $("#search_venue").val();
        populateData(keyword, false);
    });
});
