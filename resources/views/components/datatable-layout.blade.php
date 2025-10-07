<!-- 
Props for parsing value from page blade
- Set default each variable, can be null
-->
@props([
'placeholderSearch' => 'Search data...',
'idTable' => 'table-reca',
'wrapperClass' => '',
'tableClass' => '',
])

<div class="bg-white rounded-xl shadow-sm w-full p-4" id="datatable-custom-wrapper">
    <!-- Toolbar first wrapper -->
    <div class="w-full flex justify-between mb-8" id="toolbar-first-wrapper">
        <!-- Search box -->
        <x-inputs.text-input id="search-data-{{ $idTable }}" type="text" class="block w-full max-w-xs text-sm"
            placeholder="{{ $placeholderSearch }}" />
    </div>

    <!-- Table wrapper :begin -->
    <div class="relative {{ $wrapperClass }}" id="table-wrapper-{{ $idTable }}" data-simplebar>
        <!-- Loader Spinner -->
        <div id="table-loader-{{ $idTable }}"
            class="absolute inset-0 bg-base-100/80 flex justify-center items-center z-10">
            <span class="loading loading-spinner text-secondary"></span>
            <span class="ml-2 text-sm font-medium text-after-midnight">Loading data...</span>
        </div>

        <!-- No data found -->
        <div id="data-not-found-{{ $idTable }}"
            class="hidden absolute inset-0 flex justify-center items-center z-10 mt-10">
            <span class="ml-2 text-sm font-medium text-after-midnight">No matches record found...</span>
        </div>

        <!-- Data empty -->
        <div id="data-empty-{{ $idTable }}" class="hidden absolute inset-0 flex justify-center items-center z-10 mt-10">
            <span class="ml-2 text-sm font-medium text-after-midnight">
                There's no data here, add one or contact the administrator!
            </span>
        </div>

        <!-- Table -->
        <table class="table table-zebra {{ $tableClass }}" id="{{ $idTable }}">
            <!-- Table head -->
            <thead>
                <tr class="text-xs font-medium text-after-midnight/70">
                    {{ $slot }}
                </tr>
            </thead>
            <!-- Table body -->
            <tbody>
                <!-- Data populated here ... -->
            </tbody>
        </table>
    </div>
    <!-- Table wrapper :end -->

    <!-- Toolbar second :begin -->
    <div class="w-full flex justify-between mt-8" id="toolbar-second-wrapper">
        <!-- Info -->
        <p class="text-xs font-medium text-after-midnight/80" id="info-{{ $idTable }}">Show <span></span> to
            <span></span> of <span></span>
            data
        </p>

        <!-- Pagination -->
        <div class="flex items-center gap-4">
            <!-- Prev button -->
            <button class="cursor-pointer hover:text-hot-shot text-after-midnight/90" id="prev-data-{{ $idTable }}">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
            </button>
            <!-- Current page -->
            <p class="text-xs font-medium text-after-midnight/80" id="page-number-{{ $idTable }}"></p>
            <!-- Next button -->
            <button class="cursor-pointer hover:text-hot-shot text-after-midnight/90" id="next-data-{{ $idTable }}">
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
    <!-- Toolbar second :end -->
</div>