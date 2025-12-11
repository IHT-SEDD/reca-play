<div class="h-fit bg-white p-4 rounded-lg w-full shadow-sm flex flex-col justify-center items-center lg:gap-0 gap-2">
    <div x-data="profileUploader(
        @js(auth()->user()->id),
        @js(strtoupper(substr(auth()->user()->name, 0, 2))),
        @js(auth()->user()->photo_profile ? asset('storage/profile/'.auth()->user()->photo_profile) : null)
    )" class="space-y-2">

        <div class="relative w-40 h-40 rounded-full border border-base-200/90 flex items-center justify-center
               bg-gray-50 overflow-hidden" @dragover.prevent @drop.prevent="dropFile($event)">

            <!-- INITIAL -->
            <div x-show="!preview && !savedPhoto"
                class="flex items-center justify-center w-full h-full bg-white-edgar/30 rounded-full">
                <span class="text-3xl font-semibold text-gray-600" x-text="initial"></span>
            </div>

            <!-- IMAGE -->
            <img x-show="savedPhoto && !preview" :src="savedPhoto" class="object-cover w-full h-full">

            <!-- PREVIEW -->
            <img x-show="preview" :src="preview" class="object-cover w-full h-full">

            <!-- ACTION ICONS — SELALU MUNCUL -->
            <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex space-x-3 z-10">
                <!-- REMOVE -->
                <button @click.stop="remove()" type="button"
                    class="w-8 h-8 bg-vivaldi-red/30 text-vivaldi-red rounded-full flex items-center justify-center hover:bg-vivaldi-red/40 transition">
                    <i data-lucide="x" class="w-4 h-auto "></i>
                </button>

                <!-- EDIT -->
                <button @click.stop="browse()" type="button"
                    class="w-8 h-8 bg-hot-shot/30 text-hot-shot rounded-full flex items-center justify-center hover:bg-hot-shot/40 transition-all duration-200 ease-in-out">
                    <i data-lucide="image-plus" class="w-4 h-auto "></i>
                </button>
            </div>

            <!-- HIDDEN INPUT -->
            <input type="file" class="hidden" x-ref="fileInput" name="photo" @change="fileChosen" accept="image/*">

        </div>
    </div>

    <div class="flex flex-col justify-center items-center mt-2">
        <p class="font-medium text-xs text-adhesion" id="username"></p>
        <p class="font-semibold text-lg text-eerie-black" id="name"></p>
    </div>

    <div class="flex flex-col lg:flex-row justify-center items-center lg:gap-4">
        <p class="font-semibold text-xs text-hot-shot" id="join_time"></p>
    </div>
</div>