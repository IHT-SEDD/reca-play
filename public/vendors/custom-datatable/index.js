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
        responsive: true,
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
                className: "text-end whitespace-nowrap",
                width: "1%",
                render: function (data, type, row) {
                    return `<div x-data="{ open: false }" class="relative inline-block text-left">
                                <!-- Trigger -->
                                <button 
                                    @click="open = !open" 
                                    :class="[
                                        'focus:ring-0 focus:outline-none font-medium rounded-xl text-xs md:text-sm p-3 text-center inline-flex items-center transition-colors',
                                        open 
                                            ? 'bg-hot-shot text-white-owl dark:text-eerie-black' 
                                            : 'bg-white-owl hover:text-hot-shot text-after-midnight dark:text-white-owl dark:hover:text-hot-shot'
                                    ]" 
                                    type="button">
                                    <i data-lucide="ellipsis-vertical" class="w-4 h-4"></i>
                                </button>

                                <!-- Dropdown menu -->
                                <div x-show="open" x-cloak @click.outside="open = false" x-transition
                                    class="absolute right-0 z-10 mt-3 origin-top-right rounded-lg w-full min-w-fit bg-white shadow-sm divide-y divide-eerie-black border border-base-200">
                                    <ul class="flex flex-col justify-center items-start text-[13px] text-eerie-black font-medium p-2">
                                        <li>
                                            <button @click.stop="setTimeout(() => open = false, 200);editData(${row.id});" class="block w-full text-left text-sm font-medium px-3 py-2 rounded-md hover:text-hot-shot text-after-midnight">
                                                Edit
                                            </button>
                                        </li>
                                        <li>
                                            <button @click.stop="setTimeout(() => open = false, 200);editData(${row.id});" class="block w-full text-left text-sm font-medium px-3 py-2 rounded-md hover:text-hot-shot text-after-midnight">
                                                Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div> 
                        `;
                },
            },
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
            $empty.removeClass("hidden");
        } else if (info.recordsDisplay === 0 && info.recordsTotal > 0) {
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

        if (window.Alpine) {
            Alpine.flushAndStopDeferringMutations();
            Alpine.initTree(document.body);
        }
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
const toggleDropdown = (button) => {
    if (!button._dropdownMenu) {
        const menu = button.nextElementSibling;
        if (!menu) return;
        button._dropdownMenu = menu;

        menu.dataset.moved = "true";
        document.body.appendChild(menu);
    }

    const menu = button._dropdownMenu;

    // Tutup dropdown lain
    document.querySelectorAll(".dropdown-menu").forEach((m) => {
        if (m !== menu) m.classList.add("hidden");
    });

    // Toggle dropdown
    menu.classList.toggle("hidden");
    if (menu.classList.contains("hidden")) return;

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
            menu.classList.add("hidden");
            document.removeEventListener("click", closeHandler);
        }
    };
    setTimeout(() => document.addEventListener("click", closeHandler), 0);
};

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
        notyf.error("This access code is already being used by another user.");
        return true;
    }

    return false;
}

const editData = (id) => {
    const path = window.location.pathname;
    const segments = path.split("/").filter(Boolean);
    const master = segments[1];

    $.ajax({
        type: "GET",
        url: "/master/" + master + "/" + id + "/edit",
        success: function (response) {
            formEdit(response);
            if (
                window.modal_master &&
                typeof modal_master.showModal === "function"
            ) {
                modal_master.showModal();
            } else {
                console.warn(
                    "modal_master is not defined or showModal is not a function."
                );
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                showValidationErrors($form, xhr.responseJSON.errors);
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
        },
    });
};
