function initCustomDatatable({ tableId, tableDataUrl, tableColumns }) {
    // Global variables
    const $table = $(`#${tableId}`);
    const $search = $(`#search-data-${tableId}`);
    const $loader = $(`#table-loader-${tableId}`);
    const $empty = $(`#data-empty-${tableId}`);
    const $notFound = $(`#data-not-found-${tableId}`);
    const $info = $(`#info-${tableId}`);
    const $pageNumber = $(`#page-number-${tableId}`);
    const $prevBtn = $(`#prev-data-${tableId}`);
    const $nextBtn = $(`#next-data-${tableId}`);

    const table = $table.DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: tableDataUrl,
            data: function (d) {
                d.search = $search.val();
            },
        },

        // ==== Nonactive default UI ==== //
        searching: false,
        paging: true,
        lengthChange: false,
        ordering: false,
        info: false,

        pageLength: 5,
        dom: "t",

        columns: tableColumns,
        language: {
            processing: "Loading data...",
            zeroRecords: "",
            emptyTable: "",
        },
    });

    // ==== Custom Loading ==== //
    table.on("processing.dt", function (e, settings, processing) {
        $loader.toggleClass("hidden", !processing);
    });

    // ==== Custom Toggle Empty/Not Found Messages ==== //
    table.on("draw", function () {
        const info = table.page.info();

        $empty.addClass("hidden");
        $notFound.addClass("hidden");

        if (info.recordsTotal === 0) {
            // Tidak ada data sama sekali
            $empty.removeClass("hidden");
        } else if (info.recordsDisplay === 0 && info.recordsTotal > 0) {
            // Ada data, tapi tidak sesuai filter/search
            $notFound.removeClass("hidden");
        }

        $info.text(
            `Show ${info.start + 1} to ${info.end} of ${
                info.recordsDisplay
            } data`
        );
        $pageNumber.text(info.page + 1);

        $prevBtn.prop("disabled", info.page === 0);
        $nextBtn.prop("disabled", info.page === info.pages - 1);

        // Tambahkan padding hanya sekali
        $table.find("tbody").addClass("py-4");
        $table
            .find("tbody td")
            .addClass(
                "py-3.5 break-words text-xs border border-transparent"
            );

        window.lucide.createIcons({ icons: window.lucide.icons });
    });

    // ==== Custom Search ==== //
    $search.on("keyup", function () {
        table.search(this.value).draw();
    });

    // ==== Custom Pagination ==== //
    $prevBtn.on("click", function () {
        table.page("previous").draw("page");
    });
    $nextBtn.on("click", function () {
        table.page("next").draw("page");
    });

    return table;
}
