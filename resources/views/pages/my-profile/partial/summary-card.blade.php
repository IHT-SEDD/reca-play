<div class="h-fit bg-white p-4 rounded-lg w-full shadow-sm flex flex-col justify-center items-center lg:gap-0 gap-2">
<div
    x-data="profileUploader(
        @js(auth()->user()->id),
        @js(strtoupper(substr(auth()->user()->name, 0, 2))),
        @js(auth()->user()->photo_profile ? asset('storage/profile/'.auth()->user()->photo_profile) : null)
    )"
    class="space-y-2"
>

    <div
        class="relative w-40 h-40 rounded-full border-2 border-gray-300 flex items-center justify-center
               bg-gray-50 overflow-hidden"
        @dragover.prevent
        @drop.prevent="dropFile($event)"
    >

        <!-- INITIAL -->
        <div
            x-show="!preview && !savedPhoto"
            class="flex items-center justify-center w-full h-full bg-gray-100 rounded-full"
        >
            <span class="text-3xl font-semibold text-gray-600" x-text="initial"></span>
        </div>

        <!-- IMAGE -->
        <img
            x-show="savedPhoto && !preview"
            :src="savedPhoto"
            class="object-cover w-full h-full"
        >

        <!-- PREVIEW -->
        <img
            x-show="preview"
            :src="preview"
            class="object-cover w-full h-full"
        >

        <!-- ACTION ICONS — SELALU MUNCUL -->
        <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex space-x-3 z-10">

            <!-- REMOVE -->
            <button
                @click.stop="remove()"
                type="button"
                class="w-8 h-8 bg-black/70 text-white rounded-full flex items-center justify-center hover:bg-black/90 transition"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- EDIT -->
            <button
                @click.stop="browse()"
                type="button"
                class="w-8 h-8 bg-black/70 text-white rounded-full flex items-center justify-center hover:bg-black/90 transition"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15.232 5.232l3.536 3.536M9 11l6.586-6.586a2 2 0 112.828 2.828L11.828 13.828a2 2 0 01-1.414.586H6v-4a2 2 0 01.586-1.414z" />
                </svg>
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
  <p class="font-medium text-xs text-hot-shot" id="city_location">CITY</p>
  <p class="font-medium text-xs text-adhesion lg:block hidden">|</p>
  <p class="font-semibold text-xs text-adhesion" id="join_time">Joined Marc 2025</p>
 </div>
</div>
