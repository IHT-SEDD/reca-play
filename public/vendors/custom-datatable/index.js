"use strict";

function initCustomDatatable({
    tableId,
    tableDataUrl,
    tableColumns,
    withData = [],
}) {
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
                d.with = withData;
            },
        },

        // ==== Nonactive default UI ==== //
        searching: false,
        paging: true,
        lengthChange: false,
        ordering: false,
        info: false,
        responsive : true,
        pageLength: 10,
        dom: "t",

        columns: tableColumns,
        columnDefs: [
            {
                responsivePriority: 1,
                targets: buttonActionIndex,
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-end whitespace-nowrap',
                width: '1%',
               render: function (data, type, row) {
                    return `
                        <div class="relative inline-block text-right">
                            <button
                                onclick="toggleDropdown(this)"
                                class="px-3 py-2 rounded-md font-medium text-white bg-hot-shot"
                            >
                             <i data-lucide="ellipsis-vertical" class="w-4 h-auto"></i>
                            </button>

                            <!-- Dropdown -->
                            <ul class="hidden absolute right-0 z-50 w-40 p-2 bg-white rounded-lg shadow-md border border-gray-200">
                                <li>
                                    <button onclick="editData(${row.id})" class="block w-full text-left px-3 py-2 rounded-md hover:bg-orange-100 text-gray-700">
                                        Edit
                                    </button>
                                </li>
                                <li>
                                    <button onclick="deleteData(${row.id})" class="block w-full text-left px-3 py-2 rounded-md hover:bg-orange-100 text-gray-700">
                                        Delete
                                    </button>
                                </li>
                            </ul>
                        </div>
                    `;
                }
            }
        ],

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
            .addClass("py-3.5 break-words text-xs border border-transparent");

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

   // ============================
    // Dropdown handler
    // ============================
const  toggleDropdown = (button) => {
    if (!button._dropdownMenu) {
        const menu = button.nextElementSibling;
        if (!menu) return;
        button._dropdownMenu = menu;

        menu.dataset.moved = "true";
        document.body.appendChild(menu);
    }

    const menu = button._dropdownMenu;

    // Tutup dropdown lain
    document.querySelectorAll('.dropdown-menu').forEach(m => {
        if (m !== menu) m.classList.add('hidden');
    });

    // Toggle dropdown
    menu.classList.toggle('hidden');
    if (menu.classList.contains('hidden')) return;

    // Sinkronkan lebar dropdown dengan tombol
    const rect = button.getBoundingClientRect();
    menu.style.width = `100px`; // <-- ini kunci

    // Hitung posisi
    let top = window.scrollY + rect.bottom + 2;
    let left = window.scrollX + rect.left; // sesuaikan kiri tombol

    const menuRect = menu.getBoundingClientRect();
    const spaceBelow = window.innerHeight - rect.bottom;
    const spaceAbove = rect.top;
    if (spaceBelow < menuRect.height && spaceAbove > menuRect.height) {
        top = window.scrollY + rect.top - menuRect.height - 6; // flip
    }

    Object.assign(menu.style, {
        position: "absolute",
        top: `${top}px`,
        left: `${left}px`,
        zIndex: 9999,
    });

    // Klik di luar → tutup
    const closeHandler = (e) => {
        if (!menu.contains(e.target) && e.target !== button) {
            menu.classList.add('hidden');
            document.removeEventListener('click', closeHandler);
        }
    };
    setTimeout(() => document.addEventListener('click', closeHandler), 0);
}


   // ============================
    // Custom session code message handler
    // ============================
    function handleSessionCodeError(message) {
        if (!message) return false;

        const lowerMsg = message.toLowerCase();

        if (lowerMsg.includes("session code not found")) {
            notyf.error(
                "Access code not found! Please go to cashier and ask for the access code."
            );
            return true;
        }

        if (lowerMsg.includes("session code has expired")) {
            notyf.error(
                "This access code has expired. Please ask cashier for a new code."
            );
            return true;
        }

        if (lowerMsg.includes("session code is already in use")) {
            notyf.error(
                "This access code is already being used by another user."
            );
            return true;
        }

        return false;
    }

const editData = (id) => {

        const path = window.location.pathname;
        const segments = path.split('/').filter(Boolean);
        const master = segments[1];

        $.ajax({
            type: "GET",
            url: "/master/" + master + "/" + id + "/edit",
            success: function (response) {

                formEdit(response);

            },
             error: function (xhr) {
                if (xhr.status === 422) {
                    showValidationErrors(
                        $form,
                        xhr.responseJSON.errors
                    );
                    notyf.error("Please check the form for errors.");
                } else {
                    const response = xhr.responseJSON;
                    if (!handleSessionCodeError(response?.message)) {
                        notyf.error(
                            response?.message ||
                                "An error occurred. Please try again."
                        );
                    }
                    console.error(xhr);
                }
            }
        }

    );
}


